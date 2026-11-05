
<?php


require_once(__DIR__ . "/php/conexao.php");
require_once(__DIR__ . "/includes/alerta.php");




$mensagemAlerta = null;



if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    $mensagemAlerta = "Filme não encontrado.";

} else {

    $idFilme = (int) $_GET["id"];



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
            video_conteudo
        FROM conteudo
       WHERE id_conteudo = ?
 
        
    ";


    $stmt = mysqli_prepare($conn, $sql);


    if (!$stmt) {

        $mensagemAlerta = "Não foi possível carregar o filme.";

    } else {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $idFilme
        );


        mysqli_stmt_execute($stmt);


        $resultado = mysqli_stmt_get_result($stmt);


        if (!$resultado || mysqli_num_rows($resultado) === 0) {

            $mensagemAlerta = "Filme não encontrado.";

        } else {

            $filme = mysqli_fetch_assoc($resultado);

        }


        mysqli_stmt_close($stmt);

    }

}



if ($mensagemAlerta === null) {



    if ((int) $filme["classe_etaria_conteudo"] === 0) {

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

            $generosFilme[] = $nomesGeneros[$genero];

        }

    }


    $generoExibicao = !empty($generosFilme)
        ? implode(" • ", $generosFilme)
        : "Não informado";



    $dataInicio = date(
        "d/m/Y",
        strtotime($filme["janela_exib_inicio_conteudo"])
    );


    $dataFim = date(
        "d/m/Y",
        strtotime($filme["janela_exib_fim_conteudo"])
    );



    $duracao = !empty($filme["duracao_conteudo"])
        ? $filme["duracao_conteudo"]
        : "Não informado";

}



$comentarios = [];


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
          AND avaliacao.avaliacao_status = 'Ativo'
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

                $comentarios[] = $comentario;

            }

        }


        mysqli_stmt_close(
            $stmtComentarios
        );

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
            - <?= htmlspecialchars($filme["titulo_conteudo"]) ?>
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


  

    <link
        rel="stylesheet"
        href="css/filme.css"
    >

</head>


<body>


    

    <nav class="navbar navbar-dark navbar-orion">

        <div class="container-fluid">


            <a
                class="navbar-brand logo"
                href="tela_inicial_deslogado.php"
            >

                ORION TV

            </a>


            <a
                href="tela_inicial_deslogado.php"
                class="btn btn-outline-light"
            >

                <i class="bi bi-arrow-left"></i>

                Voltar

            </a>


        </div>

    </nav>



    <?php if ($mensagemAlerta === null): ?>


        

        <section class="banner-filme">


            <img
                src="<?= htmlspecialchars($filme["banner_conteudo_horizontal"]) ?>"
                class="banner-img"
                alt="<?= htmlspecialchars($filme["titulo_conteudo"]) ?>"
            >


            <div class="banner-escuro"></div>


            <div class="container banner-conteudo">


                <span class="badge bg-primary">

                    <?= htmlspecialchars(strtoupper($classificacao)) ?>

                </span>


                <h1 class="titulo-filme">

                    <?= htmlspecialchars($filme["titulo_conteudo"]) ?>

                </h1>


                <p class="informacoes-filme">

                    <?= htmlspecialchars($generoExibicao) ?>

                    •

                    <?= htmlspecialchars($duracao) ?>

                </p>


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
                    >

                        <i class="bi bi-chat-left-text"></i>

                        Comentários

                        <span class="contador-comentarios">

                            <?= count($comentarios) ?>

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
                                    src="<?= htmlspecialchars($filme["thumb_conteudo_vertical"]) ?>"
                                    class="poster-filme"
                                    alt="Poster <?= htmlspecialchars($filme["titulo_conteudo"]) ?>"
                                >

                            </a>


                        </div>



                        

                        <div class="col-lg-9">


                            

                            <div class="card-orion">


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


                            </div>



                            

                            <div class="row mt-4 g-4">


                                <div class="col-md-6">


                                    <div class="card-orion">


                                        <h3>
                                            Elenco
                                        </h3>


                                        <p>

                                            <?= htmlspecialchars(
                                                $filme["elenco_conteudo"]
                                            ) ?>

                                        </p>


                                    </div>


                                </div>



                                <div class="col-md-6">


                                    <div class="card-orion">


                                        <h3>
                                            Diretores
                                        </h3>


                                        <p>

                                            <?= htmlspecialchars(
                                                $filme["diretores_conteudo"]
                                            ) ?>

                                        </p>


                                    </div>


                                </div>


                            </div>



                            

                            <div class="row mt-4 g-4">


                                <div class="col-md-4">


                                    <div class="card-orion">


                                        <h3>
                                            Gênero
                                        </h3>


                                        <p>

                                            <?= htmlspecialchars(
                                                $generoExibicao
                                            ) ?>

                                        </p>


                                    </div>


                                </div>



                                <div class="col-md-4">


                                    <div class="card-orion">


                                        <h3>
                                            Classificação
                                        </h3>


                                        <p>

                                            <?= htmlspecialchars(
                                                $classificacao
                                            ) ?>

                                        </p>


                                    </div>


                                </div>



                                <div class="col-md-4">


                                    <div class="card-orion">


                                        <h3>
                                            Duração
                                        </h3>


                                        <p>

                                            <?= htmlspecialchars(
                                                $duracao
                                            ) ?>

                                        </p>


                                    </div>


                                </div>


                            </div>



                            

                            <div class="row mt-4 g-4">


                                <div class="col-md-6">


                                    <div class="card-orion">


                                        <h3>
                                            No catálogo desde:
                                        </h3>


                                        <p>

                                            <?= htmlspecialchars(
                                                $dataInicio
                                            ) ?>

                                        </p>


                                    </div>


                                </div>



                                <div class="col-md-6">


                                    <div class="card-orion">


                                        <h3>
                                            Disponível até:
                                        </h3>


                                        <p>

                                            <?= htmlspecialchars(
                                                $dataFim
                                            ) ?>

                                        </p>


                                    </div>


                                </div>


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

                            Faça login para deixar um comentário.

                        </p>



                        

                        <?php if (empty($comentarios)): ?>


                            <div class="comentario">


                                <p>

                                    Ainda não há comentários para este filme.

                                </p>


                            </div>


                        <?php else: ?>


                            <?php foreach (
                                $comentarios as $comentario
                            ): ?>


                                <div class="comentario">


                                    <div class="usuario-comentario">


                                        <div class="avatar">

                                            <?= htmlspecialchars(
                                                strtoupper(
                                                    mb_substr(
                                                        $comentario["nome_cliente"],
                                                        0,
                                                        1
                                                    )
                                                )
                                            ) ?>

                                        </div>


                                        <div>

                                            <strong>

                                                <?= htmlspecialchars(
                                                    $comentario["nome_cliente"]
                                                ) ?>

                                            </strong>

                                        </div>


                                    </div>


                                    <p>

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


    <?php endif; ?>



    

    <?php

    if ($mensagemAlerta !== null) {

        mostrarAlerta(
            $mensagemAlerta,
            "window.location.href = 'tela_inicial_deslogado.php'"
        );

    }

    ?>




    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>



   

    <?php if ($mensagemAlerta === null): ?>

    <script>

        const video = document.getElementById("videoFilme");

        const modalPlayer = document.getElementById("modalPlayer");


      

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

    </script>

    <?php endif; ?>


</body>

</html>

