<?php

function mostrarAlerta($mensagem, $acao = "history.back()") {

?>

<div id="orionAlerta" class="orion-overlay">

    <div
        class="orion-alerta"
        role="alertdialog"
        aria-modal="true"
    >

        <div class="orion-logo">
            ORION TV
        </div>

        <div class="orion-mensagem">
            <?= htmlspecialchars($mensagem) ?>
        </div>

        <button
            type="button"
            class="orion-botao"
            onclick="fecharOrionAlerta()"
        >
            OK
        </button>

    </div>

</div>


<style>



    #orionAlerta,
    #orionAlerta *,
    #orionAlerta *::before,
    #orionAlerta *::after {
        box-sizing: border-box;
    }




    #orionAlerta.orion-overlay {

        position: fixed !important;

        top: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        left: 0 !important;

        width: 100% !important;
        height: 100% !important;

        margin: 0 !important;

        padding: 16px !important;

        display: flex !important;

        justify-content: center !important;
        align-items: center !important;

        background: rgba(0, 0, 0, 0.75) !important;

        backdrop-filter: blur(6px) !important;
        -webkit-backdrop-filter: blur(6px) !important;

        zoom: 1 !important;

        z-index: 2147483647 !important;

        font-family:
            Arial,
            Helvetica,
            sans-serif !important;

        font-size: 16px !important;

        line-height: normal !important;

        overflow: auto !important;

        -webkit-text-size-adjust: 100%;

    }


   

    #orionAlerta .orion-alerta {

        position: relative !important;

        display: block !important;

        width: 100% !important;

        max-width: 430px !important;

        min-width: 0 !important;

        margin: 0 auto !important;

        padding: 30px 24px !important;

        background: #222 !important;

        border-radius: 16px !important;

        text-align: center !important;

        color: #ffffff !important;

        box-shadow:
            0 10px 40px rgba(0, 0, 0, 0.5) !important;

        animation:
            orionEntrada
            0.2s
            ease;

    }



    #orionAlerta .orion-logo {

        display: block !important;

        width: 100% !important;

        margin: 0 0 18px 0 !important;
        padding: 0 !important;

        font-family:
            Arial,
            Helvetica,
            sans-serif !important;

        font-size: 28px !important;

        line-height: 1.2 !important;

        font-weight: 700 !important;

        color: #006dcc !important;

    }


   

    #orionAlerta .orion-mensagem {

        display: block !important;

        width: 100% !important;

        margin: 0 0 24px 0 !important;
        padding: 0 !important;

        font-family:
            Arial,
            Helvetica,
            sans-serif !important;

        font-size: 18px !important;

        line-height: 1.5 !important;

        font-weight: 400 !important;

        color: #f1f1f1 !important;

        overflow-wrap: anywhere !important;

        word-break: normal !important;

    }


 

    #orionAlerta .orion-botao {

        display: block !important;

        width: 100% !important;

        min-width: 0 !important;

        min-height: 50px !important;

        margin: 0 !important;

        padding: 12px 20px !important;

        border: none !important;

        border-radius: 8px !important;

        background: #006dcc !important;

        color: #ffffff !important;

        font-family:
            Arial,
            Helvetica,
            sans-serif !important;

        font-size: 18px !important;

        line-height: 1.2 !important;

        font-weight: 600 !important;

        text-align: center !important;

        cursor: pointer;

        -webkit-appearance: none;

        appearance: none;

        transition:
            background 0.2s ease,
            transform 0.1s ease;

    }


    #orionAlerta .orion-botao:hover {

        background: #005aa3 !important;

    }


    #orionAlerta .orion-botao:active {

        transform: scale(0.98);

    }


  

    @keyframes orionEntrada {

        from {

            opacity: 0;

            transform: scale(0.95);

        }

        to {

            opacity: 1;

            transform: scale(1);

        }

    }


   

    @media (max-width: 400px) {

        #orionAlerta.orion-overlay {

            padding: 12px !important;

        }

        #orionAlerta .orion-alerta {

            padding: 26px 20px !important;

            border-radius: 14px !important;

        }

        #orionAlerta .orion-logo {

            font-size: 26px !important;

        }

        #orionAlerta .orion-mensagem {

            font-size: 17px !important;

        }

        #orionAlerta .orion-botao {

            min-height: 50px !important;

            font-size: 17px !important;

        }

    }




    @media (max-width: 320px) {

        #orionAlerta.orion-overlay {

            padding: 10px !important;

        }

        #orionAlerta .orion-alerta {

            padding: 22px 16px !important;

        }

        #orionAlerta .orion-logo {

            font-size: 24px !important;

        }

        #orionAlerta .orion-mensagem {

            font-size: 16px !important;

        }

        #orionAlerta .orion-botao {

            font-size: 16px !important;

        }

    }

</style>


<script>

    (function () {

        var overlay = document.getElementById("orionAlerta");

     

        if (overlay && overlay.parentNode !== document.body) {
            document.body.appendChild(overlay);
        }



        if (!document.querySelector('meta[name="viewport"]')) {
            var meta = document.createElement("meta");
            meta.name = "viewport";
            meta.content = "width=device-width, initial-scale=1";
            document.head.appendChild(meta);
        }

    })();

    function fecharOrionAlerta() {

        const alerta = document.getElementById("orionAlerta");

        if (alerta) {

            alerta.remove();

        }

        <?= $acao ?>;

    }

</script>


<?php

}

?>