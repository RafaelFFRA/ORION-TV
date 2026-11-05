
<?php

require_once("conexao.php");

$sql = "
    SELECT
        distribuidor.empresa_distribuidor,
        distribuidor.cnpj_empresa_distribuidor
    FROM distribuidor
    INNER JOIN usuario
        ON distribuidor.FK_distribuidor_id_usuario = usuario.id_usuario
    WHERE usuario.tipo_usuario = 'Distribuidor'
    ORDER BY distribuidor.empresa_distribuidor ASC
";

$resultado = mysqli_query($conn, $sql);

if (!$resultado) {
    die(
        "Erro ao consultar distribuidores: " .
        mysqli_error($conn)
    );
}

?>

<div class="table-responsive">

    <table class="table table-dark table-hover align-middle">

        <thead>

            <tr>
                <th>Empresa</th>
                <th>CNPJ</th>
            </tr>

        </thead>

        <tbody>

            <?php if (mysqli_num_rows($resultado) > 0): ?>

                <?php while ($distribuidor = mysqli_fetch_assoc($resultado)): ?>

                    <tr>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $distribuidor["empresa_distribuidor"] ?? "",
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $distribuidor["cnpj_empresa_distribuidor"] ?? "",
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>
                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td
                        colspan="2"
                        class="text-center text-secondary"
                    >
                        Nenhum distribuidor cadastrado.
                    </td>

                </tr>

            <?php endif; ?>

        </tbody>

    </table>

</div>

