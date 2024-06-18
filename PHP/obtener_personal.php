<?php
// archivo: obtener_trabajadores.php
include 'db.php';

// Verificar la conexión
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$sql = "EXEC sp_ObtenerTrabajadoresActivos;";

$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    echo 'Hubo un error al ejecutar la consulta';
    exit();
}

$trabajadores = array();

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $trabajadores[] = $row;
}

// Liberar recursos
sqlsrv_free_stmt($stmt);

// Enviar los datos como JSON
header('Content-Type: application/json');
echo json_encode($trabajadores);

// Cerrar conexión
sqlsrv_close($conn);
?>
