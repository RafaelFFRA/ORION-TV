
<?php

require_once("conexao.php");

$sql = "
    SELECT
        administrador.nome_admin,
        usuario.email
    FROM administrador
    INNER JOIN usuario
        ON administrador.FK_administrador_id_usuario = usuario.id_usuario
    WHERE usuario.tipo_usuario = 'Administrador'
    ORDER BY administrador.nome_admin ASC
";

$resultado = mysqli_query($conn, $sql);

if (!$resultado) {
    die(
        "Erro ao consultar administradores: " .
        mysqli_error($conn)
    );
}

?>

<div class="table-responsive">

    <table class="table table-dark table-hover align-middle">

        <thead>

            <tr>
                <th>Nome</th>
                <th>E-mail</th>
            </tr>

        </thead>

        <tbody>

            <?php if (mysqli_num_rows($resultado) > 0): ?>

                <?php while ($admin = mysqli_fetch_assoc($resultado)): ?>

                    <tr>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $admin["nome_admin"] ?? "",
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $admin["email"] ?? "",
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
                        Nenhum administrador cadastrado.
                    </td>

                </tr>

            <?php endif; ?>

        </tbody>

    </table>

</div>

