<?php

session_start();

require_once(__DIR__ . "/../includes/alerta.php");




if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: ../index.php");

    exit;
}




$plano = $_POST["plano"] ?? "";
$forma_pagamento = $_POST["forma_pagamento"] ?? "";



if ($plano !== "Mensal" && $plano !== "Anual") {

    mostrarAlerta(
        "Plano inválido.",
        "window.location.href = '../pagamento.php'"
    );

    exit;
}



if (
    $forma_pagamento !== "Pix" &&
    $forma_pagamento !== "Cartão" &&
    $forma_pagamento !== "Boleto"
) {

    mostrarAlerta(
        "Forma de pagamento inválida.",
        "window.location.href = '../pagamento.php'"
    );

    exit;
}




$_SESSION["cadastro_assinatura"] = [

    "plano" => $plano,

    "forma_pagamento" => $forma_pagamento

];




header("Location: ../cadastro.php");

exit;

?>