<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $currentDateTime = new DateTime();
    $currentDateTime->setTimezone(new DateTimeZone('America/Chicago'));
    $currentDate = $currentDateTime->format('Y-m-d');
    $currentTime = $currentDateTime->format('H:i:s');

    // Insertar el estado del botón en la tabla boton_actividades
    $sqlInsertBotonActividades = "INSERT INTO boton_actividades (accion, fecha, hora) VALUES (?, ?, ?)";
    $params = array($accion, $currentDate, $currentTime);
    $stmt = sqlsrv_query($conn, $sqlInsertBotonActividades, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    sqlsrv_free_stmt($stmt);
}

sqlsrv_close($conn);
?>
