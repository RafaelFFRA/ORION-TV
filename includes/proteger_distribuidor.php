<?php

session_start();

require_once(__DIR__ . "/../php/conexao.php");


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





if ($tipo_usuario !== "Distribuidor") {

    header("Location: /index.php");

    exit;
}



$sql = "
    SELECT status
    FROM distribuidor
    WHERE FK_distribuidor_id_usuario = ?
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


$distribuidor = mysqli_fetch_assoc($resultado);


mysqli_stmt_close($stmt);



if (!$distribuidor || $distribuidor["status"] !== "Ativo") {

    header("Location: /index.php");

    exit;
}