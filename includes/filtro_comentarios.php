
<?php



function normalizarComentario(string $texto): string
{
    // Converte para minúsculas
    $texto = mb_strtolower($texto, "UTF-8");



    $texto = strtr($texto, [
        "@" => "a",
        "4" => "a",
        "3" => "e",
        "1" => "i",
        "!" => "i",
        "0" => "o",
        "$" => "s",
        "5" => "s"
    ]);


    if (class_exists("Normalizer")) {

        $texto = Normalizer::normalize(
            $texto,
            Normalizer::FORM_D
        );

 
        $texto = preg_replace(
            "/\p{Mn}+/u",
            "",
            $texto
        );
    }

    
  

    $texto = preg_replace(
        "/(\p{L})\1{2,}/u",
        "$1$1",
        $texto
    );

 

    $texto = preg_replace(
        "/\s+/u",
        " ",
        $texto
    );

    return trim($texto);
}




function criarPadraoTermo(string $termo): string
{
    $termo = normalizarComentario($termo);

   

    $palavras = preg_split(
        "/\s+/u",
        $termo,
        -1,
        PREG_SPLIT_NO_EMPTY
    );

    $padraoPalavras = [];

    foreach ($palavras as $palavra) {

        $caracteres = preg_split(
            "//u",
            $palavra,
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        $padrao = "";

        foreach ($caracteres as $caractere) {

            $caractereEscapado = preg_quote(
                $caractere,
                "/"
            );

            

            $padrao .= $caractereEscapado . "+";

            

            $padrao .= "[\\s._\\-]*";
        }

        
        $padrao = rtrim(
            $padrao,
            "[\\s._\\-]*"
        );

        $padraoPalavras[] = $padrao;
    }

    

    return implode(
        "[\\s._\\-]+",
        $padraoPalavras
    );
}




function comentarioPossuiTermoProibido(string $comentario): bool
{
    $arquivoJson = __DIR__ . "/filtro_comentarios.json";

    
    if (!file_exists($arquivoJson)) {
        return false;
    }

    

    $conteudoJson = file_get_contents($arquivoJson);

    if ($conteudoJson === false) {
        return false;
    }

    

    $dados = json_decode(
        $conteudoJson,
        true
    );

    

    if (
        !is_array($dados) ||
        !isset($dados["palavras"]) ||
        !is_array($dados["palavras"])
    ) {
        return false;
    }

    

    $comentarioNormalizado = normalizarComentario(
        $comentario
    );

    
    foreach ($dados["palavras"] as $termo) {

        if (!is_string($termo)) {
            continue;
        }

        $termo = trim($termo);

        if ($termo === "") {
            continue;
        }

        $padraoTermo = criarPadraoTermo($termo);

        

        $padrao = "/(?<!\p{L})"
            . $padraoTermo
            . "(?!\p{L})/u";

        if (
            preg_match(
                $padrao,
                $comentarioNormalizado
            ) === 1
        ) {
            return true;
        }
    }

    return false;
}

