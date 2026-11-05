<?php

session_start();

require_once("conexao.php");
require_once(__DIR__ . "/../includes/alerta.php");

if (
    !isset($_SESSION["id_usuario"]) ||
    !isset($_SESSION["tipo_usuario"]) ||
    $_SESSION["tipo_usuario"] !== "Administrador"
) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../criar_contas_admin.php");
    exit;
}

$email = trim($_POST["email"] ?? "");
$senha = $_POST["senha"] ?? "";
$confirmar_senha = $_POST["confirmar_senha"] ?? "";
$tipo_usuario = $_POST["tipo_usuario"] ?? "";

$nome_admin = trim($_POST["nome_admin"] ?? "");

$empresa_distribuidor = trim($_POST["empresa_distribuidor"] ?? "");
$cnpj_empresa_distribuidor = trim($_POST["cnpj_empresa_distribuidor"] ?? "");

$nome_cliente = trim($_POST["nome_cliente"] ?? "");
$cpf_cliente = trim($_POST["cpf_cliente"] ?? "");
$status_conta_cliente = $_POST["status_conta_cliente"] ?? "Ativo";

if (
    $email === "" ||
    $senha === "" ||
    $confirmar_senha === "" ||
    $tipo_usuario === ""
) {
    mostrarAlerta(
        "Preencha todos os campos obrigatórios."
    );
    exit;
}

if ($senha !== $confirmar_senha) {
    mostrarAlerta(
        "As senhas não coincidem."
    );
    exit;
}

$tiposPermitidos = [
    "Administrador",
    "Distribuidor",
    "Cliente"
];

if (!in_array($tipo_usuario, $tiposPermitidos, true)) {
    mostrarAlerta(
        "Tipo de usuário inválido."
    );
    exit;
}

if ($tipo_usuario === "Administrador") {
    if ($nome_admin === "") {
        mostrarAlerta(
            "Digite o nome do administrador."
        );
        exit;
    }
}

if ($tipo_usuario === "Distribuidor") {
    if (
        $empresa_distribuidor === "" ||
        $cnpj_empresa_distribuidor === ""
    ) {
        mostrarAlerta(
            "Preencha o nome da empresa e o CNPJ."
        );
        exit;
    }

    $cnpjNumeros = preg_replace(
        "/\D/",
        "",
        $cnpj_empresa_distribuidor
    );

    if (strlen($cnpjNumeros) !== 14) {
        mostrarAlerta(
            "Digite um CNPJ válido."
        );
        exit;
    }

    $cnpj_empresa_distribuidor =
        substr($cnpjNumeros, 0, 2) . "." .
        substr($cnpjNumeros, 2, 3) . "." .
        substr($cnpjNumeros, 5, 3) . "/" .
        substr($cnpjNumeros, 8, 4) . "-" .
        substr($cnpjNumeros, 12, 2);
}

if ($tipo_usuario === "Cliente") {
    if (
        $nome_cliente === "" ||
        $cpf_cliente === ""
    ) {
        mostrarAlerta(
            "Preencha o nome e o CPF do cliente."
        );
        exit;
    }

    $cpfNumeros = preg_replace(
        "/\D/",
        "",
        $cpf_cliente
    );

    if (strlen($cpfNumeros) !== 11) {
        mostrarAlerta(
            "Digite um CPF válido."
        );
        exit;
    }

    $cpf_cliente =
        substr($cpfNumeros, 0, 3) . "." .
        substr($cpfNumeros, 3, 3) . "." .
        substr($cpfNumeros, 6, 3) . "-" .
        substr($cpfNumeros, 9, 2);

    $statusPermitidos = [
        "Ativo",
        "Inativo",
        "Suspenso"
    ];

    if (
        !in_array(
            $status_conta_cliente,
            $statusPermitidos,
            true
        )
    ) {
        mostrarAlerta(
            "Status de cliente inválido."
        );
        exit;
    }
}

$sql_verificar = "
    SELECT id_usuario
    FROM usuario
    WHERE email = ?
";

$stmt = mysqli_prepare(
    $conn,
    $sql_verificar
);

if (!$stmt) {
    mostrarAlerta(
        "Erro ao verificar o e-mail."
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

if (mysqli_num_rows($resultado) > 0) {
    mysqli_stmt_close($stmt);

    mostrarAlerta(
        "Este e-mail já possui uma conta."
    );
    exit;
}

mysqli_stmt_close($stmt);

if ($tipo_usuario === "Cliente") {
    $sql_verificar = "
        SELECT FK_cliente_id_cliente
        FROM cliente
        WHERE cpf_cliente = ?
    ";

    $stmt = mysqli_prepare(
        $conn,
        $sql_verificar
    );

    if (!$stmt) {
        mostrarAlerta(
            "Erro ao verificar o CPF."
        );
        exit;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $cpf_cliente
    );

    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($resultado) > 0) {
        mysqli_stmt_close($stmt);

        mostrarAlerta(
            "Este CPF já está cadastrado."
        );
        exit;
    }

    mysqli_stmt_close($stmt);
}

if ($tipo_usuario === "Distribuidor") {
    $sql_verificar = "
        SELECT id_distribuidor
        FROM distribuidor
        WHERE cnpj_empresa_distribuidor = ?
    ";

    $stmt = mysqli_prepare(
        $conn,
        $sql_verificar
    );

    if (!$stmt) {
        mostrarAlerta(
            "Erro ao verificar o CNPJ."
        );
        exit;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $cnpj_empresa_distribuidor
    );

    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($resultado) > 0) {
        mysqli_stmt_close($stmt);

        mostrarAlerta(
            "Este CNPJ já está cadastrado."
        );
        exit;
    }

    mysqli_stmt_close($stmt);
}

$senha_hash = password_hash(
    $senha,
    PASSWORD_DEFAULT
);

if ($senha_hash === false) {
    mostrarAlerta(
        "Erro ao proteger a senha."
    );
    exit;
}

mysqli_begin_transaction($conn);

try {

    $sql_usuario = "
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
            NULL,
            ?
        )
    ";

    $stmt = mysqli_prepare(
        $conn,
        $sql_usuario
    );

    if (!$stmt) {
        throw new Exception(
            "Erro ao preparar criação do usuário."
        );
    }

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $senha_hash,
        $email,
        $tipo_usuario
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(
            "Erro ao criar usuário: " .
            mysqli_stmt_error($stmt)
        );
    }

    $id_usuario = mysqli_insert_id($conn);

    mysqli_stmt_close($stmt);

    if (!$id_usuario) {
        throw new Exception(
            "Não foi possível obter o ID do usuário."
        );
    }

    if ($tipo_usuario === "Administrador") {

        $sql_admin = "
            INSERT INTO administrador
            (
                nome_admin,
                FK_administrador_id_usuario
            )
            VALUES
            (
                ?,
                ?
            )
        ";

        $stmt = mysqli_prepare(
            $conn,
            $sql_admin
        );

        if (!$stmt) {
            throw new Exception(
                "Erro ao preparar administrador: " .
                mysqli_error($conn)
            );
        }

        mysqli_stmt_bind_param(
            $stmt,
            "si",
            $nome_admin,
            $id_usuario
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(
                "Erro ao criar administrador: " .
                mysqli_stmt_error($stmt)
            );
        }

        mysqli_stmt_close($stmt);

    } elseif ($tipo_usuario === "Distribuidor") {

        $sql_distribuidor = "
            INSERT INTO distribuidor
            (
                empresa_distribuidor,
                cnpj_empresa_distribuidor,
                FK_distribuidor_id_usuario
            )
            VALUES
            (
                ?,
                ?,
                ?
            )
        ";

        $stmt = mysqli_prepare(
            $conn,
            $sql_distribuidor
        );

        if (!$stmt) {
            throw new Exception(
                "Erro ao preparar distribuidor: " .
                mysqli_error($conn)
            );
        }

        mysqli_stmt_bind_param(
            $stmt,
            "ssi",
            $empresa_distribuidor,
            $cnpj_empresa_distribuidor,
            $id_usuario
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(
                "Erro ao criar distribuidor: " .
                mysqli_stmt_error($stmt)
            );
        }

        mysqli_stmt_close($stmt);

    } elseif ($tipo_usuario === "Cliente") {

        $sql_cliente = "
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

        $stmt = mysqli_prepare(
            $conn,
            $sql_cliente
        );

        if (!$stmt) {
            throw new Exception(
                "Erro ao preparar cliente: " .
                mysqli_error($conn)
            );
        }

        mysqli_stmt_bind_param(
            $stmt,
            "sssi",
            $nome_cliente,
            $cpf_cliente,
            $status_conta_cliente,
            $id_usuario
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(
                "Erro ao criar cliente: " .
                mysqli_stmt_error($stmt)
            );
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_commit($conn);

    mysqli_close($conn);

    mostrarAlerta(
        "Conta criada com sucesso!"
    );

    exit;

} catch (Throwable $erro) {

    mysqli_rollback($conn);

    $mensagem =
        "Erro ao criar conta: " .
        $erro->getMessage();

    mysqli_close($conn);

    mostrarAlerta(
        $mensagem
    );

    exit;
}

?>