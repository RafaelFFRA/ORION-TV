<?php

session_start();
require "conexao.php";

$id_distribuidor = $_SESSION['id_distribuidor'];

$id_conteudo = $_POST['id'];

$titulo_conteudo = $_POST['titulo'];
$sinopse_conteudo = $_POST['sinopse'];
$elenco_conteudo = $_POST['elenco'];
$diretores_conteudo = $_POST['diretores'];
$genero_conteudo = $_POST['genero'];
$classe_etaria_conteudo = $_POST['classe_etaria'];
$janela_exib_inicio_conteudo = $_POST['janela_inicio'];
$janela_exib_fim_conteudo = $_POST['janela_fim'];




$sql_busca = "

SELECT thumb_conteudo, video_conteudo

FROM conteudo

WHERE id_conteudo = '$id_conteudo'
AND FK_conteudo_id_distribuidor = '$id_distribuidor'

";


$resultado = mysqli_query($conn, $sql_busca);


$filme = mysqli_fetch_assoc($resultado);


if(!$filme){

    die("Filme não encontrado ou não pertence a este distribuidor");

}


$caminho_thumb = $filme['thumb_conteudo'];
$caminho_video = $filme['video_conteudo'];





if($_FILES['thumb']['name'] != ""){

    $nome_thumb = $_FILES['thumb']['name'];

    $caminho_thumb = "thumbs/" . $nome_thumb;


    move_uploaded_file(
        $_FILES['thumb']['tmp_name'],
        $caminho_thumb
    );

}





if($_FILES['video']['name'] != ""){

    $nome_video = $_FILES['video']['name'];

    $caminho_video = "videos/" . $nome_video;


    move_uploaded_file(
        $_FILES['video']['tmp_name'],
        $caminho_video
    );

}





$sql = "

UPDATE conteudo

SET

titulo_conteudo = '$titulo_conteudo',
sinopse_conteudo = '$sinopse_conteudo',
elenco_conteudo = '$elenco_conteudo',
diretores_conteudo = '$diretores_conteudo',
genero_conteudo = '$genero_conteudo',
classe_etaria_conteudo = '$classe_etaria_conteudo',
janela_exib_inicio_conteudo = '$janela_exib_inicio_conteudo',
janela_exib_fim_conteudo = '$janela_exib_fim_conteudo',
thumb_conteudo = '$caminho_thumb',
video_conteudo = '$caminho_video'

WHERE id_conteudo = '$id_conteudo'
AND FK_conteudo_id_distribuidor = '$id_distribuidor'

";


if(mysqli_query($conn, $sql)){

   

    echo "Filme atualizado com sucesso!";

}else{

    echo "Erro: " . mysqli_error($conn);

}


?>