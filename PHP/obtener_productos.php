<?php
// archivo: obtener_productos.php
include 'db.php';

// Verificar la conexión
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Consulta SQL para obtener los productos
$sql = "EXEC sp_ObtenerProductos";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    echo 'Hubo un error al ejecutar la consulta';
    exit();
}

$productos = array();

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $productos[] = $row;
}

// Liberar recursos
sqlsrv_free_stmt($stmt);

// Enviar los datos como JSON
header('Content-Type: application/json');
echo json_encode($productos);

// Cerrar conexión
sqlsrv_close($conn);
?>
