<?php
include 'db.php';

// Obtener las fechas enviadas desde el formulario
$startDate = $_GET['start_date'];
$endDate = $_GET['end_date'];

// Realizar la consulta SQL con el filtro de fechas
$sql = "EXEC sp_ObtenerRendimientoPorFecha @startDate = ?, @endDate =?;";
$params = array($startDate, $endDate);
$result = sqlsrv_query($conn, $sql, $params);

$rendimientos = [];

if ($result === false) {
    http_response_code(500); // Error interno del servidor
    die(json_encode(['error' => 'Error en la consulta SQL: ' . sqlsrv_errors()]));
}

if (sqlsrv_has_rows($result)) {
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $rendimientos[] = $row;
    }
} else {
    http_response_code(404); // No encontrado
    die(json_encode(['error' => 'No se encontraron resultados']));
}

// Liberar los recursos y cerrar la conexión a la base de datos
sqlsrv_free_stmt($result);
sqlsrv_close($conn);

header('Content-Type: application/json');
echo json_encode($rendimientos);
?>
