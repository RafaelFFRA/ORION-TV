<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

echo "PHP funcionando.<br>";

require_once __DIR__ . '/php/conexao.php';

echo "Conexão incluída.<br>";

require_once __DIR__ . '/email/PHPMailer/src/Exception.php';
require_once __DIR__ . '/email/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/email/PHPMailer/src/SMTP.php';

echo "Arquivos do PHPMailer incluídos.";