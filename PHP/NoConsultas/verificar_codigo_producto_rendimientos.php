<?php
// Realiza la conexión a la base de datos (cambia estos valores por los tuyos)
include '../db.php';

// Verificar conexión
if ($conn === false) {
    die("Conexión fallida: " . sqlsrv_errors());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener el código de producto enviado por AJAX
    $codigoProducto = $_POST["codigoProducto"];

    // Prepara la llamada al nuevo procedimiento almacenado
    $sql = "{call sp_ValidarCodigoProducto(?)}";
    $params = array($codigoProducto);

    // Ejecuta el nuevo procedimiento almacenado
    $stmt = sqlsrv_query($conn, $sql, $params);

    // Devolver la respuesta al cliente
    if ($stmt !== false) {
        if (sqlsrv_has_rows($stmt)) {
            echo "existe";
        } else {
            echo "no_existe";
        }
    } else {
        echo "error: " . sqlsrv_errors();
    }

    // Cerrar la conexión
    sqlsrv_free_stmt($stmt);
}

// Cerrar la conexión
sqlsrv_close($conn);
