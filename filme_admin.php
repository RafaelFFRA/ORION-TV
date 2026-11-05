<?php

require_once(__DIR__ . "/includes/proteger_admin.php");
require_once(__DIR__ . "/php/conexao.php");
require_once(__DIR__ . "/includes/alerta.php");




$mensagemAlerta = null;



if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    $mensagemAlerta = "Filme não encontrado.";

} else {

    $idFilme = (int) $_GET["id"];



    if (
        isset($_POST["acao"])
        && $_POST["acao"] === "alternar_status"
    ) {

        $sqlBloqueio = "
            SELECT
                bloqueio_admin
            FROM conteudo
            WHERE id_conteudo = ?
        ";

        $stmtBloqueio = mysqli_prepare(
            $conn,
            $sqlBloqueio
        );

        if (!$stmtBloqueio) {

            $mensagemAlerta =
                "Não foi possível alterar o bloqueio do filme.";

        } else {

            mysqli_stmt_bind_param(
                $stmtBloqueio,
                "i",
                $idFilme
            );

            mysqli_stmt_execute(
                $stmtBloqueio
            );

            $resultadoBloqueio =
                mysqli_stmt_get_result(
                    $stmtBloqueio
                );

            if (
                !$resultadoBloqueio
                || mysqli_num_rows($resultadoBloqueio) === 0
            ) {

                $mensagemAlerta =
                    "Filme não encontrado.";

            } else {

                $filmeBloqueio =
                    mysqli_fetch_assoc(
                        $resultadoBloqueio
                    );

                $bloqueioAtual =
                    $filmeBloqueio["bloqueio_admin"];



                if ($bloqueioAtual === "Sim") {

                    $novoBloqueio = "Nao";

                } else {

                    $novoBloqueio = "Sim";

                }


                mysqli_stmt_close(
                    $stmtBloqueio
                );



                $sqlAlterarBloqueio = "
                    UPDATE conteudo
                    SET
                        bloqueio_admin = ?
                    WHERE id_conteudo = ?
                ";

                $stmtAlterarBloqueio = mysqli_prepare(
                    $conn,
                    $sqlAlterarBloqueio
                );

                if (!$stmtAlterarBloqueio) {

                    $mensagemAlerta =
                        "Não foi possível alterar o bloqueio do filme.";

                } else {

                    mysqli_stmt_bind_param(
                        $stmtAlterarBloqueio,
                        "si",
                        $novoBloqueio,
                        $idFilme
                    );

                    if (
                        mysqli_stmt_execute(
                            $stmtAlterarBloqueio
                        )
                    ) {

                        mysqli_stmt_close(
                            $stmtAlterarBloqueio
                        );

                        header(
                            "Location: filme_admin.php?id="
                            . $idFilme
                        );

                        exit;

                    } else {

                        $mensagemAlerta =
                            "Não foi possível alterar o bloqueio do filme.";

                        mysqli_stmt_close(
                            $stmtAlterarBloqueio
                        );
                    }
                }
            }

            if (
                isset($stmtBloqueio)
                && $stmtBloqueio
            ) {

                @mysqli_stmt_close(
                    $stmtBloqueio
                );
            }
        }
    }



    if (
        $mensagemAlerta === null
        && isset($_POST["acao"])
        && $_POST["acao"] === "inativar_comentario"
        && isset($_POST["id_avaliacao"])
        && is_numeric($_POST["id_avaliacao"])
    ) {

        $idAvaliacao = (int) $_POST["id_avaliacao"];

        $sqlInativarComentario = "
            UPDATE avaliacao
            SET avaliacao_status = 'Inativo'
            WHERE id_avaliacao = ?
            AND FK_avaliacao_id_conteudo = ?
        ";

        $stmtInativarComentario = mysqli_prepare(
            $conn,
            $sqlInativarComentario
        );

        if (!$stmtInativarComentario) {

            $mensagemAlerta =
                "Não foi possível ocultar o comentário.";

        } else {

            mysqli_stmt_bind_param(
                $stmtInativarComentario,
                "ii",
                $idAvaliacao,
                $idFilme
            );

            if (
                mysqli_stmt_execute(
                    $stmtInativarComentario
                )
            ) {

                mysqli_stmt_close(
                    $stmtInativarComentario
                );

                header(
                    "Location: filme_admin.php?id="
                    . $idFilme
                );

                exit;

            } else {

                $mensagemAlerta =
                    "Não foi possível ocultar o comentário.";

                mysqli_stmt_close(
                    $stmtInativarComentario
                );
            }
        }
    }



    if (
        $mensagemAlerta === null
        && isset($_POST["acao"])
        && $_POST["acao"] === "excluir_comentario"
        && isset($_POST["id_avaliacao"])
        && is_numeric($_POST["id_avaliacao"])
    ) {

        $idAvaliacao = (int) $_POST["id_avaliacao"];

        $sqlExcluirComentario = "
            DELETE FROM avaliacao
            WHERE id_avaliacao = ?
            AND FK_avaliacao_id_conteudo = ?
        ";

        $stmtExcluirComentario = mysqli_prepare(
            $conn,
            $sqlExcluirComentario
        );

        if (!$stmtExcluirComentario) {

            $mensagemAlerta =
                "Não foi possível excluir o comentário.";

        } else {

            mysqli_stmt_bind_param(
                $stmtExcluirComentario,
                "ii",
                $idAvaliacao,
                $idFilme
            );

            if (mysqli_stmt_execute($stmtExcluirComentario)) {

                mysqli_stmt_close($stmtExcluirComentario);

                header(
                    "Location: filme_admin.php?id="
                    . $idFilme
                    . "&comentario=excluido"
                );

                exit;

            } else {

                $mensagemAlerta =
                    "Não foi possível excluir o comentário.";

            }

            mysqli_stmt_close($stmtExcluirComentario);
        }
    }



    if (
        isset($_GET["comentario"])
        && $_GET["comentario"] === "excluido"
    ) {

        $mensagemAlerta =
            "Comentário excluído com sucesso.";
    }



    if (
        $mensagemAlerta === null
        && isset($_POST["acao"])
        && $_POST["acao"] === "excluir_filme"
    ) {

        $sqlExcluir = "
            DELETE FROM conteudo
            WHERE id_conteudo = ?
        ";

        $stmtExcluir = mysqli_prepare(
            $conn,
            $sqlExcluir
        );

        if (!$stmtExcluir) {

            $mensagemAlerta =
                "Não foi possível excluir o filme.";

        } else {

            mysqli_stmt_bind_param(
                $stmtExcluir,
                "i",
                $idFilme
            );

            if (mysqli_stmt_execute($stmtExcluir)) {

                mysqli_stmt_close($stmtExcluir);

                echo "<script>
                    window.location.href = 'tela_inicial_admin.php';
                </script>";

                exit;

            } else {

                $mensagemAlerta =
                    "Não foi possível excluir o filme.";
            }

            mysqli_stmt_close($stmtExcluir);
        }
    }



    if ($mensagemAlerta === null) {

        $sql = "
            SELECT
                id_conteudo,
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
                video_conteudo,
                conteudo_status,
                bloqueio_admin
            FROM conteudo
            WHERE id_conteudo = ?
        ";

        $stmt = mysqli_prepare(
            $conn,
            $sql
        );

        if (!$stmt) {

            $mensagemAlerta =
                "Não foi possível carregar o filme.";

        } else {

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $idFilme
            );

            mysqli_stmt_execute($stmt);

            $resultado = mysqli_stmt_get_result($stmt);

            if (
                !$resultado
                || mysqli_num_rows($resultado) === 0
            ) {

                $mensagemAlerta =
                    "Filme não encontrado.";

            } else {

                $filme = mysqli_fetch_assoc($resultado);



                if (
                    (int) $filme["classe_etaria_conteudo"] === 0
                ) {

                    $classificacao = "Livre";

                } else {

                    $classificacao =
                        (int) $filme["classe_etaria_conteudo"]
                        . " anos";
                }



                $nomesGeneros = [
                    "acao" => "Ação",
                    "aventura" => "Aventura",
                    "comedia" => "Comédia",
                    "drama" => "Drama",
                    "ficcao" => "Ficção científica",
                    "terror" => "Terror",
                    "romance" => "Romance",
                    "suspense" => "Suspense"
                ];

                $generosFilme = [];

                $generosCadastrados = explode(
                    ",",
                    $filme["genero_conteudo"]
                );

                foreach ($generosCadastrados as $genero) {

                    $genero = trim($genero);

                    if (isset($nomesGeneros[$genero])) {

                        $generosFilme[] =
                            $nomesGeneros[$genero];
                    }
                }

                $generoExibicao =
                    !empty($generosFilme)
                    ? implode(" • ", $generosFilme)
                    : "Não informado";



                $duracao =
                    !empty($filme["duracao_conteudo"])
                    ? $filme["duracao_conteudo"]
                    : "Não informado";



                $dataInicio = date(
                    "d/m/Y",
                    strtotime(
                        $filme["janela_exib_inicio_conteudo"]
                    )
                );

                $dataFim = date(
                    "d/m/Y",
                    strtotime(
                        $filme["janela_exib_fim_conteudo"]
                    )
                );
            }

            mysqli_stmt_close($stmt);
        }
    }



    $comentarios = [];
    $comentariosOcultos = [];

    if ($mensagemAlerta === null) {

        $sqlComentarios = "
            SELECT
                avaliacao.id_avaliacao,
                avaliacao.comentario_avaliacao,
                avaliacao.avaliacao_status,
                cliente.nome_cliente
            FROM avaliacao
            INNER JOIN cliente
                ON avaliacao.FK_avaliacao_id_cliente =
                   cliente.FK_cliente_id_cliente
            WHERE avaliacao.FK_avaliacao_id_conteudo = ?
            ORDER BY avaliacao.id_avaliacao DESC
        ";

        $stmtComentarios = mysqli_prepare(
            $conn,
            $sqlComentarios
        );

        if ($stmtComentarios) {

            mysqli_stmt_bind_param(
                $stmtComentarios,
                "i",
                $idFilme
            );

            mysqli_stmt_execute(
                $stmtComentarios
            );

            $resultadoComentarios =
                mysqli_stmt_get_result(
                    $stmtComentarios
                );

            if ($resultadoComentarios) {

                while (
                    $comentario =
                        mysqli_fetch_assoc(
                            $resultadoComentarios
                        )
                ) {

                    if (
                        $comentario["avaliacao_status"] === "Ativo"
                    ) {

                        $comentarios[] = $comentario;

                    } elseif (
                        $comentario["avaliacao_status"] === "Inativo"
                    ) {

                        $comentariosOcultos[] = $comentario;
                    }
                }
            }

            mysqli_stmt_close(
                $stmtComentarios
            );
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>

        ORION TV

        <?php if ($mensagemAlerta === null): ?>

            -
            <?= htmlspecialchars(
                $filme["titulo_conteudo"]
            ) ?>

        <?php endif; ?>

    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            min-height: 100%;
        }

        body {
            background-color: #080808;
            color: white;
            font-family: Arial, Helvetica, sans-serif;
            overflow-x: hidden;
        }


        

        .navbar-orion {
            width: 100%;
            height: 70px;
            background-color: #111111;
            border-bottom: 1px solid #2d2d2d;
        }

        .navbar-container {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 35px;
        }

        .logo {
            color: white;
            text-decoration: none;
            font-size: 25px;
            font-weight: bold;
        }

        .logo:hover {
            color: white;
        }

        .btn-voltar {
            color: white;
            text-decoration: none;
            border: 1px solid #aaaaaa;
            border-radius: 6px;
            padding: 8px 14px;
            font-size: 15px;
            transition: 0.2s;
        }

        .btn-voltar:hover {
            background-color: white;
            color: black;
        }


        

        .banner-filme {
            position: relative;
            width: 100%;
            height: 500px;
            overflow: hidden;
        }

        .banner-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            filter: brightness(40%);
        }

        .banner-escuro {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to right,
                rgba(0, 0, 0, 0.85),
                rgba(0, 0, 0, 0.35),
                rgba(0, 0, 0, 0.05)
            );
        }

        .banner-conteudo {
            position: absolute;
            z-index: 2;
            left: 8%;
            bottom: 65px;
            max-width: 85%;
        }

        .classificacao {
            display: inline-block;
            background-color: #0d6efd;
            padding: 7px 12px;
            border-radius: 5px;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .banner-conteudo h1 {
            margin: 0 0 10px;
            font-size: 64px;
            font-weight: 400;
        }

        .banner-conteudo p {
            margin: 0;
            color: #dddddd;
            font-size: 21px;
        }

        .banner-conteudo .btn-light {
            border-radius: 6px;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: 500;
        }


        

        .detalhes-filme {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 45px 0 0;
        }


        

        .abas-filme {
            border-bottom: 1px solid #303030;
            margin-bottom: 35px;
        }

        .abas-filme .nav-link {
            color: #999999;
            background: transparent;
            border: none;
            border-bottom: 2px solid transparent;
            border-radius: 0;
            padding: 12px 18px;
            font-size: 16px;
        }

        .abas-filme .nav-link:hover {
            color: white;
        }

        .abas-filme .nav-link.active {
            color: white;
            background: transparent;
            border-color: #0d6efd;
        }


        

        .poster-container {
            margin-bottom: 25px;
        }

        .poster-container a {
            display: block;
        }

        .poster {
            width: 100%;
            max-width: 230px;
            height: auto;
            display: block;
            border-radius: 8px;
            transition: transform 0.2s;
        }

        .poster:hover {
            transform: scale(1.02);
        }


        

        .card {
            width: 100%;
            background-color: #171717;
            color: white;
            border: 1px solid #303030;
            border-radius: 10px;
            padding: 28px;
            margin-bottom: 20px;
        }

        .card h2 {
            margin: 0 0 18px;
            color: white;
            font-size: 30px;
            font-weight: 500;
        }

        .card h3 {
            margin: 0 0 18px;
            color: white;
            font-size: 24px;
            font-weight: 500;
        }

        .card p {
            margin: 0;
            color: #c5c5c5;
            font-size: 17px;
            line-height: 1.6;
        }


        

        .grid {
            display: grid;
            gap: 20px;
            margin-bottom: 20px;
        }

        .grid-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .grid-3 {
            grid-template-columns: repeat(3, 1fr);
        }


        

        .comentarios {
            width: 100%;
            margin-bottom: 50px;
        }

        .comentarios h2 {
            margin: 0 0 10px;
            color: white;
            font-size: 30px;
            font-weight: 500;
        }

        .texto-admin {
            margin: 0 0 25px;
            color: #999999;
            font-size: 15px;
        }

        .busca-comentarios {
            width: 100%;
            margin-bottom: 20px;
        }

        .busca-comentarios input {
            width: 100%;
            padding: 11px 14px;
            background-color: #111111;
            border: 1px solid #303030;
            border-radius: 6px;
            color: white;
            font-size: 15px;
            outline: none;
            transition: 0.2s;
        }

        .busca-comentarios input:focus {
            border-color: #0d6efd;
        }

        .busca-comentarios input::placeholder {
            color: #777777;
        }


        

        .comentario {
            width: 100%;
            background-color: #171717;
            border: 1px solid #303030;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
        }

        .comentario-info {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 12px;
        }

        .comentario-info strong {
            display: block;
            color: white;
            font-size: 17px;
            margin-bottom: 4px;
        }

        .comentario p {
            margin: 0;
            color: #d0d0d0;
            font-size: 16px;
            line-height: 1.5;
        }


      

        .botoes-comentario {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .btn-excluir-comentario,
        .btn-inativar-comentario {
            border: none;
            border-radius: 5px;
            padding: 6px 11px;
            font-size: 13px;
            cursor: pointer;
            transition: 0.2s;
            white-space: nowrap;
        }

        .btn-excluir-comentario {
            background-color: #dc3545;
            color: white;
        }

        .btn-excluir-comentario:hover {
            background-color: #bb2d3b;
        }

        .btn-inativar-comentario {
            background-color: #ffc107;
            color: #111111;
        }

        .btn-inativar-comentario:hover {
            background-color: #e0a800;
            color: #111111;
        }


        

        .comentario-oculto {
            border-color: #5a2525;
        }

        .etiqueta-oculto {
            display: inline-block;
            margin-top: 5px;
            padding: 3px 8px;
            border-radius: 4px;
            background-color: #dc3545;
            color: white;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }


        

        .administracao-container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding-bottom: 80px;
        }

        .administracao {
            width: 100%;
            margin-top: 10px;
            padding: 30px;
            background-color: #151515;
            border: 1px solid #303030;
            border-radius: 10px;
        }

        .administracao h2 {
            margin: 0 0 10px;
            color: white;
            font-size: 28px;
            font-weight: 500;
        }

        .administracao p {
            margin: 0 0 25px;
            color: #999999;
            font-size: 16px;
        }


        

        .status-filme {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 18px 0;
            margin-bottom: 20px;
            border-top: 1px solid #303030;
            border-bottom: 1px solid #303030;
        }

        .status-filme-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .status-filme-titulo {
            color: white;
            font-size: 17px;
            font-weight: 500;
        }

        .status-filme-texto {
            color: #999999;
            font-size: 14px;
        }


        

        .toggle-status {
            position: relative;
            display: inline-block;
            width: 52px;
            height: 28px;
            flex-shrink: 0;
        }

        .toggle-status input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider-status {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background-color: #555555;
            border-radius: 30px;
            transition: 0.2s;
        }

        .slider-status::before {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            left: 4px;
            top: 4px;
            background-color: white;
            border-radius: 50%;
            transition: 0.2s;
        }

        .toggle-status input:checked + .slider-status {
            background-color: #168cff;
        }

        .toggle-status input:checked + .slider-status::before {
            transform: translateX(24px);
        }


        

        .btn-excluir-filme {
            border: none;
            border-radius: 6px;
            background-color: #dc3545;
            color: white;
            padding: 11px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-excluir-filme:hover {
            background-color: #bb2d3b;
        }


        

        .modal-orion {
            background-color: #181818;
            color: white;
            border: 1px solid #333333;
        }

        .modal-orion .modal-header {
            border-bottom: 1px solid #333333;
        }

        .modal-orion .modal-footer {
            border-top: 1px solid #333333;
        }

        .modal-orion .modal-body {
            color: #dddddd;
        }

        .modal-orion .modal-body p {
            color: #dddddd;
        }

        .aviso {
            color: #ff7777 !important;
            font-size: 14px !important;
        }


        

        #modalPlayer .modal-content {
            background-color: #111111 !important;
        }

        #modalPlayer .modal-header {
            background-color: #111111;
        }

        #modalPlayer video {
            max-height: 75vh;
            background-color: black;
        }


        

        @media (max-width: 900px) {

            .detalhes-filme {
                width: 94%;
            }

            .administracao-container {
                width: 94%;
            }

            .banner-conteudo {
                left: 5%;
            }

            .banner-conteudo h1 {
                font-size: 52px;
            }

            .poster {
                max-width: 180px;
            }

            .card {
                padding: 22px;
            }

            .card h2 {
                font-size: 26px;
            }

            .card h3 {
                font-size: 21px;
            }

        }


        

        @media (max-width: 600px) {

            .navbar-orion {
                height: 60px;
            }

            .navbar-container {
                padding: 0 15px;
            }

            .logo {
                font-size: 20px;
            }

            .btn-voltar {
                padding: 6px 10px;
                font-size: 13px;
            }

            .banner-filme {
                height: 350px;
            }

            .banner-escuro {
                background: linear-gradient(
                    to top,
                    rgba(0, 0, 0, 0.9),
                    rgba(0, 0, 0, 0.15)
                );
            }

            .banner-conteudo {
                left: 20px;
                right: 20px;
                bottom: 30px;
                max-width: none;
            }

            .banner-conteudo h1 {
                font-size: 38px;
            }

            .banner-conteudo p {
                font-size: 15px;
            }

            .classificacao {
                font-size: 12px;
                padding: 6px 9px;
                margin-bottom: 10px;
            }

            .banner-conteudo .btn-light {
                font-size: 14px;
                padding: 8px 15px;
            }

            .detalhes-filme {
                width: 100%;
                padding: 30px 15px 0;
            }

            .abas-filme {
                margin-bottom: 30px;
            }

            .abas-filme .nav-link {
                padding: 10px 12px;
                font-size: 14px;
            }

            .poster-container {
                text-align: center;
                margin-bottom: 30px;
            }

            .poster {
                width: 180px;
                max-width: 180px;
                margin: 0 auto;
            }

            .card {
                padding: 20px;
                margin-bottom: 15px;
            }

            .card h2 {
                font-size: 26px;
            }

            .card h3 {
                font-size: 21px;
            }

            .card p {
                font-size: 16px;
            }

            .grid-2,
            .grid-3 {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .comentarios {
                margin-bottom: 40px;
            }

            .comentarios h2 {
                font-size: 27px;
            }

            .texto-admin {
                font-size: 14px;
                line-height: 1.5;
            }

            .comentario {
                padding: 18px;
            }

            .comentario-info {
                gap: 10px;
            }

            .comentario-info strong {
                font-size: 16px;
            }

            .comentario p {
                font-size: 15px;
            }

            .botoes-comentario {
                gap: 6px;
            }

            .btn-excluir-comentario,
            .btn-inativar-comentario {
                padding: 6px 9px;
                font-size: 12px;
            }

            .administracao-container {
                width: 100%;
                padding: 0 15px 50px;
            }

            .administracao {
                margin-top: 0;
                padding: 22px;
            }

            .administracao h2 {
                font-size: 25px;
            }

            .administracao p {
                font-size: 15px;
            }

            .status-filme {
                align-items: center;
            }

            .btn-excluir-filme {
                width: 100%;
                padding: 11px;
            }

        }


        

        @media (max-width: 400px) {

            .logo {
                font-size: 18px;
            }

            .btn-inativar-comentario {
                padding: 5px 7px;
                font-size: 11px;
            }

            .btn-voltar {
                font-size: 12px;
            }

            .banner-filme {
                height: 320px;
            }

            .banner-conteudo h1 {
                font-size: 34px;
            }

            .banner-conteudo p {
                font-size: 14px;
            }

            .detalhes-filme {
                padding-left: 12px;
                padding-right: 12px;
            }

            .poster {
                width: 160px;
                max-width: 160px;
            }

            .abas-filme .nav-link {
                padding-left: 9px;
                padding-right: 9px;
                font-size: 13px;
            }

            .comentario-info {
                align-items: flex-start;
            }

            .btn-excluir-comentario,
            .btn-inativar-comentario {
                padding: 5px 7px;
                font-size: 11px;
            }

            .administracao-container {
                padding-left: 12px;
                padding-right: 12px;
            }

        }

    </style>

</head>


<body>


    

    <nav class="navbar navbar-dark navbar-orion">

        <div class="navbar-container">

            <a
                href="tela_inicial_admin.php"
                class="logo"
            >
                ORION TV
            </a>

            <a
                href="tela_inicial_admin.php"
                class="btn-voltar"
            >
                <i class="bi bi-arrow-left"></i>
                Voltar
            </a>

        </div>

    </nav>


    <?php if ($mensagemAlerta === null): ?>


        

        <section class="banner-filme">

            <img
                src="<?= htmlspecialchars(
                    $filme["banner_conteudo_horizontal"]
                ) ?>"
                class="banner-img"
                alt="<?= htmlspecialchars(
                    $filme["titulo_conteudo"]
                ) ?>"
            >

            <div class="banner-escuro"></div>

            <div class="banner-conteudo">

                <span class="classificacao">

                    <?= htmlspecialchars(
                        strtoupper($classificacao)
                    ) ?>

                </span>

                <h1>

                    <?= htmlspecialchars(
                        $filme["titulo_conteudo"]
                    ) ?>

                </h1>

                <p>

                    <?= htmlspecialchars(
                        $generoExibicao
                    ) ?>

                    •

                    <?= htmlspecialchars(
                        $duracao
                    ) ?>

                </p>

                <button
                    type="button"
                    class="btn btn-light btn-lg mt-3"
                    data-bs-toggle="modal"
                    data-bs-target="#modalPlayer"
                >

                    <i class="bi bi-play-fill"></i>

                    Assistir

                </button>

            </div>

        </section>



       

        <section class="container detalhes-filme">


            

            <ul
                class="nav nav-tabs abas-filme"
                id="filmeTabs"
            >

                <li class="nav-item">

                    <button
                        class="nav-link active"
                        data-bs-toggle="tab"
                        data-bs-target="#informacoes"
                        type="button"
                    >

                        <i class="bi bi-info-circle"></i>

                        Informações

                    </button>

                </li>


                <li class="nav-item">

                    <button
                        class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#comentarios"
                        type="button"
                    >

                        <i class="bi bi-chat-left-text"></i>

                        Comentários

                        <span class="badge bg-secondary">
                            <?= count($comentarios) ?>
                        </span>

                    </button>

                </li>


                <li class="nav-item">

                    <button
                        class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#ocultos"
                        type="button"
                    >

                        <i class="bi bi-eye-slash"></i>

                        Ocultos

                        <span class="badge bg-danger">
                            <?= count($comentariosOcultos) ?>
                        </span>

                    </button>

                </li>

            </ul>


      

            <div class="tab-content">


                

                <div
                    class="tab-pane fade show active"
                    id="informacoes"
                >

                    <div class="row">

                        <div class="col-lg-3 poster-container">

                            <a
                                href="#"
                                id="posterFilme"
                                data-bs-toggle="modal"
                                data-bs-target="#modalPlayer"
                            >

                                <img
                                    src="<?= htmlspecialchars(
                                        $filme["thumb_conteudo_vertical"]
                                    ) ?>"
                                    class="poster"
                                    alt="Poster <?= htmlspecialchars(
                                        $filme["titulo_conteudo"]
                                    ) ?>"
                                >

                            </a>

                        </div>


                        <div class="col-lg-9">

                            <section class="card">

                                <h2>
                                    Sinopse
                                </h2>

                                <p>

                                    <?= nl2br(
                                        htmlspecialchars(
                                            $filme["sinopse_conteudo"]
                                        )
                                    ) ?>

                                </p>

                            </section>


                            <div class="grid grid-2">

                                <section class="card">

                                    <h3>
                                        Elenco
                                    </h3>

                                    <p>

                                        <?= htmlspecialchars(
                                            $filme["elenco_conteudo"]
                                        ) ?>

                                    </p>

                                </section>


                                <section class="card">

                                    <h3>
                                        Diretores
                                    </h3>

                                    <p>

                                        <?= htmlspecialchars(
                                            $filme["diretores_conteudo"]
                                        ) ?>

                                    </p>

                                </section>

                            </div>


                            <div class="grid grid-3">

                                <section class="card">

                                    <h3>
                                        Gênero
                                    </h3>

                                    <p>

                                        <?= htmlspecialchars(
                                            $generoExibicao
                                        ) ?>

                                    </p>

                                </section>


                                <section class="card">

                                    <h3>
                                        Classificação
                                    </h3>

                                    <p>

                                        <?= htmlspecialchars(
                                            $classificacao
                                        ) ?>

                                    </p>

                                </section>


                                <section class="card">

                                    <h3>
                                        Duração
                                    </h3>

                                    <p>

                                        <?= htmlspecialchars(
                                            $duracao
                                        ) ?>

                                    </p>

                                </section>

                            </div>


                            <div class="grid grid-2">

                                <section class="card">

                                    <h3>
                                        No catálogo desde:
                                    </h3>

                                    <p>

                                        <?= htmlspecialchars(
                                            $dataInicio
                                        ) ?>

                                    </p>

                                </section>


                                <section class="card">

                                    <h3>
                                        Disponível até:
                                    </h3>

                                    <p>

                                        <?= htmlspecialchars(
                                            $dataFim
                                        ) ?>

                                    </p>

                                </section>

                            </div>

                        </div>

                    </div>

                </div>


                

                <div
                    class="tab-pane fade"
                    id="comentarios"
                >

                    <div class="comentarios">

                        <h2>
                            Comentários
                        </h2>

                        <p class="texto-admin">

                            Como administrador, você pode visualizar,
                            ocultar ou excluir os comentários deste filme.

                        </p>

                        <div class="busca-comentarios">

                            <input
                                type="search"
                                id="buscaComentarios"
                                placeholder="Pesquisar por usuário ou palavra..."
                                autocomplete="off"
                            >

                        </div>


                        <?php if (empty($comentarios)): ?>

                            <div class="comentario">

                                <p>
                                    Ainda não há comentários ativos para este filme.
                                </p>

                            </div>

                        <?php else: ?>

                            <?php foreach (
                                $comentarios as $comentario
                            ): ?>

                                <div
                                    class="comentario"
                                    id="comentario<?= (int) $comentario["id_avaliacao"] ?>"
                                >

                                    <div class="comentario-info">

                                        <div>

                                            <strong class="nome-comentario">

                                                <?= htmlspecialchars(
                                                    $comentario["nome_cliente"]
                                                ) ?>

                                            </strong>

                                        </div>


                                        <div class="botoes-comentario">

                                            

                                            <button
                                                type="button"
                                                class="btn-inativar-comentario"
                                                data-id="<?= (int) $comentario["id_avaliacao"] ?>"
                                                data-usuario="<?= htmlspecialchars(
                                                    $comentario["nome_cliente"],
                                                    ENT_QUOTES
                                                ) ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalInativarComentario"
                                            >

                                                <i class="bi bi-eye-slash"></i>

                                                Inativar

                                            </button>


                                            

                                            <button
                                                type="button"
                                                class="btn-excluir-comentario"
                                                data-id="<?= (int) $comentario["id_avaliacao"] ?>"
                                                data-usuario="<?= htmlspecialchars(
                                                    $comentario["nome_cliente"],
                                                    ENT_QUOTES
                                                ) ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalExcluirComentario"
                                            >

                                                <i class="bi bi-trash3"></i>

                                                Excluir

                                            </button>

                                        </div>

                                    </div>


                                    <p class="texto-comentario">

                                        <?= nl2br(
                                            htmlspecialchars(
                                                $comentario["comentario_avaliacao"]
                                            )
                                        ) ?>

                                    </p>

                                </div>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>

                </div>


                

                <div
                    class="tab-pane fade"
                    id="ocultos"
                >

                    <div class="comentarios">

                        <h2>
                            Comentários ocultos
                        </h2>

                        <p class="texto-admin">

                            Comentários que foram inativados pelo administrador.

                        </p>

                        <div class="busca-comentarios">

                            <input
                                type="search"
                                id="buscaComentariosOcultos"
                                placeholder="Pesquisar por usuário ou palavra..."
                                autocomplete="off"
                            >

                        </div>


                        <?php if (empty($comentariosOcultos)): ?>

                            <div class="comentario">

                                <p>
                                    Não há comentários ocultos para este filme.
                                </p>

                            </div>

                        <?php else: ?>

                            <?php foreach (
                                $comentariosOcultos as $comentario
                            ): ?>

                                <div
                                    class="comentario comentario-oculto"
                                    id="comentarioOculto<?= (int) $comentario["id_avaliacao"] ?>"
                                >

                                    <div class="comentario-info">

                                        <div>

                                            <strong class="nome-comentario">

                                                <?= htmlspecialchars(
                                                    $comentario["nome_cliente"]
                                                ) ?>

                                            </strong>

                                            <span class="etiqueta-oculto">
                                                Oculto
                                            </span>

                                        </div>


                                        <button
                                            type="button"
                                            class="btn-excluir-comentario"
                                            data-id="<?= (int) $comentario["id_avaliacao"] ?>"
                                            data-usuario="<?= htmlspecialchars(
                                                $comentario["nome_cliente"],
                                                ENT_QUOTES
                                            ) ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalExcluirComentario"
                                        >

                                            <i class="bi bi-trash3"></i>

                                            Excluir

                                        </button>

                                    </div>


                                    <p class="texto-comentario">

                                        <?= nl2br(
                                            htmlspecialchars(
                                                $comentario["comentario_avaliacao"]
                                            )
                                        ) ?>

                                    </p>

                                </div>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </section>



        

        <section class="administracao-container">

            <section class="administracao">

                <h2>
                    Administração
                </h2>

                <p>
                    Ações administrativas disponíveis para este filme.
                </p>


                <div class="status-filme">

                    <div class="status-filme-info">

                        <span class="status-filme-titulo">
                            Bloqueio administrativo
                        </span>

                        <span class="status-filme-texto">
                            Bloqueie ou desbloqueie este filme manualmente.
                        </span>

                    </div>


                    <form
                        method="POST"
                        action="filme_admin.php?id=<?= $idFilme ?>"
                    >

                        <input
                            type="hidden"
                            name="acao"
                            value="alternar_status"
                        >

                        <label class="toggle-status">

                            <input
                                type="checkbox"
                                <?= $filme["bloqueio_admin"] === "Sim"
                                    ? "checked"
                                    : "" ?>
                                onchange="this.form.submit()"
                            >

                            <span class="slider-status"></span>

                        </label>

                    </form>

                </div>


                <button
                    type="button"
                    class="btn-excluir-filme"
                    data-bs-toggle="modal"
                    data-bs-target="#modalExcluirFilme"
                >

                    <i class="bi bi-trash3"></i>

                    Excluir filme

                </button>

            </section>

        </section>



        

        <div
            class="modal fade"
            id="modalPlayer"
            tabindex="-1"
            aria-labelledby="tituloModalPlayer"
            aria-hidden="true"
        >

            <div
                class="modal-dialog modal-xl modal-dialog-centered"
            >

                <div class="modal-content bg-dark border-0">

                    <div class="modal-header border-0">

                        <h5
                            class="modal-title text-white"
                            id="tituloModalPlayer"
                        >

                            <?= htmlspecialchars(
                                $filme["titulo_conteudo"]
                            ) ?>

                        </h5>

                        <button
                            type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal"
                            aria-label="Fechar"
                        ></button>

                    </div>


                    <div class="modal-body p-0">

                        <video
                            id="videoFilme"
                            class="w-100 d-block"
                            controls
                            preload="metadata"
                            playsinline
                            poster="<?= htmlspecialchars(
                                $filme["banner_conteudo_horizontal"]
                            ) ?>"
                        >

                            <source
                                src="<?= htmlspecialchars(
                                    $filme["video_conteudo"]
                                ) ?>"
                                type="video/mp4"
                            >

                            Seu navegador não suporta
                            a reprodução de vídeo.

                        </video>

                    </div>

                </div>

            </div>

        </div>



        

        <div
            class="modal fade"
            id="modalInativarComentario"
            tabindex="-1"
            aria-labelledby="tituloModalInativarComentario"
            aria-hidden="true"
        >

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content modal-orion">

                    <div class="modal-header">

                        <h5
                            class="modal-title"
                            id="tituloModalInativarComentario"
                        >
                            Ocultar comentário
                        </h5>

                        <button
                            type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal"
                            aria-label="Fechar"
                        ></button>

                    </div>


                    <div class="modal-body">

                        <p>

                            Tem certeza que deseja ocultar
                            o comentário de

                            <strong id="nomeComentarioInativar">
                                este usuário
                            </strong>?

                        </p>

                        <p>
                            O comentário deixará de aparecer para os clientes,
                            mas continuará disponível na aba de comentários ocultos.
                        </p>

                    </div>


                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Cancelar
                        </button>


                        <form
                            method="POST"
                            action="filme_admin.php?id=<?= $idFilme ?>"
                        >

                            <input
                                type="hidden"
                                name="acao"
                                value="inativar_comentario"
                            >

                            <input
                                type="hidden"
                                name="id_avaliacao"
                                id="idAvaliacaoInativar"
                                value=""
                            >

                            <button
                                type="submit"
                                class="btn btn-warning"
                            >

                                <i class="bi bi-eye-slash"></i>

                                Confirmar ocultação

                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>



        

        <div
            class="modal fade"
            id="modalExcluirComentario"
            tabindex="-1"
            aria-labelledby="tituloModalComentario"
            aria-hidden="true"
        >

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content modal-orion">

                    <div class="modal-header">

                        <h5
                            class="modal-title"
                            id="tituloModalComentario"
                        >
                            Excluir comentário
                        </h5>

                        <button
                            type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal"
                            aria-label="Fechar"
                        ></button>

                    </div>


                    <div class="modal-body">

                        <p>

                            Tem certeza que deseja excluir
                            o comentário de

                            <strong id="nomeComentario">
                                este usuário
                            </strong>?

                        </p>

                        <p class="aviso">
                            Essa ação não poderá ser desfeita.
                        </p>

                    </div>


                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Cancelar
                        </button>


                        <form
                            method="POST"
                            action="filme_admin.php?id=<?= $idFilme ?>"
                        >

                            <input
                                type="hidden"
                                name="acao"
                                value="excluir_comentario"
                            >

                            <input
                                type="hidden"
                                name="id_avaliacao"
                                id="idAvaliacaoExcluir"
                                value=""
                            >

                            <button
                                type="submit"
                                class="btn btn-danger"
                            >

                                <i class="bi bi-trash3"></i>

                                Confirmar exclusão

                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>



        

        <div
            class="modal fade"
            id="modalExcluirFilme"
            tabindex="-1"
            aria-labelledby="tituloModalExcluirFilme"
            aria-hidden="true"
        >

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content modal-orion">

                    <div class="modal-header">

                        <h5
                            class="modal-title"
                            id="tituloModalExcluirFilme"
                        >
                            Excluir filme
                        </h5>

                        <button
                            type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal"
                            aria-label="Fechar"
                        ></button>

                    </div>


                    <div class="modal-body">

                        <p>

                            Tem certeza que deseja excluir o filme

                            <strong>

                                <?= htmlspecialchars(
                                    $filme["titulo_conteudo"]
                                ) ?>

                            </strong>?

                        </p>

                        <p class="aviso">
                            Essa ação não poderá ser desfeita.
                        </p>

                    </div>


                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Cancelar
                        </button>


                        <form
                            method="POST"
                            action="filme_admin.php?id=<?= $idFilme ?>"
                        >

                            <input
                                type="hidden"
                                name="acao"
                                value="excluir_filme"
                            >

                            <button
                                type="submit"
                                class="btn btn-danger"
                            >

                                <i class="bi bi-trash3"></i>

                                Confirmar exclusão

                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    <?php endif; ?>




    <?php

    if ($mensagemAlerta !== null) {

        mostrarAlerta(
            $mensagemAlerta,
            "window.location.href = 'tela_inicial_admin.php'"
        );

    }

    ?>


    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    ></script>


    <?php if ($mensagemAlerta === null): ?>

    <script>

       

        const video =
            document.getElementById("videoFilme");

        const modalPlayer =
            document.getElementById("modalPlayer");


        if (modalPlayer) {

            modalPlayer.addEventListener(
                "shown.bs.modal",
                function () {

                    if (video) {

                        video.currentTime = 0;

                        video.play().catch(function () {

                        });

                    }

                }
            );


            modalPlayer.addEventListener(
                "hidden.bs.modal",
                function () {

                    if (video) {

                        video.pause();

                        video.currentTime = 0;

                    }

                }
            );

        }


       

        const posterFilme =
            document.getElementById("posterFilme");


        if (posterFilme) {

            posterFilme.addEventListener(
                "click",
                function (event) {

                    event.preventDefault();

                }
            );

        }


     

        const botoesExcluirComentario =
            document.querySelectorAll(
                ".btn-excluir-comentario"
            );


        const nomeComentario =
            document.getElementById(
                "nomeComentario"
            );


        const idAvaliacaoExcluir =
            document.getElementById(
                "idAvaliacaoExcluir"
            );


        botoesExcluirComentario.forEach(
            function (botao) {

                botao.addEventListener(
                    "click",
                    function () {

                        const id =
                            this.getAttribute(
                                "data-id"
                            );


                        const usuario =
                            this.getAttribute(
                                "data-usuario"
                            );


                        if (nomeComentario) {

                            nomeComentario.textContent =
                                usuario;

                        }


                        if (idAvaliacaoExcluir) {

                            idAvaliacaoExcluir.value =
                                id;

                        }

                    }
                );

            }
        );


      

        const botoesInativarComentario =
            document.querySelectorAll(
                ".btn-inativar-comentario"
            );


        const nomeComentarioInativar =
            document.getElementById(
                "nomeComentarioInativar"
            );


        const idAvaliacaoInativar =
            document.getElementById(
                "idAvaliacaoInativar"
            );


        botoesInativarComentario.forEach(
            function (botao) {

                botao.addEventListener(
                    "click",
                    function () {

                        const id =
                            this.getAttribute(
                                "data-id"
                            );


                        const usuario =
                            this.getAttribute(
                                "data-usuario"
                            );


                        if (nomeComentarioInativar) {

                            nomeComentarioInativar.textContent =
                                usuario;

                        }


                        if (idAvaliacaoInativar) {

                            idAvaliacaoInativar.value =
                                id;

                        }

                    }
                );

            }
        );


   

        function normalizarTexto(texto) {

            return texto
                .toLocaleLowerCase("pt-BR")
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "");

        }


        function configurarBuscaComentarios(
            idBusca,
            idAba
        ) {

            const campoBusca =
                document.getElementById(idBusca);

            const aba =
                document.getElementById(idAba);


            if (!campoBusca || !aba) {

                return;

            }


            campoBusca.addEventListener(
                "input",
                function () {

                    const termo =
                        normalizarTexto(
                            this.value.trim()
                        );


                    const comentarios =
                        aba.querySelectorAll(
                            ".comentario"
                        );


                    comentarios.forEach(
                        function (comentario) {

                            const nome =
                                comentario.querySelector(
                                    ".nome-comentario"
                                );

                            const texto =
                                comentario.querySelector(
                                    ".texto-comentario"
                                );


                            const conteudoPesquisa =
                                normalizarTexto(
                                    (nome ? nome.textContent : "")
                                    + " "
                                    + (texto ? texto.textContent : "")
                                );


                            comentario.style.display =
                                termo === ""
                                || conteudoPesquisa.includes(termo)
                                    ? ""
                                    : "none";

                        }
                    );

                }
            );

        }


        configurarBuscaComentarios(
            "buscaComentarios",
            "comentarios"
        );


        configurarBuscaComentarios(
            "buscaComentariosOcultos",
            "ocultos"
        );

    </script>

    <?php endif; ?>


</body>

</html>