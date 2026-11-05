<?php

require_once(__DIR__ . "/includes/proteger_admin.php");
require_once "php/conexao.php";


$idUsuario = $_SESSION["id_usuario"];


$sql = "
    SELECT nome_admin
    FROM administrador
    WHERE FK_administrador_id_usuario = ?
";


$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUsuario
);

mysqli_stmt_execute($stmt);


$resultado = mysqli_stmt_get_result($stmt);

$administrador = mysqli_fetch_assoc($resultado);


$nomeAdministrador = $administrador["nome_admin"] ?? "Administrador";

?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Escolher acesso - ORION TV</title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
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
            font-family: Arial, Helvetica, sans-serif;
        }


        .acesso-card {
            width: 100%;
            max-width: 650px;

            background-color: #111111;

            border: 1px solid #292929;

            border-radius: 12px;

            color: white;

            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.4);

            padding: 40px;
        }


        .logo {
            font-size: 2rem;
            font-weight: bold;

            color: #168cff;

            letter-spacing: 1px;
        }


        .acesso-card h1 {
            font-size: 26px;
            font-weight: bold;

            margin-bottom: 10px;
        }


        .descricao {
            color: #999;

            font-size: 15px;

            margin-bottom: 35px;
        }


        .opcao-acesso {
            display: flex;

            align-items: center;

            gap: 18px;

            width: 100%;

            background-color: #1b1b1b;

            border: 1px solid #333;

            border-radius: 8px;

            padding: 20px;

            color: white;

            text-decoration: none;

            transition: 0.3s;

            margin-bottom: 15px;
        }


        .opcao-acesso:hover {
            border-color: #168cff;

            background-color: #202020;

            color: white;

            transform: translateY(-2px);
        }


        .icone-acesso {
            width: 50px;
            height: 50px;

            display: flex;

            align-items: center;
            justify-content: center;

            flex-shrink: 0;

            background-color: rgba(22, 140, 255, 0.12);

            border-radius: 8px;

            color: #168cff;

            font-size: 22px;
        }


        .texto-acesso {
            flex: 1;
        }


        .texto-acesso h2 {
            font-size: 18px;

            margin: 0 0 5px;

            color: white;
        }


        .texto-acesso p {
            margin: 0;

            color: #999;

            font-size: 14px;
        }


        .seta {
            color: #777;

            font-size: 20px;

            transition: 0.3s;
        }


        .opcao-acesso:hover .seta {
            color: #168cff;

            transform: translateX(4px);
        }


        @media (max-width: 576px) {

            .acesso-card {
                padding: 30px 20px;
            }


            .logo {
                font-size: 1.7rem;
            }


            .acesso-card h1 {
                font-size: 22px;
            }


            .descricao {
                font-size: 14px;

                margin-bottom: 25px;
            }


            .opcao-acesso {
                padding: 16px;

                gap: 14px;
            }


            .icone-acesso {
                width: 45px;
                height: 45px;

                font-size: 19px;
            }


            .texto-acesso h2 {
                font-size: 16px;
            }


            .texto-acesso p {
                font-size: 13px;
            }

        }

    </style>

</head>


<body>


    <div class="container">

        <div class="row justify-content-center align-items-center min-vh-100 py-4">

            <div class="col-11 col-sm-10 col-md-8 col-lg-7">


                <div class="acesso-card mx-auto">


                    <div class="text-center mb-4">

                        <div class="logo">
                            ORION TV
                        </div>

                    </div>


                    <div class="text-center">

                        <h1>

                            Olá, <?= htmlspecialchars($nomeAdministrador) ?>!

                        </h1>


                        <p class="descricao">

                            Qual tipo de conta deseja acessar?

                        </p>

                    </div>


                    <a
                        href="enviar_filme.php"
                        class="opcao-acesso"
                    >

                        <div class="icone-acesso">

                            <i class="bi bi-film"></i>

                        </div>


                        <div class="texto-acesso">

                            <h2>
                                Distribuidor
                            </h2>


                            <p>
                                Acessar área de envio de filmes.
                            </p>

                        </div>


                        <i class="bi bi-arrow-right seta"></i>

                    </a>


                    <a
                        href="tela_inicial.php"
                        class="opcao-acesso"
                    >

                        <div class="icone-acesso">

                            <i class="bi bi-person"></i>

                        </div>


                        <div class="texto-acesso">

                            <h2>
                                Cliente
                            </h2>


                            <p>
                                Acessar a plataforma como cliente.
                            </p>

                        </div>


                        <i class="bi bi-arrow-right seta"></i>

                    </a>


                    <a
                        href="tela_inicial_admin.php"
                        class="opcao-acesso"
                    >

                        <div class="icone-acesso">

                            <i class="bi bi-shield-lock"></i>

                        </div>


                        <div class="texto-acesso">

                            <h2>
                                Administrador
                            </h2>


                            <p>
                                Acessar o painel administrativo.
                            </p>

                        </div>


                        <i class="bi bi-arrow-right seta"></i>

                    </a>


                </div>

            </div>

        </div>

    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>