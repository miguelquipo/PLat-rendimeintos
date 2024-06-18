<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['isRunning'])) {
    // Verifica si está iniciado
    $isRunning = json_decode($_POST['isRunning']);

    if ($isRunning) {
        // Obtener la fecha y hora actual
        $currentDateTime = new DateTime();
        $currentDateTime->setTimezone(new DateTimeZone('America/Chicago')); // Cambia a tu zona horaria deseada
        $currentHour = $currentDateTime->format('H:i:s');

        // Verifica si ya existe un registro de id_tipo_ingreso = 3 para el día actual
        $sqlCheckExistingType3 = "SELECT COUNT(*) FROM rendimiento WHERE id_tipo_ingreso = 3 AND CAST(fecha_registro AS DATE) = CAST(GETDATE() AS DATE)";
        $stmtCheckExistingType3 = sqlsrv_query($conn, $sqlCheckExistingType3);
        $countType3 = 0;
        if ($stmtCheckExistingType3 !== false) {
            if (sqlsrv_fetch($stmtCheckExistingType3)) {
                $countType3 = sqlsrv_get_field($stmtCheckExistingType3, 0);
            }
            sqlsrv_free_stmt($stmtCheckExistingType3);
        }

        // Preparar la consulta de inserción con id_tipo_ingreso igual a 2
        $sqlInsertTipoIngreso2 = "INSERT INTO rendimiento (cantidad_vendida, fecha_registro, id_producto, id_trabajador, hora_registro, id_tipo_ingreso) VALUES (1, GETDATE(), NULL, NULL, ?, 2)";
        $stmtInsertTipoIngreso2 = sqlsrv_prepare($conn, $sqlInsertTipoIngreso2, array(&$currentHour));
        sqlsrv_execute($stmtInsertTipoIngreso2);

        // Buscar el registro recién insertado de id_tipo_ingreso = 2
        $sqlSelectLastType2 = "SELECT TOP 1 hora_registro FROM rendimiento WHERE id_tipo_ingreso = 2 AND CAST(fecha_registro AS DATE) = CAST(GETDATE() AS DATE) ORDER BY fecha_registro DESC, hora_registro DESC";
        $stmtSelectLastType2 = sqlsrv_query($conn, $sqlSelectLastType2);
        $lastStartTimeType2 = null;
        if ($stmtSelectLastType2 !== false) {
            if (sqlsrv_fetch($stmtSelectLastType2)) {
                $lastStartTimeType2 = sqlsrv_get_field($stmtSelectLastType2, 0);
            }
            sqlsrv_free_stmt($stmtSelectLastType2);
        }

        // Preparar la consulta de inserción con id_tipo_ingreso igual a 3
        $nextHourType3 = isset($lastStartTimeType2) ? date_add($lastStartTimeType2, date_interval_create_from_date_string('1 hour 1 second')) : $currentHour;
        $nextHourType3 = date_format($nextHourType3, 'H:i:s');
        $sqlInsertTipoIngreso3 = "INSERT INTO rendimiento (cantidad_vendida, fecha_registro, id_producto, id_trabajador, hora_registro, id_tipo_ingreso) VALUES (1, GETDATE(), NULL, NULL, ?, 3)";
        $stmtInsertTipoIngreso3 = sqlsrv_prepare($conn, $sqlInsertTipoIngreso3, array(&$nextHourType3));
        sqlsrv_execute($stmtInsertTipoIngreso3);

        // Insertar un valor en la tabla acciones
        $sqlInsertAccion = "INSERT INTO acciones (startBotton) VALUES (1)";
        $stmtInsertAccion = sqlsrv_query($conn, $sqlInsertAccion);

        // Cerrar la conexión al final del script
        sqlsrv_close($conn);
        exit();
    } else {
        // Si no está iniciado, puedes manejarlo de alguna manera (por ejemplo, mostrar un mensaje de error)
        echo "Error: No está iniciado";
        // Cerrar la conexión al final del script
        sqlsrv_close($conn);
        exit();
    }
}
?>
