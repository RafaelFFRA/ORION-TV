<?php

session_start();

require_once(__DIR__ . "/conexao.php");
require_once(__DIR__ . "/../includes/alerta.php");
require_once(__DIR__ . "/manutencao_data_conteudo.php");


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: ../index.php");
    exit;
}


$email = trim($_POST["email"] ?? "");
$senha_digitada = $_POST["senha"] ?? "";


if ($email === "" || $senha_digitada === "") {

    mostrarAlerta("Preencha todos os campos.");
    exit;
}




if (!preg_match('/^[^\s@]+@[^\s@]+\.com$/i', $email)) {

    mostrarAlerta("Digite um e-mail válido.");
    exit;
}



$sql = "SELECT * FROM usuario WHERE email = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    mostrarAlerta("Erro ao consultar o banco de dados.");
    exit;
}


mysqli_stmt_bind_param(
    $stmt,
    "s",
    $email
);


if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    mostrarAlerta("Erro ao consultar o banco de dados.");
    exit;
}


$resultado = mysqli_stmt_get_result($stmt);




if (mysqli_num_rows($resultado) === 0) {

    mysqli_stmt_close($stmt);

    mostrarAlerta("E-mail ou senha incorretos.");
    exit;
}


$usuario = mysqli_fetch_assoc($resultado);




if (!password_verify(
    $senha_digitada,
    $usuario["senha"]
)) {

    mysqli_stmt_close($stmt);

    mostrarAlerta("E-mail ou senha incorretos.");
    exit;
}




if ($usuario["tipo_usuario"] === "Administrador") {

    $sql_status = "
        SELECT status
        FROM administrador
        WHERE FK_administrador_id_usuario = ?
        LIMIT 1
    ";

    $stmt_status = mysqli_prepare($conn, $sql_status);

    if (!$stmt_status) {

        mysqli_stmt_close($stmt);

        mostrarAlerta("Erro ao consultar o status da conta.");
        exit;
    }


    mysqli_stmt_bind_param(
        $stmt_status,
        "i",
        $usuario["id_usuario"]
    );


    if (!mysqli_stmt_execute($stmt_status)) {

        mysqli_stmt_close($stmt_status);
        mysqli_stmt_close($stmt);

        mostrarAlerta("Erro ao consultar o status da conta.");
        exit;
    }


    $resultado_status = mysqli_stmt_get_result($stmt_status);

    $dados_status = mysqli_fetch_assoc($resultado_status);

    mysqli_stmt_close($stmt_status);


   

    if (!$dados_status || $dados_status["status"] !== "Ativo") {

        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        mostrarAlerta(
            "Sua conta está inativa."
        );

        exit;
    }
}




if ($usuario["tipo_usuario"] === "Distribuidor") {

    $sql_status = "
        SELECT status
        FROM distribuidor
        WHERE FK_distribuidor_id_usuario = ?
        LIMIT 1
    ";

    $stmt_status = mysqli_prepare($conn, $sql_status);

    if (!$stmt_status) {

        mysqli_stmt_close($stmt);

        mostrarAlerta("Erro ao consultar o status da conta.");
        exit;
    }


    mysqli_stmt_bind_param(
        $stmt_status,
        "i",
        $usuario["id_usuario"]
    );


    if (!mysqli_stmt_execute($stmt_status)) {

        mysqli_stmt_close($stmt_status);
        mysqli_stmt_close($stmt);

        mostrarAlerta("Erro ao consultar o status da conta.");
        exit;
    }


    $resultado_status = mysqli_stmt_get_result($stmt_status);

    $dados_status = mysqli_fetch_assoc($resultado_status);

    mysqli_stmt_close($stmt_status);


    

    if (!$dados_status || $dados_status["status"] !== "Ativo") {

        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        mostrarAlerta(
            "Sua conta está inativa."
        );

        exit;
    }
}




if ($usuario["tipo_usuario"] === "Cliente") {

    require_once(__DIR__ . "/manutencao_data_conteudo.php");

    $sql_status = "
        SELECT
            c.status_conta_cliente,
            a.status_assinatura
        FROM cliente AS c
        LEFT JOIN assinatura AS a
            ON a.FK_assinatura_id_cliente = c.FK_cliente_id_cliente
        WHERE c.FK_cliente_id_usuario = ?
    ";

    $stmt_status = mysqli_prepare($conn, $sql_status);

    if (!$stmt_status) {

        mysqli_stmt_close($stmt);

        mostrarAlerta("Erro ao consultar o status da conta.");
        exit;
    }


    mysqli_stmt_bind_param(
        $stmt_status,
        "i",
        $usuario["id_usuario"]
    );


    if (!mysqli_stmt_execute($stmt_status)) {

        mysqli_stmt_close($stmt_status);
        mysqli_stmt_close($stmt);

        mostrarAlerta("Erro ao consultar o status da conta.");
        exit;
    }


    $resultado_status = mysqli_stmt_get_result($stmt_status);

    $status_conta = null;
    $status_assinatura = null;


    if (mysqli_num_rows($resultado_status) > 0) {

        $dados_status = mysqli_fetch_assoc($resultado_status);

        $status_conta = $dados_status["status_conta_cliente"];
        $status_assinatura = $dados_status["status_assinatura"];
    }


    mysqli_stmt_close($stmt_status);


   

    $conta_inativa =
        $status_conta === "Inativo" ||
        $status_conta === "Suspenso";


    if ($conta_inativa) {

        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        mostrarAlerta(
            "Sua conta está inativa. Não é possível entrar."
        );

        exit;
    }


    

    if ($status_assinatura === null) {

        session_regenerate_id(true);

        $_SESSION["id_usuario"] = $usuario["id_usuario"];
        $_SESSION["tipo_usuario"] = $usuario["tipo_usuario"];

        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        header("Location: ../sem_assinatura.php");
        exit;
    }


   

    if ($status_assinatura === "Inativa") {

        session_regenerate_id(true);

        $_SESSION["id_usuario"] = $usuario["id_usuario"];
        $_SESSION["tipo_usuario"] = $usuario["tipo_usuario"];

        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        header("Location: ../renovar_assinatura.php");
        exit;
    }


   

    if ($status_assinatura === "Vencida") {

        session_regenerate_id(true);

        $_SESSION["id_usuario"] = $usuario["id_usuario"];
        $_SESSION["tipo_usuario"] = $usuario["tipo_usuario"];

        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        header("Location: ../renovar_assinatura.php");
        exit;
    }
}




session_regenerate_id(true);

$_SESSION["id_usuario"] = $usuario["id_usuario"];
$_SESSION["tipo_usuario"] = $usuario["tipo_usuario"];




if ($usuario["tipo_usuario"] === "Administrador") {

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: ../escolha_admin.php");
    exit;
}


if ($usuario["tipo_usuario"] === "Distribuidor") {

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: ../escolha_distribuidor.php");
    exit;
}


if ($usuario["tipo_usuario"] === "Cliente") {

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: ../tela_inicial.php");
    exit;
}




mysqli_stmt_close($stmt);
mysqli_close($conn);

session_destroy();

mostrarAlerta("Tipo de usuário inválido.");

exit;