<?php
// Realiza la conexión a la base de datos (cambia estos valores por los tuyos)
include '../db.php';

if (isset($_POST['cedula'])) {
    $cedula = $_POST['cedula'];

    // Realiza la consulta en la base de datos para verificar si la cédula existe
    $sql = "{call sp_CheckCedula(?)}";
    $params = array($cedula);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt !== false) {
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        if ($row !== null) {
            echo 'existe';
        } else {
            echo 'no_existe';
        }
    } else {
        echo 'error';
    }

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
}
?>
