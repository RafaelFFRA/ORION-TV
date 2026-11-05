<?php



require_once(__DIR__ . "/conexao.php");


if (!isset($_SESSION["id_usuario"])) {
    return;
}


$id_usuario = $_SESSION["id_usuario"];


$sql = "
    UPDATE assinatura AS a
    INNER JOIN cliente AS c
        ON c.FK_cliente_id_cliente = a.FK_assinatura_id_cliente
    SET a.status_assinatura = 'Vencida'
    WHERE c.FK_cliente_id_usuario = ?
    AND a.status_assinatura = 'Ativa'
    AND a.data_fim_assinatura < CURDATE()
";


$stmt = mysqli_prepare($conn, $sql);


if (!$stmt) {
    return;
}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_usuario
);


mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);