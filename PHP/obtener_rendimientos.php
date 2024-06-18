<?php
// Incluir tu archivo de conexión a la base de datos
include 'db.php';

// Realizar la consulta SQL para obtener los datos agrupados
$sql = "EXEC sp_ObtenerRendimiento";

$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    echo 'Hubo un error al ejecutar la consulta';
    exit();
}

$rendimientos = [];

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $rendimientos[] = $row;
}

// Liberar recursos
sqlsrv_free_stmt($stmt);

// Cerrar la conexión
sqlsrv_close($conn);

// Establecer el encabezado para indicar que la respuesta es JSON
header('Content-Type: application/json');

// Imprimir los datos en formato JSON
echo json_encode($rendimientos);
?>
