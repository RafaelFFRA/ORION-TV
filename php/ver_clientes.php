
<?php

require_once("conexao.php");

$sql = "
    SELECT
        cliente.cpf_cliente,
        usuario.email
    FROM cliente
    INNER JOIN usuario
        ON cliente.FK_cliente_id_usuario = usuario.id_usuario
    WHERE usuario.tipo_usuario = 'Cliente'
    ORDER BY cliente.cpf_cliente ASC
";

$resultado = mysqli_query($conn, $sql);

if (!$resultado) {
    die(
        "Erro ao consultar clientes: " .
        mysqli_error($conn)
    );
}

?>

<div class="table-responsive">

    <table class="table table-dark table-hover align-middle">

        <thead>

            <tr>
                <th>CPF</th>
                <th>E-mail</th>
            </tr>

        </thead>

        <tbody>

            <?php if (mysqli_num_rows($resultado) > 0): ?>

                <?php while ($cliente = mysqli_fetch_assoc($resultado)): ?>

                    <tr>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $cliente["cpf_cliente"] ?? "",
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $cliente["email"] ?? "",
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
                        Nenhum cliente cadastrado.
                    </td>

                </tr>

            <?php endif; ?>

        </tbody>

    </table>

</div>

