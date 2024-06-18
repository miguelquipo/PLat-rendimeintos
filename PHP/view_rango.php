<?php
// Incluir el archivo de conexión a la base de datos
include 'db.php';

// Verificar la conexión
if ($conn === false) {
    http_response_code(500); // Error interno del servidor
    die(json_encode(['error' => 'Conexión fallida: No se pudo conectar a la base de datos']));
}

// Consulta SQL para obtener el rango actual
$sql = "EXEC sp_ObtenerRangoActual";


$result = sqlsrv_query($conn, $sql);

// Verificar si hay resultados
if ($result === false) {
    http_response_code(500); // Error interno del servidor
    die(json_encode(['error' => 'Error en la consulta SQL: ' . print_r(sqlsrv_errors(), true)]));
}

if (sqlsrv_has_rows($result)) {
    // Convertir los resultados a un array asociativo
    $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

    // Devolver los resultados como JSON
    echo json_encode($row);
} else {
    // No se encontraron resultados
    http_response_code(404); // No encontrado
    echo json_encode(['error' => 'No se encontraron resultados']);
}

// Liberar los recursos y cerrar la conexión a la base de datos
sqlsrv_free_stmt($result);
sqlsrv_close($conn);
?>
