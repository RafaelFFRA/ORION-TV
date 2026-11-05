<?php

session_start();
require "conexao.php";


$id_avaliacao = $_GET['id']; 


$sql = "

DELETE FROM avaliacao

WHERE id_avaliacao = '$id_avaliacao'

";


if (mysqli_query($conn, $sql)) {

    echo "Avaliação excluída com sucesso!";
  

} else {

    echo "Erro ao excluir: " . mysqli_error($conn);

}

?>