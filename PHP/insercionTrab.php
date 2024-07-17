<?php
include 'db.php';

// Capturar datos del formulario
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$cedula = $_POST['cedula'] ?? '';

// Capturar el nombre del archivo de la imagen
$imagen = $_FILES['imagen']['name'];

// Ubicación donde deseas almacenar las imágenes
$directorioDestino = '../A-IMG/imgUsers/';

// Ruta completa del archivo
$rutaCompleta = $directorioDestino . $imagen;

// Mover el archivo a la ubicación deseada
move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaCompleta);

// Verificar si la cédula ya existe en la base de datos
$sql_check = "EXEC sp_CheckCedula ?";
$params_check = array($cedula);
$stmt_check = sqlsrv_query($conn, $sql_check, $params_check);

if ($stmt_check === false) {
    echo 'Hubo un error al realizar la consulta';
    exit();
}

$row_count = sqlsrv_has_rows($stmt_check);

if ($row_count === true) {
    // Si la cédula ya existe, actualizar los datos del trabajador incluyendo la imagen
    $sql_update = "EXEC sp_UpdateTrabajador ?, ?, ?, ?";
    $params_update = array($nombre, $apellido, $imagen, $cedula);
    $stmt_update = sqlsrv_query($conn, $sql_update, $params_update);

    if ($stmt_update === false) {
        echo 'Hubo un error al actualizar el Trabajador';
        exit();
    } else {
        header("Location: ../HTML/ingPersonal.html"); // Redirige a ingPersonal.html si la actualización es exitosa
        exit();
    }
} else {
    // Si la cédula no existe, realizar la inserción
    $sql_insert = "EXEC sp_InsertTrabajador ?, ?, ?, ?";
    $params_insert = array($nombre, $apellido, $cedula, $imagen);
    $stmt_insert = sqlsrv_query($conn, $sql_insert, $params_insert);

    if ($stmt_insert === false) {
        echo 'Hubo un error al ingresar el Trabajador';
        exit();
    } else {
        header("Location: ../HTML/ingPersonal.html"); // Redirige a ingPersonal.html si la inserción es exitosa
        exit();
    }
}

// Liberar recursos
sqlsrv_free_stmt($stmt_check);

// Cerrar conexión
sqlsrv_close($conn);
?>
