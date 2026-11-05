<!DOCTYPE html>

<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Streaming</title>

  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #080808;
            min-height: 100vh;
            color: white;
            font-family: Arial, Helvetica, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background-color: #111111;
            border: 1px solid #292929;
            border-radius: 12px;
            color: white;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.4);
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: #168cff;
            letter-spacing: 1px;
        }

        .btn-login {
            background-color: #168cff;
            border: none;
            color: white;
            border-radius: 6px;
            transition: 0.3s;
        }

        .btn-login:hover {
            background-color: #006dcc;
            color: white;
        }

        .form-control {
            background-color: #1b1b1b;
            color: white;
            border: 1px solid #333;
            border-radius: 6px;
        }

        .form-control:focus {
            background-color: #1b1b1b;
            color: white;
            box-shadow: 0 0 0 2px rgba(22, 140, 255, 0.15);
            border: 1px solid #168cff;
        }

        .form-control::placeholder {
            color: #777;
        }

        a {
            color: #168cff;
            text-decoration: none;
            transition: 0.3s;
        }

        a:hover {
            color: #5db2ff;
            text-decoration: underline;
        }

        .continuar-sem-cadastro {
            display: inline-block;
            margin-top: 2px;
            font-size: 14px;
            color: #999;
            transition: 0.3s;
        }

        .continuar-sem-cadastro:hover {
            color: #168cff;
            text-decoration: none;
            transform: translateX(2px);
        }
    </style>


</head>

<body>


    <div class="container">

        <div class="row justify-content-center align-items-center vh-100">

            <div class="col-11 col-sm-8 col-md-6 col-lg-4">

                <div class="card login-card shadow-lg p-4">

                    <div class="text-center mb-4">

                        <div class="logo text-primary">
                            ORION TV
                        </div>

                        <p class="text-white">
                            Faça login para continuar
                        </p>

                    </div>

                    <form method="POST" action="php/login.php">

                        <div class="mb-3">

                            <label class="form-label">
                                Email
                            </label>

                            <input type="email" class="form-control" placeholder="Digite seu email" required
                                name="email" pattern="[^@\s]+@[^@\s]+\.com">

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Senha
                            </label>

                            <input type="password" class="form-control" placeholder="Digite sua senha" required
                                name="senha">

                        </div>

                        <div class="d-flex justify-content-end mb-4">

                            <a href="esqueci_senha.php" class="text-primary">
                                Esqueceu a senha?
                            </a>

                        </div>

                        <button class="btn btn-primary w-100 text-white text-decoration-none" type="submit"
                            name="logar">
                            Entrar
                        </button>

                    </form>

                    <div class="text-center mt-4 text-primary">

                        <p>
                            Não possui conta?
                            <a href="pagamento.php" class="text-primary">
                                Cadastre-se
                            </a>
                        </p>

                    </div>

                    <div class="text-start mt-2">

                        <a href="tela_inicial_deslogado.php" class="continuar-sem-cadastro">
                            ← Continuar sem se cadastrar
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>