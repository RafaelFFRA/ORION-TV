<?php

session_start();

require_once(__DIR__ . "/conexao.php");
require_once(__DIR__ . "/../includes/alerta.php");




if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: ../renovar_assinatura.php");
    exit;
}




if (
    !isset($_SESSION["id_usuario"]) ||
    !isset($_SESSION["tipo_usuario"])
) {

    header("Location: ../index.php");
    exit;
}



if ($_SESSION["tipo_usuario"] !== "Cliente") {

    session_destroy();

    header("Location: ../index.php");
    exit;
}


$id_usuario = $_SESSION["id_usuario"];




$plano = $_POST["plano"] ?? "";
$forma_pagamento = $_POST["forma_pagamento"] ?? "";




if (
    $plano !== "Mensal" &&
    $plano !== "Anual"
) {

    mostrarAlerta(
        "Plano inválido.",
        "window.location.href = '../renovar_assinatura.php'"
    );

    exit;
}




if (
    $forma_pagamento !== "Pix" &&
    $forma_pagamento !== "Cartão" &&
    $forma_pagamento !== "Boleto"
) {

    mostrarAlerta(
        "Forma de pagamento inválida.",
        "window.location.href = '../renovar_assinatura.php'"
    );

    exit;
}




$sql_cliente = "
    SELECT
        FK_cliente_id_cliente,
        status_conta_cliente
    FROM cliente
    WHERE FK_cliente_id_usuario = ?
";


$stmt_cliente = mysqli_prepare($conn, $sql_cliente);

if (!$stmt_cliente) {

    mysqli_close($conn);

    mostrarAlerta(
        "Erro ao consultar a conta do cliente."
    );

    exit;
}


mysqli_stmt_bind_param(
    $stmt_cliente,
    "i",
    $id_usuario
);


if (!mysqli_stmt_execute($stmt_cliente)) {

    mysqli_stmt_close($stmt_cliente);
    mysqli_close($conn);

    mostrarAlerta(
        "Erro ao consultar a conta do cliente."
    );

    exit;
}


$resultado_cliente = mysqli_stmt_get_result($stmt_cliente);


if (mysqli_num_rows($resultado_cliente) === 0) {

    mysqli_stmt_close($stmt_cliente);
    mysqli_close($conn);

    mostrarAlerta(
        "Conta de cliente não encontrada."
    );

    exit;
}


$cliente = mysqli_fetch_assoc($resultado_cliente);

$id_cliente = $cliente["FK_cliente_id_cliente"];
$status_conta = $cliente["status_conta_cliente"];


mysqli_stmt_close($stmt_cliente);




if (
    $status_conta === "Inativo" ||
    $status_conta === "Suspenso"
) {

    mysqli_close($conn);

    mostrarAlerta(
        "Sua conta está inativa. Não é possível renovar a assinatura."
    );

    exit;
}




$sql_assinatura = "
    SELECT
        id_assinatura
    FROM assinatura
    WHERE FK_assinatura_id_cliente = ?
";


$stmt_assinatura = mysqli_prepare($conn, $sql_assinatura);

if (!$stmt_assinatura) {

    mysqli_close($conn);

    mostrarAlerta(
        "Erro ao consultar a assinatura."
    );

    exit;
}


mysqli_stmt_bind_param(
    $stmt_assinatura,
    "i",
    $id_cliente
);


if (!mysqli_stmt_execute($stmt_assinatura)) {

    mysqli_stmt_close($stmt_assinatura);
    mysqli_close($conn);

    mostrarAlerta(
        "Erro ao consultar a assinatura."
    );

    exit;
}


$resultado_assinatura = mysqli_stmt_get_result($stmt_assinatura);


if (mysqli_num_rows($resultado_assinatura) === 0) {

    mysqli_stmt_close($stmt_assinatura);
    mysqli_close($conn);

    mostrarAlerta(
        "Assinatura não encontrada."
    );

    exit;
}


$assinatura = mysqli_fetch_assoc($resultado_assinatura);

$id_assinatura = $assinatura["id_assinatura"];


mysqli_stmt_close($stmt_assinatura);




$data_inicio = new DateTime();

if ($plano === "Mensal") {

    $data_fim = clone $data_inicio;
    $data_fim->modify("+1 month");

} else {

    $data_fim = clone $data_inicio;
    $data_fim->modify("+1 year");
}


$data_inicio_assinatura = $data_inicio->format("Y-m-d");
$data_fim_assinatura = $data_fim->format("Y-m-d");




$sql_update = "
    UPDATE assinatura
    SET
        tipo_plano_assinatura = ?,
        data_inicio_assinatura = ?,
        data_fim_assinatura = ?,
        status_assinatura = 'Ativa',
        forma_pagamento_assinatura = ?
    WHERE
        id_assinatura = ?
        AND FK_assinatura_id_cliente = ?
";


$stmt_update = mysqli_prepare($conn, $sql_update);

if (!$stmt_update) {

    mysqli_close($conn);

    mostrarAlerta(
        "Erro ao atualizar a assinatura."
    );

    exit;
}


mysqli_stmt_bind_param(
    $stmt_update,
    "ssssii",
    $plano,
    $data_inicio_assinatura,
    $data_fim_assinatura,
    $forma_pagamento,
    $id_assinatura,
    $id_cliente
);


if (!mysqli_stmt_execute($stmt_update)) {

    mysqli_stmt_close($stmt_update);
    mysqli_close($conn);

    mostrarAlerta(
        "Erro ao renovar a assinatura."
    );

    exit;
}


mysqli_stmt_close($stmt_update);
mysqli_close($conn);




mostrarAlerta(
    "Assinatura renovada com sucesso.",
    "window.location.href = '../tela_inicial.php'"
);

exit;

?>
