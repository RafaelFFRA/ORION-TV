<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ORION TV - Cadastro</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #141414, #1f1f1f);
            min-height: 100vh;
            color: white;
        }

        .cadastro-card {
            background: #222;
            border: none;
            border-radius: 15px;
            color: white;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: #0d6efd;
        }

        .titulo-secao {
            color: #0d6efd;
            font-size: 1.2rem;
            font-weight: bold;
            border-bottom: 1px solid #444;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }

        .form-control,
        .form-select {
            background: #333;
            color: white;
            border: none;
        }

        .form-control:focus,
        .form-select:focus {
            background: #333;
            color: white;
            box-shadow: none;
            border: 1px solid #0d6efd;
        }

        .form-control::placeholder {
            color: #aaa;
        }

        .form-label {
            color: #ddd;
        }
    </style>

</head>



<body>





    <div class="container py-5">

        <div class="row justify-content-center">

            <div class="col-12 col-md-10 col-lg-8">

                <div class="card cadastro-card shadow-lg p-4 p-md-5">


                

                    <div class="text-center mb-4">

                        <div class="logo">
                            ORION TV
                        </div>

                        <p class="text-secondary">
                            Crie sua conta
                        </p>

                    </div>


                 

                    <form action="php/cadastro.php" method="POST">


                      

                        <div class="titulo-secao">
                            Dados pessoais
                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Nome
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                placeholder="Digite seu nome"
                                required
                                name="nome"
                                >
                               

                        </div>


                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    CPF
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    placeholder="Digite seu CPF"
                                    required
                                    name="cpf"
                                    maxlenght = "14"
                                    id="cpf">

                            </div>


                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Data de nascimento
                                </label>

                                <input
                                    type="date"
                                    class="form-control"
                                    required
                                    name="aniversario"
                                    maxlenght="8">

                            </div>

                        </div>


                     

                        <div class="titulo-secao mt-4">
                            Dados da conta
                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Email
                            </label>

                            <input
                                type="email"
                                class="form-control"
                                placeholder="Digite seu email"
                                required
                                name="email">

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Senha
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                placeholder="Digite sua senha"
                                required
                                name="senha">

                        </div>




                        <button
                            type="submit"
                            class="btn btn-primary w-100 py-2"
                            name="cadastrar">

                            Cadastrar

                        </button>
 <a
                            href="index.php"
                            class="btn btn-outline-secondary w-100 mt-2">
                            Voltar
                        </a>

                    </form>

                </div>

            </div>

        </div>

    </div>


    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
    </script>

<script>
    const cpf = document.getElementById("cpf");

    cpf.addEventListener("input", function () {

        let valor = cpf.value.replace(/\D/g, "");

        if (valor.length > 11) {
            valor = valor.substring(0, 11);
        }

        valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
        valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
        valor = valor.replace(/(\d{3})(\d{1,2})$/, "$1-$2");

        cpf.value = valor;

    });
</script>
</body>

</html>