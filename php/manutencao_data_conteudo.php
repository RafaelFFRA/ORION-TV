<?php



$hoje = date("Y-m-d");

$sql_status = "
    UPDATE conteudo
    SET conteudo_status = CASE
        WHEN '$hoje' < janela_exib_inicio_conteudo THEN 'Inativo'
        WHEN '$hoje' > janela_exib_fim_conteudo THEN 'Inativo'
        ELSE 'Ativo'
    END
";

mysqli_query($conn, $sql_status);