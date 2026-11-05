<?php

session_start();

require_once(__DIR__ . "/../php/conexao.php");
require_once(__DIR__ . "/alerta.php");
require_once(__DIR__ . "/../php/manutencao_data_assinatura.php");


if (!isset($_SESSION["id_usuario"])) {

    header("Location: /index.php");

    exit;
}


$id_usuario = $_SESSION["id_usuario"];

$tipo_usuario = $_SESSION["tipo_usuario"];




if ($tipo_usuario === "Administrador") {


    $sql = "
        SELECT status
        FROM administrador
        WHERE FK_administrador_id_usuario = ?
        LIMIT 1
    ";


    $stmt = mysqli_prepare($conn, $sql);


    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id_usuario
    );


    mysqli_stmt_execute($stmt);


    $resultado = mysqli_stmt_get_result($stmt);


    $admin = mysqli_fetch_assoc($resultado);


    mysqli_stmt_close($stmt);


    if (!$admin || $admin["status"] !== "Ativo") {

        header("Location: /index.php");

        exit;
    }


    return;
}





if ($tipo_usuario !== "Cliente") {

    header("Location: /index.php");

    exit;
}



$sql = "
    SELECT
        c.status_conta_cliente,
        a.status_assinatura

    FROM cliente c

    LEFT JOIN assinatura a
        ON a.FK_assinatura_id_cliente = c.FK_cliente_id_cliente

    WHERE c.FK_cliente_id_usuario = ?

    LIMIT 1
";


$stmt = mysqli_prepare($conn, $sql);


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_usuario
);


mysqli_stmt_execute($stmt);


$resultado = mysqli_stmt_get_result($stmt);


$dados = mysqli_fetch_assoc($resultado);


mysqli_stmt_close($stmt);



if (!$dados) {

    header("Location: /index.php");

    exit;
}



if ($dados["status_conta_cliente"] !== "Ativo") {

    mostrarAlerta(
        "Sua conta está inativada ou bloqueada. Não é possível acessar esta área.",
        "window.location.href = '/index.php'"
    );

    exit;
}



if ($dados["status_assinatura"] !== "Ativa") {

    mostrarAlerta(
        "Sua assinatura está inativa ou vencida. Não é possível acessar esta área.",
        "window.location.href = '/index.php'"
    );

    exit;
}