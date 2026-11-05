<?php

session_start();
require "conexao.php";


$id_assinatura = $_GET['id'];




$sql_cliente = "

SELECT FK_assinatura_id_cliente

FROM assinatura

WHERE id_assinatura = '$id_assinatura'

";


$resultado = mysqli_query($conn, $sql_cliente);

$dados = mysqli_fetch_assoc($resultado);


$id_cliente = $dados['FK_assinatura_id_cliente'];





$sql_assinatura = "

UPDATE assinatura

SET status_assinatura = 'Inativa'

WHERE id_assinatura = '$id_assinatura'

";

mysqli_query($conn, $sql_assinatura);





$sql_cliente_update = "

UPDATE cliente

SET status_conta_cliente = 'Suspenso'

WHERE FK_cliente_id_cliente = '$id_cliente'

";


if(mysqli_query($conn, $sql_cliente_update)){

  

}else{

    echo "Erro: " . mysqli_error($conn);

}


?>