<?php

require_once(__DIR__ . "/includes/proteger_cliente.php");

?>


<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Perfis</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        /* ==============================
           PERFIS
        ============================== */

        .perfil {
            text-align: center;
            color: white;
            text-decoration: none;

            transition: 0.3s;
        }

        .perfil img {
            transition: transform 0.3s ease;
        }

        .perfil:hover img {
            transform: scale(1.1);
        }


        /* ==============================
           CELULAR
        ============================== */

        @media (max-width: 600px) {

            .perfis-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 30px;
            }

            .perfil {
                width: 100%;
                max-width: 200px;
            }

        }

    </style>

</head>


<body class="bg-dark text-white">


    <h1 class="text-center mt-5">
        Quem está assistindo?
    </h1>


    <div class="container mt-5">

        <div class="row justify-content-center perfis-container">


            <!-- PERFIL 1 -->

            <a href="tela_inicial.php"
               class="col-md-2 perfil text-white text-decoration-none">

                <img
                    src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTKCXfQyj_oWlMoJCRRjNxf8TcORUptm1DHM8qi8Hp8NJAqZq637DRkjiU&s=10"
                    class="rounded-circle"
                    width="120"
                    height="120"
                    alt="Felicio">

                <h4 class="mt-3">
                    Felicio
                </h4>

            </a>


            <!-- PERFIL 2 -->

            <div class="col-md-2 perfil">

                <img
                    src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQMdm9sVDoz_p2ZZKuBi6gGNoFUUE7u7JnD1IBaYEF67y3anEoi"
                    class="rounded-circle"
                    width="120"
                    height="120"
                    alt="Rafael">

                <h4 class="mt-3">
                    Rafael
                </h4>

            </div>


            <!-- PERFIL 3 -->

            <div class="col-md-2 perfil">

                <img
                    src="https://aseguirniteroi.com.br/wp-content/uploads/2023/11/caricatura-vini-jr-por-dan.jpg"
                    class="rounded-circle"
                    width="120"
                    height="120"
                    alt="Vini Jr">

                <h4 class="mt-3">
                    Vini Jr
                </h4>

            </div>


        </div>

    </div>


</body>

</html>