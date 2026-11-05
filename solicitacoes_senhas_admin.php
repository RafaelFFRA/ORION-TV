<?php

require_once(__DIR__ . "/php/conexao.php");
require_once(__DIR__ . "/includes/proteger_admin.php");

date_default_timezone_set("America/Sao_Paulo");

$busca = (isset($_GET["busca"]) && is_string($_GET["busca"]))
    ? trim($_GET["busca"])
    : "";

$data = (isset($_GET["data"]) && is_string($_GET["data"]))
    ? trim($_GET["data"])
    : "";

$hora = (isset($_GET["hora"]) && is_string($_GET["hora"]))
    ? trim($_GET["hora"])
    : "";

$dataValida = false;

if ($data !== "") {

    $dataObj = DateTime::createFromFormat("Y-m-d", $data);

    $dataValida =
        $dataObj instanceof DateTime
        &&
        $dataObj->format("Y-m-d") === $data;
}

if (!$dataValida) {

    $data = "";
}

$horaValida = false;

if ($data !== "" && $hora !== "") {

    $horaObj = DateTime::createFromFormat("H:i", $hora);

    $horaValida =
        $horaObj instanceof DateTime
        &&
        $horaObj->format("H:i") === $hora;
}

if (!$horaValida) {

    $hora = "";
}

/*
    O banco grava criado_em com o relógio do próprio servidor de banco
    (DEFAULT current_timestamp()), que não está no fuso de São Paulo.
    Aqui o valor é convertido do fuso do banco para o de São Paulo,
    para que exibição, busca e filtro de data/horário usem o mesmo horário.
    Se a conversão não for possível, mantém o valor original.
*/

$deslocamentoSaoPaulo = (
    new DateTime("now", new DateTimeZone("America/Sao_Paulo"))
)->format("P");

/*
    Dados da conta de cada solicitação: o e-mail (único em usuario)
    identifica o usuário, e o tipo_usuario define de qual tabela vêm
    os dados (cliente, distribuidor ou administrador).
    Nenhum hash é selecionado (usuario.senha e codigo_hash ficam de fora).
    O COLLATE é necessário porque recuperacao_senha.email e usuario.email
    usam collations diferentes, o que causaria erro no JOIN.
*/

$sql = "
    SELECT

        id_recuperacao,

        email,

        criado_em,

        conta_id_usuario,
        conta_tipo_usuario,
        conta_aniversario,

        conta_id_cliente,
        conta_nome_cliente,
        conta_cpf_cliente,
        conta_status_cliente,

        conta_id_distribuidor,
        conta_empresa_distribuidor,
        conta_cnpj_distribuidor,
        conta_status_distribuidor,

        conta_id_administrador,
        conta_nome_administrador,
        conta_status_administrador

    FROM (

        SELECT

            r.id_recuperacao,

            r.email,

            COALESCE(
                CONVERT_TZ(r.criado_em, @@session.time_zone, ?),
                r.criado_em
            ) AS criado_em,

            u.id_usuario AS conta_id_usuario,
            u.tipo_usuario AS conta_tipo_usuario,
            u.aniversario AS conta_aniversario,

            c.FK_cliente_id_cliente AS conta_id_cliente,
            c.nome_cliente AS conta_nome_cliente,
            c.cpf_cliente AS conta_cpf_cliente,
            c.status_conta_cliente AS conta_status_cliente,

            d.id_distribuidor AS conta_id_distribuidor,
            d.empresa_distribuidor AS conta_empresa_distribuidor,
            d.cnpj_empresa_distribuidor AS conta_cnpj_distribuidor,
            d.status AS conta_status_distribuidor,

            a.id_administrador AS conta_id_administrador,
            a.nome_admin AS conta_nome_administrador,
            a.status AS conta_status_administrador

        FROM recuperacao_senha AS r

        LEFT JOIN usuario AS u
            ON u.email COLLATE utf8mb4_unicode_ci = r.email

        LEFT JOIN cliente AS c
            ON c.FK_cliente_id_usuario = u.id_usuario
            AND u.tipo_usuario = 'Cliente'

        LEFT JOIN distribuidor AS d
            ON d.FK_distribuidor_id_usuario = u.id_usuario
            AND u.tipo_usuario = 'Distribuidor'

        LEFT JOIN administrador AS a
            ON a.FK_administrador_id_usuario = u.id_usuario
            AND u.tipo_usuario = 'Administrador'

    ) AS solicitacoes

    WHERE 1 = 1
";

$parametros = [$deslocamentoSaoPaulo];
$tipos = "s";

if ($busca !== "") {

    /*
        Campos pesquisáveis: todos os dados exibidos no card
        (solicitação + dados da conta). Nenhum hash entra na busca.

        As condições e os parâmetros nascem desta mesma lista, então a
        quantidade de "?" e de valores vinculados nunca fica diferente.

        CPF e CNPJ também são comparados sem pontuação, porque estão
        gravados em formatos diferentes (com e sem máscara) e a busca
        precisa achar os dois.
    */

    $camposBusca = [

        // Solicitação
        "CAST(id_recuperacao AS CHAR)",
        "email",
        "CAST(criado_em AS CHAR)",
        "DATE_FORMAT(criado_em, '%d/%m/%Y %H:%i:%s')",

        // Usuário (todos os tipos)
        "CAST(conta_id_usuario AS CHAR)",
        "conta_tipo_usuario",
        "CAST(conta_aniversario AS CHAR)",
        "DATE_FORMAT(conta_aniversario, '%d/%m/%Y')",

        // Cliente
        "CAST(conta_id_cliente AS CHAR)",
        "conta_nome_cliente",
        "conta_cpf_cliente",
        "REPLACE(REPLACE(REPLACE(conta_cpf_cliente, '.', ''), '-', ''), '/', '')",
        "conta_status_cliente",

        // Distribuidor
        "CAST(conta_id_distribuidor AS CHAR)",
        "conta_empresa_distribuidor",
        "conta_cnpj_distribuidor",
        "REPLACE(REPLACE(REPLACE(conta_cnpj_distribuidor, '.', ''), '-', ''), '/', '')",
        "conta_status_distribuidor",

        // Administrador
        "CAST(conta_id_administrador AS CHAR)",
        "conta_nome_administrador",
        "conta_status_administrador"
    ];

    $condicoesBusca = [];

    foreach ($camposBusca as $campoBusca) {

        $condicoesBusca[] =
            $campoBusca . " LIKE ? ESCAPE '!'";
    }

    $sql .= "
        AND (
            " . implode(" OR ", $condicoesBusca) . "
        )
    ";

    $termoBusca = "%" . str_replace(
        ["!", "%", "_"],
        ["!!", "!%", "!_"],
        $busca
    ) . "%";

    foreach ($camposBusca as $campoBusca) {

        $parametros[] = $termoBusca;
    }

    $tipos .= str_repeat("s", count($camposBusca));
}

if ($data !== "") {

    $fusoSaoPaulo = new DateTimeZone("America/Sao_Paulo");

    if ($hora !== "") {

        $inicio = new DateTime(
            $data . " " . $hora . ":00",
            $fusoSaoPaulo
        );

        $fim = clone $inicio;

        $fim->modify("+1 minute");
    }

    else {

        $inicio = new DateTime(
            $data . " 00:00:00",
            $fusoSaoPaulo
        );

        $fim = clone $inicio;

        $fim->modify("+1 day");
    }

    $sql .= "
        AND criado_em >= ?
        AND criado_em < ?
    ";

    $parametros[] = $inicio->format("Y-m-d H:i:s");
    $parametros[] = $fim->format("Y-m-d H:i:s");

    $tipos .= "ss";
}

$sql .= "
    ORDER BY
        criado_em DESC,
        id_recuperacao DESC
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    die(
        "Erro ao consultar solicitações: " .
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

$solicitacoes = [];

while (
    $linha =
    mysqli_fetch_assoc($resultado)
) {

    $solicitacoes[] = $linha;
}

mysqli_stmt_close($stmt);

$totalSolicitacoes = count($solicitacoes);

$temFiltro =
    $busca !== ""
    ||
    $data !== "";

function formatarDataHoraSolicitacao($dataHora, $formato)
{
    if (!$dataHora) {
        return "";
    }

    $objeto = DateTime::createFromFormat(
        "Y-m-d H:i:s",
        $dataHora,
        new DateTimeZone("America/Sao_Paulo")
    );

    if (!$objeto) {
        return "";
    }

    return $objeto->format($formato);
}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>ORION TV - Solicitações de senha</title>

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
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background-color: #080808;
        min-height: 100vh;
        color: white;
        font-family: Arial, Helvetica, sans-serif;
    }

    .navbar-orion {
        min-height: 80px;
        padding: 15px 5%;
        background-color: #080808;
        border-bottom: 1px solid #292929;

        position: sticky;
        top: 0;
        z-index: 1030;
    }

    .logo {
        color: #168cff !important;
        font-size: 28px;
        font-weight: bold;
        letter-spacing: 1px;
    }

    .btn-voltar {
        padding: 9px 18px;
        border-radius: 6px;
    }

    .pagina {
        max-width: 1500px;
        padding: 45px 25px 70px;
        margin: auto;
    }

    .cabecalho {
        margin-bottom: 30px;
    }

    .cabecalho h1 {
        font-size: 30px;
        margin-bottom: 8px;
    }

    .cabecalho p {
        color: #999;
        margin: 0;
    }

    .filtros {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .filtro-busca {
        flex: 1;
        min-width: 240px;
        max-width: 500px;

        height: 46px;

        display: flex;
        align-items: center;

        background-color: #151515;

        border: 1px solid #303030;
        border-radius: 7px;

        padding: 0 14px;
    }

    .filtro-busca i {
        color: #777;
        margin-right: 10px;
        flex-shrink: 0;
    }

    .filtro-busca input {
        width: 100%;
        min-width: 0;

        background: transparent;
        border: none;
        outline: none;

        color: white;
        font-size: 14px;
    }

    .filtro-busca input::placeholder {
        color: #777;
    }

    .campo-filtro {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .campo-filtro > span {
        color: #777;
        font-size: 12px;
    }

    .filtro-status {
        height: 46px;

        min-width: 170px;

        background-color: #151515;
        color: #ccc;

        border: 1px solid #303030;
        border-radius: 7px;

        padding: 0 14px;

        outline: none;

        color-scheme: dark;
    }

    .filtro-status:focus {
        border-color: #168cff;
    }

    .filtro-status:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn-criar-conta {
        height: 46px;

        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;

        text-decoration: none;

        background-color: #168cff;
        color: white;

        padding: 0 18px;

        border-radius: 7px;

        white-space: nowrap;
        flex-shrink: 0;
    }

    .btn-criar-conta:hover {
        background-color: #006dcc;
        color: white;
    }

    .btn-limpar {
        height: 46px;

        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;

        text-decoration: none;

        background-color: #151515;
        color: #ccc;

        border: 1px solid #303030;

        padding: 0 18px;

        border-radius: 7px;

        white-space: nowrap;
        flex-shrink: 0;
    }

    .btn-limpar:hover {
        border-color: #3c3c3c;
        color: white;
    }

    .dica-filtro {
        color: #777;

        font-size: 12px;

        margin: 0 0 30px;
    }

    .card-solicitacao {
        height: 100%;

        background-color: #111;

        border: 1px solid #292929;
        border-radius: 12px;

        padding: 22px;

        transition:
            border-color 0.2s,
            transform 0.2s;
    }

    .card-solicitacao:hover {
        border-color: #3c3c3c;
        transform: translateY(-2px);
    }

    .cliente-topo {
        display: flex;
        align-items: center;

        gap: 13px;

        margin-bottom: 18px;
    }

    .cliente-topo > div:last-child {
        min-width: 0;
    }

    .avatar {
        width: 48px;
        height: 48px;

        flex-shrink: 0;

        display: flex;
        align-items: center;
        justify-content: center;

        background-color: #1d1d1d;

        border: 1px solid #333;

        border-radius: 50%;

        color: #168cff;

        font-size: 20px;
    }

    .cliente-topo h2 {
        font-size: 18px;
        margin: 0 0 4px;

        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .cliente-topo p {
        color: #888;

        font-size: 13px;

        margin: 0;

        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .informacoes {
        display: grid;

        grid-template-columns: 1fr 1fr;

        gap: 14px;

        padding-top: 18px;

        border-top: 1px solid #292929;
    }

    .info span {
        display: block;

        color: #777;

        font-size: 12px;

        margin-bottom: 5px;
    }

    .info strong {
        display: block;

        color: #ddd;

        font-size: 14px;
    }

    .situacoes {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .situacao {
        display: inline-flex;
        align-items: center;

        gap: 8px;

        padding: 6px 11px;

        border-radius: 20px;

        font-size: 12px;

        margin-bottom: 0;
    }

    .situacao span {
        width: 7px;
        height: 7px;

        border-radius: 50%;

        display: block;
    }

    .situacao.tipo {
        background-color: rgba(22, 140, 255, 0.10);
        color: #5aaeff;
    }

    .situacao.tipo span {
        background-color: #5aaeff;
    }

    .situacao.ativa {
        background-color: rgba(50, 200, 120, 0.10);
        color: #63d99a;
    }

    .situacao.ativa span {
        background-color: #63d99a;
    }

    .situacao.atrasada {
        background-color: rgba(255, 170, 50, 0.10);
        color: #ffb74d;
    }

    .situacao.atrasada span {
        background-color: #ffb74d;
    }

    .situacao.suspensa {
        background-color: rgba(220, 70, 70, 0.10);
        color: #ff7777;
    }

    .situacao.suspensa span {
        background-color: #ff7777;
    }

    .informacoes-conta {
        margin-top: 18px;
    }

    .informacoes-conta .info {
        min-width: 0;
    }

    .informacoes-conta .info strong {
        overflow-wrap: anywhere;
    }

    .info-largo {
        grid-column: 1 / -1;
    }

    .titulo-conta {
        grid-column: 1 / -1;

        color: #999;

        font-size: 12px;

        text-transform: uppercase;

        letter-spacing: 0.5px;
    }

    .sem-solicitacoes {
        text-align: center;

        padding: 80px 20px;

        color: #888;
    }

    .sem-solicitacoes i {
        display: block;

        font-size: 45px;

        color: #444;

        margin-bottom: 15px;
    }

    .sem-solicitacoes strong {
        display: block;

        color: #ccc;

        font-size: 18px;

        margin-bottom: 8px;
    }

    @media (max-width: 768px) {

        .navbar-orion {
            padding: 12px 4%;
        }

        .logo {
            font-size: 23px;
        }

        .btn-voltar {
            padding: 8px 12px;
            font-size: 13px;
            white-space: nowrap;
        }

        .pagina {
            padding: 30px 15px 50px;
        }

        .cabecalho h1 {
            font-size: 25px;
        }

        .filtros {
            display: flex;
            flex-direction: row;
            align-items: stretch;
            gap: 10px;
        }

        .filtro-busca {
            flex: 0 0 100%;
            width: 100%;
            max-width: none;
            min-width: 0;
        }

        .campo-filtro {
            flex: 1 1 calc(50% - 5px);
            min-width: 0;
        }

        .filtro-status {
            width: 100%;
            min-width: 0;
            padding: 0 8px;
            font-size: 13px;
        }

        .btn-criar-conta,
        .btn-limpar {
            flex: 1 1 calc(50% - 5px);
            min-width: 0;
            height: 44px;
            padding: 0 10px;
            gap: 6px;
            font-size: 14px;
            text-align: center;
        }

        .informacoes {
            gap: 16px;
        }

    }

    @media (max-width: 480px) {

        .navbar-orion .container-fluid {
            gap: 10px;
            flex-wrap: nowrap;
        }

        .logo {
            font-size: 20px;
            margin-right: 0;
            white-space: nowrap;
        }

        .btn-voltar {
            font-size: 12px;
            padding: 7px 10px;
        }

        .filtros {
            gap: 9px;
        }

        .filtro-busca {
            flex-basis: 100%;
        }

        .campo-filtro {
            flex-basis: calc(50% - 5px);
        }

        .campo-filtro > span {
            font-size: 11px;
        }

        .filtro-status {
            font-size: 12px;
        }

        .btn-criar-conta,
        .btn-limpar {
            flex-basis: calc(50% - 5px);
            padding: 0 6px;
            font-size: 13px;
            gap: 5px;
        }

        .cliente-topo h2 {
            max-width: 220px;
        }

        .informacoes {
            grid-template-columns: 1fr 1fr;
        }

    }

    @media (max-width: 360px) {

        .logo {
            font-size: 18px;
        }

        .btn-voltar {
            font-size: 11px;
            padding: 7px 8px;
        }

        .btn-criar-conta,
        .btn-limpar {
            font-size: 12px;
            gap: 4px;
        }

    }

</style>

</head>

<body>

<nav class="navbar navbar-dark navbar-orion">

<div class="container-fluid">

    <a
        href="tela_inicial_admin.php"
        class="navbar-brand logo"
    >
        ORION TV
    </a>

    <a
        href="assinaturas_admin.php"
        class="btn btn-outline-light btn-voltar"
    >

        <i class="bi bi-arrow-left"></i>

        Voltar

    </a>

</div>

</nav>

<main class="pagina">

<div class="cabecalho">

    <h1>
        Solicitações de senha
    </h1>

    <p>
        Consulte as solicitações de recuperação de senha dos usuários.
        <?php echo $totalSolicitacoes; ?>
        <?php echo $totalSolicitacoes === 1 ? "solicitação encontrada." : "solicitações encontradas."; ?>
    </p>

</div>

<form
    method="GET"
    action="solicitacoes_senhas_admin.php"
    class="filtros"
>

    <div class="filtro-busca">

        <i class="bi bi-search"></i>

        <input
            type="text"
            name="busca"
            placeholder="Buscar por e-mail, nome, CPF, ID, data..."
            title="Busca em ID, e-mail, data, horário, tipo, nome, empresa, CPF, CNPJ, nascimento e status"
            value="<?php echo htmlspecialchars(
                $busca,
                ENT_QUOTES,
                "UTF-8"
            ); ?>"
        >

    </div>

    <label class="campo-filtro">

        <span>
            Data
        </span>

        <input
            type="date"
            name="data"
            id="filtroData"
            class="filtro-status"
            value="<?php echo htmlspecialchars(
                $data,
                ENT_QUOTES,
                "UTF-8"
            ); ?>"
        >

    </label>

    <label class="campo-filtro">

        <span>
            Horário (opcional)
        </span>

        <input
            type="time"
            name="hora"
            id="filtroHora"
            class="filtro-status"
            value="<?php echo htmlspecialchars(
                $hora,
                ENT_QUOTES,
                "UTF-8"
            ); ?>"
            <?php echo $data === "" ? "disabled" : ""; ?>
        >

    </label>

    <button
        type="submit"
        class="btn-criar-conta border-0"
    >

        <i class="bi bi-search"></i>

        Buscar

    </button>

    <?php if ($temFiltro) { ?>

        <a
            href="solicitacoes_senhas_admin.php"
            class="btn-limpar"
        >

            <i class="bi bi-x-circle"></i>

            Limpar

        </a>

    <?php } ?>

</form>

<p class="dica-filtro">
    Para filtrar por horário, selecione primeiro uma data.
</p>

<div class="row g-4">

    <?php if (!empty($solicitacoes)) { ?>

        <?php foreach ($solicitacoes as $solicitacao) { ?>

            <?php

            $idSolicitacao =
                (int) $solicitacao["id_recuperacao"];

            $emailSolicitacao =
                $solicitacao["email"] ?? "";

            $criadoEm =
                $solicitacao["criado_em"] ?? "";

            $dataSolicitacao =
                formatarDataHoraSolicitacao(
                    $criadoEm,
                    "d/m/Y"
                );

            $horaSolicitacao =
                formatarDataHoraSolicitacao(
                    $criadoEm,
                    "H:i:s"
                );

            $idUsuarioConta =
                $solicitacao["conta_id_usuario"] ?? null;

            $contaEncontrada =
                $idUsuarioConta !== null;

            $tipoConta =
                $solicitacao["conta_tipo_usuario"] ?? "";

            $aniversarioConta =
                $solicitacao["conta_aniversario"] ?? "";

            $nascimentoConta = "";

            if ($aniversarioConta !== "") {

                $nascimentoConta =
                    formatarDataHoraSolicitacao(
                        $aniversarioConta . " 00:00:00",
                        "d/m/Y"
                    );
            }

            if ($nascimentoConta === "") {

                $nascimentoConta = "Não informado";
            }

            $cadastroEncontrado = false;

            $statusConta = "";

            $dadosConta = [];

            if (
                $tipoConta === "Cliente"
                &&
                ($solicitacao["conta_id_cliente"] ?? null) !== null
            ) {

                $cadastroEncontrado = true;

                $statusConta =
                    $solicitacao["conta_status_cliente"] ?? "";

                $dadosConta = [
                    ["ID do usuário", $idUsuarioConta, false],
                    ["ID do cliente", $solicitacao["conta_id_cliente"], false],
                    ["Nome", $solicitacao["conta_nome_cliente"] ?? "", true],
                    ["CPF", $solicitacao["conta_cpf_cliente"] ?? "", false],
                    ["Data de nascimento", $nascimentoConta, false]
                ];
            }

            elseif (
                $tipoConta === "Distribuidor"
                &&
                ($solicitacao["conta_id_distribuidor"] ?? null) !== null
            ) {

                $cadastroEncontrado = true;

                $statusConta =
                    $solicitacao["conta_status_distribuidor"] ?? "";

                $dadosConta = [
                    ["ID do usuário", $idUsuarioConta, false],
                    ["ID do distribuidor", $solicitacao["conta_id_distribuidor"], false],
                    ["Empresa", $solicitacao["conta_empresa_distribuidor"] ?? "", true],
                    ["CNPJ", $solicitacao["conta_cnpj_distribuidor"] ?? "", false],
                    ["Data de nascimento", $nascimentoConta, false]
                ];
            }

            elseif (
                $tipoConta === "Administrador"
                &&
                ($solicitacao["conta_id_administrador"] ?? null) !== null
            ) {

                $cadastroEncontrado = true;

                $statusConta =
                    $solicitacao["conta_status_administrador"] ?? "";

                $dadosConta = [
                    ["ID do usuário", $idUsuarioConta, false],
                    ["ID do administrador", $solicitacao["conta_id_administrador"], false],
                    ["Nome", $solicitacao["conta_nome_administrador"] ?? "", true],
                    ["Data de nascimento", $nascimentoConta, false]
                ];
            }

            elseif ($contaEncontrada) {

                $dadosConta = [
                    ["ID do usuário", $idUsuarioConta, false],
                    ["Data de nascimento", $nascimentoConta, false]
                ];
            }

            $situacoesConta = [];

            if (!$contaEncontrada) {

                $situacoesConta[] = [
                    "atrasada",
                    "Conta não encontrada"
                ];
            }

            else {

                $situacoesConta[] = [
                    "tipo",
                    $tipoConta
                ];

                if (!$cadastroEncontrado) {

                    $situacoesConta[] = [
                        "atrasada",
                        "Cadastro de " . strtolower($tipoConta) . " não encontrado"
                    ];
                }

                elseif ($statusConta === "Ativo") {

                    $situacoesConta[] = [
                        "ativa",
                        "Conta ativa"
                    ];
                }

                else {

                    $situacoesConta[] = [
                        "suspensa",
                        "Conta inativa"
                    ];
                }
            }

            ?>

            <div class="col-xl-4 col-lg-6 col-md-6">

                <div class="card-solicitacao">

                    <div class="cliente-topo">

                        <div class="avatar">

                            <i class="bi bi-key-fill"></i>

                        </div>

                        <div>

                            <h2
                                title="<?php echo htmlspecialchars(
                                    $emailSolicitacao,
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>"
                            >

                                <?php

                                echo htmlspecialchars(
                                    $emailSolicitacao,
                                    ENT_QUOTES,
                                    "UTF-8"
                                );

                                ?>

                            </h2>

                            <p>

                                ID da solicitação:
                                <?php echo $idSolicitacao; ?>

                            </p>

                        </div>

                    </div>

                    <div class="situacoes">

                        <?php foreach ($situacoesConta as $situacaoConta) { ?>

                            <div
                                class="situacao <?php echo htmlspecialchars(
                                    $situacaoConta[0],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>"
                            >

                                <span></span>

                                <?php

                                echo htmlspecialchars(
                                    $situacaoConta[1],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );

                                ?>

                            </div>

                        <?php } ?>

                    </div>

                    <div class="informacoes">

                        <div class="info">

                            <span>
                                Data
                            </span>

                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    $dataSolicitacao,
                                    ENT_QUOTES,
                                    "UTF-8"
                                );

                                ?>

                            </strong>

                        </div>

                        <div class="info">

                            <span>
                                Horário
                            </span>

                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    $horaSolicitacao,
                                    ENT_QUOTES,
                                    "UTF-8"
                                );

                                ?>

                            </strong>

                        </div>

                    </div>

                    <?php if (!empty($dadosConta)) { ?>

                        <div class="informacoes informacoes-conta">

                            <div class="titulo-conta">
                                Dados da conta
                            </div>

                            <?php foreach ($dadosConta as $dadoConta) { ?>

                                <div
                                    class="info<?php echo $dadoConta[2] ? " info-largo" : ""; ?>"
                                >

                                    <span>

                                        <?php

                                        echo htmlspecialchars(
                                            $dadoConta[0],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );

                                        ?>

                                    </span>

                                    <strong>

                                        <?php

                                        echo htmlspecialchars(
                                            (string) $dadoConta[1],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );

                                        ?>

                                    </strong>

                                </div>

                            <?php } ?>

                        </div>

                    <?php } ?>

                </div>

            </div>

        <?php } ?>

    <?php } else { ?>

        <div class="col-12">

            <div class="card-solicitacao sem-solicitacoes">

                <i class="bi bi-key"></i>

                <strong>
                    Nenhuma solicitação encontrada
                </strong>

                <p>

                    <?php

                    if ($temFiltro) {

                        echo "Nenhuma solicitação corresponde à busca ou ao filtro de data.";

                    }

                    else {

                        echo "Ainda não existem solicitações de recuperação de senha.";

                    }

                    ?>

                </p>

            </div>

        </div>

    <?php } ?>

</div>

</main>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

<script>

    (function () {

        var campoData = document.getElementById("filtroData");

        var campoHora = document.getElementById("filtroHora");

        if (!campoData || !campoHora) {

            return;

        }

        function atualizarHorario() {

            if (campoData.value === "") {

                campoHora.value = "";

                campoHora.disabled = true;

            } else {

                campoHora.disabled = false;

            }

        }

        campoData.addEventListener("input", atualizarHorario);

        campoData.addEventListener("change", atualizarHorario);

        window.addEventListener("pageshow", atualizarHorario);

        atualizarHorario();

    })();

</script>

<script>

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