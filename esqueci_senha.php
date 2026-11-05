<?php

session_start();

if (isset($_GET["reiniciar"]) && $_GET["reiniciar"] === "1") {
    unset(
        $_SESSION["email_recuperacao"],
        $_SESSION["etapa_recuperacao"],
        $_SESSION["codigo_validado"],
        $_SESSION["id_recuperacao_validado"]
    );
}

require_once(__DIR__ . "/php/conexao.php");
require_once(__DIR__ . "/includes/alerta.php");

$etapa = $_SESSION["etapa_recuperacao"] ?? "email";
$mensagemSucesso = "";
$mensagemErro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $acao = $_POST["acao"] ?? "";

    if ($acao === "enviar_codigo") {

        $email = trim($_POST["email"] ?? "");

        if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensagemErro = "Digite um e-mail válido.";
            $etapa = "email";
        } else {

            $sql = "SELECT id_usuario FROM usuario WHERE email = ? LIMIT 1";
            $stmt = mysqli_prepare($conn, $sql);

            if (!$stmt) {
                $mensagemErro = "Não foi possível verificar o e-mail.";
                $etapa = "email";
            } else {

                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);

                $emailExiste = mysqli_stmt_num_rows($stmt) > 0;

                mysqli_stmt_close($stmt);

                if (!$emailExiste) {
                    $mensagemErro = "Este e-mail não está cadastrado.";
                    $etapa = "email";
                } else {

                    $codigo = (string) random_int(100000, 999999);
                    $codigoHash = hash("sha256", $codigo);

                    $sql = "INSERT INTO recuperacao_senha
                                (email, codigo_hash, criado_em)
                            VALUES (?, ?, NOW())";

                    $stmt = mysqli_prepare($conn, $sql);

                    if (!$stmt) {
                        $mensagemErro = "Não foi possível preparar a recuperação de senha.";
                        $etapa = "email";
                    } else {

                        mysqli_stmt_bind_param($stmt, "ss", $email, $codigoHash);
                        $registroSalvo = mysqli_stmt_execute($stmt);
                        $idRecuperacao = $registroSalvo ? mysqli_insert_id($conn) : 0;
                        mysqli_stmt_close($stmt);

                        if (!$registroSalvo || $idRecuperacao <= 0) {
                            $mensagemErro = "Não foi possível registrar o código.";
                            $etapa = "email";
                        } else {

                            $envioEmailSucesso = false;
                            require(__DIR__ . "/email/testar_email.php");

                            if (empty($envioEmailSucesso)) {
                                $mensagemErro = "Não foi possível enviar o e-mail. Tente novamente.";
                                $etapa = "email";
                            } else {

                                $_SESSION["email_recuperacao"] = $email;
                                $_SESSION["etapa_recuperacao"] = "codigo";

                                unset(
                                    $_SESSION["codigo_validado"],
                                    $_SESSION["id_recuperacao_validado"]
                                );

                                $etapa = "codigo";
                                $mensagemSucesso = "Código enviado! Verifique sua caixa de entrada.";
                            }
                        }
                    }
                }
            }
        }
    }

    elseif ($acao === "confirmar_codigo") {

        $email = $_SESSION["email_recuperacao"] ?? "";
        $codigo = trim($_POST["codigo"] ?? "");
        $etapa = "codigo";

        if ($email === "") {

            unset(
                $_SESSION["email_recuperacao"],
                $_SESSION["etapa_recuperacao"],
                $_SESSION["codigo_validado"],
                $_SESSION["id_recuperacao_validado"]
            );

            $etapa = "email";
            $mensagemErro = "A sessão de recuperação expirou. Informe seu e-mail novamente.";

        } elseif (!preg_match('/^[0-9]{6}$/', $codigo)) {

            $mensagemErro = "Digite o código de seis dígitos recebido por e-mail.";

        } else {

            $sql = "SELECT id_recuperacao, codigo_hash,
                           (criado_em >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)) AS dentro_prazo
                    FROM recuperacao_senha
                    WHERE email = ?
                    ORDER BY id_recuperacao DESC
                    LIMIT 1";

            $stmt = mysqli_prepare($conn, $sql);

            if (!$stmt) {
                $mensagemErro = "Não foi possível verificar o código.";
            } else {

                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $idRecuperacao, $hashArmazenado, $dentroPrazo);
                $registroEncontrado = mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);

                $codigoHashInformado = hash("sha256", $codigo);

                if (
                    !$registroEncontrado ||
                    !$dentroPrazo ||
                    !hash_equals($hashArmazenado, $codigoHashInformado)
                ) {
                    $mensagemErro = "Código incorreto. Solicite um novo código.";
                } else {
                    $_SESSION["codigo_validado"] = $hashArmazenado;
                    $_SESSION["id_recuperacao_validado"] = (int) $idRecuperacao;
                    $_SESSION["etapa_recuperacao"] = "senha";

                    $etapa = "senha";
                    $mensagemSucesso = "Código confirmado! Agora crie sua nova senha.";
                }
            }
        }
    }

    elseif ($acao === "alterar_senha") {

        $email = $_SESSION["email_recuperacao"] ?? "";
        $hashValidado = $_SESSION["codigo_validado"] ?? "";
        $idRecuperacaoValidado = (int) ($_SESSION["id_recuperacao_validado"] ?? 0);
        $senha = $_POST["senha"] ?? "";
        $confirmarSenha = $_POST["confirmar_senha"] ?? "";
        $etapa = "senha";

        if ($email === "" || $hashValidado === "" || $idRecuperacaoValidado <= 0) {

            unset(
                $_SESSION["email_recuperacao"],
                $_SESSION["etapa_recuperacao"],
                $_SESSION["codigo_validado"],
                $_SESSION["id_recuperacao_validado"]
            );

            $etapa = "email";
            $mensagemErro = "A validação expirou. Inicie a recuperação novamente.";

        } elseif ($senha !== $confirmarSenha) {

            $mensagemErro = "As senhas não coincidem.";

        } else {

            $sql = "SELECT id_recuperacao
                    FROM recuperacao_senha
                    WHERE email = ?
                    ORDER BY id_recuperacao DESC
                    LIMIT 1";

            $stmt = mysqli_prepare($conn, $sql);

            if (!$stmt) {
                $mensagemErro = "Não foi possível validar a recuperação.";
            } else {

                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $idRecuperacaoMaisRecente);
                $registroEncontrado = mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);

                $solicitacaoMaisRecenteValida =
                    $registroEncontrado &&
                    (int) $idRecuperacaoMaisRecente === $idRecuperacaoValidado;

                if (!$solicitacaoMaisRecenteValida) {
                    unset(
                        $_SESSION["email_recuperacao"],
                        $_SESSION["etapa_recuperacao"],
                        $_SESSION["codigo_validado"],
                        $_SESSION["id_recuperacao_validado"]
                    );

                    $etapa = "email";
                    $mensagemErro = "Foi solicitada uma recuperação mais recente. Inicie o processo novamente.";
                } else {

                    $sql = "SELECT id_recuperacao
                            FROM recuperacao_senha
                            WHERE id_recuperacao = ?
                              AND email = ?
                              AND codigo_hash = ?
                              AND criado_em >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
                            LIMIT 1";

                    $stmt = mysqli_prepare($conn, $sql);

                    if (!$stmt) {
                        $mensagemErro = "Não foi possível validar a recuperação.";
                    } else {

                        mysqli_stmt_bind_param(
                            $stmt,
                            "iss",
                            $idRecuperacaoValidado,
                            $email,
                            $hashValidado
                        );

                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_store_result($stmt);
                        $recuperacaoValida = mysqli_stmt_num_rows($stmt) === 1;
                        mysqli_stmt_close($stmt);

                        if (!$recuperacaoValida) {
                            unset(
                                $_SESSION["email_recuperacao"],
                                $_SESSION["etapa_recuperacao"],
                                $_SESSION["codigo_validado"],
                                $_SESSION["id_recuperacao_validado"]
                            );

                            $etapa = "email";
                            $mensagemErro = "O código expirou ou foi substituído. Inicie a recuperação novamente.";
                        } else {

                            $novaSenhaHash = password_hash($senha, PASSWORD_DEFAULT);
                            $sql = "UPDATE usuario SET senha = ? WHERE email = ?";
                            $stmt = mysqli_prepare($conn, $sql);

                            if (!$stmt) {
                                $mensagemErro = "Não foi possível preparar a alteração da senha.";
                            } else {

                                mysqli_stmt_bind_param($stmt, "ss", $novaSenhaHash, $email);
                                $senhaAtualizada = mysqli_stmt_execute($stmt);
                                $usuariosAlterados = mysqli_stmt_affected_rows($stmt);
                                mysqli_stmt_close($stmt);

                                if (!$senhaAtualizada || $usuariosAlterados < 1) {
                                    $mensagemErro = "Não foi possível alterar a senha.";
                                } else {

                                    $hashInvalido = hash("sha256", bin2hex(random_bytes(32)));
                                    $sql = "UPDATE recuperacao_senha
                                            SET codigo_hash = ?
                                            WHERE id_recuperacao = ?";
                                    $stmt = mysqli_prepare($conn, $sql);

                                    if ($stmt) {
                                        mysqli_stmt_bind_param($stmt, "si", $hashInvalido, $idRecuperacaoValidado);
                                        mysqli_stmt_execute($stmt);
                                        mysqli_stmt_close($stmt);
                                    }

                                    unset(
                                        $_SESSION["email_recuperacao"],
                                        $_SESSION["etapa_recuperacao"],
                                        $_SESSION["codigo_validado"],
                                        $_SESSION["id_recuperacao_validado"]
                                    );

                                    $etapa = "concluido";
                                    $mensagemSucesso = "Senha alterada com sucesso! Você já pode entrar com sua nova senha.";
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

if ($etapa !== "concluido" && isset($_SESSION["email_recuperacao"])) {
    $_SESSION["etapa_recuperacao"] = $etapa;
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Orion TV - Recuperação de senha</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/esqueci_senha.css">
<style>
    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    html {
        min-height: 100%;
        width: 100%;
    }

    body {
        min-height: 100vh;
        width: 100%;
        margin: 0;
        overflow-x: hidden;
    }

    .pagina {
        width: 100%;
        min-height: 100vh;
        padding: clamp(16px, 4vw, 40px);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .card-senha {
        width: 100%;
        max-width: 460px;
        min-width: 0;
        margin: auto;
        padding: clamp(20px, 5vw, 36px);
        overflow-wrap: anywhere;
    }

    .card-senha h1,
    .card-senha .descricao,
    .card-senha p {
        max-width: 100%;
        overflow-wrap: anywhere;
    }

    .card-senha form,
    .card-senha .campo {
        width: 100%;
        min-width: 0;
    }

    .card-senha input,
    .card-senha .form-control,
    .card-senha button,
    .card-senha .btn-enviar {
        max-width: 100%;
        min-width: 0;
    }

    .card-senha input,
    .card-senha .form-control {
        width: 100%;
    }

    .card-senha button,
    .card-senha .btn-enviar {
        display: block;
        width: 100%;
        white-space: normal;
        overflow-wrap: anywhere;
    }

    .card-senha .voltar {
        display: inline-block;
        max-width: 100%;
        overflow-wrap: anywhere;
    }

    @media (max-width: 600px) {
        .pagina {
            align-items: center;
            padding: 20px 14px;
        }

        .card-senha {
            max-width: 100%;
            padding: 24px 18px;
            border-radius: 14px;
        }

        .card-senha h1 {
            font-size: clamp(1.4rem, 6vw, 1.8rem);
            line-height: 1.25;
        }

        .card-senha .descricao {
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .card-senha input,
        .card-senha .form-control {
            font-size: 16px;
        }

        .card-senha button,
        .card-senha .btn-enviar {
            min-height: 44px;
        }
    }

    @media (max-width: 360px) {
        .pagina {
            padding: 14px 10px;
        }

        .card-senha {
            padding: 20px 14px;
        }

        .card-senha h1 {
            font-size: 1.35rem;
        }
    }

    @media (max-height: 500px) and (min-width: 601px) {
        .pagina {
            align-items: flex-start;
        }
    }
</style>
</head>
<body>
<main class="pagina">
    <div class="card-senha">
        <div class="logo">ORION TV</div>

        <?php if ($etapa === "concluido"): ?>
            <h1>Senha alterada!</h1>
            <p class="descricao">
                Sua senha foi alterada com sucesso.
                Agora você pode entrar na sua conta com a nova senha.
            </p>

            <?php if ($mensagemSucesso !== ""): ?>
                <p style="color:#168cff; margin-bottom:16px;">
                    <?= htmlspecialchars($mensagemSucesso, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <a href="index.php" class="btn-enviar" style="display:block;text-align:center;text-decoration:none;">
                Voltar para o login
            </a>
        <?php else: ?>
            <h1>
                <?php if ($etapa === "email"): ?>
                    Esqueceu sua senha?
                <?php elseif ($etapa === "codigo"): ?>
                    Confirme seu código
                <?php else: ?>
                    Crie uma nova senha
                <?php endif; ?>
            </h1>

            <p class="descricao">
                <?php if ($etapa === "email"): ?>
                    Digite o e-mail cadastrado na sua conta para receber um código de recuperação.
                <?php elseif ($etapa === "codigo"): ?>
                    Digite o código de seis dígitos que enviamos para seu e-mail.
                <?php else: ?>
                    Digite sua nova senha e confirme para concluir a recuperação.
                <?php endif; ?>
            </p>

            <?php if ($mensagemSucesso !== ""): ?>
                <p style="color:#168cff; margin-bottom:16px;">
                    <?= htmlspecialchars($mensagemSucesso, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <?php if ($mensagemErro !== ""): ?>
                <p style="color:#ff6464; margin-bottom:16px;">
                    <?= htmlspecialchars($mensagemErro, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <form id="formularioRecuperacao" method="POST" action="esqueci_senha.php">
                <?php if ($etapa === "email"): ?>
                    <input type="hidden" name="acao" value="enviar_codigo">
                    <div class="campo">
                        <label for="email">E-mail</label>
                        <input
                            type="email"
                            id="email"
                            class="form-control"
                            placeholder="Digite seu e-mail"
                            required
                            name="email"
                            autocomplete="email"
                            value="<?= htmlspecialchars($_POST["email"] ?? "", ENT_QUOTES, 'UTF-8') ?>"
                        >
                    </div>
                    <button type="submit" class="btn-enviar" id="btnEnviarCodigo">Enviar código</button>
                <?php elseif ($etapa === "codigo"): ?>
                    <input type="hidden" name="acao" value="confirmar_codigo">
                    <div class="campo">
                        <label for="codigo">Código</label>
                        <input
                            type="text"
                            id="codigo"
                            class="form-control"
                            placeholder="Digite os 6 dígitos"
                            required
                            name="codigo"
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            minlength="6"
                            maxlength="6"
                            autocomplete="one-time-code"
                            autofocus
                        >
                    </div>
                    <button type="submit" class="btn-enviar">Confirmar código</button>
                <?php elseif ($etapa === "senha"): ?>
                    <input type="hidden" name="acao" value="alterar_senha">
                    <div class="campo">
                        <label for="senha">Nova senha</label>
                        <input
                            type="password"
                            id="senha"
                            class="form-control"
                            placeholder="Digite a nova senha"
                            required
                            name="senha"
                            autocomplete="new-password"
                        >
                    </div>
                    <div class="campo">
                        <label for="confirmar_senha">Confirmar nova senha</label>
                        <input
                            type="password"
                            id="confirmar_senha"
                            class="form-control"
                            placeholder="Digite a senha novamente"
                            required
                            name="confirmar_senha"
                            autocomplete="new-password"
                        >
                    </div>
                    <button type="submit" class="btn-enviar">Alterar senha</button>
                <?php endif; ?>
            </form>

            <?php if ($etapa !== "email"): ?>
                <a href="esqueci_senha.php?reiniciar=1" class="voltar">Recomeçar recuperação</a>
            <?php endif; ?>
            <a href="index.php" class="voltar">← Voltar para o login</a>
        <?php endif; ?>
    </div>
</main>

<script>
    const formulario = document.getElementById("formularioRecuperacao");

    if (formulario) {
        formulario.addEventListener("submit", function(event) {
            const botao = formulario.querySelector('button[type="submit"]');

            if (botao && botao.disabled) {
                event.preventDefault();
            }
        });
    }

    const campoCodigo = document.getElementById("codigo");

    if (campoCodigo) {
        campoCodigo.addEventListener("input", function() {
            this.value = this.value.replace(/[^0-9]/g, "").slice(0, 6);
        });
    }

    const campoSenha = document.getElementById("senha");
    const campoConfirmacao = document.getElementById("confirmar_senha");

    if (campoSenha && campoConfirmacao && formulario) {
        formulario.addEventListener("submit", function(event) {
            if (campoSenha.value !== campoConfirmacao.value) {
                event.preventDefault();
                campoConfirmacao.setCustomValidity("As senhas não coincidem.");
                campoConfirmacao.reportValidity();
            } else {
                campoConfirmacao.setCustomValidity("");
            }
        });

        campoConfirmacao.addEventListener("input", function() {
            this.setCustomValidity("");
        });
    }
</script>
</body>
</html>
