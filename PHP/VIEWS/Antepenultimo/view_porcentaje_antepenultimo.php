<?php
// Incluir el archivo de conexión a la base de datos
include '../../db.php';

// Verificar la conexión
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Consulta SQL para obtener el porcentaje de ingresos por hora
$sql = "EXEC sp_ObtenerPorcentajeAntepenul";

$result = sqlsrv_query($conn, $sql);

// Verificar si hay resultados
if ($result !== false) {
    // Convertir los resultados a un array asociativo
    $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

    // Devolver los resultados como JSON
    echo json_encode($row);
} else {
    // No se encontraron resultados
    echo json_encode(array('error' => 'No se encontraron resultados'));
}

// Liberar los recursos y cerrar la conexión a la base de datos
sqlsrv_free_stmt($result);
sqlsrv_close($conn);
?>
