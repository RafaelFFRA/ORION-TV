<?php

require_once(__DIR__ . "/php/conexao.php");
require_once(__DIR__ . "/includes/proteger_admin.php");

date_default_timezone_set("America/Sao_Paulo");

$busca = trim($_GET["busca"] ?? "");
$filtro = $_GET["status"] ?? "";

$filtrosPermitidos = [
    "",
    "ativa",
    "atrasada",
    "suspensa"
];

if (!in_array($filtro, $filtrosPermitidos, true)) {
    $filtro = "";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $acao = $_POST["acao"] ?? "";

    $idAssinatura = filter_input(
        INPUT_POST,
        "id_assinatura",
        FILTER_VALIDATE_INT
    );

    if (
        !$idAssinatura ||
        !in_array(
            $acao,
            [
                "suspender",
                "reativar",
                "suspender_conta",
                "reativar_conta"
            ],
            true
        )
    ) {

        header("Location: assinaturas_admin.php?erro=acao_invalida");
        exit;
    }

    mysqli_begin_transaction($conn);

    try {

        $sql = "
            SELECT
                a.id_assinatura,
                a.FK_assinatura_id_cliente,
                c.FK_cliente_id_usuario
            FROM assinatura AS a
            INNER JOIN cliente AS c
                ON c.FK_cliente_id_cliente =
                   a.FK_assinatura_id_cliente
            WHERE a.id_assinatura = ?
        ";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            throw new Exception(
                "Erro ao buscar a assinatura."
            );
        }

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $idAssinatura
        );

        mysqli_stmt_execute($stmt);

        $resultado =
            mysqli_stmt_get_result($stmt);

        $assinatura =
            mysqli_fetch_assoc($resultado);

        mysqli_stmt_close($stmt);

        if (!$assinatura) {

            throw new Exception(
                "Assinatura não encontrada."
            );
        }

        $idCliente =
            (int) $assinatura["FK_assinatura_id_cliente"];

        if ($acao === "suspender") {

            $sql = "
                UPDATE assinatura
                SET status_assinatura = 'Inativa'
                WHERE id_assinatura = ?
            ";

            $stmt = mysqli_prepare($conn, $sql);

            if (!$stmt) {
                throw new Exception(
                    "Erro ao suspender a assinatura."
                );
            }

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $idAssinatura
            );

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $sql = "
                UPDATE cliente
                SET status_conta_cliente = 'Inativo'
                WHERE FK_cliente_id_cliente = ?
            ";

            $stmt = mysqli_prepare($conn, $sql);

            if (!$stmt) {
                throw new Exception(
                    "Erro ao inativar a conta."
                );
            }

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $idCliente
            );

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $mensagem =
                "Assinatura e conta suspensas com sucesso.";
        }

        elseif ($acao === "reativar") {

            $sql = "
                UPDATE assinatura
                SET status_assinatura = 'Ativa'
                WHERE id_assinatura = ?
            ";

            $stmt = mysqli_prepare($conn, $sql);

            if (!$stmt) {
                throw new Exception(
                    "Erro ao reativar a assinatura."
                );
            }

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $idAssinatura
            );

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $mensagem =
                "Assinatura reativada com sucesso.";
        }

        elseif ($acao === "suspender_conta") {

            $sql = "
                UPDATE cliente
                SET status_conta_cliente = 'Inativo'
                WHERE FK_cliente_id_cliente = ?
            ";

            $stmt = mysqli_prepare($conn, $sql);

            if (!$stmt) {
                throw new Exception(
                    "Erro ao suspender a conta."
                );
            }

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $idCliente
            );

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $mensagem =
                "Conta suspensa com sucesso.";
        }

        else {

            $sql = "
                UPDATE cliente
                SET status_conta_cliente = 'Ativo'
                WHERE FK_cliente_id_cliente = ?
            ";

            $stmt = mysqli_prepare($conn, $sql);

            if (!$stmt) {
                throw new Exception(
                    "Erro ao reativar a conta."
                );
            }

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $idCliente
            );

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $mensagem =
                "Conta reativada com sucesso.";
        }

        mysqli_commit($conn);

        header(
            "Location: assinaturas_admin.php?sucesso=" .
            urlencode($mensagem)
        );

        exit;

    } catch (Throwable $erro) {

        mysqli_rollback($conn);

        header(
            "Location: assinaturas_admin.php?erro=" .
            urlencode($erro->getMessage())
        );

        exit;
    }
}

$sql = "
    SELECT

        a.id_assinatura,

        a.tipo_plano_assinatura,

        a.data_inicio_assinatura,

        a.data_fim_assinatura,

        a.status_assinatura,

        a.forma_pagamento_assinatura,

        a.FK_assinatura_id_cliente,

        c.nome_cliente,

        c.cpf_cliente,

        c.status_conta_cliente,

        u.email

    FROM assinatura AS a

    INNER JOIN cliente AS c
        ON c.FK_cliente_id_cliente =
           a.FK_assinatura_id_cliente

    INNER JOIN usuario AS u
        ON u.id_usuario =
           c.FK_cliente_id_usuario

    WHERE u.tipo_usuario = 'Cliente'
";

$parametros = [];
$tipos = "";

if ($busca !== "") {

    $sql .= "
        AND (
            c.nome_cliente LIKE ?
            OR u.email LIKE ?
            OR c.cpf_cliente LIKE ?
        )
    ";

    $termoBusca = "%" . $busca . "%";

    $parametros[] = $termoBusca;
    $parametros[] = $termoBusca;
    $parametros[] = $termoBusca;

    $tipos .= "sss";
}

if ($filtro === "ativa") {

    $sql .= "
        AND a.status_assinatura = 'Ativa'
        AND c.status_conta_cliente = 'Ativo'
        AND a.data_fim_assinatura >= CURDATE()
    ";
}

elseif ($filtro === "atrasada") {

    $sql .= "
        AND a.status_assinatura = 'Ativa'
        AND c.status_conta_cliente = 'Ativo'
        AND a.data_fim_assinatura < CURDATE()
    ";
}

elseif ($filtro === "suspensa") {

    $sql .= "
        AND (
            a.status_assinatura = 'Inativa'
            OR c.status_conta_cliente IN ('Inativo', 'Suspenso')
        )
    ";
}

$sql .= "
    ORDER BY
        a.data_fim_assinatura ASC,
        a.id_assinatura DESC
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    die(
        "Erro ao consultar assinaturas: " .
        mysqli_error($conn)
    );
}

if (!empty($parametros)) {

    mysqli_stmt_bind_param(
        $stmt,
        $tipos,
        ...$parametros
    );
}

mysqli_stmt_execute($stmt);

$resultado =
    mysqli_stmt_get_result($stmt);

$assinaturas = [];

while (
    $linha =
    mysqli_fetch_assoc($resultado)
) {

    $assinaturas[] = $linha;
}

mysqli_stmt_close($stmt);

function formatarData($data)
{
    if (!$data) {
        return "";
    }

    return date(
        "d/m/Y",
        strtotime($data)
    );
}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>ORION TV - Assinaturas</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
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

    .navbar-orion {
        min-height: 80px;
        padding: 15px 5%;
        background-color: #080808;
        border-bottom: 1px solid #292929;

   
        position: sticky;
        top: 0;
        z-index: 1030;
    }

    .logo {
        color: #168cff !important;
        font-size: 28px;
        font-weight: bold;
        letter-spacing: 1px;
    }

    .btn-voltar {
        padding: 9px 18px;
        border-radius: 6px;
    }

    .pagina {
        max-width: 1500px;
        padding: 45px 25px 70px;
        margin: auto;
    }

    .cabecalho {
        margin-bottom: 30px;
    }

    .cabecalho h1 {
        font-size: 30px;
        margin-bottom: 8px;
    }

    .cabecalho p {
        color: #999;
        margin: 0;
    }

    .filtros {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 30px;
    }

    .filtro-busca {
        flex: 1;
        min-width: 240px;
        max-width: 500px;

        height: 46px;

        display: flex;
        align-items: center;

        background-color: #151515;

        border: 1px solid #303030;
        border-radius: 7px;

        padding: 0 14px;
    }

    .filtro-busca i {
        color: #777;
        margin-right: 10px;
    }

    .filtro-busca input {
        width: 100%;

        background: transparent;
        border: none;
        outline: none;

        color: white;
        font-size: 14px;
    }

    .filtro-busca input::placeholder {
        color: #777;
    }

    .filtro-status {
        height: 46px;

        min-width: 190px;

        background-color: #151515;
        color: #ccc;

        border: 1px solid #303030;
        border-radius: 7px;

        padding: 0 14px;

        outline: none;
    }

    .filtro-status:focus {
        border-color: #168cff;
    }

    .btn-criar-conta {
        height: 46px;

        display: inline-flex;
        align-items: center;
        gap: 8px;

        text-decoration: none;

        background-color: #168cff;
        color: white;

        padding: 0 18px;

        border-radius: 7px;

        white-space: nowrap;
    }

    .btn-criar-conta:hover {
        background-color: #006dcc;
        color: white;
    }

    .card-assinatura {
        height: 100%;

        background-color: #111;

        border: 1px solid #292929;
        border-radius: 12px;

        padding: 22px;

        transition:
            border-color 0.2s,
            transform 0.2s;
    }

    .card-assinatura:hover {
        border-color: #3c3c3c;
        transform: translateY(-2px);
    }

    .cliente-topo {
        display: flex;
        align-items: center;

        gap: 13px;

        margin-bottom: 18px;
    }

    .avatar {
        width: 48px;
        height: 48px;

        flex-shrink: 0;

        display: flex;
        align-items: center;
        justify-content: center;

        background-color: #1d1d1d;

        border: 1px solid #333;

        border-radius: 50%;

        color: #168cff;

        font-size: 20px;
    }

    .cliente-topo h2 {
        font-size: 18px;
        margin: 0 0 4px;

        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .cliente-topo p {
        color: #888;

        font-size: 13px;

        margin: 0;

        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .situacoes {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .situacao {
        display: inline-flex;
        align-items: center;

        gap: 8px;

        padding: 6px 11px;

        border-radius: 20px;

        font-size: 12px;

        margin-bottom: 0;
    }

    .situacao span {
        width: 7px;
        height: 7px;

        border-radius: 50%;

        display: block;
    }

    .situacao.ativa {
        background-color: rgba(50, 200, 120, 0.10);
        color: #63d99a;
    }

    .situacao.ativa span {
        background-color: #63d99a;
    }

    .situacao.atrasada {
        background-color: rgba(255, 170, 50, 0.10);
        color: #ffb74d;
    }

    .situacao.atrasada span {
        background-color: #ffb74d;
    }

    .situacao.suspensa {
        background-color: rgba(220, 70, 70, 0.10);
        color: #ff7777;
    }

    .situacao.suspensa span {
        background-color: #ff7777;
    }

    .informacoes {
        display: grid;

        grid-template-columns: 1fr 1fr;

        gap: 14px;

        padding: 18px 0;

        border-top: 1px solid #292929;
        border-bottom: 1px solid #292929;

        margin-bottom: 18px;
    }

    .info span {
        display: block;

        color: #777;

        font-size: 12px;

        margin-bottom: 5px;
    }

    .info strong {
        display: block;

        color: #ddd;

        font-size: 14px;
    }

    .btn-suspender,
    .btn-reativar {
        width: 100%;

        border-radius: 7px;

        padding: 10px;

        cursor: pointer;

        font-size: 14px;
    }

    .btn-suspender {
        background-color: rgba(180, 40, 40, 0.10);

        border: 1px solid #4a2020;

        color: #ff7777;
    }

    .btn-suspender:hover {
        background-color: #b52b2b;
        color: white;
    }

    .btn-reativar {
        background-color: rgba(50, 200, 120, 0.10);

        border: 1px solid #205237;

        color: #63d99a;
    }

    .btn-reativar:hover {
        background-color: #267b4b;
        color: white;
    }

    .sem-assinaturas {
        text-align: center;

        padding: 80px 20px;

        color: #888;
    }

    .sem-assinaturas i {
        display: block;

        font-size: 45px;

        color: #444;

        margin-bottom: 15px;
    }

    .sem-assinaturas strong {
        display: block;

        color: #ccc;

        font-size: 18px;

        margin-bottom: 8px;
    }

    .modal-orion {
        background-color: #151515;

        color: white;

        border: 1px solid #333;

        border-radius: 10px;
    }

    .modal-orion .modal-header {
        border-bottom: 1px solid #292929;
    }

    .modal-orion .modal-footer {
        border-top: 1px solid #292929;
    }

    .modal-orion .aviso {
        color: #999;

        font-size: 14px;
    }

    @media (max-width: 768px) {

        .navbar-orion {
            padding: 12px 4%;
        }

        .logo {
            font-size: 23px;
        }

        .btn-voltar {
            padding: 8px 12px;
            font-size: 13px;
        }

        .pagina {
            padding: 30px 15px 50px;
        }

        .cabecalho h1 {
            font-size: 25px;
        }

        .filtros {
            align-items: stretch;
        }

        .filtro-busca {
            width: 100%;
            max-width: none;
        }

        .filtro-status {
            flex: 1;
            min-width: 0;
        }

        .btn-criar-conta {
            flex: 1;
            justify-content: center;
        }

        .informacoes {
            gap: 16px;
        }

    }

    @media (max-width: 480px) {

        .navbar-orion .container-fluid {
            gap: 10px;
        }

        .logo {
            font-size: 20px;
        }

        .btn-voltar {
            font-size: 12px;
            padding: 7px 10px;
        }

        .cliente-topo h2 {
            max-width: 220px;
        }

        .informacoes {
            grid-template-columns: 1fr 1fr;
        }

    }

</style>

</head>

<body>

<nav class="navbar navbar-dark navbar-orion">

<div class="container-fluid">

    <a
        href="tela_inicial_admin.php"
        class="navbar-brand logo"
    >
        ORION TV
    </a>

    <a
        href="tela_inicial_admin.php"
        class="btn btn-outline-light btn-voltar"
    >

        <i class="bi bi-arrow-left"></i>

        Voltar

    </a>

</div>

</nav>

<main class="pagina">

<div class="cabecalho">

    <h1>
        Assinaturas
    </h1>

    <p>
        Consulte e gerencie as assinaturas dos clientes.
    </p>

</div>

<form
    method="GET"
    action="assinaturas_admin.php"
    class="filtros"
>

    <div class="filtro-busca">

        <i class="bi bi-search"></i>

        <input
            type="text"
            name="busca"
            placeholder="Buscar cliente..."
            value="<?php echo htmlspecialchars(
                $busca,
                ENT_QUOTES,
                "UTF-8"
            ); ?>"
        >

    </div>

    <select
        name="status"
        class="filtro-status"
        onchange="this.form.submit()"
    >

        <option
            value=""
            <?php echo $filtro === "" ? "selected" : ""; ?>
        >
            Todas as situações
        </option>

        <option
            value="ativa"
            <?php echo $filtro === "ativa" ? "selected" : ""; ?>
        >
            Ativas
        </option>

        <option
            value="atrasada"
            <?php echo $filtro === "atrasada" ? "selected" : ""; ?>
        >
            Atrasadas
        </option>

        <option
            value="suspensa"
            <?php echo $filtro === "suspensa" ? "selected" : ""; ?>
        >
            Suspensas
        </option>

    </select>

    <button
        type="submit"
        class="btn-criar-conta border-0"
    >

        <i class="bi bi-search"></i>

        Buscar

    </button>

   <a
    href="ver_contas_usuarios.php"
    class="btn-criar-conta"
>

    <i class="bi bi-people"></i>

    Visualizar contas

</a>

<a
    href="gerenciar_conteudos_administrador.php"
    class="btn-criar-conta"
>

    <i class="bi bi-film"></i>

    Gerenciar conteúdos

</a>

<a
    href="solicitacoes_senhas_admin.php"
    class="btn-criar-conta"
>

    <i class="bi bi-key"></i>

    Solicitações de senha

</a>

</form>

<div class="row g-4">

    <?php if (!empty($assinaturas)) { ?>

        <?php foreach ($assinaturas as $assinatura) { ?>

            <?php

            $nome =
                $assinatura["nome_cliente"] ?? "";

            $email =
                $assinatura["email"] ?? "";

            $tipoPlano =
                $assinatura["tipo_plano_assinatura"] ?? "";

            $formaPagamento =
                $assinatura["forma_pagamento_assinatura"] ?? "";

            $dataInicio =
                $assinatura["data_inicio_assinatura"] ?? "";

            $dataFim =
                $assinatura["data_fim_assinatura"] ?? "";

            $statusAssinatura =
                $assinatura["status_assinatura"] ?? "";

            $statusCliente =
                $assinatura["status_conta_cliente"] ?? "";

            if ($statusAssinatura === "Inativa") {

                $classeSituacaoAssinatura =
                    "suspensa";

                $textoSituacaoAssinatura =
                    "Assinatura suspensa";

            }

            elseif (
                $statusAssinatura === "Ativa"
                &&
                !empty($dataFim)
                &&
                strtotime($dataFim) < strtotime(date("Y-m-d"))
            ) {

                $classeSituacaoAssinatura =
                    "atrasada";

                $textoSituacaoAssinatura =
                    "Pagamento atrasado";

            }

            else {

                $classeSituacaoAssinatura =
                    "ativa";

                $textoSituacaoAssinatura =
                    "Assinatura ativa";
            }

            if (
                $statusCliente === "Inativo"
                ||
                $statusCliente === "Suspenso"
            ) {

                $classeSituacaoConta =
                    "suspensa";

                $textoSituacaoConta =
                    "Conta inativa";

            }

            else {

                $classeSituacaoConta =
                    "ativa";

                $textoSituacaoConta =
                    "Conta ativa";
            }

            ?>

            <div class="col-xl-4 col-lg-6 col-md-6">

                <div class="card-assinatura">

                    <div class="cliente-topo">

                        <div class="avatar">

                            <i class="bi bi-person-fill"></i>

                        </div>

                        <div>

                            <h2>

                                <?php

                                echo htmlspecialchars(
                                    $nome,
                                    ENT_QUOTES,
                                    "UTF-8"
                                );

                                ?>

                            </h2>

                            <p>

                                <?php

                                echo htmlspecialchars(
                                    $email,
                                    ENT_QUOTES,
                                    "UTF-8"
                                );

                                ?>

                            </p>

                        </div>

                    </div>

                    <div class="situacoes">

                        <div
                            class="situacao <?php echo $classeSituacaoAssinatura; ?>"
                        >

                            <span></span>

                            <?php

                            echo $textoSituacaoAssinatura;

                            ?>

                        </div>

                        <div
                            class="situacao <?php echo $classeSituacaoConta; ?>"
                        >

                            <span></span>

                            <?php

                            echo $textoSituacaoConta;

                            ?>

                        </div>

                    </div>

                    <div class="informacoes">

                        <div class="info">

                            <span>
                                Plano
                            </span>

                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    $tipoPlano,
                                    ENT_QUOTES,
                                    "UTF-8"
                                );

                                ?>

                            </strong>

                        </div>

                        <div class="info">

                            <span>
                                Pagamento
                            </span>

                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    $formaPagamento,
                                    ENT_QUOTES,
                                    "UTF-8"
                                );

                                ?>

                            </strong>

                        </div>

                        <div class="info">

                            <span>
                                Início
                            </span>

                            <strong>

                                <?php

                                echo formatarData(
                                    $dataInicio
                                );

                                ?>

                            </strong>

                        </div>

                        <div class="info">

                            <span>
                                Vencimento
                            </span>

                            <strong>

                                <?php

                                echo formatarData(
                                    $dataFim
                                );

                                ?>

                            </strong>

                        </div>

                    </div>

                    <?php

                    if (
                        $statusAssinatura === "Ativa"
                        &&
                        $statusCliente === "Ativo"
                    ) {

                    ?>

                        <button
                            type="button"
                            class="btn-suspender"
                            data-bs-toggle="modal"
                            data-bs-target="#modalSuspender"
                            data-id="<?php echo (int) $assinatura["id_assinatura"]; ?>"
                            data-nome="<?php echo htmlspecialchars(
                                $nome,
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>"
                        >

                            <i class="bi bi-pause-circle"></i>

                            Suspender assinatura

                        </button>

                    <?php

                    }

                    elseif (
                        $statusAssinatura === "Ativa"
                        &&
                        (
                            $statusCliente === "Inativo"
                            ||
                            $statusCliente === "Suspenso"
                        )
                    ) {

                    ?>

                        <form
                            method="POST"
                            action="assinaturas_admin.php"
                        >

                            <input
                                type="hidden"
                                name="acao"
                                value="reativar_conta"
                            >

                            <input
                                type="hidden"
                                name="id_assinatura"
                                value="<?php echo (int) $assinatura["id_assinatura"]; ?>"
                            >

                            <button
                                type="submit"
                                class="btn-reativar"
                            >

                                <i class="bi bi-person-check"></i>

                                Reativar conta

                            </button>

                        </form>

                    <?php

                    }

                    elseif (
                        $statusAssinatura === "Inativa"
                        &&
                        $statusCliente === "Ativo"
                    ) {

                    ?>

                        <div class="d-flex gap-2">

                            <form
                                method="POST"
                                action="assinaturas_admin.php"
                                class="flex-fill"
                            >

                                <input
                                    type="hidden"
                                    name="acao"
                                    value="reativar"
                                >

                                <input
                                    type="hidden"
                                    name="id_assinatura"
                                    value="<?php echo (int) $assinatura["id_assinatura"]; ?>"
                                >

                                <button
                                    type="submit"
                                    class="btn-reativar"
                                >

                                    <i class="bi bi-play-circle"></i>

                                    Reativar assinatura

                                </button>

                            </form>

                            <button
                                type="button"
                                class="btn-suspender flex-fill"
                                data-bs-toggle="modal"
                                data-bs-target="#modalSuspenderConta"
                                data-id="<?php echo (int) $assinatura["id_assinatura"]; ?>"
                                data-nome="<?php echo htmlspecialchars(
                                    $nome,
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>"
                            >

                                <i class="bi bi-person-x"></i>

                                Suspender conta

                            </button>

                        </div>

                    <?php

                    }

                    elseif (
                        $statusAssinatura === "Inativa"
                        &&
                        (
                            $statusCliente === "Inativo"
                            ||
                            $statusCliente === "Suspenso"
                        )
                    ) {

                    ?>

                        <div class="d-flex gap-2">

                            <form
                                method="POST"
                                action="assinaturas_admin.php"
                                class="flex-fill"
                            >

                                <input
                                    type="hidden"
                                    name="acao"
                                    value="reativar"
                                >

                                <input
                                    type="hidden"
                                    name="id_assinatura"
                                    value="<?php echo (int) $assinatura["id_assinatura"]; ?>"
                                >

                                <button
                                    type="submit"
                                    class="btn-reativar"
                                >

                                    <i class="bi bi-play-circle"></i>

                                    Reativar assinatura

                                </button>

                            </form>

                            <form
                                method="POST"
                                action="assinaturas_admin.php"
                                class="flex-fill"
                            >

                                <input
                                    type="hidden"
                                    name="acao"
                                    value="reativar_conta"
                                >

                                <input
                                    type="hidden"
                                    name="id_assinatura"
                                    value="<?php echo (int) $assinatura["id_assinatura"]; ?>"
                                >

                                <button
                                    type="submit"
                                    class="btn-reativar"
                                >

                                    <i class="bi bi-person-check"></i>

                                    Reativar conta

                                </button>

                            </form>

                        </div>

                    <?php } ?>

                </div>

            </div>

        <?php } ?>

    <?php } else { ?>

        <div class="col-12">

            <div class="card-assinatura sem-assinaturas">

                <i class="bi bi-credit-card"></i>

                <strong>
                    Nenhuma assinatura encontrada
                </strong>

                <p>

                    <?php

                    if ($busca !== "") {

                        echo "Nenhuma assinatura corresponde à busca.";

                    }

                    elseif ($filtro === "ativa") {

                        echo "Não existem assinaturas ativas.";

                    }

                    elseif ($filtro === "atrasada") {

                        echo "Não existem assinaturas atrasadas.";

                    }

                    elseif ($filtro === "suspensa") {

                        echo "Não existem assinaturas suspensas.";

                    }

                    else {

                        echo "Ainda não existem assinaturas cadastradas.";

                    }

                    ?>

                </p>

            </div>

        </div>

    <?php } ?>

</div>

</main>

<div
    class="modal fade"
    id="modalSuspender"
    tabindex="-1"
    aria-hidden="true"
>

<div class="modal-dialog modal-dialog-centered">

    <div class="modal-content modal-orion">

        <div class="modal-header">

            <h5 class="modal-title">

                Suspender assinatura

            </h5>

            <button
                type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="modal"
            ></button>

        </div>

        <div class="modal-body">

            <p>

                Tem certeza que deseja suspender a assinatura de
                <strong id="nomeClienteModal"></strong>?

            </p>

            <p class="aviso">

                A assinatura e a conta do cliente ficarão inativas
                ao mesmo tempo.

            </p>

        </div>

        <div class="modal-footer">

            <button
                type="button"
                class="btn btn-secondary"
                data-bs-dismiss="modal"
            >

                Cancelar

            </button>

            <form
                method="POST"
                action="assinaturas_admin.php"
            >

                <input
                    type="hidden"
                    name="acao"
                    value="suspender"
                >

                <input
                    type="hidden"
                    name="id_assinatura"
                    id="idAssinaturaModal"
                    value=""
                >

                <button
                    type="submit"
                    class="btn btn-danger"
                >

                    <i class="bi bi-pause-circle"></i>

                    Confirmar suspensão

                </button>

            </form>

        </div>

    </div>

</div>

</div>

<div
    class="modal fade"
    id="modalSuspenderConta"
    tabindex="-1"
    aria-hidden="true"
>

<div class="modal-dialog modal-dialog-centered">

    <div class="modal-content modal-orion">

        <div class="modal-header">

            <h5 class="modal-title">

                Suspender conta

            </h5>

            <button
                type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="modal"
            ></button>

        </div>

        <div class="modal-body">

            <p>

                Tem certeza que deseja suspender a conta de
                <strong id="nomeClienteModalConta"></strong>?

            </p>

            <p class="aviso">

                A conta ficará inativa e o cliente não poderá
                acessar o sistema.

            </p>

        </div>

        <div class="modal-footer">

            <button
                type="button"
                class="btn btn-secondary"
                data-bs-dismiss="modal"
            >

                Cancelar

            </button>

            <form
                method="POST"
                action="assinaturas_admin.php"
            >

                <input
                    type="hidden"
                    name="acao"
                    value="suspender_conta"
                >

                <input
                    type="hidden"
                    name="id_assinatura"
                    id="idAssinaturaModalConta"
                    value=""
                >

                <button
                    type="submit"
                    class="btn btn-danger"
                >

                    <i class="bi bi-person-x"></i>

                    Confirmar suspensão

                </button>

            </form>

        </div>

    </div>

</div>

</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

<script>

const modalSuspender =
    document.getElementById("modalSuspender");

modalSuspender.addEventListener(
    "show.bs.modal",
    function (event) {

        const botao =
            event.relatedTarget;

        const id =
            botao.getAttribute("data-id");

        const nome =
            botao.getAttribute("data-nome");

        document.getElementById(
            "idAssinaturaModal"
        ).value = id;

        document.getElementById(
            "nomeClienteModal"
        ).textContent = nome;

    }
);

const modalSuspenderConta =
    document.getElementById("modalSuspenderConta");

modalSuspenderConta.addEventListener(
    "show.bs.modal",
    function (event) {

        const botao =
            event.relatedTarget;

        const id =
            botao.getAttribute("data-id");

        const nome =
            botao.getAttribute("data-nome");

        document.getElementById(
            "idAssinaturaModalConta"
        ).value = id;

        document.getElementById(
            "nomeClienteModalConta"
        ).textContent = nome;

    }
);

</script>

<?php

if (isset($_GET["sucesso"])) {

    $mensagem =
        $_GET["sucesso"];

?>

<script>

    mostrarAlerta(
        <?php

        echo json_encode(
            $mensagem,
            JSON_UNESCAPED_UNICODE
        );

        ?>,
        "window.location.href = 'assinaturas_admin.php'"
    );

</script>

<?php

}

if (isset($_GET["erro"])) {

    $mensagemErro =
        $_GET["erro"];

?>

<script>

    mostrarAlerta(
        <?php

        echo json_encode(
            $mensagemErro,
            JSON_UNESCAPED_UNICODE
        );

        ?>,
        "window.location.href = 'assinaturas_admin.php'"
    );

</script>

<?php

}

?>

<script>

    /*
        Mantém a posição da rolagem da página após cada ação
        (inativar, ativar, bloquear, excluir, filtrar, buscar etc.),
        evitando voltar ao topo quando a página é recarregada.
    */

    (function () {

        var CHAVE = "orion_posicao_rolagem";

        var VALIDADE = 10 * 60 * 1000;

        function posicaoAtual() {

            return window.pageYOffset ||
                document.documentElement.scrollTop ||
                document.body.scrollTop ||
                0;

        }

        function salvarPosicao() {

            try {

                sessionStorage.setItem(
                    CHAVE,
                    JSON.stringify({
                        caminho: window.location.pathname,
                        y: posicaoAtual(),
                        hora: Date.now()
                    })
                );

            } catch (erro) {}

        }

        function limparPosicao() {

            try {

                sessionStorage.removeItem(CHAVE);

            } catch (erro) {}

        }

        function lerPosicao() {

            try {

                var bruto = sessionStorage.getItem(CHAVE);

                if (!bruto) {

                    return null;

                }

                var dados = JSON.parse(bruto);

                if (
                    !dados ||
                    dados.caminho !== window.location.pathname ||
                    typeof dados.y !== "number" ||
                    (Date.now() - dados.hora) > VALIDADE
                ) {

                    return null;

                }

                return dados;

            } catch (erro) {

                return null;

            }

        }

        function rolarPara(y) {

            var raiz = document.documentElement;

            var comportamentoAnterior = raiz.style.scrollBehavior;

            raiz.style.scrollBehavior = "auto";

            window.scrollTo(0, y);

            raiz.style.scrollBehavior = comportamentoAnterior;

        }

        var salvo = lerPosicao();

        if (salvo) {

            rolarPara(salvo.y);

            window.addEventListener("load", function () {

                if (posicaoAtual() === 0) {

                    rolarPara(salvo.y);

                }

            });

            if (!/[?&](sucesso|erro)=/.test(window.location.search)) {

                limparPosicao();

            }

        }

        if (
            window.HTMLFormElement &&
            HTMLFormElement.prototype.submit
        ) {

            var submitOriginal = HTMLFormElement.prototype.submit;

            HTMLFormElement.prototype.submit = function () {

                salvarPosicao();

                return submitOriginal.apply(this, arguments);

            };

        }

        document.addEventListener("submit", function (evento) {

            if (!evento.defaultPrevented) {

                salvarPosicao();

            }

        });

        document.addEventListener("click", function (evento) {

            if (
                evento.defaultPrevented ||
                evento.button !== 0 ||
                evento.ctrlKey ||
                evento.metaKey ||
                evento.shiftKey ||
                evento.altKey
            ) {

                return;

            }

            var alvo = evento.target;

            var link = alvo && alvo.closest
                ? alvo.closest("a[href]")
                : null;

            if (!link) {

                return;

            }

            if (link.target && link.target !== "_self") {

                return;

            }

            if (
                link.hostname === window.location.hostname &&
                link.pathname === window.location.pathname
            ) {

                salvarPosicao();

            } else {

                limparPosicao();

            }

        });

    })();

</script>

</body>

</html>
