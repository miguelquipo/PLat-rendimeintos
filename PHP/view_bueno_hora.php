<?php
include 'db.php';

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$sql = "EXEC sp_ObtenerBuenoHora";

$result = sqlsrv_query($conn, $sql);

$observacion = [];

if ($result !== false) {
    while ($trabajador = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $observacion[] = $trabajador;
    }
    sqlsrv_free_stmt($result);
}

sqlsrv_close($conn);

header('Content-Type: application/json');
echo json_encode($observacion);
?>
