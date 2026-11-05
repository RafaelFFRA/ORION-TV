<?php

require_once(__DIR__ . "/php/conexao.php");



$pesquisa = trim($_GET["pesquisa"] ?? "");
$genero = trim($_GET["genero"] ?? "");



$generos = [];

$sqlGeneros = "
    SELECT genero_conteudo
    FROM conteudo
    WHERE genero_conteudo IS NOT NULL
    AND genero_conteudo <> ''
    AND conteudo_status = 'Ativo'
    AND bloqueio_admin = 'Não'
    ORDER BY genero_conteudo ASC
";


$resultadoGeneros = mysqli_query($conn, $sqlGeneros);


if ($resultadoGeneros) {

    while ($linhaGenero = mysqli_fetch_assoc($resultadoGeneros)) {

        $listaGeneros =
            explode(
                ",",
                $linhaGenero["genero_conteudo"]
            );


        foreach ($listaGeneros as $generoIndividual) {

            $generoIndividual =
                trim($generoIndividual);


            if (
                $generoIndividual !== ""
                &&
                !in_array(
                    $generoIndividual,
                    $generos,
                    true
                )
            ) {

                $generos[] =
                    $generoIndividual;
            }
        }
    }
}


natcasesort($generos);

$generos = array_values($generos);



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

    die(
        "Erro ao consultar filmes: "
        .
        mysqli_error($conn)
    );
}



if (!empty($parametros)) {

    mysqli_stmt_bind_param(
        $stmt,
        $tipos,
        ...$parametros
    );
}



mysqli_stmt_execute($stmt);


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



$hashBuscaInicial =
    md5(
        json_encode(
            $filmes,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        )
    );

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>ORION TV - Busca</title>

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
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background-color: #080808;
    min-height: 100vh;
    color: white;
    font-family:
        Arial,
        Helvetica,
        sans-serif;
}

.navbar-orion {
    min-height: 80px;
    padding: 15px 5%;
    background-color: #080808;
    border-bottom: 1px solid #292929;
}

.logo {
    color: #168cff !important;
    font-size: 28px;
    font-weight: bold;
    letter-spacing: 1px;
}

.btn-voltar {
    padding: 9px 18px;
    border-radius: 6px;
}

.pagina {
    max-width: 1500px;
    padding:
        45px
        25px
        70px;
    margin: auto;
}

.cabecalho {
    margin-bottom: 30px;
}

.cabecalho h1 {
    font-size: 30px;
    margin-bottom: 8px;
}

.cabecalho p {
    color: #999;
    margin: 0;
}

.filtros {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 30px;
}

.filtro-busca {
    flex: 1;
    min-width: 240px;
    max-width: 650px;
    height: 46px;
    display: flex;
    align-items: center;
    background-color: #151515;
    border: 1px solid #303030;
    border-radius: 7px;
    padding: 0 14px;
    transition:
        border-color 0.2s,
        box-shadow 0.2s;
}

.filtro-busca:focus-within {
    border-color: #168cff;
    box-shadow:
        0 0 0 2px
        rgba(22, 140, 255, 0.12);
}

.filtro-busca i {
    color: #777;
    font-size: 17px;
    margin-right: 10px;
    flex-shrink: 0;
}

.filtro-busca input {
    width: 100%;
    height: 100%;
    background: transparent;
    border: none;
    outline: none;
    color: white;
    font-size: 14px;
}

.filtro-busca input::placeholder {
    color: #777;
}

.filtro-genero {
    height: 46px;
    min-width: 190px;
    background-color: #151515;
    color: #ccc;
    border: 1px solid #303030;
    border-radius: 7px;
    padding:
        0
        14px;
    outline: none;
    font-size: 14px;
    cursor: pointer;
    transition:
        border-color 0.2s;
}

.filtro-genero:focus {
    border-color: #168cff;
}

.filtro-genero option {
    background-color: #151515;
    color: white;
}

.btn-buscar {
    height: 46px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background-color: #168cff;
    color: white;
    padding:
        0
        18px;
    border-radius: 7px;
    white-space: nowrap;
    cursor: pointer;
    transition:
        background-color 0.2s;
}

.btn-buscar:hover {
    background-color: #006dcc;
    color: white;
}

.btn-limpar {
    height: 46px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    color: #999;
    text-decoration: none;
    padding:
        0
        12px;
    border-radius: 7px;
    white-space: nowrap;
    transition:
        color 0.2s,
        background-color 0.2s;
}

.btn-limpar:hover {
    color: white;
    background-color: #151515;
}

.cabecalho-resultados {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 22px;
}

.cabecalho-resultados h2 {
    font-size: 22px;
    margin: 0;
}

.quantidade-resultados {
    color: #777;
    font-size: 13px;
    white-space: nowrap;
}

.card-filme {
    display: block;
    width: 100%;
    text-decoration: none;
    color: white;
    overflow: hidden;
    transition:
        transform 0.2s,
        color 0.2s;
}

.card-filme:hover {
    transform: translateY(-3px);
    color: #168cff;
}

.card-filme img {
    width: 100%;
    aspect-ratio: 2 / 3;
    object-fit: cover;
    display: block;
    border-radius: 8px;
    background-color: #151515;
    border: 1px solid #202020;
    transition:
        border-color 0.2s;
}

.card-filme:hover img {
    border-color: #168cff;
}

.card-filme h3 {
    font-size: 15px;
    line-height: 1.35;
    margin:
        10px
        0
        0;
    height:
        calc(1.35em * 2);
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    overflow: hidden;
    text-overflow: ellipsis;
    word-break: normal;
    overflow-wrap: normal;
}

.sem-resultados {
    text-align: center;
    padding: 80px 20px;
    color: #888;
    background-color: #111;
    border: 1px solid #292929;
    border-radius: 12px;
}

.sem-resultados i {
    display: block;
    font-size: 45px;
    color: #444;
    margin-bottom: 15px;
}

.sem-resultados strong {
    display: block;
    color: #ccc;
    font-size: 18px;
    margin-bottom: 8px;
}

.sem-resultados p {
    margin: 0;
    color: #777;
}

@media (max-width: 768px) {

    .navbar-orion {
        padding:
            12px
            4%;
    }

    .logo {
        font-size: 23px;
    }

    .btn-voltar {
        padding:
            8px
            12px;
        font-size: 13px;
    }

    .pagina {
        padding:
            30px
            15px
            50px;
    }

    .cabecalho h1 {
        font-size: 25px;
    }

    .filtros {
        align-items: stretch;
    }

    .filtro-busca {
        width: 100%;
        max-width: none;
    }

    .filtro-genero {
        flex: 1;
        min-width: 0;
    }

    .btn-buscar {
        min-width: 100px;
    }

    .btn-limpar {
        padding:
            0
            10px;
    }

    .cabecalho-resultados h2 {
        font-size: 21px;
    }

    .card-filme h3 {
        font-size: 14px;
    }

}

@media (max-width: 480px) {

    .navbar-orion .container-fluid {
        gap: 10px;
    }

    .logo {
        font-size: 20px;
    }

    .btn-voltar {
        font-size: 12px;
        padding:
            7px
            10px;
    }

    .pagina {
        padding:
            25px
            12px
            45px;
    }

    .cabecalho {
        margin-bottom: 25px;
    }

    .cabecalho h1 {
        font-size: 23px;
    }

    .cabecalho p {
        font-size: 14px;
    }

    .filtro-busca {
        flex: 1 1 100%;
        width: 100%;
        min-width: 0;
    }

    .filtro-genero {
        flex: 1;
        min-width: 0;
        width: auto;
        height: 46px;
    }

    .btn-buscar {
        height: 46px;
        padding:
            0
            15px;
    }

    .btn-limpar {
        height: 46px;
        padding:
            0
            8px;
        font-size: 13px;
    }

    .cabecalho-resultados {
        align-items: flex-start;
        flex-direction: column;
        gap: 5px;
        margin-bottom: 18px;
    }

    .cabecalho-resultados h2 {
        font-size: 20px;
    }

    .quantidade-resultados {
        font-size: 12px;
    }

    .card-filme h3 {
        font-size: 13px;
        line-height: 1.3;
        height:
            calc(1.3em * 2);
    }

    .row.g-4 {
        --bs-gutter-x: 1rem;
        --bs-gutter-y: 1.5rem;
    }

}

@media (max-width: 360px) {

    .filtro-genero {
        width: 100%;
        flex: 1 1 100%;
    }

    .btn-buscar {
        flex: 1;
        min-width: 0;
    }

    .btn-limpar {
        flex: 0 0 auto;
    }

    .card-filme h3 {
        font-size: 12px;
    }

}

</style>

</head>

<body>

<nav class="navbar navbar-dark navbar-orion">

    <div class="container-fluid">

        <a
            href="tela_inicial.php"
            class="navbar-brand logo"
        >
            ORION TV
        </a>

        <a
            href="tela_inicial.php"
            class="btn btn-outline-light btn-voltar"
        >

            <i class="bi bi-arrow-left"></i>

            Voltar

        </a>

    </div>

</nav>

<main class="pagina">

<div class="cabecalho">

    <h1>
        Buscar filmes
    </h1>

    <p>
        Encontre filmes e séries por nome ou gênero.
    </p>

</div>

<form
    method="GET"
    action="busca.php"
    class="filtros"
>

    <div class="filtro-busca">

        <i class="bi bi-search"></i>

        <input
            type="text"
            name="pesquisa"
            placeholder="Pesquisar filmes e séries..."
            value="<?php echo htmlspecialchars(
                $pesquisa,
                ENT_QUOTES,
                "UTF-8"
            ); ?>"
        >

    </div>

    <select
        name="genero"
        class="filtro-genero"
    >

        <option value="">
            Todos os gêneros
        </option>

        <?php foreach ($generos as $generoItem) { ?>

            <option
                value="<?php echo htmlspecialchars(
                    $generoItem,
                    ENT_QUOTES,
                    "UTF-8"
                ); ?>"
                <?php echo (
                    $genero === $generoItem
                )
                    ? "selected"
                    : ""; ?>
            >

                <?php

                echo htmlspecialchars(
                    $generoItem,
                    ENT_QUOTES,
                    "UTF-8"
                );

                ?>

            </option>

        <?php } ?>

    </select>

    <button
        type="submit"
        class="btn-buscar border-0"
    >

        <i class="bi bi-search"></i>

        Buscar

    </button>

    <?php if (
        $pesquisa !== ""
        ||
        $genero !== ""
    ) { ?>

        <a
            href="busca.php"
            class="btn-limpar"
        >

            <i class="bi bi-x-circle"></i>

            Limpar filtros

        </a>

    <?php } ?>

</form>

<div class="cabecalho-resultados">

    <h2>
        Resultados da busca
    </h2>

    <?php if (!empty($filmes)) { ?>

        <span class="quantidade-resultados">

            <?php

            echo count($filmes);

            echo count($filmes) === 1
                ? " resultado"
                : " resultados";

            ?>

        </span>

    <?php } ?>

</div>

<div class="row g-4">

    <?php if (!empty($filmes)) { ?>

        <?php foreach ($filmes as $filme) { ?>

            <div
                class="col-6 col-sm-4 col-md-3 col-lg-2"
            >

                <a
                    href="filme.php?id=<?php echo (int) $filme["id_conteudo"]; ?>"
                    class="card-filme"
                >

                    <img
                        src="<?php echo htmlspecialchars(
                            $filme["thumb_conteudo_vertical"],
                            ENT_QUOTES,
                            "UTF-8"
                        ); ?>"
                        alt="<?php echo htmlspecialchars(
                            $filme["titulo_conteudo"],
                            ENT_QUOTES,
                            "UTF-8"
                        ); ?>"
                    >

                    <h3>

                        <?php

                        echo htmlspecialchars(
                            $filme["titulo_conteudo"],
                            ENT_QUOTES,
                            "UTF-8"
                        );

                        ?>

                    </h3>

                </a>

            </div>

        <?php } ?>

    <?php } else { ?>

        <div class="col-12">

            <div class="sem-resultados">

                <i class="bi bi-film"></i>

                <strong>
                    Nenhum filme encontrado
                </strong>

                <?php if ($pesquisa !== "") { ?>

                    <p>
                        Nenhum conteúdo corresponde à pesquisa.
                    </p>

                <?php } elseif ($genero !== "") { ?>

                    <p>
                        Nenhum filme encontrado neste gênero.
                    </p>

                <?php } else { ?>

                    <p>
                        Ainda não existem filmes cadastrados.
                    </p>

                <?php } ?>

            </div>

        </div>

    <?php } ?>

</div>

</main>


<script>

(function () {

    const hashInicial =
        "<?php echo $hashBuscaInicial; ?>";


    const pesquisa =
        <?php echo json_encode(
            $pesquisa,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ); ?>;


    const genero =
        <?php echo json_encode(
            $genero,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ); ?>;


    let verificando = false;


    async function verificarBusca() {

        if (verificando) {
            return;
        }


        verificando = true;


        try {

            const parametros =
                new URLSearchParams();


            parametros.append(
                "pesquisa",
                pesquisa
            );


            parametros.append(
                "genero",
                genero
            );


            parametros.append(
                "_",
                Date.now().toString()
            );


            const resposta =
                await fetch(
                    "php/verificar_busca.php?" +
                    parametros.toString(),
                    {
                        method: "GET",
                        cache: "no-store"
                    }
                );


            if (!resposta.ok) {
                return;
            }


            const dados =
                await resposta.json();


            if (
                dados.sucesso === true
                &&
                dados.hash !== hashInicial
            ) {

                window.location.reload();

            }

        } catch (erro) {

            /*
             * Se a verificação falhar,
             * a busca continua funcionando normalmente.
             */

        } finally {

            verificando = false;

        }

    }


    setInterval(
        verificarBusca,
        3000
    );

})();

</script>

</body>

</html>