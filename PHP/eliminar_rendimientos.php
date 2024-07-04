<?php
// Realiza la conexión a la base de datos (cambia estos valores por los tuyos)
include 'db.php';

// Verifica si se ha enviado una solicitud POST y si los campos del formulario están definidos
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['numero-cedula']) && isset($_POST['id-producto']) && isset($_POST['cantidad'])) {
    // Obtén los valores de los campos del formulario
    $cedula = $_POST['numero-cedula'];
    $idProducto = $_POST['id-producto'];
    $cantidad = $_POST['cantidad'];

    // Consulta para eliminar registros según los parámetros recibidos
    $sql = "WITH CTE AS (
                SELECT TOP $cantidad id_rendimiento
                FROM rendimiento
                WHERE id_producto = ?
                  AND id_trabajador IN (
                    SELECT id_trabajador
                    FROM trabajadores
                    WHERE cedula = ?
                  )
                ORDER BY id_rendimiento DESC
            )
            DELETE FROM CTE";

    $params = array($idProducto, $cedula);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        // Manejar el caso de error en la eliminación
        header("Location: ../rendimientos.html?success=false&error=eliminar_error");
        exit();
    } else {
        // Redirigir con éxito
        header("Location: ../rendimientos.html?success=true");
        exit();
    }
}

// Cerrar conexión
sqlsrv_close($conn);
?>
