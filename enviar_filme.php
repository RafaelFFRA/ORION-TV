<?php

require_once(__DIR__ . "/includes/proteger_distribuidor.php");
require_once(__DIR__ . "/includes/alerta.php");



$mensagemAlerta = null;

if (isset($_GET["sucesso"]) && $_GET["sucesso"] === "1") {

    $mensagemAlerta = "Filme cadastrado com sucesso.";

}

if (isset($_GET["erro"])) {

    $mensagens = [

        "preparar_distribuidor" => "Não foi possível identificar o distribuidor.",

        "distribuidor_nao_encontrado" => "O usuário não está cadastrado como distribuidor.",

        "campos" => "Preencha todos os campos obrigatórios.",

        "duracao" => "A duração do filme não pode ter mais de 20 caracteres.",

        "generos" => "Selecione de 1 a 3 gêneros.",

        "classificacao" => "Classificação etária inválida.",

        "datas" => "A data final não pode ser anterior à data inicial.",

        "arquivos" => "É necessário enviar a capa, o banner e o vídeo.",

        "thumb" => "Ocorreu um erro ao enviar a capa vertical.",

        "banner" => "Ocorreu um erro ao enviar o banner horizontal.",

        "video" => "Ocorreu um erro ao enviar o vídeo.",

        "pasta_thumb" => "Não foi possível acessar a pasta das capas verticais.",

        "pasta_banner" => "Não foi possível acessar a pasta dos banners horizontais.",

        "pasta_video" => "Não foi possível acessar a pasta dos vídeos.",

        "formato_thumb" => "A capa vertical precisa ser JPG, PNG ou WEBP.",

        "formato_banner" => "O banner horizontal precisa ser JPG, PNG ou WEBP.",

        "formato_video" => "Formato de vídeo não permitido.",

        "salvar_thumb" => "Não foi possível salvar a capa vertical.",

        "salvar_banner" => "Não foi possível salvar o banner horizontal.",

        "salvar_video" => "Não foi possível salvar o vídeo.",

        "preparar_cadastro" => "Não foi possível preparar o cadastro do filme.",

        "cadastro" => "Não foi possível cadastrar o filme."
    ];

    $codigoErro = $_GET["erro"];

    if (isset($mensagens[$codigoErro])) {

        $mensagemAlerta = $mensagens[$codigoErro];

    } else {

        $mensagemAlerta = "Ocorreu um erro ao cadastrar o filme.";

    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Orion TV - Enviar Filme</title>

   
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  
    <link rel="stylesheet" href="css/enviar_filme.css">

    <style>
        

        .generos {

            display: grid;

            grid-template-columns: repeat(2, 1fr);

            gap: 10px;

            margin-top: 10px;

        }


        .genero-option {

            display: flex;

            align-items: center;

            gap: 10px;

            width: 100%;

            min-height: 45px;

            padding: 10px 12px;

            border: 1px solid #ccc;

            border-radius: 8px;

            background: transparent;

            cursor: pointer;

            transition:
                border-color 0.2s ease,
                background-color 0.2s ease;

            margin: 0;

        }


        

        .genero-option:hover {

            border-color: #006dcc;

            background-color: rgba(0, 109, 204, 0.04);

        }


        

        .genero-option input {

            appearance: none;

            -webkit-appearance: none;

            width: 18px;

            height: 18px;

            min-width: 18px;

            margin: 0;

            border: 2px solid #aaa;

            border-radius: 4px;

            background: transparent;

            cursor: pointer;

            position: relative;

            transition:
                background-color 0.2s ease,
                border-color 0.2s ease;

        }


        

        .genero-option input:checked {

            background-color: #006dcc;

            border-color: #006dcc;

        }


        

        .genero-option input:checked::after {

            content: "";

            position: absolute;

            left: 4px;

            top: 0px;

            width: 5px;

            height: 10px;

            border: solid #fff;

            border-width: 0 2px 2px 0;

            transform: rotate(45deg);

        }


        

        .genero-option span {

            font-size: 16px;

            color: inherit;

            cursor: pointer;

            line-height: 1.2;

        }


        

        .genero-option:has(input:checked) {

            border-color: #006dcc;

            background-color: rgba(0, 109, 204, 0.04);

        }


        

        .genero-option:has(input:disabled) {

            opacity: 0.5;

            cursor: not-allowed;

        }


        .genero-option:has(input:disabled) span {

            cursor: not-allowed;

        }


        

        .genero-info {

            display: block;

            margin-top: 8px;

            color: #777;

            font-size: 13px;

        }


        

        @media (max-width: 500px) {

            .generos {

                grid-template-columns: 1fr;

            }

        }
    </style>

</head>


<body>


    

    <header class="topo">

        <div class="logo">
            ORION TV
        </div>


        <?php if (isset($_SESSION["tipo_usuario"]) && $_SESSION["tipo_usuario"] === "Administrador"): ?>

<a href="gerenciar_conteudos_administrador.php" class="btn-voltar">
    Sair
</a>

<?php else: ?>

<a href="escolha_distribuidor.php" class="btn-voltar">
    Sair
</a>

<?php endif; ?>

    </header>



    

    <main class="container formulario-container">

        <div class="formulario-card">


            <h1>
                Enviar filme
            </h1>


            <p class="descricao">
                Preencha as informações do filme e envie os arquivos necessários.
            </p>



            

            <form action="php/cadastrar_filme.php" method="POST" enctype="multipart/form-data" id="formFilme">


                

                <div class="campo">

                    <label for="titulo">
                        Título
                    </label>

                    <input type="text" id="titulo" name="titulo" class="form-control"
                        placeholder="Digite o título do filme" required>

                </div>



                

                <div class="campo">

                    <label for="sinopse">
                        Sinopse
                    </label>

                    <textarea id="sinopse" name="sinopse" class="form-control" placeholder="Digite a sinopse do filme"
                        rows="5" required></textarea>

                </div>



                

                <div class="campo">

                    <label for="elenco">
                        Elenco
                    </label>

                    <input type="text" id="elenco" name="elenco" class="form-control"
                        placeholder="Ex: Ator 1, Ator 2, Ator 3" required>

                </div>



                

                <div class="campo">

                    <label for="diretores">
                        Diretores
                    </label>

                    <input type="text" id="diretores" name="diretores" class="form-control"
                        placeholder="Digite o(s) diretor(es)" required>

                </div>



                

                <div class="campo">

                    <label>
                        Gênero
                    </label>

                    <div class="generos">

                        <label class="genero-option">

                            <input type="checkbox" name="generos[]" value="acao">

                            <span>
                                Ação
                            </span>

                        </label>


                        <label class="genero-option">

                            <input type="checkbox" name="generos[]" value="aventura">

                            <span>
                                Aventura
                            </span>

                        </label>


                        <label class="genero-option">

                            <input type="checkbox" name="generos[]" value="comedia">

                            <span>
                                Comédia
                            </span>

                        </label>


                        <label class="genero-option">

                            <input type="checkbox" name="generos[]" value="drama">

                            <span>
                                Drama
                            </span>

                        </label>


                        <label class="genero-option">

                            <input type="checkbox" name="generos[]" value="ficcao">

                            <span>
                                Ficção científica
                            </span>

                        </label>


                        <label class="genero-option">

                            <input type="checkbox" name="generos[]" value="terror">

                            <span>
                                Terror
                            </span>

                        </label>


                        <label class="genero-option">

                            <input type="checkbox" name="generos[]" value="romance">

                            <span>
                                Romance
                            </span>

                        </label>


                        <label class="genero-option">

                            <input type="checkbox" name="generos[]" value="suspense">

                            <span>
                                Suspense
                            </span>

                        </label>

                    </div>

                    <small class="genero-info">
                        Selecione de 1 a 3 gêneros.
                    </small>

                </div>



                

                <div class="campo">

                    <label for="classe_etaria">
                        Classe etária
                    </label>

                    <select id="classe_etaria" name="classe_etaria" class="form-select" required>

                        <option value="" selected disabled>
                            Selecione a classificação
                        </option>


                        <option value="livre">
                            Livre
                        </option>


                        <option value="10">
                            10 anos
                        </option>


                        <option value="12">
                            12 anos
                        </option>


                        <option value="14">
                            14 anos
                        </option>


                        <option value="16">
                            16 anos
                        </option>


                       

                    </select>

                </div>

                

                <div class="campo">

                    <label for="duracao">
                        Duração
                    </label>

                    <input type="text" id="duracao" name="duracao" class="form-control" placeholder="Ex: 1h48min"
                        maxlength="20" required>

                    <small>
                        Informe a duração do filme. Exemplo: 1h48min.
                    </small>

                </div>



                

                <div class="campo">

                    <label for="janela_inicial">
                        Janela de exibição inicial
                    </label>

                    <input type="date" id="janela_inicial" name="janela_inicial" class="form-control" required>

                </div>



                

                <div class="campo">

                    <label for="janela_final">
                        Janela de exibição final
                    </label>

                    <input type="date" id="janela_final" name="janela_final" class="form-control" required>

                </div>



                

                <div class="campo">

                    <label for="thumb">
                        Capa do filme (vertical)
                    </label>


                    <div class="upload-box">

                        <i class="bi bi-image"></i>


                        <span>
                            Selecione a capa vertical do filme
                        </span>


                        <input type="file" id="thumb" name="thumb" accept="image/*" required>

                    </div>


                    <small>
                        Envie uma imagem vertical. Formatos recomendados:
                        JPG, JPEG ou PNG.
                    </small>

                </div>



                

                <div class="campo">

                    <label for="banner">
                        Banner do filme (horizontal)
                    </label>


                    <div class="upload-box">

                        <i class="bi bi-image"></i>


                        <span>
                            Selecione o banner horizontal do filme
                        </span>


                        <input type="file" id="banner" name="banner" accept="image/*" required>

                    </div>


                    <small>
                        Envie uma imagem horizontal. Formatos recomendados:
                        JPG, JPEG ou PNG.
                    </small>

                </div>



                

                <div class="campo">

                    <label for="video">
                        Vídeo do filme
                    </label>


                    <div class="upload-box">

                        <i class="bi bi-camera-reels"></i>


                        <span>
                            Selecione o vídeo do filme
                        </span>


                        <input type="file" id="video" name="video" accept="video/*" required>

                    </div>


                    <small>
                        Selecione o arquivo de vídeo do filme.
                    </small>

                </div>



             

                <button type="submit" class="btn-enviar">

                    <i class="bi bi-cloud-arrow-up"></i>

                    Enviar filme

                </button>


            </form>

        </div>

    </main>



    

    <?php

    if ($mensagemAlerta !== null) {

        mostrarAlerta($mensagemAlerta);

    }

    ?>


    

    <script>

        const checkboxesGenero = document.querySelectorAll(
            'input[name="generos[]"]'
        );

        const formFilme = document.getElementById("formFilme");


        checkboxesGenero.forEach(function (checkbox) {

            checkbox.addEventListener("change", function () {

                const selecionados = document.querySelectorAll(
                    'input[name="generos[]"]:checked'
                );

                if (selecionados.length >= 3) {

                    checkboxesGenero.forEach(function (outroCheckbox) {

                        if (!outroCheckbox.checked) {

                            outroCheckbox.disabled = true;

                        }

                    });

                } else {

                    checkboxesGenero.forEach(function (outroCheckbox) {

                        outroCheckbox.disabled = false;

                    });

                }

            });

        });


        

        formFilme.addEventListener("submit", function (evento) {

            const selecionados = document.querySelectorAll(
                'input[name="generos[]"]:checked'
            );

            if (selecionados.length < 1) {

                evento.preventDefault();

                const primeiraCheckbox = checkboxesGenero[0];

                primeiraCheckbox.setCustomValidity(
                    "Selecione pelo menos um gênero."
                );

                primeiraCheckbox.reportValidity();

                setTimeout(function () {

                    primeiraCheckbox.setCustomValidity("");

                }, 100);

            }

        });


        // Limpa todas as caixas de texto do formulário depois que o filme
        // é cadastrado com sucesso (o cadastro volta para esta página com
        // ?sucesso=1). Se ocorrer algum erro, nada é limpo.

        const envioConcluido = new URLSearchParams(
            window.location.search
        ).get("sucesso") === "1";

        const chaveEnvioConcluido = "orion_filme_enviado";

        function limparFormularioFilme() {

            formFilme.reset();

            formFilme.querySelectorAll(
                'input[type="text"], input[type="date"], input[type="file"], textarea'
            ).forEach(function (campo) {

                campo.value = "";

            });

            checkboxesGenero.forEach(function (checkbox) {

                checkbox.checked = false;

                checkbox.disabled = false;

            });

            document.getElementById("classe_etaria").selectedIndex = 0;

        }

        if (envioConcluido) {

            try {

                sessionStorage.setItem(chaveEnvioConcluido, "1");

            } catch (erro) {}

            limparFormularioFilme();

        }

        window.addEventListener("pageshow", function () {

            let marcado = false;

            try {

                marcado = sessionStorage.getItem(chaveEnvioConcluido) === "1";

            } catch (erro) {}

            if (envioConcluido || marcado) {

                limparFormularioFilme();

            }

            if (!envioConcluido) {

                try {

                    sessionStorage.removeItem(chaveEnvioConcluido);

                } catch (erro) {}

            }

        });

    </script>


</body>

</html>
