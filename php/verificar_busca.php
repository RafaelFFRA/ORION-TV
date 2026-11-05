<?php

require_once(__DIR__ . "/conexao.php");




$pesquisa = trim($_GET["pesquisa"] ?? "");

$genero = trim($_GET["genero"] ?? "");




$sql = "
    SELECT

        id_conteudo,
        titulo_conteudo,
        sinopse_conteudo,
        elenco_conteudo,
        diretores_conteudo,
        genero_conteudo,
        classe_etaria_conteudo,
        janela_exib_inicio_conteudo,
        janela_exib_fim_conteudo,
        thumb_conteudo_vertical,
        banner_conteudo_horizontal,
        video_conteudo,
        duracao_conteudo,
        FK_conteudo_id_distribuidor

    FROM conteudo

    WHERE 1 = 1

    AND conteudo_status = 'Ativo'
    AND bloqueio_admin = 'Não'
";


$parametros = [];

$tipos = "";




if ($pesquisa !== "") {

    $sql .= "
        AND (
            titulo_conteudo LIKE ?
            OR sinopse_conteudo LIKE ?
            OR elenco_conteudo LIKE ?
            OR diretores_conteudo LIKE ?
            OR genero_conteudo LIKE ?
            OR CAST(classe_etaria_conteudo AS CHAR) LIKE ?
            OR CAST(janela_exib_inicio_conteudo AS CHAR) LIKE ?
            OR CAST(janela_exib_fim_conteudo AS CHAR) LIKE ?
            OR duracao_conteudo LIKE ?
        )
    ";


    $termoBusca =
        "%" . $pesquisa . "%";


    $parametros[] = $termoBusca;
    $parametros[] = $termoBusca;
    $parametros[] = $termoBusca;
    $parametros[] = $termoBusca;
    $parametros[] = $termoBusca;
    $parametros[] = $termoBusca;
    $parametros[] = $termoBusca;
    $parametros[] = $termoBusca;
    $parametros[] = $termoBusca;


    $tipos .= "sssssssss";
}




if ($genero !== "") {

    $sql .= "
        AND (
            genero_conteudo = ?
            OR genero_conteudo LIKE ?
            OR genero_conteudo LIKE ?
            OR genero_conteudo LIKE ?
        )
    ";


    $generoExato =
        $genero;


    $generoInicio =
        $genero . ",%";


    $generoMeio =
        "%, " . $genero . ",%";


    $generoFinal =
        "%, " . $genero;


    $parametros[] =
        $generoExato;

    $parametros[] =
        $generoInicio;

    $parametros[] =
        $generoMeio;

    $parametros[] =
        $generoFinal;


    $tipos .= "ssss";
}




$sql .= "
    ORDER BY titulo_conteudo ASC
";




$stmt =
    mysqli_prepare(
        $conn,
        $sql
    );


if (!$stmt) {

    http_response_code(500);

    header("Content-Type: application/json; charset=UTF-8");

    echo json_encode([
        "sucesso" => false
    ]);

    exit;
}




if (!empty($parametros)) {

    mysqli_stmt_bind_param(
        $stmt,
        $tipos,
        ...$parametros
    );
}




if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    http_response_code(500);

    header("Content-Type: application/json; charset=UTF-8");

    echo json_encode([
        "sucesso" => false
    ]);

    exit;
}


$resultado =
    mysqli_stmt_get_result($stmt);


$filmes = [];


while (
    $filme =
    mysqli_fetch_assoc($resultado)
) {

    $filmes[] =
        $filme;
}


mysqli_stmt_close($stmt);




$hash =
    md5(
        json_encode(
            $filmes,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        )
    );




header(
    "Content-Type: application/json; charset=UTF-8"
);

header(
    "Cache-Control: no-store, no-cache, must-revalidate, max-age=0"
);

header(
    "Pragma: no-cache"
);


echo json_encode(
    [
        "sucesso" => true,
        "hash" => $hash
    ],
    JSON_UNESCAPED_UNICODE
);