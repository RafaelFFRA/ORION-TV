<?php

require_once(__DIR__ . "/includes/proteger_distribuidor.php");
require_once(__DIR__ . "/php/conexao.php");
require_once(__DIR__ . "/includes/alerta.php");



$mensagemAlerta = null;

if (isset($_GET["sucesso"]) && $_GET["sucesso"] === "1") {

    $mensagemAlerta = "Filme alterado com sucesso.";

}

if (isset($_GET["erro"])) {

    $mensagens = [

        "id" => "Filme não encontrado.",

        "distribuidor" => "Não foi possível identificar o distribuidor.",

        "nao_autorizado" => "Você não tem permissão para alterar este filme.",

        "consulta" => "Não foi possível carregar os dados do filme.",

        "campos" => "Preencha todos os campos obrigatórios.",

        "duracao" => "A duração do filme não pode ter mais de 20 caracteres.",

        "generos" => "Selecione de 1 a 3 gêneros.",

        "classificacao" => "Classificação etária inválida.",

        "datas" => "A data final não pode ser anterior à data inicial.",

        "thumb" => "Ocorreu um erro ao enviar a nova capa vertical.",

        "banner" => "Ocorreu um erro ao enviar o novo banner horizontal.",

        "video" => "Ocorreu um erro ao enviar o novo vídeo.",

        "pasta_thumb" => "Não foi possível acessar a pasta das capas verticais.",

        "pasta_banner" => "Não foi possível acessar a pasta dos banners horizontais.",

        "pasta_video" => "Não foi possível acessar a pasta dos vídeos.",

        "formato_thumb" => "A capa vertical precisa ser JPG, PNG ou WEBP.",

        "formato_banner" => "O banner horizontal precisa ser JPG, PNG ou WEBP.",

        "formato_video" => "Formato de vídeo não permitido.",

        "salvar_thumb" => "Não foi possível salvar a nova capa vertical.",

        "salvar_banner" => "Não foi possível salvar o novo banner horizontal.",

        "salvar_video" => "Não foi possível salvar o novo vídeo.",

        "preparar_alteracao" => "Não foi possível preparar a alteração do filme.",

        "alteracao" => "Não foi possível alterar o filme."

    ];

    $codigoErro = $_GET["erro"];

    if (isset($mensagens[$codigoErro])) {

        $mensagemAlerta = $mensagens[$codigoErro];

    } else {

        $mensagemAlerta = "Ocorreu um erro ao alterar o filme.";

    }
}



if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: gerenciar_conteudos_distribuidor.php?erro=id");
    exit;
}

$id_conteudo = (int) $_GET["id"];



$tipo_usuario = $_SESSION["tipo_usuario"];

$id_distribuidor = null;



if ($tipo_usuario === "Distribuidor") {

    $id_usuario = (int) $_SESSION["id_usuario"];

    $sql_distribuidor = "
        SELECT id_distribuidor
        FROM distribuidor
        WHERE FK_distribuidor_id_usuario = ?
    ";

    $stmt_distribuidor = mysqli_prepare(
        $conn,
        $sql_distribuidor
    );

    if (!$stmt_distribuidor) {

        header(
            "Location: gerenciar_conteudos_distribuidor.php?erro=distribuidor"
        );

        exit;
    }

    mysqli_stmt_bind_param(
        $stmt_distribuidor,
        "i",
        $id_usuario
    );

    if (!mysqli_stmt_execute($stmt_distribuidor)) {

        mysqli_stmt_close($stmt_distribuidor);

        header(
            "Location: gerenciar_conteudos_distribuidor.php?erro=distribuidor"
        );

        exit;
    }

    mysqli_stmt_bind_result(
        $stmt_distribuidor,
        $id_distribuidor
    );

    $encontrou_distribuidor = mysqli_stmt_fetch(
        $stmt_distribuidor
    );

    mysqli_stmt_close($stmt_distribuidor);


    if (!$encontrou_distribuidor) {

        header(
            "Location: gerenciar_conteudos_distribuidor.php?erro=distribuidor"
        );

        exit;
    }

    $id_distribuidor = (int) $id_distribuidor;
}



if ($tipo_usuario === "Distribuidor") {

    $sql = "
        SELECT
            id_conteudo,
            FK_conteudo_id_distribuidor,
            titulo_conteudo,
            sinopse_conteudo,
            elenco_conteudo,
            diretores_conteudo,
            genero_conteudo,
            classe_etaria_conteudo,
            duracao_conteudo,
            janela_exib_inicio_conteudo,
            janela_exib_fim_conteudo,
            thumb_conteudo_vertical,
            banner_conteudo_horizontal,
            video_conteudo
        FROM conteudo
        WHERE id_conteudo = ?
        AND FK_conteudo_id_distribuidor = ?
    ";

    $stmt = mysqli_prepare(
        $conn,
        $sql
    );

    if (!$stmt) {

        header(
            "Location: gerenciar_conteudos_distribuidor.php?erro=consulta"
        );

        exit;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $id_conteudo,
        $id_distribuidor
    );

} else {

    $sql = "
        SELECT
            id_conteudo,
            FK_conteudo_id_distribuidor,
            titulo_conteudo,
            sinopse_conteudo,
            elenco_conteudo,
            diretores_conteudo,
            genero_conteudo,
            classe_etaria_conteudo,
            duracao_conteudo,
            janela_exib_inicio_conteudo,
            janela_exib_fim_conteudo,
            thumb_conteudo_vertical,
            banner_conteudo_horizontal,
            video_conteudo
        FROM conteudo
        WHERE id_conteudo = ?
    ";

    $stmt = mysqli_prepare(
        $conn,
        $sql
    );

    if (!$stmt) {

        header(
            "Location: gerenciar_conteudos_distribuidor.php?erro=consulta"
        );

        exit;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id_conteudo
    );
}


if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    header(
        "Location: gerenciar_conteudos_distribuidor.php?erro=consulta"
    );

    exit;
}


$resultado = mysqli_stmt_get_result($stmt);

$filme = mysqli_fetch_assoc($resultado);

mysqli_stmt_close($stmt);


if (!$filme) {

    header(
        "Location: gerenciar_conteudos_distribuidor.php?erro=nao_autorizado"
    );

    exit;
}



$id_distribuidor = (int) $filme["FK_conteudo_id_distribuidor"];



$titulo = $filme["titulo_conteudo"];
$sinopse = $filme["sinopse_conteudo"];
$elenco = $filme["elenco_conteudo"];
$diretores = $filme["diretores_conteudo"];

$generosSelecionados = array_filter(
    explode(",", $filme["genero_conteudo"])
);

$classeEtaria = (int) $filme["classe_etaria_conteudo"];

$duracao = $filme["duracao_conteudo"];

$janelaInicial = $filme["janela_exib_inicio_conteudo"];
$janelaFinal = $filme["janela_exib_fim_conteudo"];



if ($_SERVER["REQUEST_METHOD"] === "POST") {



    $titulo = trim($_POST["titulo"] ?? "");
    $sinopse = trim($_POST["sinopse"] ?? "");
    $elenco = trim($_POST["elenco"] ?? "");
    $diretores = trim($_POST["diretores"] ?? "");

    $classeEtariaPost = $_POST["classe_etaria"] ?? "";

    $duracao = trim($_POST["duracao"] ?? "");

    $janelaInicial = $_POST["janela_inicial"] ?? "";
    $janelaFinal = $_POST["janela_final"] ?? "";

    $generos = $_POST["generos"] ?? [];



    if (
        $titulo === "" ||
        $sinopse === "" ||
        $elenco === "" ||
        $diretores === "" ||
        empty($generos) ||
        $classeEtariaPost === "" ||
        $duracao === "" ||
        $janelaInicial === "" ||
        $janelaFinal === ""
    ) {

        header(
            "Location: alterar_filme.php?id="
            . $id_conteudo
            . "&erro=campos"
        );

        exit;
    }



    $generosPermitidos = [
        "acao",
        "aventura",
        "comedia",
        "drama",
        "ficcao",
        "terror",
        "romance",
        "suspense"
    ];


    if (!is_array($generos)) {

        header(
            "Location: alterar_filme.php?id="
            . $id_conteudo
            . "&erro=generos"
        );

        exit;
    }


    $generos = array_unique($generos);


    if (count($generos) < 1 || count($generos) > 3) {

        header(
            "Location: alterar_filme.php?id="
            . $id_conteudo
            . "&erro=generos"
        );

        exit;
    }


    foreach ($generos as $genero) {

        if (!in_array($genero, $generosPermitidos, true)) {

            header(
                "Location: alterar_filme.php?id="
                . $id_conteudo
                . "&erro=generos"
            );

            exit;
        }
    }


    $genero = implode(",", $generos);



    if (strlen($duracao) > 20) {

        header(
            "Location: alterar_filme.php?id="
            . $id_conteudo
            . "&erro=duracao"
        );

        exit;
    }



    if ($classeEtariaPost === "livre") {

        $classeEtaria = 0;

    } else {

        $classeEtaria = (int) $classeEtariaPost;

    }


    $classesPermitidas = [
        0,
        10,
        12,
        14,
        16,
        18
    ];


    if (!in_array($classeEtaria, $classesPermitidas, true)) {

        header(
            "Location: alterar_filme.php?id="
            . $id_conteudo
            . "&erro=classificacao"
        );

        exit;
    }



    if ($janelaFinal < $janelaInicial) {

        header(
            "Location: alterar_filme.php?id="
            . $id_conteudo
            . "&erro=datas"
        );

        exit;
    }



    $caminhoThumbSql = $filme["thumb_conteudo_vertical"];
    $caminhoBannerSql = $filme["banner_conteudo_horizontal"];
    $caminhoVideoSql = $filme["video_conteudo"];


    $novoCaminhoThumbFisico = null;
    $novoCaminhoBannerFisico = null;
    $novoCaminhoVideoFisico = null;



    $pastaThumb = __DIR__ . "/thumbs/verticais/";
    $pastaBanner = __DIR__ . "/thumbs/horizontais/";
    $pastaVideo = __DIR__ . "/vids/";



    $tiposImagemPermitidos = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];


    $tiposVideoPermitidos = [
        "video/mp4" => "mp4",
        "video/webm" => "webm",
        "video/ogg" => "ogv",
        "video/quicktime" => "mov"
    ];



    if (
        isset($_FILES["thumb"]) &&
        $_FILES["thumb"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {

        $thumb = $_FILES["thumb"];


        if ($thumb["error"] !== UPLOAD_ERR_OK) {

            header(
                "Location: alterar_filme.php?id="
                . $id_conteudo
                . "&erro=thumb"
            );

            exit;
        }


        if (!is_dir($pastaThumb)) {

            if (!mkdir($pastaThumb, 0755, true)) {

                header(
                    "Location: alterar_filme.php?id="
                    . $id_conteudo
                    . "&erro=pasta_thumb"
                );

                exit;
            }
        }


        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        $mimeThumb = finfo_file(
            $finfo,
            $thumb["tmp_name"]
        );

        finfo_close($finfo);


        if (!isset($tiposImagemPermitidos[$mimeThumb])) {

            header(
                "Location: alterar_filme.php?id="
                . $id_conteudo
                . "&erro=formato_thumb"
            );

            exit;
        }


        $nomeBase = uniqid("filme_", true);

        $extThumb = $tiposImagemPermitidos[$mimeThumb];

        $nomeThumb = $nomeBase . "_vertical." . $extThumb;

        $novoCaminhoThumbFisico =
            $pastaThumb . $nomeThumb;

        $caminhoThumbSql =
            "thumbs/verticais/" . $nomeThumb;


        if (!move_uploaded_file(
            $thumb["tmp_name"],
            $novoCaminhoThumbFisico
        )) {

            header(
                "Location: alterar_filme.php?id="
                . $id_conteudo
                . "&erro=salvar_thumb"
            );

            exit;
        }
    }



    if (
        isset($_FILES["banner"]) &&
        $_FILES["banner"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {

        $banner = $_FILES["banner"];


        if ($banner["error"] !== UPLOAD_ERR_OK) {

            if ($novoCaminhoThumbFisico !== null) {
                unlink($novoCaminhoThumbFisico);
            }

            header(
                "Location: alterar_filme.php?id="
                . $id_conteudo
                . "&erro=banner"
            );

            exit;
        }


        if (!is_dir($pastaBanner)) {

            if (!mkdir($pastaBanner, 0755, true)) {

                if ($novoCaminhoThumbFisico !== null) {
                    unlink($novoCaminhoThumbFisico);
                }

                header(
                    "Location: alterar_filme.php?id="
                    . $id_conteudo
                    . "&erro=pasta_banner"
                );

                exit;
            }
        }


        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        $mimeBanner = finfo_file(
            $finfo,
            $banner["tmp_name"]
        );

        finfo_close($finfo);


        if (!isset($tiposImagemPermitidos[$mimeBanner])) {

            if ($novoCaminhoThumbFisico !== null) {
                unlink($novoCaminhoThumbFisico);
            }

            header(
                "Location: alterar_filme.php?id="
                . $id_conteudo
                . "&erro=formato_banner"
            );

            exit;
        }


        $nomeBase = uniqid("filme_", true);

        $extBanner = $tiposImagemPermitidos[$mimeBanner];

        $nomeBanner = $nomeBase . "_horizontal." . $extBanner;

        $novoCaminhoBannerFisico =
            $pastaBanner . $nomeBanner;

        $caminhoBannerSql =
            "thumbs/horizontais/" . $nomeBanner;


        if (!move_uploaded_file(
            $banner["tmp_name"],
            $novoCaminhoBannerFisico
        )) {

            if ($novoCaminhoThumbFisico !== null) {
                unlink($novoCaminhoThumbFisico);
            }

            header(
                "Location: alterar_filme.php?id="
                . $id_conteudo
                . "&erro=salvar_banner"
            );

            exit;
        }
    }



    if (
        isset($_FILES["video"]) &&
        $_FILES["video"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {

        $video = $_FILES["video"];


        if ($video["error"] !== UPLOAD_ERR_OK) {

            if ($novoCaminhoThumbFisico !== null) {
                unlink($novoCaminhoThumbFisico);
            }

            if ($novoCaminhoBannerFisico !== null) {
                unlink($novoCaminhoBannerFisico);
            }

            header(
                "Location: alterar_filme.php?id="
                . $id_conteudo
                . "&erro=video"
            );

            exit;
        }


        if (!is_dir($pastaVideo)) {

            if (!mkdir($pastaVideo, 0755, true)) {

                if ($novoCaminhoThumbFisico !== null) {
                    unlink($novoCaminhoThumbFisico);
                }

                if ($novoCaminhoBannerFisico !== null) {
                    unlink($novoCaminhoBannerFisico);
                }

                header(
                    "Location: alterar_filme.php?id="
                    . $id_conteudo
                    . "&erro=pasta_video"
                );

                exit;
            }
        }


        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        $mimeVideo = finfo_file(
            $finfo,
            $video["tmp_name"]
        );

        finfo_close($finfo);


        if (!isset($tiposVideoPermitidos[$mimeVideo])) {

            if ($novoCaminhoThumbFisico !== null) {
                unlink($novoCaminhoThumbFisico);
            }

            if ($novoCaminhoBannerFisico !== null) {
                unlink($novoCaminhoBannerFisico);
            }

            header(
                "Location: alterar_filme.php?id="
                . $id_conteudo
                . "&erro=formato_video"
            );

            exit;
        }


        $nomeBase = uniqid("filme_", true);

        $extVideo = $tiposVideoPermitidos[$mimeVideo];

        $nomeVideo = $nomeBase . "_video." . $extVideo;

        $novoCaminhoVideoFisico =
            $pastaVideo . $nomeVideo;

        $caminhoVideoSql =
            "vids/" . $nomeVideo;


        if (!move_uploaded_file(
            $video["tmp_name"],
            $novoCaminhoVideoFisico
        )) {

            if ($novoCaminhoThumbFisico !== null) {
                unlink($novoCaminhoThumbFisico);
            }

            if ($novoCaminhoBannerFisico !== null) {
                unlink($novoCaminhoBannerFisico);
            }

            header(
                "Location: alterar_filme.php?id="
                . $id_conteudo
                . "&erro=salvar_video"
            );

            exit;
        }
    }



    $sql = "
        UPDATE conteudo
        SET
            titulo_conteudo = ?,
            sinopse_conteudo = ?,
            elenco_conteudo = ?,
            diretores_conteudo = ?,
            genero_conteudo = ?,
            classe_etaria_conteudo = ?,
            duracao_conteudo = ?,
            janela_exib_inicio_conteudo = ?,
            janela_exib_fim_conteudo = ?,
            thumb_conteudo_vertical = ?,
            banner_conteudo_horizontal = ?,
            video_conteudo = ?
        WHERE id_conteudo = ?
        AND FK_conteudo_id_distribuidor = ?
    ";


    $stmt = mysqli_prepare(
        $conn,
        $sql
    );


    if (!$stmt) {

        if ($novoCaminhoThumbFisico !== null) {
            unlink($novoCaminhoThumbFisico);
        }

        if ($novoCaminhoBannerFisico !== null) {
            unlink($novoCaminhoBannerFisico);
        }

        if ($novoCaminhoVideoFisico !== null) {
            unlink($novoCaminhoVideoFisico);
        }

        header(
            "Location: alterar_filme.php?id="
            . $id_conteudo
            . "&erro=preparar_alteracao"
        );

        exit;
    }


    mysqli_stmt_bind_param(
        $stmt,
        "sssssissssssii",
        $titulo,
        $sinopse,
        $elenco,
        $diretores,
        $genero,
        $classeEtaria,
        $duracao,
        $janelaInicial,
        $janelaFinal,
        $caminhoThumbSql,
        $caminhoBannerSql,
        $caminhoVideoSql,
        $id_conteudo,
        $id_distribuidor
    );


    if (!mysqli_stmt_execute($stmt)) {

        if ($novoCaminhoThumbFisico !== null) {
            unlink($novoCaminhoThumbFisico);
        }

        if ($novoCaminhoBannerFisico !== null) {
            unlink($novoCaminhoBannerFisico);
        }

        if ($novoCaminhoVideoFisico !== null) {
            unlink($novoCaminhoVideoFisico);
        }

        mysqli_stmt_close($stmt);

        header(
            "Location: alterar_filme.php?id="
            . $id_conteudo
            . "&erro=alteracao"
        );

        exit;
    }


    mysqli_stmt_close($stmt);



    if (
        $novoCaminhoThumbFisico !== null &&
        !empty($filme["thumb_conteudo_vertical"])
    ) {

        $arquivoAntigo =
            __DIR__ . "/" . $filme["thumb_conteudo_vertical"];

        if (is_file($arquivoAntigo)) {
            unlink($arquivoAntigo);
        }
    }


    if (
        $novoCaminhoBannerFisico !== null &&
        !empty($filme["banner_conteudo_horizontal"])
    ) {

        $arquivoAntigo =
            __DIR__ . "/" . $filme["banner_conteudo_horizontal"];

        if (is_file($arquivoAntigo)) {
            unlink($arquivoAntigo);
        }
    }


    if (
        $novoCaminhoVideoFisico !== null &&
        !empty($filme["video_conteudo"])
    ) {

        $arquivoAntigo =
            __DIR__ . "/" . $filme["video_conteudo"];

        if (is_file($arquivoAntigo)) {
            unlink($arquivoAntigo);
        }
    }


    header(
        "Location: alterar_filme.php?id="
        . $id_conteudo
        . "&sucesso=1"
    );

    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Orion TV - Alterar Filme</title>

   

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >



    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

 

    <link
        rel="stylesheet"
        href="css/enviar_filme.css"
    >


    <style>

    

        .generos {

            display: grid;

            grid-template-columns: repeat(2, 1fr);

            gap: 10px;

            margin-top: 10px;

        }


        .genero-option {

            display: flex;

            align-items: center;

            gap: 10px;

            width: 100%;

            min-height: 45px;

            padding: 10px 12px;

            border: 1px solid #ccc;

            border-radius: 8px;

            background: transparent;

            cursor: pointer;

            transition:
                border-color 0.2s ease,
                background-color 0.2s ease;

            margin: 0;

        }


        .genero-option:hover {

            border-color: #006dcc;

            background-color: rgba(0, 109, 204, 0.04);

        }


        .genero-option input {

            appearance: none;

            -webkit-appearance: none;

            width: 18px;

            height: 18px;

            min-width: 18px;

            margin: 0;

            border: 2px solid #aaa;

            border-radius: 4px;

            background: transparent;

            cursor: pointer;

            position: relative;

            transition:
                background-color 0.2s ease,
                border-color 0.2s ease;

        }


        .genero-option input:checked {

            background-color: #006dcc;

            border-color: #006dcc;

        }


        .genero-option input:checked::after {

            content: "";

            position: absolute;

            left: 4px;

            top: 0px;

            width: 5px;

            height: 10px;

            border: solid #fff;

            border-width: 0 2px 2px 0;

            transform: rotate(45deg);

        }


        .genero-option span {

            font-size: 16px;

            color: inherit;

            cursor: pointer;

            line-height: 1.2;

        }


        .genero-option:has(input:checked) {

            border-color: #006dcc;

            background-color: rgba(0, 109, 204, 0.04);

        }


        .genero-option:has(input:disabled) {

            opacity: 0.5;

            cursor: not-allowed;

        }


        .genero-option:has(input:disabled) span {

            cursor: not-allowed;

        }


        .genero-info {

            display: block;

            margin-top: 8px;

            color: #777;

            font-size: 13px;

        }


        

        .arquivo-atual {

            display: block;

            margin-top: 8px;

            color: #777;

            font-size: 13px;

        }


      

        @media (max-width: 500px) {

            .generos {

                grid-template-columns: 1fr;

            }

        }

    </style>

</head>


<body>


 

    <header class="topo">

        <div class="logo">

            ORION TV

        </div>


        <a
            href="<?= ($_SESSION["tipo_usuario"] ?? "") === "Admin"
                ? "gerenciar_conteudos_admin.php"
                : "gerenciar_conteudos_distribuidor.php"
            ?>"
            class="btn-voltar"
        >
            Voltar
        </a>

    </header>





    <main class="container formulario-container">

        <div class="formulario-card">


            <h1>
                Alterar filme
            </h1>


            <p class="descricao">
                Altere as informações do filme e, se necessário, substitua os arquivos.
            </p>




            <form
                action="alterar_filme.php?id=<?= (int) $id_conteudo ?>"
                method="POST"
                enctype="multipart/form-data"
                id="formFilme"
            >


              

                <div class="campo">

                    <label for="titulo">
                        Título
                    </label>

                    <input
                        type="text"
                        id="titulo"
                        name="titulo"
                        class="form-control"
                        placeholder="Digite o título do filme"
                        value="<?= htmlspecialchars($titulo) ?>"
                        required
                    >

                </div>



         

                <div class="campo">

                    <label for="sinopse">
                        Sinopse
                    </label>

                    <textarea
                        id="sinopse"
                        name="sinopse"
                        class="form-control"
                        placeholder="Digite a sinopse do filme"
                        rows="5"
                        required
                    ><?= htmlspecialchars($sinopse) ?></textarea>

                </div>



             

                <div class="campo">

                    <label for="elenco">
                        Elenco
                    </label>

                    <input
                        type="text"
                        id="elenco"
                        name="elenco"
                        class="form-control"
                        placeholder="Ex: Ator 1, Ator 2, Ator 3"
                        value="<?= htmlspecialchars($elenco) ?>"
                        required
                    >

                </div>



           

                <div class="campo">

                    <label for="diretores">
                        Diretores
                    </label>

                    <input
                        type="text"
                        id="diretores"
                        name="diretores"
                        class="form-control"
                        placeholder="Digite o(s) diretor(es)"
                        value="<?= htmlspecialchars($diretores) ?>"
                        required
                    >

                </div>



            

                <div class="campo">

                    <label>
                        Gênero
                    </label>


                    <div class="generos">


                        <label class="genero-option">

                            <input
                                type="checkbox"
                                name="generos[]"
                                value="acao"
                                <?= in_array("acao", $generosSelecionados, true) ? "checked" : "" ?>
                            >

                            <span>
                                Ação
                            </span>

                        </label>



                        <label class="genero-option">

                            <input
                                type="checkbox"
                                name="generos[]"
                                value="aventura"
                                <?= in_array("aventura", $generosSelecionados, true) ? "checked" : "" ?>
                            >

                            <span>
                                Aventura
                            </span>

                        </label>



                        <label class="genero-option">

                            <input
                                type="checkbox"
                                name="generos[]"
                                value="comedia"
                                <?= in_array("comedia", $generosSelecionados, true) ? "checked" : "" ?>
                            >

                            <span>
                                Comédia
                            </span>

                        </label>



                        <label class="genero-option">

                            <input
                                type="checkbox"
                                name="generos[]"
                                value="drama"
                                <?= in_array("drama", $generosSelecionados, true) ? "checked" : "" ?>
                            >

                            <span>
                                Drama
                            </span>

                        </label>



                        <label class="genero-option">

                            <input
                                type="checkbox"
                                name="generos[]"
                                value="ficcao"
                                <?= in_array("ficcao", $generosSelecionados, true) ? "checked" : "" ?>
                            >

                            <span>
                                Ficção científica
                            </span>

                        </label>



                        <label class="genero-option">

                            <input
                                type="checkbox"
                                name="generos[]"
                                value="terror"
                                <?= in_array("terror", $generosSelecionados, true) ? "checked" : "" ?>
                            >

                            <span>
                                Terror
                            </span>

                        </label>



                        <label class="genero-option">

                            <input
                                type="checkbox"
                                name="generos[]"
                                value="romance"
                                <?= in_array("romance", $generosSelecionados, true) ? "checked" : "" ?>
                            >

                            <span>
                                Romance
                            </span>

                        </label>



                        <label class="genero-option">

                            <input
                                type="checkbox"
                                name="generos[]"
                                value="suspense"
                                <?= in_array("suspense", $generosSelecionados, true) ? "checked" : "" ?>
                            >

                            <span>
                                Suspense
                            </span>

                        </label>


                    </div>


                    <small class="genero-info">
                        Selecione de 1 a 3 gêneros.
                    </small>

                </div>



            

                <div class="campo">

                    <label for="classe_etaria">
                        Classe etária
                    </label>


                    <select
                        id="classe_etaria"
                        name="classe_etaria"
                        class="form-select"
                        required
                    >


                        <option
                            value=""
                            disabled
                            <?= $classeEtaria === null ? "selected" : "" ?>
                        >
                            Selecione a classificação
                        </option>


                        <option
                            value="livre"
                            <?= $classeEtaria === 0 ? "selected" : "" ?>
                        >
                            Livre
                        </option>


                        <option
                            value="10"
                            <?= $classeEtaria === 10 ? "selected" : "" ?>
                        >
                            10 anos
                        </option>


                        <option
                            value="12"
                            <?= $classeEtaria === 12 ? "selected" : "" ?>
                        >
                            12 anos
                        </option>


                        <option
                            value="14"
                            <?= $classeEtaria === 14 ? "selected" : "" ?>
                        >
                            14 anos
                        </option>


                        <option
                            value="16"
                            <?= $classeEtaria === 16 ? "selected" : "" ?>
                        >
                            16 anos
                        </option>


                        <?php if ($classeEtaria === 18): ?>

                            <option value="18" selected>
                                18 anos
                            </option>

                        <?php endif; ?>


                    </select>

                </div>



              

                <div class="campo">

                    <label for="duracao">
                        Duração
                    </label>


                    <input
                        type="text"
                        id="duracao"
                        name="duracao"
                        class="form-control"
                        placeholder="Ex: 1h48min"
                        maxlength="20"
                        value="<?= htmlspecialchars($duracao) ?>"
                        required
                    >


                    <small>
                        Informe a duração do filme. Exemplo: 1h48min.
                    </small>

                </div>



             

                <div class="campo">

                    <label for="janela_inicial">
                        Janela de exibição inicial
                    </label>


                    <input
                        type="date"
                        id="janela_inicial"
                        name="janela_inicial"
                        class="form-control"
                        value="<?= htmlspecialchars($janelaInicial) ?>"
                        required
                    >

                </div>



            

                <div class="campo">

                    <label for="janela_final">
                        Janela de exibição final
                    </label>


                    <input
                        type="date"
                        id="janela_final"
                        name="janela_final"
                        class="form-control"
                        value="<?= htmlspecialchars($janelaFinal) ?>"
                        required
                    >

                </div>



           

                <div class="campo">

                    <label for="thumb">
                        Capa do filme (vertical)
                    </label>


                    <div class="upload-box">

                        <i class="bi bi-image"></i>


                        <span>
                            Selecione uma nova capa vertical
                        </span>


                        <input
                            type="file"
                            id="thumb"
                            name="thumb"
                            accept="image/*"
                        >

                    </div>


                    <small>
                        Se não quiser alterar a capa atual, não selecione um arquivo.
                    </small>


                    <span class="arquivo-atual">

                        <i class="bi bi-check2"></i>

                        Capa atual mantida caso nenhuma nova seja enviada.

                    </span>

                </div>



               

                <div class="campo">

                    <label for="banner">
                        Banner do filme (horizontal)
                    </label>


                    <div class="upload-box">

                        <i class="bi bi-image"></i>


                        <span>
                            Selecione um novo banner horizontal
                        </span>


                        <input
                            type="file"
                            id="banner"
                            name="banner"
                            accept="image/*"
                        >

                    </div>


                    <small>
                        Se não quiser alterar o banner atual, não selecione um arquivo.
                    </small>


                    <span class="arquivo-atual">

                        <i class="bi bi-check2"></i>

                        Banner atual mantido caso nenhum novo seja enviado.

                    </span>

                </div>



         

                <div class="campo">

                    <label for="video">
                        Vídeo do filme
                    </label>


                    <div class="upload-box">

                        <i class="bi bi-camera-reels"></i>


                        <span>
                            Selecione um novo vídeo
                        </span>


                        <input
                            type="file"
                            id="video"
                            name="video"
                            accept="video/*"
                        >

                    </div>


                    <small>
                        Se não quiser alterar o vídeo atual, não selecione um arquivo.
                    </small>


                    <span class="arquivo-atual">

                        <i class="bi bi-check2"></i>

                        Vídeo atual mantido caso nenhum novo seja enviado.

                    </span>

                </div>



      

                <button
                    type="submit"
                    class="btn-enviar"
                >

                    <i class="bi bi-pencil-square"></i>

                    Salvar alterações

                </button>


            </form>

        </div>

    </main>



 

    <?php

    if ($mensagemAlerta !== null) {

        mostrarAlerta($mensagemAlerta);

    }

    ?>





    <script>

        const checkboxesGenero = document.querySelectorAll(
            'input[name="generos[]"]'
        );

        const formFilme = document.getElementById("formFilme");


        function atualizarLimiteGeneros() {

            const selecionados = document.querySelectorAll(
                'input[name="generos[]"]:checked'
            );


            if (selecionados.length >= 3) {

                checkboxesGenero.forEach(function (outroCheckbox) {

                    if (!outroCheckbox.checked) {

                        outroCheckbox.disabled = true;

                    }

                });

            } else {

                checkboxesGenero.forEach(function (outroCheckbox) {

                    outroCheckbox.disabled = false;

                });

            }

        }


        checkboxesGenero.forEach(function (checkbox) {

            checkbox.addEventListener("change", function () {

                atualizarLimiteGeneros();

            });

        });


        atualizarLimiteGeneros();


       

        formFilme.addEventListener("submit", function (evento) {

            const selecionados = document.querySelectorAll(
                'input[name="generos[]"]:checked'
            );


            if (selecionados.length < 1) {

                evento.preventDefault();

                const primeiraCheckbox = checkboxesGenero[0];

                primeiraCheckbox.setCustomValidity(
                    "Selecione pelo menos um gênero."
                );

                primeiraCheckbox.reportValidity();


                setTimeout(function () {

                    primeiraCheckbox.setCustomValidity("");

                }, 100);

            }

        });

    </script>


</body>

</html>