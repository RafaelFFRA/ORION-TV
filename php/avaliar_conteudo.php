<?php

session_start();
require "conexao.php";


$id_cliente = $_SESSION['id_cliente'];


if (isset($_POST['enviar'])) { 


$comentario_avaliacao = $_POST['comentario'];

$id_conteudo = $_POST['id_conteudo'];



$sql = "

INSERT INTO avaliacao(

comentario_avaliacao,
FK_avaliacao_id_cliente,
FK_avaliacao_id_conteudo

)

VALUES(

'$comentario_avaliacao',
'$id_cliente',
'$id_conteudo'

)";


if (mysqli_query($conn, $sql)) {

    echo "Avaliação enviada com sucesso!";
    //header("Location: filme.php?id=$id_conteudo");

} else {

    echo "Erro ao enviar avaliação: " . mysqli_error($conn);

}


}

?>