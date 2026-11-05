<?php

require_once(__DIR__ . "/conexao.php");
require_once(__DIR__ . "/../includes/alerta.php");




if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: ../esqueci_senha.php");

    exit;
}




$email = trim($_POST["email"] ?? "");

$senha = $_POST["senha"] ?? "";




if ($email === "" || $senha === "") {

    mostrarAlerta(
        "Preencha todos os campos.",
        "window.location.href = '../esqueci_senha.php'"
    );

    exit;
}




$sql = "
    SELECT id_usuario
    FROM usuario
    WHERE email = ?
";


$stmt = mysqli_prepare($conn, $sql);


if (!$stmt) {

    mostrarAlerta(
        "Erro ao consultar o banco de dados.",
        "window.location.href = '../esqueci_senha.php'"
    );

    exit;
}


mysqli_stmt_bind_param(
    $stmt,
    "s",
    $email
);


mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);




if (mysqli_num_rows($resultado) === 0) {

    mysqli_stmt_close($stmt);

    mostrarAlerta(
        "Este e-mail não possui uma conta.",
        "window.location.href = '../esqueci_senha.php'"
    );

    exit;
}




$usuario = mysqli_fetch_assoc($resultado);

$id_usuario = $usuario["id_usuario"];

mysqli_stmt_close($stmt);




$sql = "
    UPDATE usuario
    SET senha = ?
    WHERE id_usuario = ?
";


$stmt = mysqli_prepare($conn, $sql);


if (!$stmt) {

    mostrarAlerta(
        "Erro ao preparar a alteração da senha.",
        "window.location.href = '../esqueci_senha.php'"
    );

    exit;
}


mysqli_stmt_bind_param(
    $stmt,
    "si",
    $senha,
    $id_usuario
);


if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    mostrarAlerta(
        "Não foi possível alterar a senha.",
        "window.location.href = '../esqueci_senha.php'"
    );

    exit;
}


mysqli_stmt_close($stmt);
mysqli_close($conn);




mostrarAlerta(
    "Senha alterada com sucesso!",
    "window.location.href = '../index.php'"
);

exit;

?>