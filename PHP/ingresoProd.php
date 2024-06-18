<?php
include 'db.php';

if (isset($_POST['product-name'], $_POST['product-rendimiento-hora'])) {
    $productName = $_POST['product-name'];
    $productRendimientoHora = $_POST['product-rendimiento-hora'];

    // Verificar si el nombre del producto ya existe en la base de datos
    $sql_check = "SELECT * FROM productos WHERE nombre_producto = ?";
    $params = array($productName);
    $stmt = sqlsrv_query($conn, $sql_check, $params);

    if ($stmt === false) {
        echo 'Hubo un error al realizar la consulta';
        exit();
    }

    $row_count = sqlsrv_has_rows($stmt);

    if ($row_count === true) {
        // Si el nombre del producto ya existe, realizar una actualización
        $sql_update = "UPDATE productos SET rendimiento_producto_hora = ? WHERE nombre_producto = ?";
        $params_update = array($productRendimientoHora, $productName);
        $stmt_update = sqlsrv_query($conn, $sql_update, $params_update);

        if ($stmt_update === false) {
            echo 'Hubo un error al actualizar el Producto';
            exit();
        } else {
            echo 'Se ha actualizado correctamente';
            header("Location: ../ingProductos1.html"); // Redirigir con mensaje de éxito
            exit();
        }
    } else {
        // Si el nombre del producto no existe, realizar la inserción
        $sql_insert = "INSERT INTO productos (nombre_producto, rendimiento_producto_hora) VALUES (?, ?)";
        $params_insert = array($productName, $productRendimientoHora);
        $stmt_insert = sqlsrv_query($conn, $sql_insert, $params_insert);

        if ($stmt_insert === false) {
            echo 'Hubo un error al ingresar el Producto';
            exit();
        } else {
            echo 'Se ha ingresado correctamente';
            header("Location: ../ingProductos1.html"); // Redirigir con mensaje de éxito
            exit();
        }
    }

    sqlsrv_free_stmt($stmt); // Liberar recursos
}

sqlsrv_close($conn); // Cerrar la conexión a la base de datos
?>
