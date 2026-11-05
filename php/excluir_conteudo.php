<?php

session_start();
require "conexao.php";


$id_conteudo = $_GET['id']; 



$sql = "

DELETE FROM conteudo

WHERE id_conteudo = '$id_conteudo'

";



if(mysqli_query($conn, $sql)){

    header("Location: catalogo.php");

}else{

    echo "Erro ao excluir: " . mysqli_error($conn);

}


?>