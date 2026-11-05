<?php

require_once(__DIR__ . "/includes/proteger_cliente.php");
require_once(__DIR__ . "/php/conexao.php");
require_once(__DIR__ . "/includes/alerta.php");
require_once(__DIR__ . "/includes/filtro_comentarios.php");


$id_usuario = $_SESSION["id_usuario"] ?? null;

if (!$id_usuario) {
    header("Location: index.php");
    exit;
}



if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["salvar_alteracoes"])) {

    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";
    $confirmar_senha = $_POST["confirmar_senha"] ?? "";

    if ($nome === "" || $email === "") {

        mostrarAlerta("Preencha todos os campos obrigatórios.");
        exit;
    }



    if (comentarioPossuiTermoProibido($nome)) {

        mostrarAlerta(
            "O nome informado contém termos que não são permitidos."
        );

        exit;
    }



    $sql_email = "
        SELECT id_usuario
        FROM usuario
        WHERE email = ?
        AND id_usuario <> ?
    ";

    $stmt_email = mysqli_prepare($conn, $sql_email);

    if (!$stmt_email) {

        mostrarAlerta("Erro ao verificar o e-mail.");
        exit;
    }

    mysqli_stmt_bind_param(
        $stmt_email,
        "si",
        $email,
        $id_usuario
    );

    mysqli_stmt_execute($stmt_email);

    $resultado_email = mysqli_stmt_get_result($stmt_email);

    if (mysqli_num_rows($resultado_email) > 0) {

        mysqli_stmt_close($stmt_email);

        mostrarAlerta("Este e-mail já está sendo usado por outra conta.");
        exit;
    }

    mysqli_stmt_close($stmt_email);



    $alterar_senha = false;

    if ($senha !== "" || $confirmar_senha !== "") {

        if ($senha === "" || $confirmar_senha === "") {

            mostrarAlerta("Preencha os dois campos de senha.");
            exit;
        }

        if ($senha !== $confirmar_senha) {

            mostrarAlerta("As senhas não coincidem.");
            exit;
        }

        $alterar_senha = true;
    }



    $sql_cliente = "
        SELECT FK_cliente_id_cliente
        FROM cliente
        WHERE FK_cliente_id_usuario = ?
    ";

    $stmt_cliente = mysqli_prepare($conn, $sql_cliente);

    if (!$stmt_cliente) {

        mostrarAlerta("Erro ao consultar a conta.");
        exit;
    }

    mysqli_stmt_bind_param(
        $stmt_cliente,
        "i",
        $id_usuario
    );

    mysqli_stmt_execute($stmt_cliente);

    $resultado_cliente = mysqli_stmt_get_result($stmt_cliente);
    $cliente = mysqli_fetch_assoc($resultado_cliente);

    mysqli_stmt_close($stmt_cliente);


    if (!$cliente) {

        mostrarAlerta("Cliente não encontrado.");
        exit;
    }

    $id_cliente = $cliente["FK_cliente_id_cliente"];



    mysqli_begin_transaction($conn);

    try {


        $sql_nome = "
            UPDATE cliente
            SET nome_cliente = ?
            WHERE FK_cliente_id_cliente = ?
        ";

        $stmt_nome = mysqli_prepare($conn, $sql_nome);

        if (!$stmt_nome) {
            throw new Exception();
        }

        mysqli_stmt_bind_param(
            $stmt_nome,
            "si",
            $nome,
            $id_cliente
        );

        if (!mysqli_stmt_execute($stmt_nome)) {

            mysqli_stmt_close($stmt_nome);
            throw new Exception();
        }

        mysqli_stmt_close($stmt_nome);



        $sql_email_update = "
            UPDATE usuario
            SET email = ?
            WHERE id_usuario = ?
        ";

        $stmt_email_update = mysqli_prepare($conn, $sql_email_update);

        if (!$stmt_email_update) {
            throw new Exception();
        }

        mysqli_stmt_bind_param(
            $stmt_email_update,
            "si",
            $email,
            $id_usuario
        );

        if (!mysqli_stmt_execute($stmt_email_update)) {

            mysqli_stmt_close($stmt_email_update);
            throw new Exception();
        }

        mysqli_stmt_close($stmt_email_update);



        if ($alterar_senha) {

            $senha_hash = password_hash(
                $senha,
                PASSWORD_DEFAULT
            );

            if ($senha_hash === false) {
                throw new Exception();
            }


            $sql_senha = "
                UPDATE usuario
                SET senha = ?
                WHERE id_usuario = ?
            ";

            $stmt_senha = mysqli_prepare($conn, $sql_senha);

            if (!$stmt_senha) {
                throw new Exception();
            }

            mysqli_stmt_bind_param(
                $stmt_senha,
                "si",
                $senha_hash,
                $id_usuario
            );

            if (!mysqli_stmt_execute($stmt_senha)) {

                mysqli_stmt_close($stmt_senha);
                throw new Exception();
            }

            mysqli_stmt_close($stmt_senha);
        }



        mysqli_commit($conn);



        if ($alterar_senha) {

            mostrarAlerta(
                "Alterações e senha salvas com sucesso.",
                "location.href = 'conta.php'"
            );

        } else {

            mostrarAlerta(
                "Alterações salvas com sucesso.",
                "location.href = 'conta.php'"
            );
        }

        exit;

    } catch (Exception $e) {

        mysqli_rollback($conn);

        mostrarAlerta(
            "Não foi possível salvar as alterações."
        );

        exit;
    }
}



if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["excluir_conta"])) {


    $sql_cliente = "
        SELECT FK_cliente_id_cliente
        FROM cliente
        WHERE FK_cliente_id_usuario = ?
    ";

    $stmt_cliente = mysqli_prepare($conn, $sql_cliente);

    if (!$stmt_cliente) {

        mostrarAlerta(
            "Erro ao localizar a conta.",
            "location.href = 'conta.php'"
        );

        exit;
    }

    mysqli_stmt_bind_param(
        $stmt_cliente,
        "i",
        $id_usuario
    );

    mysqli_stmt_execute($stmt_cliente);

    $resultado_cliente = mysqli_stmt_get_result($stmt_cliente);
    $cliente = mysqli_fetch_assoc($resultado_cliente);

    mysqli_stmt_close($stmt_cliente);


    if (!$cliente) {

        mostrarAlerta(
            "Cliente não encontrado.",
            "location.href = 'conta.php'"
        );

        exit;
    }

    $id_cliente = $cliente["FK_cliente_id_cliente"];



    mysqli_begin_transaction($conn);

    try {


        $sql_inativar = "
            UPDATE cliente
            SET status_conta_cliente = 'Inativo'
            WHERE FK_cliente_id_cliente = ?
        ";

        $stmt_inativar = mysqli_prepare($conn, $sql_inativar);

        if (!$stmt_inativar) {
            throw new Exception();
        }

        mysqli_stmt_bind_param(
            $stmt_inativar,
            "i",
            $id_cliente
        );

        if (!mysqli_stmt_execute($stmt_inativar)) {
            throw new Exception();
        }

        mysqli_stmt_close($stmt_inativar);



        $sql_assinatura = "
            UPDATE assinatura
            SET status_assinatura = 'Inativa'
            WHERE FK_assinatura_id_cliente = ?
        ";

        $stmt_assinatura = mysqli_prepare($conn, $sql_assinatura);

        if (!$stmt_assinatura) {
            throw new Exception();
        }

        mysqli_stmt_bind_param(
            $stmt_assinatura,
            "i",
            $id_cliente
        );

        if (!mysqli_stmt_execute($stmt_assinatura)) {
            throw new Exception();
        }

        mysqli_stmt_close($stmt_assinatura);



        mysqli_commit($conn);



        session_unset();
        session_destroy();

        mysqli_close($conn);

        header("Location: index.php");
        exit;

    } catch (Exception $e) {

        mysqli_rollback($conn);

        mysqli_close($conn);

        mostrarAlerta(
            "Não foi possível cancelar a conta.",
            "location.href = 'conta.php'"
        );

        exit;
    }
}



$sql = "
    SELECT
        c.nome_cliente,
        u.email,
        a.tipo_plano_assinatura,
        a.data_fim_assinatura,
        a.status_assinatura
    FROM cliente AS c
    INNER JOIN usuario AS u
        ON u.id_usuario = c.FK_cliente_id_usuario
    LEFT JOIN assinatura AS a
        ON a.FK_assinatura_id_cliente = c.FK_cliente_id_cliente
    WHERE c.FK_cliente_id_usuario = ?
    ORDER BY a.id_assinatura DESC
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    mostrarAlerta("Erro ao consultar os dados da conta.");
    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_usuario
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$dados = mysqli_fetch_assoc($resultado);

mysqli_stmt_close($stmt);

mysqli_close($conn);


if (!$dados) {

    session_unset();
    session_destroy();

    header("Location: index.php");
    exit;
}


$nome_cliente = htmlspecialchars(
    $dados["nome_cliente"],
    ENT_QUOTES,
    "UTF-8"
);

$email_cliente = htmlspecialchars(
    $dados["email"],
    ENT_QUOTES,
    "UTF-8"
);



if ($dados["tipo_plano_assinatura"] !== null) {

    $plano_assinatura = htmlspecialchars(
        $dados["tipo_plano_assinatura"],
        ENT_QUOTES,
        "UTF-8"
    );

    if (!empty($dados["data_fim_assinatura"])) {

        $data_fim = new DateTime(
            $dados["data_fim_assinatura"]
        );

        $data_cobranca = $data_fim->format("d/m/Y");

    } else {

        $data_cobranca = "—";
    }

} else {

    $plano_assinatura = "Sem assinatura";
    $data_cobranca = "—";
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

<title>Orion TV - Perfil</title>



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
    href="css/conta.css"
>

</head>

<body>



<header class="topo">


<div class="logo">
    ORION TV
</div>

<a
    href="tela_inicial.php"
    class="btn-voltar"
>
    Voltar
</a>


</header>



<main class="perfil-container">


<div class="perfil-card">


    

    <form
        method="POST"
        class="informacoes"
    >


       

        <div class="campo">

            <label for="nome">
                Nome
            </label>

            <input
                type="text"
                id="nome"
                name="nome"
                value="<?php echo $nome_cliente; ?>"
            >

        </div>


       
        
    

        <div class="campo">

            <label for="email">
                E-mail
            </label>

            <input
                type="email"
                id="email"
                name="email"
                value="<?php echo $email_cliente; ?>"
            >

        </div>


        

        <div class="campo">

            <label for="senha">
                Nova senha
            </label>

            <input
                type="password"
                id="senha"
                name="senha"
                autocomplete="new-password"
            >

        </div>


        

        <div class="campo">

            <label for="confirmar_senha">
                Confirmar nova senha
            </label>

            <input
                type="password"
                id="confirmar_senha"
                name="confirmar_senha"
                placeholder="Digite a nova senha novamente"
                autocomplete="new-password"
            >

        </div>


        

        <button
            type="submit"
            name="salvar_alteracoes"
            class="btn-salvar"
        >

            <i class="bi bi-check-lg"></i>

            Salvar alterações

        </button>

    </form>




    <div class="secao">

        <h2>

            <i class="bi bi-credit-card"></i>

            Assinatura

        </h2>


        <div class="assinatura">


            <div class="informacao-assinatura">

                <span class="titulo-plano">
                    Plano atual
                </span>

                <strong>
                    <?php echo $plano_assinatura; ?>
                </strong>

            </div>


            <div class="informacao-assinatura">

                <span class="titulo-plano">
                    Próxima cobrança
                </span>

                <strong>
                    <?php echo $data_cobranca; ?>
                </strong>

            </div>

        </div>

    </div>


   
    <div class="secao conta">

        <h2>

            <i class="bi bi-person-circle"></i>

            Conta

        </h2>


        <a
            href="/php/logout.php"
            style="text-decoration: none;"
        >

            <button
                type="button"
                class="btn-sair"
            >

                <i class="bi bi-box-arrow-right"></i>

                Sair da conta

            </button>

        </a>


  

        <button
            class="btn-cancelar"
            data-bs-toggle="modal"
            data-bs-target="#modalCancelar"
            type="button"
        >

            Excluir Conta

        </button>

    </div>

</div>


</main>



<div
    class="modal fade"
    id="modalCancelar"
    tabindex="-1"
    aria-hidden="true"
>


<div class="modal-dialog modal-dialog-centered">

    <div class="modal-content modal-orion">


        <div class="modal-header">

            <h5 class="modal-title">
                Cancelar assinatura?
            </h5>

            <button
                type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="modal"
            ></button>

        </div>


        <div class="modal-body">

            <p>
                Tem certeza que deseja excluir sua conta?
            </p>

        </div>


        <div class="modal-footer">

            <button
                type="button"
                class="btn-voltar-modal"
                data-bs-dismiss="modal"
            >
                Voltar
            </button>


            <form method="POST">

                <button
                    type="submit"
                    name="excluir_conta"
                    class="btn-confirmar-cancelamento"
                >

                    Confirmar cancelamento

                </button>

            </form>

        </div>

    </div>

</div>


</div>



<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>
