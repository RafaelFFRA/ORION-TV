<?php

require_once(__DIR__ . "/includes/proteger_cliente.php");
require_once(__DIR__ . "/php/conexao.php");
require_once(__DIR__ . "/includes/alerta.php");
require_once(__DIR__ . "/php/manutencao_data_conteudo.php"); 

$filmes = [];

$sql = "
    SELECT
        id_conteudo,
        titulo_conteudo,
        genero_conteudo,
        thumb_conteudo_vertical,
        banner_conteudo_horizontal    
    FROM conteudo
    WHERE conteudo_status = 'Ativo'  
    AND bloqueio_admin = 'Não'
    ORDER BY id_conteudo DESC
";

$resultado = mysqli_query($conn, $sql);

if ($resultado) {

    while ($filme = mysqli_fetch_assoc($resultado)) {

        $filmes[] = $filme;

    }

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

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>ORION TV</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
>

<link rel="stylesheet" href="css/tela_inicial2.css">

<style>

    .logout-btn {
        color: #ff3b3b !important;
        font-size: 22px;
    }

    .logout-btn:hover {
        color: #ff0000 !important;
    }

    .lista-filmes a {
        display: block !important;
        flex-shrink: 0;
        position: relative !important;
        z-index: 20 !important;
        pointer-events: auto !important;
        cursor: pointer !important;
    }

    .lista-filmes a img {
        display: block;
        cursor: pointer !important;
        pointer-events: auto !important;
    }

    .conteudo-recente {
        position: relative;
        z-index: 20;
    }

    .conteudo-recente .lista-filmes {
        display: flex;
        gap: 15px;
        overflow-x: auto;
    }

    .conteudo-recente .lista-filmes a {
        display: block !important;
        flex-shrink: 0;
        position: relative !important;
        z-index: 30 !important;
        pointer-events: auto !important;
        cursor: pointer !important;
    }

    .conteudo-recente .lista-filmes img {
        display: block;
        pointer-events: auto !important;
        cursor: pointer !important;
    }

    .sem-filmes {
        color: #aaa;
        margin-bottom: 0;
    }

    .banner-info {
        max-width: 70%;
    }

    .banner-info h1 {
        overflow-wrap: break-word;
        word-wrap: break-word;
        white-space: normal;
    }

    @media (max-width: 768px) {

        .banner-info {
            width: 88%;
            max-width: 88%;
        }

        .banner-info h1 {
            font-size: clamp(1.5rem, 7vw, 2.8rem);
            line-height: 1.05;
            margin-bottom: 8px;

            white-space: normal;
            overflow-wrap: anywhere;
            word-break: normal;
        }

        .banner-info p {
            font-size: 0.85rem;
            line-height: 1.2;
        }

        .conteudo-recente h3,
        .container-fluid h3 {
            font-size: 1.35rem;
            line-height: 1.2;
        }

        .conteudo-recente .lista-filmes,
        .lista-filmes {
            gap: 10px;
        }

    }

    @media (max-width: 480px) {

        .banner-info {
            width: 92%;
            max-width: 92%;
        }

        .banner-info h1 {
            font-size: clamp(1.35rem, 7.5vw, 2.2rem);
            line-height: 1.05;
            margin-bottom: 6px;
        }

        .banner-info p {
            font-size: 0.78rem;
        }

        .conteudo-recente h3,
        .container-fluid h3 {
            font-size: 1.2rem;
        }

    }

    @media (max-width: 360px) {

        .banner-info {
            width: 94%;
            max-width: 94%;
        }

        .banner-info h1 {
            font-size: clamp(1.25rem, 7.5vw, 1.9rem);
            line-height: 1.03;
        }

        .banner-info p {
            font-size: 0.72rem;
        }

    }

</style>

</head>

<body>

<nav class="navbar navbar-expand navbar-dark bg-dark">

<a
    class="navbar-brand fw-bold fs-3"
    href="#"
>

    ORION TV

</a>

<div class="ms-auto d-flex align-items-center">

    <?php if (isset($_SESSION["tipo_usuario"]) && $_SESSION["tipo_usuario"] === "Administrador"): ?>

        <a
            href="escolha_admin.php"
            class="btn btn-dark me-3"
            title="Voltar"
        >
            <i class="bi bi-arrow-left"></i>
        </a>

    <?php endif; ?>

    <a
        href="busca.php"
        class="btn btn-dark me-3"
    >
        <i class="bi bi-search"></i>
    </a>

    <a
        href="conta.php"
        class="btn btn-dark me-3"
    >
        <i class="bi bi-person-circle"></i>
    </a>

    <a
        href="/php/logout.php"
        class="logout-btn"
        title="Sair"
    >
        <i class="bi bi-box-arrow-right"></i>
    </a>

</div>

</nav>

<div id="catalogo">

    <?php if (!empty($filmes)): ?>

    <?php $filmeBanner = $filmes[0]; ?>

    <div class="banner">

        <img
            src="<?= htmlspecialchars($filmeBanner["banner_conteudo_horizontal"]) ?>"
            class="banner-img"
            alt="<?= htmlspecialchars($filmeBanner["titulo_conteudo"]) ?>"
        >

        <div class="banner-info">

            <h1>
                <?= htmlspecialchars(strtoupper($filmeBanner["titulo_conteudo"])) ?>
            </h1>

            <p>
                Conteúdo adicionado recentemente.
            </p>

        </div>

    </div>

    <div class="container-fluid mt-5 conteudo-recente">

        <h3 class="text-white mb-3">
            Conteúdo adicionado recentemente
        </h3>

        <div class="lista-filmes">

            <?php

            $filmesRecentes = array_slice($filmes, 0, 6);

            ?>

            <?php foreach ($filmesRecentes as $filme): ?>

                <a
                    href="./filme.php?id=<?= (int) $filme["id_conteudo"] ?>"
                    style="display: block; position: relative; z-index: 9999; pointer-events: auto; cursor: pointer;"
                >

                    <img
                        src="<?= htmlspecialchars($filme["thumb_conteudo_vertical"]) ?>"
                        alt="<?= htmlspecialchars($filme["titulo_conteudo"]) ?>"
                    >

                </a>

            <?php endforeach; ?>

        </div>

    </div>

    <?php foreach ($nomesGeneros as $chaveGenero => $nomeGenero): ?>

    <?php

    $filmesGenero = [];

    foreach ($filmes as $filme) {

        $generos = explode(
            ",",
            strtolower($filme["genero_conteudo"])
        );

        foreach ($generos as $genero) {

            if (trim($genero) === $chaveGenero) {

                $filmesGenero[] = $filme;

                break;

            }

        }

    }

    ?>

    <?php if (!empty($filmesGenero)): ?>

        <div class="container-fluid mt-5">

            <h3 class="text-white mb-3">

                <?= htmlspecialchars($nomeGenero) ?>

            </h3>

            <div class="lista-filmes">

                <?php foreach ($filmesGenero as $filme): ?>

                    <a
                        href="./filme.php?id=<?= (int) $filme["id_conteudo"] ?>"
                        style="display: block; position: relative; z-index: 9999; pointer-events: auto; cursor: pointer;"
                    >

                        <img
                            src="<?= htmlspecialchars($filme["thumb_conteudo_vertical"]) ?>"
                            alt="<?= htmlspecialchars($filme["titulo_conteudo"]) ?>"
                        >

                    </a>

                <?php endforeach; ?>

            </div>

        </div>

    <?php endif; ?>

    <?php endforeach; ?>

    <?php else: ?>

    <div class="container-fluid mt-5">

        <h3 class="text-white mb-3">
            Catálogo
        </h3>

        <p class="sem-filmes">
            Nenhum filme cadastrado ainda.
        </p>

    </div>

    <?php endif; ?>

</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

<script>

    function atualizarControles(lista) {

        const controle = lista.parentElement;

        if (!controle || !controle.classList.contains("controle-filmes")) {
            return;
        }

        const setaEsquerda = controle.querySelector(".seta-filmes.esquerda");
        const setaDireita = controle.querySelector(".seta-filmes.direita");
        const barra = controle.querySelector(".barra-filmes");

        if (!setaEsquerda || !setaDireita || !barra) {
            return;
        }

        const maxScroll = Math.max(0, lista.scrollWidth - lista.clientWidth);

        if (maxScroll <= 1) {

            setaEsquerda.classList.remove("visivel");
            setaDireita.classList.remove("visivel");
            barra.classList.remove("visivel");

            return;
        }

        const posicaoAtual = Math.max(
            0,
            Math.min(lista.scrollLeft, maxScroll)
        );

        barra.max = String(Math.round(maxScroll));
        barra.value = String(Math.round(posicaoAtual));
        barra.classList.add("visivel");

        setaEsquerda.classList.toggle("visivel", posicaoAtual > 1);
        setaDireita.classList.toggle("visivel", posicaoAtual < maxScroll - 1);
    }


    function irParaFilme(lista, direcao) {

        const itens = Array.from(lista.querySelectorAll("a"));

        if (itens.length === 0) {
            return;
        }

        const maxScroll = Math.max(0, lista.scrollWidth - lista.clientWidth);
        const baseLista = lista.getBoundingClientRect().left;

        const posicaoAtual =
            (lista._destinoFilmes !== null && lista._destinoFilmes !== undefined)
                ? lista._destinoFilmes
                : lista.scrollLeft;

        const posicoes = itens.map(function (item) {
            return item.getBoundingClientRect().left - baseLista + lista.scrollLeft;
        });

        let destino;

        if (direcao > 0) {

            destino = posicoes.find(function (posicao) {
                return posicao > posicaoAtual + 2;
            });

            if (destino === undefined) {
                destino = maxScroll;
            }

        } else {

            destino = 0;

            for (let i = posicoes.length - 1; i >= 0; i--) {

                if (posicoes[i] < posicaoAtual - 2) {
                    destino = posicoes[i];
                    break;
                }

            }

        }

        destino = Math.max(0, Math.min(destino, maxScroll));

        lista._destinoFilmes = destino;

        clearTimeout(lista._temporizadorFilmes);

        lista._temporizadorFilmes = setTimeout(function () {
            lista._destinoFilmes = null;
        }, 500);

        lista.scrollTo({
            left: destino,
            behavior: "smooth"
        });
    }

    function atualizarTodosControles() {

        document
            .querySelectorAll("#catalogo .lista-filmes")
            .forEach(atualizarControles);

    }

    function configurarControlesFilmes() {

        const listas = document.querySelectorAll("#catalogo .lista-filmes");

        listas.forEach(function (lista) {

            let controle = lista.parentElement;

            if (!controle || !controle.classList.contains("controle-filmes")) {

                controle = document.createElement("div");
                controle.className = "controle-filmes";

                lista.parentNode.insertBefore(controle, lista);
                controle.appendChild(lista);
            }

            let setaEsquerda = controle.querySelector(".seta-filmes.esquerda");
            let setaDireita = controle.querySelector(".seta-filmes.direita");
            let barra = controle.querySelector(".barra-filmes");

            if (!setaEsquerda) {

                setaEsquerda = document.createElement("button");
                setaEsquerda.type = "button";
                setaEsquerda.className = "seta-filmes esquerda";
                setaEsquerda.setAttribute(
                    "aria-label",
                    "Mover filmes para a esquerda"
                );
                setaEsquerda.innerHTML = '<i class="bi bi-chevron-left"></i>';

                controle.appendChild(setaEsquerda);

                setaEsquerda.addEventListener("click", function () {
                    irParaFilme(lista, -1);
                });
            }

            if (!setaDireita) {

                setaDireita = document.createElement("button");
                setaDireita.type = "button";
                setaDireita.className = "seta-filmes direita";
                setaDireita.setAttribute(
                    "aria-label",
                    "Mover filmes para a direita"
                );
                setaDireita.innerHTML = '<i class="bi bi-chevron-right"></i>';

                controle.appendChild(setaDireita);

                setaDireita.addEventListener("click", function () {
                    irParaFilme(lista, 1);
                });
            }

            if (!barra) {

                barra = document.createElement("input");
                barra.type = "range";
                barra.className = "barra-filmes";
                barra.min = "0";
                barra.step = "1";
                barra.value = "0";
                barra.setAttribute(
                    "aria-label",
                    "Movimentar filmes horizontalmente"
                );

                controle.appendChild(barra);

                barra.addEventListener("input", function () {
                    lista._destinoFilmes = null;
                    lista.scrollLeft = Number(this.value);
                });
            }

            if (lista.dataset.controlesFilmes !== "true") {

                lista.dataset.controlesFilmes = "true";

                lista.addEventListener("scroll", function () {
                    atualizarControles(lista);
                }, {
                    passive: true
                });
            }

            atualizarControles(lista);
        });

        requestAnimationFrame(atualizarTodosControles);

        setTimeout(atualizarTodosControles, 100);
    }

  
    let ultimoHtml = null;

    const catalogoInicial = document.getElementById("catalogo");

    if (catalogoInicial) {
        ultimoHtml = catalogoInicial.innerHTML.trim();
    }

    configurarControlesFilmes();

    window.addEventListener("resize", function () {
        configurarControlesFilmes();
    });

    window.addEventListener("load", function () {
        configurarControlesFilmes();
    });

    let atualizacaoEmAndamento = false;

    function atualizarCatalogo() {

        if (atualizacaoEmAndamento) {
            return;
        }

        atualizacaoEmAndamento = true;

        fetch("php/atualizar_catalogo.php?tempo=" + Date.now(), {
            cache: "no-store"
        })
        .then(resposta => {

            if (!resposta.ok) {
                throw new Error("Erro ao atualizar catálogo.");
            }

            return resposta.text();

        })
        .then(html => {

            const catalogo = document.getElementById("catalogo");

            if (!catalogo || html.trim() === "") {
                return;
            }

            const modelo = document.createElement("template");

            modelo.innerHTML = html.trim();

            const novoHtml = modelo.innerHTML.trim();

         
            if (novoHtml === ultimoHtml) {
                return;
            }

            ultimoHtml = novoHtml;

            const posicoesScroll = Array.from(
                catalogo.querySelectorAll(".lista-filmes")
            ).map(lista => lista.scrollLeft);

            catalogo.innerHTML = novoHtml;

            configurarControlesFilmes();

            const novasListas = catalogo.querySelectorAll(".lista-filmes");

            novasListas.forEach((lista, indice) => {

                if (posicoesScroll[indice] !== undefined) {
                    lista.scrollLeft = posicoesScroll[indice];
                }

            });

            configurarControlesFilmes();

        })
        .catch(erro => {

            console.log(
                "Não foi possível atualizar o catálogo:",
                erro
            );

        })
        .finally(() => {

            atualizacaoEmAndamento = false;

        });

    }

    setInterval(atualizarCatalogo, 3000);

</script>

</body>

</html>