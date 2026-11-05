<?php

session_start();

require_once(__DIR__ . "/conexao.php");
require_once(__DIR__ . "/../includes/alerta.php");
require_once(__DIR__ . "/../includes/filtro_comentarios.php");




if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: ../index.php");

    exit;
}




if (!isset($_SESSION["cadastro_assinatura"])) {

    mostrarAlerta(
        "É necessário iniciar o cadastro pelo pagamento.",
        "window.location.href = '../index.php'"
    );

    exit;
}




$plano = $_SESSION["cadastro_assinatura"]["plano"];

$forma_pagamento =
    $_SESSION["cadastro_assinatura"]["forma_pagamento"];




$nome = trim($_POST["nome"] ?? "");

$cpf = trim($_POST["cpf"] ?? "");

$aniversario = $_POST["aniversario"] ?? "";

$email = trim($_POST["email"] ?? "");

$senha = $_POST["senha"] ?? "";




if (comentarioPossuiTermoProibido($nome)) {

    mostrarAlerta(
        "O nome informado contém termos que não são permitidos.",
        "window.location.href = '../cadastro.php'"
    );

    exit;
}




if (
    $nome === "" ||
    $cpf === "" ||
    $aniversario === "" ||
    $email === "" ||
    $senha === ""
) {

    mostrarAlerta(
        "Preencha todos os campos.",
        "window.location.href = '../cadastro.php'"
    );

    exit;
}




if (!preg_match('/^[^\s@]+@[^\s@]+\.com$/i', $email)) {

    mostrarAlerta(
        "Digite um e-mail válido.",
        "window.location.href = '../cadastro.php'"
    );

    exit;
}




$cpf = preg_replace("/\D/", "", $cpf);




if (strlen($cpf) !== 11) {

    mostrarAlerta(
        "Digite um CPF válido.",
        "window.location.href = '../cadastro.php'"
    );

    exit;
}




try {

    $data_nascimento = new DateTime($aniversario);

    $data_atual = new DateTime();

} catch (Exception $e) {

    mostrarAlerta(
        "Data de nascimento inválida.",
        "window.location.href = '../cadastro.php'"
    );

    exit;
}




if ($data_nascimento > $data_atual) {

    mostrarAlerta(
        "Data de nascimento inválida.",
        "window.location.href = '../cadastro.php'"
    );

    exit;
}




$idade = $data_atual->diff($data_nascimento)->y;




if ($idade < 18) {

 

    unset($_SESSION["cadastro_assinatura"]);

    mostrarAlerta(
        "É necessário ter 18 anos ou mais para criar uma conta.",
        "window.location.href = '../index.php'"
    );

    exit;
}




$sql = "SELECT id_usuario FROM usuario WHERE email = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    mostrarAlerta(
        "Erro ao consultar o banco de dados.",
        "window.location.href = '../index.php'"
    );

    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $email
);

if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    mostrarAlerta(
        "Erro ao consultar o e-mail.",
        "window.location.href = '../index.php'"
    );

    exit;
}

mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {

    mysqli_stmt_close($stmt);

    mostrarAlerta(
        "Este e-mail já possui uma conta.",
        "window.location.href = '../index.php'"
    );

    exit;
}

mysqli_stmt_close($stmt);




$sql = "
    SELECT 1
    FROM cliente
    WHERE cpf_cliente = ?
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    mostrarAlerta(
        "Erro ao consultar o CPF.",
        "window.location.href = '../index.php'"
    );

    exit;
}


mysqli_stmt_bind_param(
    $stmt,
    "s",
    $cpf
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);


if (mysqli_num_rows($resultado) > 0) {

    mysqli_stmt_close($stmt);

    mostrarAlerta(
        "Este CPF já possui uma conta.",
        "window.location.href = '../index.php'"
    );

    exit;
}

mysqli_stmt_close($stmt);



$tipo_usuario = "Cliente";

$status_cliente = "Ativo";

$status_assinatura = "Ativa";




$senha_hash = password_hash(
    $senha,
    PASSWORD_DEFAULT
);


if ($senha_hash === false) {

    mostrarAlerta(
        "Não foi possível proteger a senha da conta.",
        "window.location.href = '../cadastro.php'"
    );

    exit;
}




$data_inicio = new DateTime();


if ($plano === "Mensal") {

    $data_fim = clone $data_inicio;

    $data_fim->modify("+1 month");

} else {

    $data_fim = clone $data_inicio;

    $data_fim->modify("+1 year");
}


$data_inicio_assinatura =
    $data_inicio->format("Y-m-d");

$data_fim_assinatura =
    $data_fim->format("Y-m-d");




mysqli_begin_transaction($conn);


try {


   

    $sql = "
        INSERT INTO usuario
        (
            senha,
            email,
            aniversario,
            tipo_usuario
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?
        )
    ";

    $stmt = mysqli_prepare($conn, $sql);


    if (!$stmt) {

        throw new Exception();
    }


    mysqli_stmt_bind_param(
        $stmt,
        "ssss",
        $senha_hash,
        $email,
        $aniversario,
        $tipo_usuario
    );


    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception();
    }


  

    $id_usuario = mysqli_insert_id($conn);

    mysqli_stmt_close($stmt);


   

    $sql = "
        INSERT INTO cliente
        (
            nome_cliente,
            cpf_cliente,
            status_conta_cliente,
            FK_cliente_id_usuario
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?
        )
    ";

    $stmt = mysqli_prepare($conn, $sql);


    if (!$stmt) {

        throw new Exception();
    }


    mysqli_stmt_bind_param(
        $stmt,
        "sssi",
        $nome,
        $cpf,
        $status_cliente,
        $id_usuario
    );


    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception();
    }


    

    $id_cliente = mysqli_insert_id($conn);

    mysqli_stmt_close($stmt);


  

    $sql = "
        INSERT INTO assinatura
        (
            tipo_plano_assinatura,
            data_inicio_assinatura,
            data_fim_assinatura,
            status_assinatura,
            forma_pagamento_assinatura,
            FK_assinatura_id_cliente
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ";

    $stmt = mysqli_prepare($conn, $sql);


    if (!$stmt) {

        throw new Exception();
    }


    mysqli_stmt_bind_param(
        $stmt,
        "sssssi",
        $plano,
        $data_inicio_assinatura,
        $data_fim_assinatura,
        $status_assinatura,
        $forma_pagamento,
        $id_cliente
    );


    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception();
    }


    mysqli_stmt_close($stmt);


   

    mysqli_commit($conn);

    mysqli_close($conn);


   

    unset($_SESSION["cadastro_assinatura"]);


  

    mostrarAlerta(
        "Conta criada com sucesso!",
        "window.location.href = '../tela_inicial.php'"
    );

    exit;


} catch (Exception $e) {


  

    mysqli_rollback($conn);

    mysqli_close($conn);


    mostrarAlerta(
        "Não foi possível concluir o cadastro. Tente novamente.",
        "window.location.href = '../cadastro.php'"
    );

    exit;
}

?>