<?php
include 'db.php';

if (isset($_POST['product-name'], $_POST['product-rendimiento-hora'])) {
    $productName = $_POST['product-name'];
    $productRendimientoHora = $_POST['product-rendimiento-hora'];
    $productId = $_POST['product-id'];

    if (empty($productId)) {
        // Insertar un nuevo producto
        $sql_insert = "INSERT INTO productos (nombre_producto, rendimiento_producto_hora) VALUES (?, ?)";
        $params_insert = array($productName, $productRendimientoHora);
        $stmt_insert = sqlsrv_query($conn, $sql_insert, $params_insert);

        if ($stmt_insert === false) {
            echo 'Hubo un error al ingresar el Producto';
            exit();
        } else {
            echo 'Se ha ingresado correctamente';
            header("Location: ../HTML/ingProductos1.html"); // Redirigir con mensaje de éxito
            exit();
        }
    } else {
        // Actualizar producto existente
        $sql_update = "UPDATE productos SET nombre_producto = ?, rendimiento_producto_hora = ? WHERE id_producto = ?";
        $params_update = array($productName, $productRendimientoHora, $productId);
        $stmt_update = sqlsrv_query($conn, $sql_update, $params_update);

        if ($stmt_update === false) {
            echo 'Hubo un error al actualizar el Producto';
            exit();
        } else {
            echo 'Se ha actualizado correctamente';
            header("Location: ../HTML/ingProductos1.html"); // Redirigir con mensaje de éxito
            exit();
        }
    }

    sqlsrv_free_stmt($stmt); // Liberar recursos
}

sqlsrv_close($conn); // Cerrar la conexión a la base de datos
?>
