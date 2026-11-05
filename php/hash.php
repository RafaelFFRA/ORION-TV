<?php

require_once(__DIR__ . "/conexao.php");

$sql = "SELECT id_usuario, senha FROM usuario";

$resultado = mysqli_query($conn, $sql);

if (!$resultado) {
    die("Erro ao consultar as senhas.");
}

while ($usuario = mysqli_fetch_assoc($resultado)) {

    $hash = password_hash(
        $usuario["senha"],
        PASSWORD_DEFAULT
    );

    if ($hash === false) {
        die(
            "Erro ao gerar hash para o usuário ID "
            . $usuario["id_usuario"]
        );
    }

    $sql_update = "
        UPDATE usuario
        SET senha = ?
        WHERE id_usuario = ?
    ";

    $stmt = mysqli_prepare($conn, $sql_update);

    if (!$stmt) {
        die("Erro ao preparar atualização.");
    }

    mysqli_stmt_bind_param(
        $stmt,
        "si",
        $hash,
        $usuario["id_usuario"]
    );

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);

        die(
            "Erro ao atualizar a senha do usuário ID "
            . $usuario["id_usuario"]
        );
    }

    mysqli_stmt_close($stmt);
}

mysqli_free_result($resultado);
mysqli_close($conn);

echo "Todas as senhas foram transformadas em hash com sucesso.";