<?php

session_start();

require_once(__DIR__ . "/php/conexao.php");
require_once(__DIR__ . "/includes/alerta.php");

if (!isset($_SESSION["id_usuario"]) || !isset($_SESSION["tipo_usuario"])) {
    header("Location: index.php");
    exit;
}

if ($_SESSION["tipo_usuario"] !== "Cliente") {
    header("Location: index.php");
    exit;
}

$id_usuario = $_SESSION["id_usuario"];

$sql_cliente = "
    SELECT FK_cliente_id_cliente, status_conta_cliente
    FROM cliente
    WHERE FK_cliente_id_usuario = ?
";

$stmt_cliente = mysqli_prepare($conn, $sql_cliente);

if (!$stmt_cliente) {
    mostrarAlerta("Erro ao consultar a conta.");
    exit;
}

mysqli_stmt_bind_param($stmt_cliente, "i", $id_usuario);
mysqli_stmt_execute($stmt_cliente);

$resultado_cliente = mysqli_stmt_get_result($stmt_cliente);

if (mysqli_num_rows($resultado_cliente) === 0) {
    mysqli_stmt_close($stmt_cliente);
    mostrarAlerta("Cliente não encontrado.");
    exit;
}

$cliente = mysqli_fetch_assoc($resultado_cliente);

$id_cliente = $cliente["FK_cliente_id_cliente"];
$status_conta = $cliente["status_conta_cliente"];

mysqli_stmt_close($stmt_cliente);

if ($status_conta !== "Ativo") {
    mostrarAlerta("Sua conta está inativa. Não é possível criar uma assinatura.");
    exit;
}

$sql_verificar = "
    SELECT id_assinatura
    FROM assinatura
    WHERE FK_assinatura_id_cliente = ?
    LIMIT 1
";

$stmt_verificar = mysqli_prepare($conn, $sql_verificar);

if (!$stmt_verificar) {
    mostrarAlerta("Erro ao verificar a assinatura.");
    exit;
}

mysqli_stmt_bind_param($stmt_verificar, "i", $id_cliente);
mysqli_stmt_execute($stmt_verificar);

$resultado_verificar = mysqli_stmt_get_result($stmt_verificar);

if (mysqli_num_rows($resultado_verificar) > 0) {
    mysqli_stmt_close($stmt_verificar);
    mysqli_close($conn);

    header("Location: tela_inicial.php");
    exit;
}

mysqli_stmt_close($stmt_verificar);

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["finalizar_assinatura"])) {

    $plano = $_POST["plano"] ?? "";
    $forma_pagamento = $_POST["forma_pagamento"] ?? "";
    $aniversario = $_POST["aniversario"] ?? "";

    if ($aniversario === "") {
        mostrarAlerta("Informe sua data de nascimento.");
        exit;
    }

    $data_nascimento = DateTime::createFromFormat("Y-m-d", $aniversario);

    if (
        !$data_nascimento ||
        $data_nascimento->format("Y-m-d") !== $aniversario
    ) {
        mostrarAlerta("Digite uma data de nascimento válida.");
        exit;
    }

    $hoje = new DateTime();

    if ($data_nascimento > $hoje) {
        mostrarAlerta("A data de nascimento não pode ser futura.");
        exit;
    }

    $idade = $hoje->diff($data_nascimento)->y;

    if ($idade < 18) {
        mostrarAlerta("É necessário ter 18 anos ou mais para criar uma assinatura.");
        exit;
    }

    if ($plano !== "Mensal" && $plano !== "Anual") {
        mostrarAlerta("Escolha um plano válido.");
        exit;
    }

    if (
        $forma_pagamento !== "Pix" &&
        $forma_pagamento !== "Cartão" &&
        $forma_pagamento !== "Boleto"
    ) {
        mostrarAlerta("Escolha uma forma de pagamento válida.");
        exit;
    }

    $data_inicio = date("Y-m-d");

    if ($plano === "Mensal") {
        $data_fim = date("Y-m-d", strtotime("+1 month"));
    } else {
        $data_fim = date("Y-m-d", strtotime("+1 year"));
    }

    mysqli_begin_transaction($conn);

    try {

        $sql_aniversario = "
            UPDATE usuario
            SET aniversario = ?
            WHERE id_usuario = ?
        ";

        $stmt_aniversario = mysqli_prepare(
            $conn,
            $sql_aniversario
        );

        if (!$stmt_aniversario) {
            throw new Exception(
                "Erro ao preparar o cadastro da data de nascimento."
            );
        }

        mysqli_stmt_bind_param(
            $stmt_aniversario,
            "si",
            $aniversario,
            $id_usuario
        );

        if (!mysqli_stmt_execute($stmt_aniversario)) {
            throw new Exception(
                "Não foi possível salvar a data de nascimento."
            );
        }

        mysqli_stmt_close($stmt_aniversario);

        $sql_assinatura = "
            INSERT INTO assinatura (
                tipo_plano_assinatura,
                data_inicio_assinatura,
                data_fim_assinatura,
                status_assinatura,
                forma_pagamento_assinatura,
                FK_assinatura_id_cliente
            )
            VALUES (?, ?, ?, 'Ativa', ?, ?)
        ";

        $stmt_assinatura = mysqli_prepare(
            $conn,
            $sql_assinatura
        );

        if (!$stmt_assinatura) {
            throw new Exception(
                "Erro ao preparar a criação da assinatura."
            );
        }

        mysqli_stmt_bind_param(
            $stmt_assinatura,
            "ssssi",
            $plano,
            $data_inicio,
            $data_fim,
            $forma_pagamento,
            $id_cliente
        );

        if (!mysqli_stmt_execute($stmt_assinatura)) {
            throw new Exception(
                "Não foi possível criar sua assinatura."
            );
        }

        mysqli_stmt_close($stmt_assinatura);

        mysqli_commit($conn);
        mysqli_close($conn);

        header("Location: tela_inicial.php");
        exit;

    } catch (Throwable $erro) {

        mysqli_rollback($conn);
        mysqli_close($conn);

        mostrarAlerta(
            $erro->getMessage()
        );

        exit;
    }
}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ORION TV - Assinatura</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        body {
            background: linear-gradient(135deg, #141414, #1f1f1f);
            min-height: 100vh;
            color: white;
        }

        .pagamento-card {
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

        .plano {
            background: #333;
            border: 1px solid #444;
            border-radius: 10px;
            padding: 18px;
            cursor: pointer;
            transition: 0.2s;
        }

        .plano:hover {
            border-color: #0d6efd;
        }

        .plano-selecionado {
            border: 2px solid #0d6efd;
        }

        .preco {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0d6efd;
        }

        .resumo {
            background: #181818;
            border-radius: 10px;
            padding: 20px;
        }

        .linha-resumo {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

    </style>

</head>

<body>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-12 col-md-10 col-lg-8">

            <div class="card pagamento-card shadow-lg p-4 p-md-5">

                <div class="text-center mb-4">

                    <div class="logo">
                        ORION TV
                    </div>

                    <p class="text-secondary">
                        Finalize sua assinatura
                    </p>

                </div>

                <form method="POST" action="sem_assinatura.php">

                    <div class="titulo-secao">
                        Dados da conta
                    </div>

                    <div class="mb-4">

                        <label class="form-label">
                            Data de nascimento
                        </label>

                        <input
                            type="date"
                            class="form-control"
                            name="aniversario"
                            required
                        >

                    </div>

                    <div class="titulo-secao">
                        Escolha seu plano
                    </div>

                    <div class="row g-3 mb-4">

                        <div class="col-md-6">

                            <label
                                class="plano plano-selecionado w-100"
                                id="planoMensal"
                            >

                                <div class="d-flex justify-content-between">

                                    <div>

                                        <h5>
                                            Plano Mensal
                                        </h5>

                                        <p class="text-secondary mb-0">
                                            Cobrança mensal
                                        </p>

                                    </div>

                                    <input
                                        type="radio"
                                        name="plano"
                                        value="Mensal"
                                        checked
                                        required
                                        class="form-check-input plano-radio"
                                    >

                                </div>

                                <div class="preco mt-3">
                                    R$ 19,90
                                </div>

                                <small class="text-secondary">
                                    por mês
                                </small>

                            </label>

                        </div>

                        <div class="col-md-6">

                            <label
                                class="plano w-100"
                                id="planoAnual"
                            >

                                <div class="d-flex justify-content-between">

                                    <div>

                                        <h5>
                                            Plano Anual
                                        </h5>

                                        <p class="text-secondary mb-0">
                                            Cobrança anual
                                        </p>

                                    </div>

                                    <input
                                        type="radio"
                                        name="plano"
                                        value="Anual"
                                        class="form-check-input plano-radio"
                                    >

                                </div>

                                <div class="preco mt-3">
                                    R$ 199,90
                                </div>

                                <small class="text-secondary">
                                    por ano
                                </small>

                            </label>

                        </div>

                    </div>

                    <div class="titulo-secao mt-4">
                        Forma de pagamento
                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Método
                        </label>

                        <select
                            class="form-select"
                            name="forma_pagamento"
                            required
                        >

                            <option
                                value=""
                                selected
                                disabled
                            >
                                Escolha uma forma de pagamento
                            </option>

                            <option value="Pix">
                                PIX
                            </option>

                            <option value="Cartão">
                                Cartão de crédito
                            </option>

                            <option value="Boleto">
                                Boleto
                            </option>

                        </select>

                    </div>

                    <div class="titulo-secao mt-4">
                        Resumo do pedido
                    </div>

                    <div class="resumo mb-4">

                        <div class="linha-resumo">

                            <span>
                                Plano
                            </span>

                            <span id="resumoPlano">
                                Mensal
                            </span>

                        </div>

                        <div class="linha-resumo">

                            <span>
                                Valor
                            </span>

                            <span id="resumoValor">
                                R$ 19,90
                            </span>

                        </div>

                        <hr>

                        <div class="linha-resumo fw-bold">

                            <span>
                                Total
                            </span>

                            <span
                                class="text-primary"
                                id="resumoTotal"
                            >
                                R$ 19,90
                            </span>

                        </div>

                    </div>

                    <button
                        class="btn btn-primary w-100 py-2"
                        type="submit"
                        name="finalizar_assinatura"
                    >
                        Finalizar pagamento
                    </button>

                    <a
                        href="index.php"
                        class="btn btn-outline-secondary w-100 mt-2"
                    >
                        Voltar
                    </a>

                </form>

            </div>

        </div>

    </div>

</div>

<script>

    const radios = document.querySelectorAll(".plano-radio");

    const planoMensal =
        document.getElementById("planoMensal");

    const planoAnual =
        document.getElementById("planoAnual");

    const resumoPlano =
        document.getElementById("resumoPlano");

    const resumoValor =
        document.getElementById("resumoValor");

    const resumoTotal =
        document.getElementById("resumoTotal");

    radios.forEach(function (radio) {

        radio.addEventListener("change", function () {

            planoMensal.classList.remove(
                "plano-selecionado"
            );

            planoAnual.classList.remove(
                "plano-selecionado"
            );

            if (this.value === "Mensal") {

                planoMensal.classList.add(
                    "plano-selecionado"
                );

                resumoPlano.textContent = "Mensal";
                resumoValor.textContent = "R$ 19,90";
                resumoTotal.textContent = "R$ 19,90";

            }

            if (this.value === "Anual") {

                planoAnual.classList.add(
                    "plano-selecionado"
                );

                resumoPlano.textContent = "Anual";
                resumoValor.textContent = "R$ 199,90";
                resumoTotal.textContent = "R$ 199,90";

            }

        });

    });

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>