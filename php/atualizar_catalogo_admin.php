<?php

require_once(__DIR__ . "/conexao.php");




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




if (!empty($filmes)):

    $filmeBanner = $filmes[0];

?>

  

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
                    href="./filme_admin.php?id=<?= (int) $filme["id_conteudo"] ?>"
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


    <!-- ========================================================
    CATÁLOGO
    ======================================================== -->

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
                        href="./filme_admin.php?id=<?= (int) $filme["id_conteudo"] ?>"
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