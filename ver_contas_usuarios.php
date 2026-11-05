<?php

require_once(__DIR__ . "/php/conexao.php");
require_once(__DIR__ . "/includes/proteger_admin.php");

$busca = trim($_GET["busca"] ?? "");

$filtro = $_GET["tipo"] ?? "Todos";

$tiposPermitidos = [
    "Todos",
    "Administrador",
    "Distribuidor",
    "Cliente"
];

if (!in_array($filtro, $tiposPermitidos, true)) {
    $filtro = "Todos";
}

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    &&
    isset($_POST["alterar_status"])
) {

    $id_usuario = filter_input(
        INPUT_POST,
        "id_usuario",
        FILTER_VALIDATE_INT
    );

    if (!$id_usuario) {

        header(
            "Location: ver_contas_usuarios.php?tipo=" .
            urlencode($filtro) .
            "&erro=id_invalido"
        );

        exit;
    }

    mysqli_begin_transaction($conn);

    try {

        $sql = "
            SELECT
                u.tipo_usuario,
                a.status AS status_administrador,
                d.status AS status_distribuidor,
                c.status_conta_cliente
            FROM usuario AS u

            LEFT JOIN administrador AS a
                ON a.FK_administrador_id_usuario = u.id_usuario

            LEFT JOIN distribuidor AS d
                ON d.FK_distribuidor_id_usuario = u.id_usuario

            LEFT JOIN cliente AS c
                ON c.FK_cliente_id_usuario = u.id_usuario

            WHERE u.id_usuario = ?
        ";

        $stmt = mysqli_prepare(
            $conn,
            $sql
        );

        if (!$stmt) {

            throw new Exception(
                "Erro ao preparar a consulta do status."
            );
        }

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $id_usuario
        );

        mysqli_stmt_execute($stmt);

        $resultadoUsuario =
            mysqli_stmt_get_result($stmt);

        $usuario =
            mysqli_fetch_assoc($resultadoUsuario);

        mysqli_stmt_close($stmt);

        if (!$usuario) {

            throw new Exception(
                "Conta não encontrada."
            );
        }

        $tipoUsuario =
            $usuario["tipo_usuario"];

        if ($tipoUsuario === "Administrador") {

            $statusAtual =
                $usuario["status_administrador"] ?? "Ativo";

            $novoStatus =
                $statusAtual === "Ativo"
                ? "Inativo"
                : "Ativo";

            $sql = "
                UPDATE administrador
                SET status = ?
                WHERE FK_administrador_id_usuario = ?
            ";

            $stmt = mysqli_prepare(
                $conn,
                $sql
            );

            if (!$stmt) {

                throw new Exception(
                    "Erro ao preparar alteração do administrador."
                );
            }

            mysqli_stmt_bind_param(
                $stmt,
                "si",
                $novoStatus,
                $id_usuario
            );

            if (!mysqli_stmt_execute($stmt)) {

                throw new Exception(
                    "Erro ao alterar status do administrador."
                );
            }

            $linhasAfetadas =
                mysqli_stmt_affected_rows($stmt);

            mysqli_stmt_close($stmt);

            if ($linhasAfetadas <= 0) {

                throw new Exception(
                    "Administrador não encontrado."
                );
            }
        }

        elseif ($tipoUsuario === "Distribuidor") {

            $statusAtual =
                $usuario["status_distribuidor"] ?? "Ativo";

            $novoStatus =
                $statusAtual === "Ativo"
                ? "Inativo"
                : "Ativo";

            $sql = "
                UPDATE distribuidor
                SET status = ?
                WHERE FK_distribuidor_id_usuario = ?
            ";

            $stmt = mysqli_prepare(
                $conn,
                $sql
            );

            if (!$stmt) {

                throw new Exception(
                    "Erro ao preparar alteração do distribuidor."
                );
            }

            mysqli_stmt_bind_param(
                $stmt,
                "si",
                $novoStatus,
                $id_usuario
            );

            if (!mysqli_stmt_execute($stmt)) {

                throw new Exception(
                    "Erro ao alterar status do distribuidor."
                );
            }

            $linhasAfetadas =
                mysqli_stmt_affected_rows($stmt);

            mysqli_stmt_close($stmt);

            if ($linhasAfetadas <= 0) {

                throw new Exception(
                    "Distribuidor não encontrado."
                );
            }
        }

        elseif ($tipoUsuario === "Cliente") {

            $statusAtual =
                $usuario["status_conta_cliente"] ?? "Ativo";

            $novoStatus =
                $statusAtual === "Ativo"
                ? "Inativo"
                : "Ativo";

            $sql = "
                UPDATE cliente
                SET status_conta_cliente = ?
                WHERE FK_cliente_id_usuario = ?
            ";

            $stmt = mysqli_prepare(
                $conn,
                $sql
            );

            if (!$stmt) {

                throw new Exception(
                    "Erro ao preparar alteração do cliente."
                );
            }

            mysqli_stmt_bind_param(
                $stmt,
                "si",
                $novoStatus,
                $id_usuario
            );

            if (!mysqli_stmt_execute($stmt)) {

                throw new Exception(
                    "Erro ao alterar status do cliente."
                );
            }

            $linhasAfetadas =
                mysqli_stmt_affected_rows($stmt);

            mysqli_stmt_close($stmt);

            if ($linhasAfetadas <= 0) {

                throw new Exception(
                    "Cliente não encontrado."
                );
            }
        }

        else {

            throw new Exception(
                "Tipo de conta inválido."
            );
        }

        mysqli_commit($conn);

        $mensagemStatus =
            $novoStatus === "Ativo"
            ? "status_ativado"
            : "status_inativado";

        header(
            "Location: ver_contas_usuarios.php?tipo=" .
            urlencode($filtro) .
            "&sucesso=" .
            $mensagemStatus
        );

        exit;

    } catch (Throwable $erro) {

        mysqli_rollback($conn);

        header(
            "Location: ver_contas_usuarios.php?tipo=" .
            urlencode($filtro) .
            "&erro=" .
            urlencode($erro->getMessage())
        );

        exit;
    }
}

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    &&
    isset($_POST["excluir_conta"])
) {

    $id_usuario = filter_input(
        INPUT_POST,
        "id_usuario",
        FILTER_VALIDATE_INT
    );

    if (!$id_usuario) {

        header(
            "Location: ver_contas_usuarios.php?tipo=" .
            urlencode($filtro) .
            "&erro=id_invalido"
        );

        exit;
    }

    mysqli_begin_transaction($conn);

    try {

        $sql = "
            SELECT tipo_usuario
            FROM usuario
            WHERE id_usuario = ?
        ";

        $stmt = mysqli_prepare(
            $conn,
            $sql
        );

        if (!$stmt) {

            throw new Exception(
                "Erro ao preparar a consulta."
            );
        }

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $id_usuario
        );

        mysqli_stmt_execute($stmt);

        $resultadoUsuario =
            mysqli_stmt_get_result($stmt);

        $usuario =
            mysqli_fetch_assoc($resultadoUsuario);

        mysqli_stmt_close($stmt);

        if (!$usuario) {

            throw new Exception(
                "Conta não encontrada."
            );
        }

        $tipoUsuario =
            $usuario["tipo_usuario"];

        if ($tipoUsuario === "Cliente") {

            $sql = "
                SELECT FK_cliente_id_cliente
                FROM cliente
                WHERE FK_cliente_id_usuario = ?
            ";

            $stmt = mysqli_prepare(
                $conn,
                $sql
            );

            if (!$stmt) {

                throw new Exception(
                    "Erro ao buscar cliente."
                );
            }

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $id_usuario
            );

            mysqli_stmt_execute($stmt);

            $resultadoCliente =
                mysqli_stmt_get_result($stmt);

            $cliente =
                mysqli_fetch_assoc($resultadoCliente);

            mysqli_stmt_close($stmt);

            if ($cliente) {

                $id_cliente =
                    $cliente["FK_cliente_id_cliente"];

                $sql = "
                    DELETE FROM avaliacao
                    WHERE FK_avaliacao_id_cliente = ?
                ";

                $stmt = mysqli_prepare(
                    $conn,
                    $sql
                );

                if (!$stmt) {

                    throw new Exception(
                        "Erro ao excluir avaliações do cliente."
                    );
                }

                mysqli_stmt_bind_param(
                    $stmt,
                    "i",
                    $id_cliente
                );

                if (!mysqli_stmt_execute($stmt)) {

                    throw new Exception(
                        "Erro ao excluir avaliações do cliente."
                    );
                }

                mysqli_stmt_close($stmt);

                $sql = "
                    DELETE FROM assinatura
                    WHERE FK_assinatura_id_cliente = ?
                ";

                $stmt = mysqli_prepare(
                    $conn,
                    $sql
                );

                if (!$stmt) {

                    throw new Exception(
                        "Erro ao excluir assinatura do cliente."
                    );
                }

                mysqli_stmt_bind_param(
                    $stmt,
                    "i",
                    $id_cliente
                );

                if (!mysqli_stmt_execute($stmt)) {

                    throw new Exception(
                        "Erro ao excluir assinatura do cliente."
                    );
                }

                mysqli_stmt_close($stmt);

                $sql = "
                    DELETE FROM cliente
                    WHERE FK_cliente_id_usuario = ?
                ";

                $stmt = mysqli_prepare(
                    $conn,
                    $sql
                );

                if (!$stmt) {

                    throw new Exception(
                        "Erro ao preparar exclusão do cliente."
                    );
                }

                mysqli_stmt_bind_param(
                    $stmt,
                    "i",
                    $id_usuario
                );

                if (!mysqli_stmt_execute($stmt)) {

                    throw new Exception(
                        "Erro ao excluir cliente."
                    );
                }

                mysqli_stmt_close($stmt);
            }
        }

        elseif ($tipoUsuario === "Administrador") {

            $sql = "
                DELETE FROM administrador
                WHERE FK_administrador_id_usuario = ?
            ";

            $stmt = mysqli_prepare(
                $conn,
                $sql
            );

            if (!$stmt) {

                throw new Exception(
                    "Erro ao excluir administrador."
                );
            }

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $id_usuario
            );

            if (!mysqli_stmt_execute($stmt)) {

                throw new Exception(
                    "Erro ao excluir administrador."
                );
            }

            mysqli_stmt_close($stmt);
        }

        elseif ($tipoUsuario === "Distribuidor") {

            $sql = "
                SELECT id_distribuidor
                FROM distribuidor
                WHERE FK_distribuidor_id_usuario = ?
            ";

            $stmt = mysqli_prepare(
                $conn,
                $sql
            );

            if (!$stmt) {

                throw new Exception(
                    "Erro ao buscar distribuidor."
                );
            }

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $id_usuario
            );

            mysqli_stmt_execute($stmt);

            $resultadoDistribuidor =
                mysqli_stmt_get_result($stmt);

            $distribuidor =
                mysqli_fetch_assoc($resultadoDistribuidor);

            mysqli_stmt_close($stmt);

            if ($distribuidor) {

                $id_distribuidor =
                    $distribuidor["id_distribuidor"];

                $sql = "
                    SELECT id_conteudo
                    FROM conteudo
                    WHERE FK_conteudo_id_distribuidor = ?
                ";

                $stmt = mysqli_prepare(
                    $conn,
                    $sql
                );

                if (!$stmt) {

                    throw new Exception(
                        "Erro ao buscar conteúdos."
                    );
                }

                mysqli_stmt_bind_param(
                    $stmt,
                    "i",
                    $id_distribuidor
                );

                mysqli_stmt_execute($stmt);

                $resultadoConteudos =
                    mysqli_stmt_get_result($stmt);

                while (
                    $conteudo =
                    mysqli_fetch_assoc($resultadoConteudos)
                ) {

                    $id_conteudo =
                        $conteudo["id_conteudo"];

                    $sqlAvaliacao = "
                        DELETE FROM avaliacao
                        WHERE FK_avaliacao_id_conteudo = ?
                    ";

                    $stmtAvaliacao =
                        mysqli_prepare(
                            $conn,
                            $sqlAvaliacao
                        );

                    if (!$stmtAvaliacao) {

                        throw new Exception(
                            "Erro ao excluir avaliações."
                        );
                    }

                    mysqli_stmt_bind_param(
                        $stmtAvaliacao,
                        "i",
                        $id_conteudo
                    );

                    if (!mysqli_stmt_execute($stmtAvaliacao)) {

                        throw new Exception(
                            "Erro ao excluir avaliações."
                        );
                    }

                    mysqli_stmt_close(
                        $stmtAvaliacao
                    );
                }

                mysqli_stmt_close($stmt);

                $sql = "
                    DELETE FROM conteudo
                    WHERE FK_conteudo_id_distribuidor = ?
                ";

                $stmt = mysqli_prepare(
                    $conn,
                    $sql
                );

                if (!$stmt) {

                    throw new Exception(
                        "Erro ao excluir conteúdos."
                    );
                }

                mysqli_stmt_bind_param(
                    $stmt,
                    "i",
                    $id_distribuidor
                );

                if (!mysqli_stmt_execute($stmt)) {

                    throw new Exception(
                        "Erro ao excluir conteúdos."
                    );
                }

                mysqli_stmt_close($stmt);

                $sql = "
                    DELETE FROM distribuidor
                    WHERE FK_distribuidor_id_usuario = ?
                ";

                $stmt = mysqli_prepare(
                    $conn,
                    $sql
                );

                if (!$stmt) {

                    throw new Exception(
                        "Erro ao preparar exclusão do distribuidor."
                    );
                }

                mysqli_stmt_bind_param(
                    $stmt,
                    "i",
                    $id_usuario
                );

                if (!mysqli_stmt_execute($stmt)) {

                    throw new Exception(
                        "Erro ao excluir distribuidor."
                    );
                }

                mysqli_stmt_close($stmt);
            }
        }

        $sql = "
            DELETE FROM usuario
            WHERE id_usuario = ?
        ";

        $stmt = mysqli_prepare(
            $conn,
            $sql
        );

        if (!$stmt) {

            throw new Exception(
                "Erro ao excluir usuário."
            );
        }

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $id_usuario
        );

        if (!mysqli_stmt_execute($stmt)) {

            throw new Exception(
                "Erro ao excluir usuário."
            );
        }

        $linhasAfetadas =
            mysqli_stmt_affected_rows($stmt);

        mysqli_stmt_close($stmt);

        if ($linhasAfetadas <= 0) {

            throw new Exception(
                "Não foi possível excluir a conta."
            );
        }

        mysqli_commit($conn);

        header(
            "Location: ver_contas_usuarios.php?tipo=" .
            urlencode($filtro) .
            "&sucesso=excluido"
        );

        exit;

    } catch (Throwable $erro) {

        mysqli_rollback($conn);

        header(
            "Location: ver_contas_usuarios.php?tipo=" .
            urlencode($filtro) .
            "&erro=" .
            urlencode($erro->getMessage())
        );

        exit;
    }
}

$sql = "
    SELECT
        u.id_usuario,
        u.email,
        u.tipo_usuario,

        a.status AS status_administrador,

        d.status AS status_distribuidor,

        c.status_conta_cliente,

        CASE
            WHEN u.tipo_usuario = 'Administrador'
                THEN a.nome_admin

            WHEN u.tipo_usuario = 'Distribuidor'
                THEN d.empresa_distribuidor

            WHEN u.tipo_usuario = 'Cliente'
                THEN c.nome_cliente

            ELSE ''
        END AS nome,

        CASE
            WHEN u.tipo_usuario = 'Distribuidor'
                THEN d.cnpj_empresa_distribuidor

            WHEN u.tipo_usuario = 'Cliente'
                THEN c.cpf_cliente

            ELSE ''
        END AS cpf_cnpj

    FROM usuario AS u

    LEFT JOIN administrador AS a
        ON a.FK_administrador_id_usuario = u.id_usuario

    LEFT JOIN distribuidor AS d
        ON d.FK_distribuidor_id_usuario = u.id_usuario

    LEFT JOIN cliente AS c
        ON c.FK_cliente_id_usuario = u.id_usuario
";

$parametros = [];
$tipos = "";

if ($filtro !== "Todos") {

    $sql .= "
        WHERE u.tipo_usuario = ?
    ";

    $parametros[] = $filtro;
    $tipos .= "s";
}

if ($busca !== "") {

    $sql .= $filtro !== "Todos"
        ? " AND ("
        : " WHERE (";

    $sql .= "
        CAST(u.id_usuario AS CHAR) LIKE ?
        OR (
            CASE
                WHEN u.tipo_usuario = 'Administrador'
                    THEN a.nome_admin

                WHEN u.tipo_usuario = 'Distribuidor'
                    THEN d.empresa_distribuidor

                WHEN u.tipo_usuario = 'Cliente'
                    THEN c.nome_cliente

                ELSE ''
            END
        ) LIKE ?

        OR u.email LIKE ?

        OR (
            CASE
                WHEN u.tipo_usuario = 'Distribuidor'
                    THEN d.cnpj_empresa_distribuidor

                WHEN u.tipo_usuario = 'Cliente'
                    THEN c.cpf_cliente

                ELSE ''
            END
        ) LIKE ?

        OR u.tipo_usuario LIKE ?
    )
    ";

    $termoBusca = "%" . $busca . "%";

    $parametros[] = $termoBusca;
    $parametros[] = $termoBusca;
    $parametros[] = $termoBusca;
    $parametros[] = $termoBusca;
    $parametros[] = $termoBusca;

    $tipos .= "sssss";
}

$sql .= "
    ORDER BY u.id_usuario DESC
";

$stmt = mysqli_prepare(
    $conn,
    $sql
);

if (!$stmt) {

    die(
        "Erro ao consultar as contas: " .
        mysqli_error($conn)
    );
}

if (!empty($parametros)) {

    mysqli_stmt_bind_param(
        $stmt,
        $tipos,
        ...$parametros
    );
}

mysqli_stmt_execute($stmt);

$resultado =
    mysqli_stmt_get_result($stmt);

$contas = [];

while (
    $linha =
    mysqli_fetch_assoc($resultado)
) {

    $contas[] = $linha;
}

mysqli_stmt_close($stmt);

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Ver Contas - ORION TV</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
>

<style>

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html,
    body {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    body {
        background-color: #080808;
        min-height: 100vh;
        color: white;
        font-family: Arial, Helvetica, sans-serif;

        overflow-x: clip;
    }

    .topo {

        width: 100%;
        max-width: 100%;

        min-height: 80px;

        display: flex;
        align-items: center;
        justify-content: space-between;

        padding: 15px 5%;

        background-color: #080808;

        border-bottom: 1px solid #292929;

        gap: 15px;

  
        position: sticky;
        top: 0;
        z-index: 1030;
    }

    .logo {

        font-size: 28px;
        font-weight: bold;

        color: #168cff;

        letter-spacing: 1px;

        text-decoration: none;

        flex-shrink: 0;
    }

    .logo:hover {
        color: #168cff;
    }

    .botoes-topo {

        display: flex;
        align-items: center;

        gap: 10px;

        min-width: 0;
    }

    .btn-voltar {

        text-decoration: none;

        color: white;

        background-color: #222;

        border: 1px solid #333;

        padding: 10px 20px;

        border-radius: 6px;

        transition: 0.2s;

        white-space: nowrap;
    }

    .btn-voltar:hover {

        background-color: #333;

        color: white;
    }

    .btn-criar {

        text-decoration: none;

        color: white;

        background-color: #168cff;

        padding: 10px 20px;

        border-radius: 6px;

        transition: 0.2s;

        white-space: nowrap;
    }

    .btn-criar:hover {

        background-color: #006dcc;

        color: white;
    }

    .pagina-container {

        width: 100%;
        max-width: 1250px;

        margin: 0 auto;

        padding: 45px 20px 70px;

        min-width: 0;
    }

    h1 {

        font-size: 30px;

        margin-bottom: 8px;

        overflow-wrap: anywhere;
    }

    .descricao {

        color: #999;

        margin-bottom: 30px;

        overflow-wrap: anywhere;
    }

    .filtros {

        display: flex;

        flex-wrap: wrap;

        gap: 10px;

        margin-bottom: 25px;

        width: 100%;

        min-width: 0;
    }

    .filtro {

        text-decoration: none;

        background-color: #1b1b1b;

        border: 1px solid #333;

        color: #ccc;

        padding: 9px 18px;

        border-radius: 6px;

        transition: 0.2s;

        min-width: 0;

        max-width: 100%;
    }

    .filtro:hover {

        border-color: #168cff;

        color: white;
    }

    .filtro.ativo {

        background-color: #168cff;

        border-color: #168cff;

        color: white;
    }

    .filtros .campo-busca {

        min-width: 280px;

        flex: 1;
    }

    .filtros .filtro-buscar {

        cursor: pointer;
    }

    .lista-card {

        width: 100%;
        max-width: 100%;

        background-color: #111;

        border: 1px solid #292929;

        border-radius: 12px;

        overflow: hidden;

        min-width: 0;
    }

    .tabela-container {

        width: 100%;

        max-width: 100%;

        overflow-x: auto;
    }

    .tabela-contas {

        width: 100%;

        border-collapse: collapse;

        min-width: 1150px;

   
        table-layout: fixed;
    }

    .tabela-contas th {

        background-color: #171717;

        color: #999;

        font-size: 13px;

        font-weight: normal;

        text-align: left;

        padding: 17px 16px;

        border-bottom: 1px solid #292929;

        white-space: nowrap;
    }

    .tabela-contas td {

        padding: 18px 16px;

        border-bottom: 1px solid #292929;

        font-size: 14px;

        vertical-align: middle;

   
        word-break: break-word;

        overflow-wrap: anywhere;
    }

    .tabela-contas tbody tr:last-child td {

        border-bottom: none;
    }

    .tabela-contas tbody tr:hover {

        background-color: #171717;
    }

    .nome-usuario {

        font-weight: bold;

        color: #fff;

        overflow-wrap: anywhere;
    }

    .coluna-email,
    .coluna-cpf-cnpj,
    .coluna-tipo,
    .coluna-status,
    .coluna-acao {

        text-align: center !important;
    }

    .tabela-contas th:first-child {

        width: 80px;
    }

    .tabela-contas th.coluna-cpf-cnpj {

        width: 168px;
    }

    .tabela-contas th.coluna-tipo {

        width: 144px;
    }

    .tabela-contas th.coluna-status {

        width: 136px;
    }

    .tabela-contas th.coluna-acao {

        width: 236px;
    }

    .tipo {

        display: inline-block;

        padding: 5px 10px;

        border-radius: 20px;

        font-size: 12px;

        white-space: nowrap;

        max-width: 100%;
    }

    .tipo-administrador {

        background-color: rgba(22, 140, 255, 0.15);

        color: #5db2ff;
    }

    .tipo-distribuidor {

        background-color: rgba(130, 90, 255, 0.15);

        color: #b69cff;
    }

    .tipo-cliente {

        background-color: rgba(50, 200, 120, 0.12);

        color: #63d99a;
    }

    .status {

        display: inline-block;

        padding: 5px 10px;

        border-radius: 20px;

        font-size: 12px;

        font-weight: 500;

        white-space: nowrap;
    }

    .status-ativo {

        background-color: rgba(50, 200, 120, 0.12);

        color: #63d99a;

        border: 1px solid rgba(50, 200, 120, 0.18);
    }

    .status-inativo {

        background-color: rgba(220, 150, 30, 0.12);

        color: #e6b55d;

        border: 1px solid rgba(220, 150, 30, 0.18);
    }

    .acoes-conta {

        display: flex;

        align-items: center;

        justify-content: center;

        gap: 7px;

        flex-wrap: wrap;
    }

    .acoes-conta form {

        margin: 0;
    }

    .btn-inativar {

        background-color: rgba(220, 150, 30, 0.12);

        border: 1px solid #5a431d;

        color: #e6b55d;

        padding: 7px 11px;

        border-radius: 6px;

        cursor: pointer;

        white-space: nowrap;

        transition: 0.2s;

        max-width: 100%;
    }

    .btn-inativar:hover {

        background-color: #9a6b18;

        color: white;
    }

    .btn-ativar {

        background-color: rgba(50, 180, 100, 0.12);

        border: 1px solid #244a33;

        color: #65d18e;

        padding: 7px 11px;

        border-radius: 6px;

        cursor: pointer;

        white-space: nowrap;

        transition: 0.2s;

        max-width: 100%;
    }

    .btn-ativar:hover {

        background-color: #2d8a50;

        color: white;
    }

    .btn-excluir {

        background-color: rgba(180, 40, 40, 0.12);

        border: 1px solid #4a2020;

        color: #ff7777;

        padding: 7px 12px;

        border-radius: 6px;

        cursor: pointer;

        white-space: nowrap;

        transition: 0.2s;

        max-width: 100%;
    }

    .btn-excluir:hover {

        background-color: #b52b2b;

        color: white;
    }

    .sem-contas {

        text-align: center;

        padding: 70px 20px;

        color: #999;

        overflow-wrap: anywhere;
    }

    .sem-contas i {

        display: block;

        font-size: 42px;

        color: #444;

        margin-bottom: 15px;
    }

    .sem-contas strong {

        display: block;

        color: #ccc;

        margin-bottom: 8px;

        font-size: 17px;
    }

    .cards-mobile {

        display: none;

        width: 100%;

        min-width: 0;
    }

    .conta-card {

        width: 100%;
        max-width: 100%;

        padding: 18px;

        border-bottom: 1px solid #292929;

        min-width: 0;
    }

    .conta-card:last-child {

        border-bottom: none;
    }

    .conta-card-topo {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 12px;

        margin-bottom: 18px;

        min-width: 0;
    }

    .conta-card-nome {

        font-size: 16px;

        font-weight: bold;

        color: white;

        word-break: break-word;

        overflow-wrap: anywhere;

        min-width: 0;
    }

    .conta-card-id {

        color: #666;

        font-size: 12px;

        flex-shrink: 0;
    }

    .conta-info {

        display: flex;

        flex-direction: column;

        gap: 12px;

        min-width: 0;
    }

    .info-item {

        display: flex;

        flex-direction: column;

        gap: 4px;

        min-width: 0;
    }

    .info-label {

        color: #777;

        font-size: 11px;

        text-transform: uppercase;

        letter-spacing: 0.5px;
    }

    .info-valor {

        color: #ddd;

        font-size: 14px;

        word-break: break-word;

        overflow-wrap: anywhere;

        min-width: 0;
    }

    .conta-card-footer {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 12px;

        margin-top: 18px;

        min-width: 0;
    }

    .conta-card-acoes {

        display: flex;

        align-items: center;

        justify-content: flex-end;

        gap: 7px;

        flex-wrap: wrap;

        min-width: 0;
    }

    .conta-card-acoes form {

        margin: 0;
    }

    .modal-exclusao {

        position: fixed;

        inset: 0;

        z-index: 9999;

        display: none;

        align-items: center;

        justify-content: center;

        padding: 20px;

        background-color: rgba(0, 0, 0, 0.78);

        backdrop-filter: blur(4px);
    }

    .modal-exclusao.aberto {

        display: flex;
    }

    .modal-caixa {

        width: 100%;

        max-width: 440px;

        background-color: #111;

        border: 1px solid #303030;

        border-radius: 14px;

        padding: 28px;

        box-shadow:
            0 20px 60px rgba(0, 0, 0, 0.7);

        animation: aparecerModal 0.18s ease-out;

        min-width: 0;
    }

    @keyframes aparecerModal {

        from {

            opacity: 0;

            transform: scale(0.96);
        }

        to {

            opacity: 1;

            transform: scale(1);
        }
    }

    .modal-icone {

        width: 52px;

        height: 52px;

        margin: 0 auto 18px;

        display: flex;

        align-items: center;

        justify-content: center;

        border-radius: 50%;

        background-color: rgba(180, 40, 40, 0.14);

        border: 1px solid #4a2020;

        color: #ff6f6f;

        font-size: 23px;
    }

    .modal-titulo {

        text-align: center;

        color: white;

        font-size: 21px;

        font-weight: bold;

        margin-bottom: 10px;
    }

    .modal-texto {

        text-align: center;

        color: #999;

        font-size: 14px;

        line-height: 1.5;

        margin-bottom: 22px;
    }

    .modal-conta {

        background-color: #191919;

        border: 1px solid #292929;

        border-radius: 9px;

        padding: 14px 15px;

        margin-bottom: 22px;

        min-width: 0;
    }

    .modal-conta-nome {

        color: white;

        font-weight: bold;

        font-size: 15px;

        margin-bottom: 6px;

        word-break: break-word;

        overflow-wrap: anywhere;
    }

    .modal-conta-info {

        color: #888;

        font-size: 13px;

        line-height: 1.5;

        word-break: break-word;

        overflow-wrap: anywhere;
    }

    .modal-botoes {

        display: flex;

        gap: 10px;
    }

    .modal-btn {

        flex: 1;

        border: none;

        border-radius: 7px;

        padding: 11px 15px;

        font-size: 14px;

        cursor: pointer;

        transition: 0.2s;

        min-width: 0;
    }

    .modal-cancelar {

        background-color: #242424;

        border: 1px solid #333;

        color: white;
    }

    .modal-cancelar:hover {

        background-color: #333;
    }

    .modal-confirmar {

        background-color: #b52b2b;

        color: white;
    }

    .modal-confirmar:hover {

        background-color: #d03636;
    }

    @media (max-width: 768px) {

        .topo {

            min-height: 70px;

            padding: 12px 4%;

            gap: 10px;
        }

        .logo {

            font-size: 22px;
        }

        .botoes-topo {

            gap: 7px;
        }

        .btn-voltar,
        .btn-criar {

            padding: 8px 10px;

            font-size: 12px;
        }

        .pagina-container {

            width: 100%;

            padding: 28px 12px 50px;
        }

        h1 {

            font-size: 24px;
        }

        .descricao {

            font-size: 14px;

            margin-bottom: 22px;
        }

        .filtros {

            display: grid;

            grid-template-columns: repeat(6, minmax(0, 1fr));

            gap: 8px;

            margin-bottom: 18px;

            width: 100%;
        }

        .filtro {

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 6px;

            width: 100%;

            min-width: 0;

            max-width: 100%;

            height: 44px;

            padding: 0 4px;

            font-size: clamp(10px, 3.3vw, 13px);

            line-height: 1;

            text-align: center;

            white-space: nowrap;

            overflow: visible;
        }

        .filtros .campo-busca {

            display: block;

            grid-column: 1 / -1;

            order: 1;

            width: 100%;

            min-width: 0;

            height: 48px;

            padding: 0 14px;

            flex: none;

            font-size: 14px;

            text-align: left;
        }

        .filtros .filtro-todas {

            grid-column: span 2;

            order: 2;
        }

        .filtros .filtro-administradores {

            grid-column: span 2;

            order: 3;
        }

        .filtros .filtro-distribuidores {

            grid-column: span 2;

            order: 4;
        }

        .filtros .filtro-clientes {

            grid-column: span 3;

            order: 5;
        }

        .filtros .filtro-buscar {

            grid-column: span 3;

            order: 6;
        }

        .tabela-container {

            display: none;
        }

        .cards-mobile {

            display: block;
        }

        .lista-card {

            border-radius: 10px;
        }

        .conta-card-acoes {

            width: 100%;

            justify-content: flex-end;
        }

        .btn-inativar,
        .btn-ativar,
        .btn-excluir {

            padding: 7px 10px;

            font-size: 12px;
        }

        .modal-exclusao {

            padding: 15px;
        }

        .modal-caixa {

            max-width: 100%;

            padding: 23px 18px;

            border-radius: 12px;
        }

        .modal-titulo {

            font-size: 19px;
        }

        .modal-texto {

            font-size: 13px;
        }

        .modal-botoes {

            flex-direction: column-reverse;
        }

        .modal-btn {

            width: 100%;

            padding: 12px;
        }
    }

    @media (max-width: 480px) {

        .topo {

            padding: 11px 3%;

            gap: 6px;
        }

        .logo {

            font-size: 20px;
        }

        .botoes-topo {

            gap: 5px;
        }

        .btn-voltar,
        .btn-criar {

            padding: 7px 9px;

            font-size: 11px;
        }

        .pagina-container {

            width: 100%;

            padding: 25px 10px 45px;
        }

        h1 {

            font-size: 22px;
        }

        .descricao {

            font-size: 13px;
        }

        .filtros {

            gap: 7px;
        }

        .filtros .campo-busca {

            height: 48px;

            font-size: 13px;
        }

        .conta-card {

            padding: 16px;
        }

        .conta-card-nome {

            font-size: 15px;
        }

        .info-valor {

            font-size: 13px;
        }

        .conta-card-footer {

            flex-direction: column;

            align-items: flex-start;
        }

        .conta-card-footer > .tipo {

            flex: 0 0 auto;

            width: fit-content;

            max-width: max-content;
        }

        .conta-card-acoes {

            justify-content: stretch;

            width: 100%;
        }

        .conta-card-acoes form {

            flex: 1;
        }

        .conta-card-acoes button {

            width: 100%;
        }

        .modal-icone {

            width: 48px;

            height: 48px;

            font-size: 21px;
        }
    }

    @media (max-width: 360px) {

        .topo {

            padding-left: 10px;

            padding-right: 10px;
        }

        .logo {

            font-size: 18px;
        }

        .btn-voltar,
        .btn-criar {

            padding: 6px 7px;

            font-size: 10px;
        }

        .pagina-container {

            padding-left: 8px;

            padding-right: 8px;
        }

        .filtros {

            gap: 6px;
        }

        .conta-card {

            padding: 14px;
        }
    }

</style>

</head>

<body>

<header class="topo">

<a
    href="tela_inicial_admin.php"
    class="logo"
>
    ORION TV
</a>

<div class="botoes-topo">

    <a
        href="assinaturas_admin.php"
        class="btn-voltar"
    >
        Voltar
    </a>

    <a
        href="criar_contas_admin.php"
        class="btn-criar"
    >

        <i class="bi bi-plus-lg"></i>

        Criar Contas

    </a>

</div>

</header>

<main class="pagina-container">

<h1>
    Gerenciar contas
</h1>

<p class="descricao">
    Visualize e gerencie todas as contas cadastradas.
</p>

<form
    method="GET"
    action="ver_contas_usuarios.php"
    class="filtros"
>

    <input
        type="text"
        name="busca"
        class="filtro campo-busca"
        placeholder="Buscar por ID, nome, email, CPF/CNPJ ou tipo..."
        value="<?php echo htmlspecialchars($busca, ENT_QUOTES, "UTF-8"); ?>"
    >

    <input
        type="hidden"
        name="tipo"
        value="<?php echo htmlspecialchars($filtro, ENT_QUOTES, "UTF-8"); ?>"
    >

    <button
        type="submit"
        class="filtro ativo filtro-buscar"
    >

        <i class="bi bi-search"></i>

        Buscar

    </button>

    <a
        href="ver_contas_usuarios.php?tipo=Todos&busca=<?php echo urlencode($busca); ?>"
        class="filtro filtro-todas <?php echo $filtro === "Todos" ? "ativo" : ""; ?>"
    >
        Todas
    </a>

    <a
        href="ver_contas_usuarios.php?tipo=Administrador&busca=<?php echo urlencode($busca); ?>"
        class="filtro filtro-administradores <?php echo $filtro === "Administrador" ? "ativo" : ""; ?>"
    >
        Administradores
    </a>

    <a
        href="ver_contas_usuarios.php?tipo=Distribuidor&busca=<?php echo urlencode($busca); ?>"
        class="filtro filtro-distribuidores <?php echo $filtro === "Distribuidor" ? "ativo" : ""; ?>"
    >
        Distribuidores
    </a>

    <a
        href="ver_contas_usuarios.php?tipo=Cliente&busca=<?php echo urlencode($busca); ?>"
        class="filtro filtro-clientes <?php echo $filtro === "Cliente" ? "ativo" : ""; ?>"
    >
        Clientes
    </a>

</form>

<div class="lista-card">

    <?php if (!empty($contas)) { ?>

        <div class="tabela-container">

            <table class="tabela-contas">

                <thead>

                    <tr>

                        <th>
                            ID
                        </th>

                        <th>
                            Nome
                        </th>

                        <th class="coluna-email">
                            Email
                        </th>

                        <th class="coluna-cpf-cnpj">
                            CPF/CNPJ
                        </th>

                        <th class="coluna-tipo">
                            Tipo
                        </th>

                        <th class="coluna-status">
                            Status da conta
                        </th>

                        <th class="coluna-acao">
                            Ação
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($contas as $conta) { ?>

                        <?php

                        $tipo =
                            $conta["tipo_usuario"] ?? "";

                        $nome =
                            $conta["nome"] ?? "";

                        $email =
                            $conta["email"] ?? "";

                        $cpfCnpj =
                            $conta["cpf_cnpj"] ?? "";

                        if ($tipo === "Administrador") {

                            $statusConta =
                                $conta["status_administrador"] ?? "Ativo";

                        } elseif ($tipo === "Distribuidor") {

                            $statusConta =
                                $conta["status_distribuidor"] ?? "Ativo";

                        } elseif ($tipo === "Cliente") {

                            $statusConta =
                                $conta["status_conta_cliente"] ?? "Ativo";

                        } else {

                            $statusConta = "Ativo";
                        }

                        $statusInativo =
                            $statusConta === "Inativo";

                        $textoBotaoStatus =
                            $statusInativo
                            ? "Ativar"
                            : "Inativar";

                        $iconeBotaoStatus =
                            $statusInativo
                            ? "bi-check-circle"
                            : "bi-pause-circle";

                        $classeBotaoStatus =
                            $statusInativo
                            ? "btn-ativar"
                            : "btn-inativar";

                        $classeStatus =
                            $statusInativo
                            ? "status-inativo"
                            : "status-ativo";

                        $classeTipo = "";

                        if ($tipo === "Administrador") {

                            $classeTipo =
                                "tipo-administrador";

                        } elseif ($tipo === "Distribuidor") {

                            $classeTipo =
                                "tipo-distribuidor";

                        } elseif ($tipo === "Cliente") {

                            $classeTipo =
                                "tipo-cliente";
                        }

                        $nomeSeguro =
                            htmlspecialchars(
                                $nome,
                                ENT_QUOTES,
                                "UTF-8"
                            );

                        $emailSeguro =
                            htmlspecialchars(
                                $email,
                                ENT_QUOTES,
                                "UTF-8"
                            );

                        $tipoSeguro =
                            htmlspecialchars(
                                $tipo,
                                ENT_QUOTES,
                                "UTF-8"
                            );

                        $cpfCnpjSeguro =
                            htmlspecialchars(
                                $cpfCnpj,
                                ENT_QUOTES,
                                "UTF-8"
                            );

                        ?>

                        <tr>

                            <td>

                                #<?php
                                echo (int) $conta["id_usuario"];
                                ?>

                            </td>

                            <td class="nome-usuario">

                                <?php
                                echo $nomeSeguro;
                                ?>

                            </td>

                            <td class="coluna-email">

                                <?php
                                echo $emailSeguro;
                                ?>

                            </td>

                            <td class="coluna-cpf-cnpj">

                                <?php
                                echo $cpfCnpjSeguro;
                                ?>

                            </td>

                            <td class="coluna-tipo">

                                <span
                                    class="tipo <?php echo $classeTipo; ?>"
                                >

                                    <?php
                                    echo $tipoSeguro;
                                    ?>

                                </span>

                            </td>

                            <td class="coluna-status">

                                <span
                                    class="status <?php echo $classeStatus; ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $statusConta,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );
                                    ?>

                                </span>

                            </td>

                            <td class="coluna-acao">

                                <div class="acoes-conta">

                                    <form
                                        method="POST"
                                        action="ver_contas_usuarios.php?tipo=<?php echo urlencode($filtro); ?>"
                                    >

                                        <input
                                            type="hidden"
                                            name="id_usuario"
                                            value="<?php echo (int) $conta["id_usuario"]; ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="alterar_status"
                                            value="1"
                                        >

                                        <button
                                            type="submit"
                                            class="<?php echo $classeBotaoStatus; ?>"
                                        >

                                            <i
                                                class="bi <?php echo $iconeBotaoStatus; ?>"
                                            ></i>

                                            <?php
                                            echo $textoBotaoStatus;
                                            ?>

                                        </button>

                                    </form>

                                    <form
                                        method="POST"
                                        action="ver_contas_usuarios.php?tipo=<?php echo urlencode($filtro); ?>"
                                        class="form-exclusao"
                                    >

                                        <input
                                            type="hidden"
                                            name="id_usuario"
                                            value="<?php echo (int) $conta["id_usuario"]; ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="excluir_conta"
                                            value="1"
                                        >

                                        <button
                                            type="button"
                                            class="btn-excluir"
                                            onclick="abrirModalExclusao(this)"
                                            data-nome="<?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-email="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-tipo="<?php echo htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8'); ?>"
                                        >

                                            <i class="bi bi-trash3"></i>

                                            Excluir

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php } ?>

                </tbody>

            </table>

        </div>

        <div class="cards-mobile">

            <?php foreach ($contas as $conta) { ?>

                <?php

                $tipo =
                    $conta["tipo_usuario"] ?? "";

                $nome =
                    $conta["nome"] ?? "";

                $email =
                    $conta["email"] ?? "";

                $cpfCnpj =
                    $conta["cpf_cnpj"] ?? "";

                if ($tipo === "Administrador") {

                    $statusConta =
                        $conta["status_administrador"] ?? "Ativo";

                } elseif ($tipo === "Distribuidor") {

                    $statusConta =
                        $conta["status_distribuidor"] ?? "Ativo";

                } elseif ($tipo === "Cliente") {

                    $statusConta =
                        $conta["status_conta_cliente"] ?? "Ativo";

                } else {

                    $statusConta = "Ativo";
                }

                $statusInativo =
                    $statusConta === "Inativo";

                $textoBotaoStatus =
                    $statusInativo
                    ? "Ativar"
                    : "Inativar";

                $iconeBotaoStatus =
                    $statusInativo
                    ? "bi-check-circle"
                    : "bi-pause-circle";

                $classeBotaoStatus =
                    $statusInativo
                    ? "btn-ativar"
                    : "btn-inativar";

                $classeStatus =
                    $statusInativo
                    ? "status-inativo"
                    : "status-ativo";

                $classeTipo = "";

                if ($tipo === "Administrador") {

                    $classeTipo =
                        "tipo-administrador";

                } elseif ($tipo === "Distribuidor") {

                    $classeTipo =
                        "tipo-distribuidor";

                } elseif ($tipo === "Cliente") {

                    $classeTipo =
                        "tipo-cliente";
                }

                ?>

                <div class="conta-card">

                    <div class="conta-card-topo">

                        <div class="conta-card-nome">

                            <?php
                            echo htmlspecialchars(
                                $nome,
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>

                        </div>

                        <div class="conta-card-id">

                            #<?php
                            echo (int) $conta["id_usuario"];
                            ?>

                        </div>

                    </div>

                    <div class="conta-info">

                        <div class="info-item">

                            <span class="info-label">
                                Email
                            </span>

                            <span class="info-valor">

                                <?php
                                echo htmlspecialchars(
                                    $email,
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                                ?>

                            </span>

                        </div>

                        <div class="info-item">

                            <span class="info-label">

                                <?php

                                echo $tipo === "Cliente"
                                    ? "CPF"
                                    : (
                                        $tipo === "Distribuidor"
                                        ? "CNPJ"
                                        : "CPF/CNPJ"
                                    );

                                ?>

                            </span>

                            <span class="info-valor">

                                <?php
                                echo htmlspecialchars(
                                    $cpfCnpj,
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                                ?>

                            </span>

                        </div>

                        <div class="info-item">

                            <span class="info-label">
                                Status da conta
                            </span>

                            <span>

                                <span
                                    class="status <?php echo $classeStatus; ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $statusConta,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );
                                    ?>

                                </span>

                            </span>

                        </div>

                    </div>

                    <div class="conta-card-footer">

                        <span
                            class="tipo <?php echo $classeTipo; ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $tipo,
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>

                        </span>

                        <div class="conta-card-acoes">

                            <form
                                method="POST"
                                action="ver_contas_usuarios.php?tipo=<?php echo urlencode($filtro); ?>"
                            >

                                <input
                                    type="hidden"
                                    name="id_usuario"
                                    value="<?php echo (int) $conta["id_usuario"]; ?>"
                                >

                                <input
                                    type="hidden"
                                    name="alterar_status"
                                    value="1"
                                >

                                <button
                                    type="submit"
                                    class="<?php echo $classeBotaoStatus; ?>"
                                >

                                    <i
                                        class="bi <?php echo $iconeBotaoStatus; ?>"
                                    ></i>

                                    <?php
                                    echo $textoBotaoStatus;
                                    ?>

                                </button>

                            </form>

                            <form
                                method="POST"
                                action="ver_contas_usuarios.php?tipo=<?php echo urlencode($filtro); ?>"
                                class="form-exclusao"
                            >

                                <input
                                    type="hidden"
                                    name="id_usuario"
                                    value="<?php echo (int) $conta["id_usuario"]; ?>"
                                >

                                <input
                                    type="hidden"
                                    name="excluir_conta"
                                    value="1"
                                >

                                <button
                                    type="button"
                                    class="btn-excluir"
                                    onclick="abrirModalExclusao(this)"
                                    data-nome="<?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-email="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-tipo="<?php echo htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8'); ?>"
                                >

                                    <i class="bi bi-trash3"></i>

                                    Excluir

                                </button>

                            </form>

                        </div>

                    </div>

                </div>

            <?php } ?>

        </div>

    <?php } else { ?>

        <div class="sem-contas">

            <i class="bi bi-people"></i>

            <strong>
                Nenhuma conta encontrada
            </strong>

            <p>

                <?php

                if ($filtro === "Todos") {

                    echo "Ainda não existem contas cadastradas.";

                } else {

                    echo "Não existe nenhuma conta do tipo " .

                        htmlspecialchars(
                            $filtro,
                            ENT_QUOTES,
                            "UTF-8"
                        ) .

                        ".";
                }

                ?>

            </p>

        </div>

    <?php } ?>

</div>

</main>

<div
    class="modal-exclusao"
    id="modalExclusao"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modalTitulo"
>

<div class="modal-caixa">

    <div class="modal-icone">

        <i class="bi bi-trash3"></i>

    </div>

    <h2
        class="modal-titulo"
        id="modalTitulo"
    >
        Excluir conta?
    </h2>

    <p class="modal-texto">

        Essa ação é permanente e não poderá ser desfeita.

        <br>

        Deseja realmente excluir esta conta?

    </p>

    <div class="modal-conta">

        <div
            class="modal-conta-nome"
            id="modalNome"
        >
        </div>

        <div
            class="modal-conta-info"
            id="modalEmail"
        >
        </div>

        <div
            class="modal-conta-info"
            id="modalTipo"
        >
        </div>

    </div>

    <div class="modal-botoes">

        <button
            type="button"
            class="modal-btn modal-cancelar"
            onclick="fecharModalExclusao()"
        >

            Cancelar

        </button>

        <button
            type="button"
            class="modal-btn modal-confirmar"
            onclick="confirmarExclusao()"
        >

            <i class="bi bi-trash3"></i>

            Excluir conta

        </button>

    </div>

</div>

</div>

<script>

let formularioExclusaoAtual = null;

function abrirModalExclusao(botao) {

    formularioExclusaoAtual =
        botao.closest("form");

    const nome =
        botao.getAttribute("data-nome") || "";

    const email =
        botao.getAttribute("data-email") || "";

    const tipo =
        botao.getAttribute("data-tipo") || "";

    document.getElementById("modalNome").textContent =
        nome;

    document.getElementById("modalEmail").textContent =
        email;

    document.getElementById("modalTipo").textContent =
        "Tipo: " + tipo;

    const modal =
        document.getElementById("modalExclusao");

    modal.classList.add("aberto");

    document.body.style.overflow =
        "hidden";

    setTimeout(function () {

        document
            .querySelector(".modal-cancelar")
            .focus();

    }, 50);

}

function fecharModalExclusao() {

    const modal =
        document.getElementById("modalExclusao");

    modal.classList.remove("aberto");

    document.body.style.overflow =
        "";

    formularioExclusaoAtual =
        null;

}

function confirmarExclusao() {

    if (!formularioExclusaoAtual) {

        return;
    }

    formularioExclusaoAtual.submit();

}

document
    .getElementById("modalExclusao")
    .addEventListener("click", function (evento) {

        if (evento.target === this) {

            fecharModalExclusao();

        }

    });

document.addEventListener(
    "keydown",
    function (evento) {

        if (
            evento.key === "Escape"
            &&
            document
                .getElementById("modalExclusao")
                .classList
                .contains("aberto")
        ) {

            fecharModalExclusao();

        }

    }
);

</script>

<?php

if (
    isset($_GET["sucesso"])
    &&
    $_GET["sucesso"] === "excluido"
) {

?>

<script>

    mostrarAlerta(
        "Conta excluída com sucesso.",
        "window.location.href = 'ver_contas_usuarios.php?tipo=<?php echo urlencode($filtro); ?>'"
    );

</script>

<?php

}

if (
    isset($_GET["sucesso"])
    &&
    $_GET["sucesso"] === "status_inativado"
) {

?>

<script>

    mostrarAlerta(
        "Conta inativada com sucesso.",
        "window.location.href = 'ver_contas_usuarios.php?tipo=<?php echo urlencode($filtro); ?>'"
    );

</script>

<?php

}

if (
    isset($_GET["sucesso"])
    &&
    $_GET["sucesso"] === "status_ativado"
) {

?>

<script>

    mostrarAlerta(
        "Conta ativada com sucesso.",
        "window.location.href = 'ver_contas_usuarios.php?tipo=<?php echo urlencode($filtro); ?>'"
    );

</script>

<?php

}

if (isset($_GET["erro"])) {

    $mensagemErro =
        $_GET["erro"];

    if ($mensagemErro === "id_invalido") {

        $mensagemErro =
            "ID da conta inválido.";
    }

?>

<script>

    mostrarAlerta(
        <?php

        echo json_encode(
            $mensagemErro,
            JSON_UNESCAPED_UNICODE
        );

        ?>,
        "window.location.href = 'ver_contas_usuarios.php?tipo=<?php echo urlencode($filtro); ?>'"
    );

</script>

<?php

}

?>

<script>

    /*
        Mantém a posição da rolagem da página após cada ação
        (inativar, ativar, bloquear, excluir, filtrar, buscar etc.),
        evitando voltar ao topo quando a página é recarregada.
    */

    (function () {

        var CHAVE = "orion_posicao_rolagem";

        var VALIDADE = 10 * 60 * 1000;

        function posicaoAtual() {

            return window.pageYOffset ||
                document.documentElement.scrollTop ||
                document.body.scrollTop ||
                0;

        }

        function salvarPosicao() {

            try {

                sessionStorage.setItem(
                    CHAVE,
                    JSON.stringify({
                        caminho: window.location.pathname,
                        y: posicaoAtual(),
                        hora: Date.now()
                    })
                );

            } catch (erro) {}

        }

        function limparPosicao() {

            try {

                sessionStorage.removeItem(CHAVE);

            } catch (erro) {}

        }

        function lerPosicao() {

            try {

                var bruto = sessionStorage.getItem(CHAVE);

                if (!bruto) {

                    return null;

                }

                var dados = JSON.parse(bruto);

                if (
                    !dados ||
                    dados.caminho !== window.location.pathname ||
                    typeof dados.y !== "number" ||
                    (Date.now() - dados.hora) > VALIDADE
                ) {

                    return null;

                }

                return dados;

            } catch (erro) {

                return null;

            }

        }

        function rolarPara(y) {

            var raiz = document.documentElement;

            var comportamentoAnterior = raiz.style.scrollBehavior;

            raiz.style.scrollBehavior = "auto";

            window.scrollTo(0, y);

            raiz.style.scrollBehavior = comportamentoAnterior;

        }

        var salvo = lerPosicao();

        if (salvo) {

            rolarPara(salvo.y);

            window.addEventListener("load", function () {

                if (posicaoAtual() === 0) {

                    rolarPara(salvo.y);

                }

            });

            if (!/[?&](sucesso|erro)=/.test(window.location.search)) {

                limparPosicao();

            }

        }

        if (
            window.HTMLFormElement &&
            HTMLFormElement.prototype.submit
        ) {

            var submitOriginal = HTMLFormElement.prototype.submit;

            HTMLFormElement.prototype.submit = function () {

                salvarPosicao();

                return submitOriginal.apply(this, arguments);

            };

        }

        document.addEventListener("submit", function (evento) {

            if (!evento.defaultPrevented) {

                salvarPosicao();

            }

        });

        document.addEventListener("click", function (evento) {

            if (
                evento.defaultPrevented ||
                evento.button !== 0 ||
                evento.ctrlKey ||
                evento.metaKey ||
                evento.shiftKey ||
                evento.altKey
            ) {

                return;

            }

            var alvo = evento.target;

            var link = alvo && alvo.closest
                ? alvo.closest("a[href]")
                : null;

            if (!link) {

                return;

            }

            if (link.target && link.target !== "_self") {

                return;

            }

            if (
                link.hostname === window.location.hostname &&
                link.pathname === window.location.pathname
            ) {

                salvarPosicao();

            } else {

                limparPosicao();

            }

        });

    })();

</script>

</body>

</html>
