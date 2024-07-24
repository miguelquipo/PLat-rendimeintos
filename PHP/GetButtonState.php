<?php
include 'db.php';

// Consulta para obtener el estado más reciente del botón
$sql = "SELECT TOP 1 accion FROM boton_actividades ORDER BY fecha DESC, hora DESC";
$stmt = sqlsrv_query($conn, $sql);
$action = null;

if ($stmt !== false) {
    if (sqlsrv_fetch($stmt)) {
        $action = sqlsrv_get_field($stmt, 0);
    }
    sqlsrv_free_stmt($stmt);
}

$isRunning = ($action === 'INICIO') ? true : false;
echo json_encode($isRunning);

sqlsrv_close($conn);
?>
