<?php

require_once(__DIR__ . "/includes/proteger_admin.php");
require_once(__DIR__ . "/php/conexao.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $acao = $_POST["acao"] ?? "";
    $id_conteudo = (int) ($_POST["id_conteudo"] ?? 0);

    if ($id_conteudo <= 0) {

        header("Location: gerenciar_conteudos_administrador.php");
        exit;

    }

    if ($acao === "bloquear") {

        $sql = "
            UPDATE conteudo
            SET bloqueio_admin = 'Sim'
            WHERE id_conteudo = ?
        ";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $id_conteudo
            );

            mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);

        }

    } elseif ($acao === "desbloquear") {

        $sql = "
            UPDATE conteudo
            SET bloqueio_admin = 'Não'
            WHERE id_conteudo = ?
        ";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $id_conteudo
            );

            mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);

        }

    }

    elseif ($acao === "inativar") {

        $sql = "
            UPDATE conteudo
            SET conteudo_status = 'Inativo'
            WHERE id_conteudo = ?
        ";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $id_conteudo
            );

            mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);

        }

    } elseif ($acao === "ativar") {

        $sql = "
            UPDATE conteudo
            SET conteudo_status = 'Ativo'
            WHERE id_conteudo = ?
        ";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $id_conteudo
            );

            mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);

        }

    }

    elseif ($acao === "excluir") {

        mysqli_begin_transaction($conn);

        try {

            $sql_avaliacoes = "
                DELETE FROM avaliacao
                WHERE FK_avaliacao_id_conteudo = ?
            ";

            $stmt_avaliacoes = mysqli_prepare(
                $conn,
                $sql_avaliacoes
            );

            if (!$stmt_avaliacoes) {
                throw new Exception();
            }

            mysqli_stmt_bind_param(
                $stmt_avaliacoes,
                "i",
                $id_conteudo
            );

            if (!mysqli_stmt_execute($stmt_avaliacoes)) {
                throw new Exception();
            }

            mysqli_stmt_close($stmt_avaliacoes);

            $sql_conteudo = "
                DELETE FROM conteudo
                WHERE id_conteudo = ?
            ";

            $stmt_conteudo = mysqli_prepare(
                $conn,
                $sql_conteudo
            );

            if (!$stmt_conteudo) {
                throw new Exception();
            }

            mysqli_stmt_bind_param(
                $stmt_conteudo,
                "i",
                $id_conteudo
            );

            if (!mysqli_stmt_execute($stmt_conteudo)) {
                throw new Exception();
            }

            mysqli_stmt_close($stmt_conteudo);

            mysqli_commit($conn);

        } catch (Exception $e) {

            mysqli_rollback($conn);

        }

    }

    header("Location: gerenciar_conteudos_administrador.php");
    exit;

}

$pesquisa = trim($_GET["pesquisa"] ?? "");

if ($pesquisa !== "") {

    $sql = "
        SELECT
            c.id_conteudo,
            c.titulo_conteudo,
            c.genero_conteudo,
            c.classe_etaria_conteudo,
            c.duracao_conteudo,
            c.janela_exib_inicio_conteudo,
            c.janela_exib_fim_conteudo,
            c.conteudo_status,
            c.bloqueio_admin,

            d.empresa_distribuidor,
            d.cnpj_empresa_distribuidor,

            a.nome_admin

        FROM conteudo AS c

        LEFT JOIN distribuidor AS d
            ON d.id_distribuidor = c.FK_conteudo_id_distribuidor

        LEFT JOIN administrador AS a
            ON a.id_administrador = c.FK_conteudo_id_administrador

        WHERE
            CAST(c.id_conteudo AS CHAR) LIKE ?
            OR c.titulo_conteudo LIKE ?
            OR c.genero_conteudo LIKE ?
            OR (
                CASE
                    WHEN c.classe_etaria_conteudo = 0
                    THEN 'Livre'
                    ELSE CONCAT(
                        c.classe_etaria_conteudo,
                        ' anos'
                    )
                END
            ) LIKE ?
            OR CAST(c.duracao_conteudo AS CHAR) LIKE ?
            OR DATE_FORMAT(
                c.janela_exib_inicio_conteudo,
                '%d/%m/%Y'
            ) LIKE ?
            OR DATE_FORMAT(
                c.janela_exib_fim_conteudo,
                '%d/%m/%Y'
            ) LIKE ?
            OR c.sinopse_conteudo LIKE ?
            OR c.elenco_conteudo LIKE ?
            OR c.diretores_conteudo LIKE ?
            OR d.empresa_distribuidor LIKE ?
            OR d.cnpj_empresa_distribuidor LIKE ?
            OR a.nome_admin LIKE ?

        ORDER BY c.id_conteudo DESC
    ";

    $stmt = mysqli_prepare(
        $conn,
        $sql
    );

    if (!$stmt) {

        die(
            "Não foi possível preparar a pesquisa dos conteúdos: " .
            mysqli_error($conn)
        );

    }

    $termoPesquisa = "%" . $pesquisa . "%";

    mysqli_stmt_bind_param(
        $stmt,
        "sssssssssssss",
        $termoPesquisa,
        $termoPesquisa,
        $termoPesquisa,
        $termoPesquisa,
        $termoPesquisa,
        $termoPesquisa,
        $termoPesquisa,
        $termoPesquisa,
        $termoPesquisa,
        $termoPesquisa,
        $termoPesquisa,
        $termoPesquisa,
        $termoPesquisa
    );

} else {

    $sql = "
        SELECT
            c.id_conteudo,
            c.titulo_conteudo,
            c.genero_conteudo,
            c.classe_etaria_conteudo,
            c.duracao_conteudo,
            c.janela_exib_inicio_conteudo,
            c.janela_exib_fim_conteudo,
            c.conteudo_status,
            c.bloqueio_admin,

            d.empresa_distribuidor,
            d.cnpj_empresa_distribuidor,

            a.nome_admin

        FROM conteudo AS c

        LEFT JOIN distribuidor AS d
            ON d.id_distribuidor = c.FK_conteudo_id_distribuidor

        LEFT JOIN administrador AS a
            ON a.id_administrador = c.FK_conteudo_id_administrador

        ORDER BY c.id_conteudo DESC
    ";

    $stmt = mysqli_prepare(
        $conn,
        $sql
    );

    if (!$stmt) {

        die(
            "Não foi possível preparar a consulta dos conteúdos: " .
            mysqli_error($conn)
        );

    }

}

if (!mysqli_stmt_execute($stmt)) {

    die(
        "Não foi possível executar a consulta dos conteúdos: " .
        mysqli_stmt_error($stmt)
    );

}

mysqli_stmt_store_result($stmt);

mysqli_stmt_bind_result(
    $stmt,
    $id_conteudo,
    $titulo_conteudo,
    $genero_conteudo,
    $classe_etaria_conteudo,
    $duracao_conteudo,
    $janela_exib_inicio_conteudo,
    $janela_exib_fim_conteudo,
    $conteudo_status,
    $bloqueio_admin,
    $empresa_distribuidor,
    $cnpj_empresa_distribuidor,
    $nome_admin
);

$quantidade_resultados =
    mysqli_stmt_num_rows($stmt);

?>
<!DOCTYPE html>

<html lang="pt-br">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Orion TV - Gerenciar Conteúdos</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
>

<style>

    * {
        box-sizing: border-box;
    }

    body {

        margin: 0;

        min-height: 100vh;

        background: #080808;

        color: #fff;

        font-family: Arial, Helvetica, sans-serif;

    }

    .topo {

        width: 100%;

        min-height: 70px;

        padding: 0 35px;

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 20px;

        background: #111;

        border-bottom: 1px solid #222;

        position: sticky;

        top: 0;

        z-index: 1030;

    }

    .logo {

        color: #168cff;

        font-size: 25px;

        font-weight: bold;

        letter-spacing: 1px;

        flex-shrink: 0;

    }

    .botoes-topo {

        display: flex;

        align-items: center;

        justify-content: flex-end;

        gap: 8px;

        flex-wrap: wrap;

    }

    .btn-topo {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 7px;

        padding: 9px 15px;

        border: 1px solid #444;

        border-radius: 3px;

        color: #fff;

        background: transparent;

        text-decoration: none;

        font-size: 14px;

        transition: 0.2s;

        white-space: nowrap;

    }

    .btn-topo:hover {

        background: #222;

        color: #fff;

    }

    .btn-enviar {

        border-color: #168cff;

        color: #168cff;

    }

    .btn-enviar:hover {

        background: #168cff;

        color: #fff;

    }

    .btn-logout {

        border-color: #dc3545;

        color: #ff7777;

    }

    .btn-logout:hover {

        background: #dc3545;

        color: #fff;

    }

    .pagina {

        width: 100%;

        max-width: 1450px;

        margin: 0 auto;

        padding: 45px 25px 60px;

    }

    .cabecalho-pagina {

        margin-bottom: 30px;

    }

    .cabecalho-pagina h1 {

        margin: 0 0 8px;

        font-size: 30px;

        font-weight: bold;

        overflow-wrap: break-word;

    }

    .cabecalho-pagina p {

        margin: 0;

        color: #aaa;

        font-size: 15px;

        overflow-wrap: break-word;

    }

    .pesquisa-container {

        display: flex;

        gap: 10px;

        margin-bottom: 20px;

    }

    .campo-pesquisa {

        flex: 1;

        min-width: 0;

        height: 45px;

        padding: 0 15px;

        border: 1px solid #333;

        border-radius: 3px;

        background: #111;

        color: #fff;

        outline: none;

        font-size: 14px;

    }

    .campo-pesquisa::placeholder {

        color: #777;

    }

    .campo-pesquisa:focus {

        border-color: #168cff;

    }

    .btn-pesquisar {

        height: 45px;

        padding: 0 18px;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 7px;

        border: 1px solid #168cff;

        border-radius: 7px;

        background: transparent;

        color: #168cff;

        font-size: 14px;

        font-weight: bold;

        cursor: pointer;

        transition: 0.2s;

    }

    .btn-pesquisar:hover {

        background: #168cff;

        color: #fff;

    }

    .btn-limpar {

        height: 45px;

        padding: 0 18px;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 7px;

        border: 1px solid #444;

        border-radius: 7px;

        background: transparent;

        color: #aaa;

        text-decoration: none;

        font-size: 14px;

        font-weight: bold;

        transition: 0.2s;

    }

    .btn-limpar:hover {

        background: #222;

        color: #fff;

    }

    .conteudo-card {

        background: #111;

        border: 1px solid #222;

        border-radius: 12px;

        overflow: hidden;

    }

    .tabela-container {

        width: 100%;

        overflow-x: hidden;

    }

    table {

        width: 100%;

        table-layout: auto;

        border-collapse: collapse;

    }

    thead {

        background: #171717;

    }

    th {

        padding: 14px 9px;

        text-align: center;

        vertical-align: middle;

        color: #aaa;

        font-size: 13px;

        font-weight: bold;

        white-space: nowrap;

        border-bottom: 1px solid #292929;

    }

    td {

        padding: 15px 9px;

        text-align: center;

        vertical-align: middle;

        border-bottom: 1px solid #222;

        font-size: 14px;

    }

    tbody tr:last-child td {

        border-bottom: none;

    }

    tbody tr:hover {

        background: #151515;

    }

    .titulo {

        max-width: 240px;

        margin: 0 auto;

        font-weight: bold;

        overflow-wrap: break-word;

        text-align: center;

    }

    .genero {

        max-width: 180px;

        margin: 0 auto;

        color: #aaa;

        overflow-wrap: break-word;

        text-align: center;

    }

    .status {

        display: inline-block;

        padding: 5px 10px;

        border-radius: 20px;

        font-size: 12px;

        font-weight: bold;

        white-space: nowrap;

    }

    .status-ativo {

        color: #6ee7a0;

        background: rgba(40, 167, 69, 0.12);

        border: 1px solid rgba(40, 167, 69, 0.3);

    }

    .status-inativo {

        color: #aaa;

        background: rgba(108, 117, 125, 0.12);

        border: 1px solid rgba(108, 117, 125, 0.3);

    }

    .bloqueado {

        color: #ff7777;

        background: rgba(220, 53, 69, 0.12);

        border: 1px solid rgba(220, 53, 69, 0.3);

    }

    .nao-bloqueado {

        color: #6ee7a0;

        background: rgba(40, 167, 69, 0.12);

        border: 1px solid rgba(40, 167, 69, 0.3);

    }

    .bloqueio {

        text-align: center;

    }

    .acoes {

        display: grid;

        grid-template-columns: repeat(2, 1fr);

        grid-template-rows: repeat(2, auto);

        align-items: center;

        justify-items: center;

        gap: 7px;

        min-width: 270px;

        margin: 0 auto;

    }

    .acoes form {

        display: block !important;

        width: 100%;

    }

    .btn-acao {

        width: 100%;

        min-width: 115px;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 6px;

        padding: 8px 11px;

        border-radius: 7px;

        background: transparent;

        text-decoration: none;

        font-size: 12px;

        font-weight: bold;

        white-space: nowrap;

        transition: 0.2s;

        cursor: pointer;

    }

    .btn-alterar {

        border: 1px solid #168cff;

        color: #168cff;

    }

    .btn-alterar:hover {

        background: #168cff;

        color: #fff;

    }

    .btn-bloquear {

        border: 1px solid #dc3545;

        color: #ff7777;

    }

    .btn-bloquear:hover {

        background: #dc3545;

        color: #fff;

    }

    .btn-desbloquear {

        border: 1px solid #6ee7a0;

        color: #6ee7a0;

    }

    .btn-desbloquear:hover {

        background: #28a745;

        color: #fff;

    }

    .btn-inativar {

        border: 1px solid #777;

        color: #aaa;

    }

    .btn-inativar:hover {

        background: #555;

        color: #fff;

    }

    .btn-ativar {

        border: 1px solid #6ee7a0;

        color: #6ee7a0;

    }

    .btn-ativar:hover {

        background: #28a745;

        color: #fff;

    }

    .btn-excluir {

        border: 1px solid #dc3545;

        color: #ff7777;

    }

    .btn-excluir:hover {

        background: #dc3545;

        color: #fff;

    }

    .sem-conteudos {

        padding: 60px 25px;

        text-align: center;

        color: #888;

    }

    .sem-conteudos i {

        display: block;

        margin-bottom: 15px;

        font-size: 42px;

        color: #444;

    }

    .sem-conteudos p {

        margin: 0;

        font-size: 15px;

    }

    @media (max-width: 700px) {

        .topo {

            padding: 12px 18px;

            align-items: flex-start;

            flex-direction: column;

        }

        .logo {

            font-size: 21px;

        }

        .botoes-topo {

            width: 100%;

            justify-content: flex-start;

        }

        .btn-topo {

            flex: 1;

        }

        .pagina {

            padding: 30px 15px 45px;

        }

        .cabecalho-pagina h1 {

            font-size: 25px;

        }

        .pesquisa-container {

            flex-direction: column;

            align-items: stretch;

        }

        .campo-pesquisa {

            flex: 0 0 auto;

            width: 100%;

            height: 52px;

            min-height: 52px;

        }

        .btn-pesquisar,
        .btn-limpar {

            flex: 0 0 auto;

            width: 100%;

            height: 52px;

            min-height: 52px;

        }

        .tabela-container {

            overflow-x: auto;

            -webkit-overflow-scrolling: touch;

        }

        table {

            min-width: 1500px;

        }

    }

</style>

</head>

<body>

<header class="topo">

    <div class="logo">

        ORION TV

    </div>

    <div class="botoes-topo">

        <a
            href="enviar_filme.php"
            class="btn-topo btn-enviar"
        >

            <i class="bi bi-plus-circle"></i>

            Enviar

        </a>

        <a
            href="php/logout.php"
            class="btn-topo btn-logout"
        >

            <i class="bi bi-box-arrow-right"></i>

            Logout

        </a>

        <a
            href="assinaturas_admin.php"
            class="btn-topo"
        >

            <i class="bi bi-arrow-left"></i>

            Voltar

        </a>

    </div>

</header>

<main class="pagina">

    <div class="cabecalho-pagina">

        <h1>
            Gerenciar conteúdos
        </h1>

        <p>
            Gerencie todos os conteúdos cadastrados no ORION TV.
        </p>

    </div>

    <form
        method="GET"
        action="gerenciar_conteudos_administrador.php"
        class="pesquisa-container"
    >

        <input
            type="text"
            name="pesquisa"
            class="campo-pesquisa"
            placeholder="Pesquisar por ID, título, gênero, classificação, duração, datas, sinopse, elenco ou diretor..."
            value="<?= htmlspecialchars($pesquisa) ?>"
        >

        <button
            type="submit"
            class="btn-pesquisar"
        >

            <i class="bi bi-search"></i>

            Pesquisar

        </button>

        <?php if ($pesquisa !== ""): ?>

            <a
                href="gerenciar_conteudos_administrador.php"
                class="btn-limpar"
            >

                <i class="bi bi-x-lg"></i>

                Limpar

            </a>

        <?php endif; ?>

    </form>

    <div class="conteudo-card">

        <?php if ($quantidade_resultados === 0): ?>

            <div class="sem-conteudos">

                <i class="bi bi-film"></i>

                <?php if ($pesquisa !== ""): ?>

                    <p>

                        Nenhum conteúdo encontrado para
                        "<strong><?= htmlspecialchars($pesquisa) ?></strong>".

                    </p>

                <?php else: ?>

                    <p>
                        Nenhum conteúdo cadastrado.
                    </p>

                <?php endif; ?>

            </div>

        <?php else: ?>

            <div class="tabela-container">

                <table>

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Título</th>

                            <th>Empresa</th>

                            <th>CNPJ</th>

                            <th>Gênero</th>

                            <th>Classificação</th>

                            <th>Duração</th>

                            <th>Início</th>

                            <th>Fim</th>

                            <th>Status</th>

                            <th class="bloqueio">
                                Bloqueio
                            </th>

                            <th>Ações</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php while (mysqli_stmt_fetch($stmt)): ?>

                            <tr>

                                <td>

                                    <?= (int) $id_conteudo ?>

                                </td>

                                <td>

                                    <div class="titulo">

                                        <?= htmlspecialchars(
                                            $titulo_conteudo
                                        ) ?>

                                    </div>

                                </td>

                                <td>

                                    <div class="genero">

                                        <?php if (
                                            !empty($nome_admin)
                                        ): ?>

                                            Administrador:
                                            <?= htmlspecialchars(
                                                $nome_admin,
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ) ?>

                                        <?php elseif (
                                            !empty($empresa_distribuidor)
                                        ): ?>

                                            <?= htmlspecialchars(
                                                $empresa_distribuidor,
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ) ?>

                                        <?php else: ?>

                                            —

                                        <?php endif; ?>

                                    </div>

                                </td>

                                <td>

                                    <div class="genero">

                                        <?php if (
                                            !empty($nome_admin)
                                        ): ?>

                                            —

                                        <?php else: ?>

                                            <?= htmlspecialchars(
                                                $cnpj_empresa_distribuidor ?? "",
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ) ?>

                                        <?php endif; ?>

                                    </div>

                                </td>

                                <td>

                                    <div class="genero">

                                        <?= htmlspecialchars(
                                            $genero_conteudo
                                        ) ?>

                                    </div>

                                </td>

                                <td>

                                    <?php

                                    $classe =
                                        $classe_etaria_conteudo;

                                    if ((int) $classe === 0) {

                                        echo "Livre";

                                    } else {

                                        echo (int) $classe . " anos";

                                    }

                                    ?>

                                </td>

                                <td>

                                    <?= htmlspecialchars(
                                        $duracao_conteudo
                                    ) ?>

                                </td>

                                <td>

                                    <?= date(
                                        "d/m/Y",
                                        strtotime(
                                            $janela_exib_inicio_conteudo
                                        )
                                    ) ?>

                                </td>

                                <td>

                                    <?= date(
                                        "d/m/Y",
                                        strtotime(
                                            $janela_exib_fim_conteudo
                                        )
                                    ) ?>

                                </td>

                                <td>

                                    <?php if (
                                        $conteudo_status === "Ativo"
                                    ): ?>

                                        <span class="status status-ativo">
                                            Ativo
                                        </span>

                                    <?php else: ?>

                                        <span class="status status-inativo">
                                            Inativo
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td class="bloqueio">

                                    <?php if (
                                        $bloqueio_admin === "Sim"
                                    ): ?>

                                        <span class="status bloqueado">
                                            Bloqueado
                                        </span>

                                    <?php else: ?>

                                        <span class="status nao-bloqueado">
                                            Desbloqueado
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <div class="acoes">

                                        <a
                                            href="alterar_filme_admin.php?id=<?= (int) $id_conteudo ?>"
                                            class="btn-acao btn-alterar"
                                        >

                                            <i class="bi bi-pencil-square"></i>

                                            Alterar

                                        </a>

                                        <?php if (
                                            $bloqueio_admin === "Sim"
                                        ): ?>

                                            <form method="POST">

                                                <input
                                                    type="hidden"
                                                    name="acao"
                                                    value="desbloquear"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="id_conteudo"
                                                    value="<?= (int) $id_conteudo ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn-acao btn-desbloquear"
                                                >

                                                    <i class="bi bi-unlock"></i>

                                                    Desbloquear

                                                </button>

                                            </form>

                                        <?php else: ?>

                                            <form method="POST">

                                                <input
                                                    type="hidden"
                                                    name="acao"
                                                    value="bloquear"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="id_conteudo"
                                                    value="<?= (int) $id_conteudo ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn-acao btn-bloquear"
                                                >

                                                    <i class="bi bi-lock"></i>

                                                    Bloquear

                                                </button>

                                            </form>

                                        <?php endif; ?>

                                        <?php if (
                                            $conteudo_status === "Ativo"
                                        ): ?>

                                            <form method="POST">

                                                <input
                                                    type="hidden"
                                                    name="acao"
                                                    value="inativar"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="id_conteudo"
                                                    value="<?= (int) $id_conteudo ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn-acao btn-inativar"
                                                >

                                                    <i class="bi bi-eye-slash"></i>

                                                    Inativar

                                                </button>

                                            </form>

                                        <?php else: ?>

                                            <form method="POST">

                                                <input
                                                    type="hidden"
                                                    name="acao"
                                                    value="ativar"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="id_conteudo"
                                                    value="<?= (int) $id_conteudo ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn-acao btn-ativar"
                                                >

                                                    <i class="bi bi-eye"></i>

                                                    Ativar

                                                </button>

                                            </form>

                                        <?php endif; ?>

                                        <form
                                            method="POST"
                                            onsubmit="return confirm('Tem certeza que deseja excluir este conteúdo? Esta ação não poderá ser desfeita.');"
                                        >

                                            <input
                                                type="hidden"
                                                name="acao"
                                                value="excluir"
                                            >

                                            <input
                                                type="hidden"
                                                name="id_conteudo"
                                                value="<?= (int) $id_conteudo ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn-acao btn-excluir"
                                            >

                                                <i class="bi bi-trash3"></i>

                                                Excluir

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </div>

</main>

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

<?php

mysqli_stmt_close($stmt);

?>
