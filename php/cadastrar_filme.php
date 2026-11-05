<?php

require_once(__DIR__ . "/../includes/proteger_distribuidor.php");
require_once(__DIR__ . "/conexao.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../enviar_filme.php");
    exit;
}




if (
    !isset($_SESSION["id_usuario"]) ||
    !isset($_SESSION["tipo_usuario"])
) {
    header("Location: ../index.php");
    exit;
}

$id_usuario = (int) $_SESSION["id_usuario"];

$id_distribuidor = null;
$id_administrador = null;




$sql_administrador = "
    SELECT id_administrador
    FROM administrador
    WHERE FK_administrador_id_usuario = ?
";

$stmt_administrador = mysqli_prepare(
    $conn,
    $sql_administrador
);

if (!$stmt_administrador) {
    header("Location: ../enviar_filme.php?erro=preparar_administrador");
    exit;
}

mysqli_stmt_bind_param(
    $stmt_administrador,
    "i",
    $id_usuario
);

if (!mysqli_stmt_execute($stmt_administrador)) {
    mysqli_stmt_close($stmt_administrador);

    header("Location: ../enviar_filme.php?erro=consultar_administrador");
    exit;
}

mysqli_stmt_bind_result(
    $stmt_administrador,
    $id_administrador
);

$encontrou_administrador = mysqli_stmt_fetch(
    $stmt_administrador
);

mysqli_stmt_close($stmt_administrador);




if ($encontrou_administrador) {

    $id_administrador = (int) $id_administrador;

}




else {

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
        header("Location: ../enviar_filme.php?erro=preparar_distribuidor");
        exit;
    }

    mysqli_stmt_bind_param(
        $stmt_distribuidor,
        "i",
        $id_usuario
    );

    if (!mysqli_stmt_execute($stmt_distribuidor)) {
        mysqli_stmt_close($stmt_distribuidor);

        header("Location: ../enviar_filme.php?erro=consultar_distribuidor");
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
        header("Location: ../enviar_filme.php?erro=responsavel_nao_encontrado");
        exit;
    }

    $id_distribuidor = (int) $id_distribuidor;
}




$titulo = trim($_POST["titulo"] ?? "");
$sinopse = trim($_POST["sinopse"] ?? "");
$elenco = trim($_POST["elenco"] ?? "");
$diretores = trim($_POST["diretores"] ?? "");
$classe_etaria = $_POST["classe_etaria"] ?? "";
$duracao = trim($_POST["duracao"] ?? "");
$janela_inicial = $_POST["janela_inicial"] ?? "";
$janela_final = $_POST["janela_final"] ?? "";

$generos = $_POST["generos"] ?? [];


if (
    $titulo === "" ||
    $sinopse === "" ||
    $elenco === "" ||
    $diretores === "" ||
    empty($generos) ||
    $classe_etaria === "" ||
    $duracao === "" ||
    $janela_inicial === "" ||
    $janela_final === ""
) {
    header("Location: ../enviar_filme.php?erro=campos");
    exit;
}



$generos_permitidos = [
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
    header("Location: ../enviar_filme.php?erro=generos");
    exit;
}

$generos = array_unique($generos);

if (count($generos) < 1 || count($generos) > 3) {
    header("Location: ../enviar_filme.php?erro=generos");
    exit;
}

foreach ($generos as $genero) {

    if (!in_array($genero, $generos_permitidos, true)) {
        header("Location: ../enviar_filme.php?erro=generos");
        exit;
    }
}

$genero = implode(",", $generos);




if (strlen($duracao) > 20) {
    header("Location: ../enviar_filme.php?erro=duracao");
    exit;
}



if ($classe_etaria === "livre") {
    $classe_etaria = 0;
} else {
    $classe_etaria = (int) $classe_etaria;
}

$classes_permitidas = [
    0,
    10,
    12,
    14,
    16,
    18
];

if (!in_array($classe_etaria, $classes_permitidas, true)) {
    header("Location: ../enviar_filme.php?erro=classificacao");
    exit;
}




if ($janela_final < $janela_inicial) {
    header("Location: ../enviar_filme.php?erro=datas");
    exit;
}




if (
    !isset($_FILES["thumb"]) ||
    !isset($_FILES["banner"]) ||
    !isset($_FILES["video"])
) {
    header("Location: ../enviar_filme.php?erro=arquivos");
    exit;
}

$thumb = $_FILES["thumb"];
$banner = $_FILES["banner"];
$video = $_FILES["video"];


if ($thumb["error"] !== UPLOAD_ERR_OK) {
    header("Location: ../enviar_filme.php?erro=thumb");
    exit;
}

if ($banner["error"] !== UPLOAD_ERR_OK) {
    header("Location: ../enviar_filme.php?erro=banner");
    exit;
}

if ($video["error"] !== UPLOAD_ERR_OK) {
    header("Location: ../enviar_filme.php?erro=video");
    exit;
}




$pasta_thumb = __DIR__ . "/../thumbs/verticais/";
$pasta_banner = __DIR__ . "/../thumbs/horizontais/";
$pasta_video = __DIR__ . "/../vids/";


if (!is_dir($pasta_thumb)) {

    if (!mkdir($pasta_thumb, 0755, true)) {
        header("Location: ../enviar_filme.php?erro=pasta_thumb");
        exit;
    }
}


if (!is_dir($pasta_banner)) {

    if (!mkdir($pasta_banner, 0755, true)) {
        header("Location: ../enviar_filme.php?erro=pasta_banner");
        exit;
    }
}


if (!is_dir($pasta_video)) {

    if (!mkdir($pasta_video, 0755, true)) {
        header("Location: ../enviar_filme.php?erro=pasta_video");
        exit;
    }
}




$tipos_imagem_permitidos = [
    "image/jpeg" => "jpg",
    "image/png" => "png",
    "image/webp" => "webp"
];

$tipos_video_permitidos = [
    "video/mp4" => "mp4",
    "video/webm" => "webm",
    "video/ogg" => "ogv",
    "video/quicktime" => "mov"
];


$finfo = finfo_open(FILEINFO_MIME_TYPE);

$mime_thumb = finfo_file(
    $finfo,
    $thumb["tmp_name"]
);

$mime_banner = finfo_file(
    $finfo,
    $banner["tmp_name"]
);

$mime_video = finfo_file(
    $finfo,
    $video["tmp_name"]
);

finfo_close($finfo);


if (!isset($tipos_imagem_permitidos[$mime_thumb])) {
    header("Location: ../enviar_filme.php?erro=formato_thumb");
    exit;
}

if (!isset($tipos_imagem_permitidos[$mime_banner])) {
    header("Location: ../enviar_filme.php?erro=formato_banner");
    exit;
}

if (!isset($tipos_video_permitidos[$mime_video])) {
    header("Location: ../enviar_filme.php?erro=formato_video");
    exit;
}




$nome_base = uniqid("filme_", true);

$ext_thumb = $tipos_imagem_permitidos[$mime_thumb];
$ext_banner = $tipos_imagem_permitidos[$mime_banner];
$ext_video = $tipos_video_permitidos[$mime_video];

$nome_thumb = $nome_base . "_vertical." . $ext_thumb;
$nome_banner = $nome_base . "_horizontal." . $ext_banner;
$nome_video = $nome_base . "_video." . $ext_video;


$caminho_thumb_fisico =
    $pasta_thumb . $nome_thumb;

$caminho_banner_fisico =
    $pasta_banner . $nome_banner;

$caminho_video_fisico =
    $pasta_video . $nome_video;


$caminho_thumb_sql =
    "thumbs/verticais/" . $nome_thumb;

$caminho_banner_sql =
    "thumbs/horizontais/" . $nome_banner;

$caminho_video_sql =
    "vids/" . $nome_video;




if (!move_uploaded_file(
    $thumb["tmp_name"],
    $caminho_thumb_fisico
)) {
    header("Location: ../enviar_filme.php?erro=salvar_thumb");
    exit;
}




if (!move_uploaded_file(
    $banner["tmp_name"],
    $caminho_banner_fisico
)) {

    unlink($caminho_thumb_fisico);

    header("Location: ../enviar_filme.php?erro=salvar_banner");
    exit;
}




if (!move_uploaded_file(
    $video["tmp_name"],
    $caminho_video_fisico
)) {

    unlink($caminho_thumb_fisico);
    unlink($caminho_banner_fisico);

    header("Location: ../enviar_filme.php?erro=salvar_video");
    exit;
}



$conteudo_status = "Inativo";


$sql = "
    INSERT INTO conteudo (
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
        conteudo_status,
        video_conteudo,
        FK_conteudo_id_distribuidor,
        FK_conteudo_id_administrador
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
";


$stmt = mysqli_prepare(
    $conn,
    $sql
);


if (!$stmt) {

    unlink($caminho_thumb_fisico);
    unlink($caminho_banner_fisico);
    unlink($caminho_video_fisico);

    header("Location: ../enviar_filme.php?erro=preparar_cadastro");
    exit;
}


mysqli_stmt_bind_param(
    $stmt,
    "sssssisssssssii",
    $titulo,
    $sinopse,
    $elenco,
    $diretores,
    $genero,
    $classe_etaria,
    $duracao,
    $janela_inicial,
    $janela_final,
    $caminho_thumb_sql,
    $caminho_banner_sql,
    $conteudo_status,
    $caminho_video_sql,
    $id_distribuidor,
    $id_administrador
);


if (!mysqli_stmt_execute($stmt)) {

    unlink($caminho_thumb_fisico);
    unlink($caminho_banner_fisico);
    unlink($caminho_video_fisico);

    mysqli_stmt_close($stmt);

    header("Location: ../enviar_filme.php?erro=cadastro");
    exit;
}


mysqli_stmt_close($stmt);


header("Location: ../enviar_filme.php?sucesso=1");
exit;