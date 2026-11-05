<?php

session_start();

require_once(__DIR__ . "/../php/conexao.php");


if (!isset($_SESSION["id_usuario"])) {

    header("Location: /index.php");

    exit;
}


if ($_SESSION["tipo_usuario"] !== "Administrador") {

    header("Location: /index.php");

    exit;
}


$id_usuario = $_SESSION["id_usuario"];


$sql = "
    SELECT status
    FROM administrador
    WHERE FK_administrador_id_usuario = ?
    LIMIT 1
";


$stmt = mysqli_prepare($conn, $sql);


if (!$stmt) {

    header("Location: /index.php");

    exit;
}


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