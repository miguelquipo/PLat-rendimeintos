<?php
// Realiza la conexión a la base de datos (cambia estos valores por los tuyos)
include '../db.php';

// Define el intervalo de tiempo entre ejecuciones (en segundos)
$intervaloEjecucion = 5; // 5 segundos (ajústalo según tus necesidades)

// Bucle para la ejecución continua en segundo plano
while (true) {
    // Obtener la fecha y hora actual
    $currentDateTime = new DateTime();
    $currentDateTime->setTimezone(new DateTimeZone('America/Chicago')); // Cambia a tu zona horaria deseada

    // Obtener la fecha formateada
    $formattedDate = $currentDateTime->format('Y-m-d');

    // Obtener la hora del último registro con id_tipo_ingreso = 3 para el día actual
    $stmtSelectLastType3 = sqlsrv_query($conn, "SELECT TOP 1 hora_registro FROM rendimiento WHERE id_tipo_ingreso = 3 AND CONVERT(DATE, fecha_registro) = ? ORDER BY fecha_registro DESC, hora_registro DESC", array($formattedDate));
    $lastStartTimeType3 = null;
    if ($stmtSelectLastType3 !== false) {
        $row = sqlsrv_fetch_array($stmtSelectLastType3, SQLSRV_FETCH_ASSOC);
        if ($row !== null) {
            $lastStartTimeType3 = $row['hora_registro'];
        }
        sqlsrv_free_stmt($stmtSelectLastType3);
    }

    // Si existe un registro de id_tipo_ingreso = 3 para el día actual y la hora actual es mayor o igual a la hora del último registro
    if (!empty($lastStartTimeType3) && $currentDateTime->format('H:i:s') >= $lastStartTimeType3->format('H:i:s')) {
        // Obtener la hora del último registro con id_tipo_ingreso = 2 para el día actual
        $stmtSelectLastType2 = sqlsrv_query($conn, "SELECT TOP 1 hora_registro FROM rendimiento WHERE id_tipo_ingreso = 2 AND CONVERT(DATE, fecha_registro) = ? ORDER BY fecha_registro DESC, hora_registro DESC", array($formattedDate));
        $lastStartTimeType2 = null;
        if ($stmtSelectLastType2 !== false) {
            $row = sqlsrv_fetch_array($stmtSelectLastType2, SQLSRV_FETCH_ASSOC);
            if ($row !== null) {
                $lastStartTimeType2 = $row['hora_registro'];
            }
            sqlsrv_free_stmt($stmtSelectLastType2);
        }

        // Insertar nuevo registro de id_tipo_ingreso = 2 con una hora de diferencia al último registro
        if (!empty($lastStartTimeType2)) {
            $nextHourType2 = (new DateTime($lastStartTimeType2->format('Y-m-d H:i:s')))->add(new DateInterval('PT1H2S'))->format('H:i:s');
            $stmtInsertType2 = sqlsrv_query($conn, "INSERT INTO rendimiento (cantidad_vendida, fecha_registro, id_tipo_ingreso, hora_registro) VALUES (1, ?, 2, ?)", array($formattedDate, $nextHourType2));
        }

        // Insertar nuevo registro de id_tipo_ingreso = 3 con una hora de diferencia al último registro
        $nextHourType3 = (new DateTime($lastStartTimeType3->format('Y-m-d H:i:s')))->add(new DateInterval('PT1H2S'))->format('H:i:s');
        $stmtInsertType3 = sqlsrv_query($conn, "INSERT INTO rendimiento (cantidad_vendida, fecha_registro, id_tipo_ingreso, hora_registro) VALUES (1, ?, 3, ?)", array($formattedDate, $nextHourType3));
    }

    // Obtener el último rango de horas registrado (inicio y fin)
    $stmtUltimoRango = sqlsrv_query($conn, "SELECT MAX(CASE WHEN id_tipo_ingreso = 2 THEN hora_registro END) AS hora_inicio,
                                              MAX(CASE WHEN id_tipo_ingreso = 3 THEN hora_registro END) AS hora_fin
                                        FROM rendimiento
                                        WHERE CAST(fecha_registro AS DATE) = CAST(GETDATE() AS DATE)");
    $resultUltimoRango = sqlsrv_fetch_array($stmtUltimoRango);
    $ultimaHoraRegistroInicio = null;
    $ultimaHoraRegistroFin = null;

    if ($resultUltimoRango) {
        $ultimaHoraRegistroInicio = new DateTime($resultUltimoRango['hora_inicio']->format('Y-m-d H:i:s'));
        $ultimaHoraRegistroFin = new DateTime($resultUltimoRango['hora_fin']->format('Y-m-d H:i:s'));
    }

    // Si no hay registros de inicio y fin, se asume que aún no se ha iniciado el rango para el día actual
    if ($ultimaHoraRegistroInicio === null && $ultimaHoraRegistroFin === null) {
        $ultimaHoraRegistroInicio = new DateTime();
        $ultimaHoraRegistroInicio->setTime($ultimaHoraRegistroInicio->format('H'), 0, 0);
        $ultimaHoraRegistroFin = new DateTime();
        $ultimaHoraRegistroFin->setTime($ultimaHoraRegistroFin->format('H') + 1, 0, 0);
    }

    // Determinar la última hora de registro según la última hora de inicio o fin obtenida
    $ultimaHoraRegistro = ($ultimaHoraRegistroInicio !== null) ? $ultimaHoraRegistroInicio : $ultimaHoraRegistroFin;

    // Buscar ingresos con hora mayor a la hora fin actual
    $stmtIngresosFueraDeRango = sqlsrv_query($conn, "SELECT id_tipo_ingreso, hora_registro
                                                        FROM rendimiento
                                                        WHERE id_tipo_ingreso = 1
                                                        AND hora_registro > ?", array($ultimaHoraRegistroFin->format('Y-m-d H:i:s')));
    while ($rowIngresoFueraDeRango = sqlsrv_fetch_array($stmtIngresosFueraDeRango, SQLSRV_FETCH_ASSOC)) {
        $idTipoIngreso = $rowIngresoFueraDeRango['id_tipo_ingreso'];
        $horaRegistro = new DateTime($rowIngresoFueraDeRango['hora_registro']->format('Y-m-d H:i:s'));

        // Actualizar ingresos fuera de rango
        $horaAleatoria = generarHoraAleatoriaEnRango($ultimaHoraRegistroInicio, $ultimaHoraRegistroFin);

        $stmtActualizarIngreso = sqlsrv_query($conn, "UPDATE rendimiento
                                                        SET id_tipo_ingreso = 5,
                                                            hora_registro = ?
                                                        WHERE id_tipo_ingreso = 1
                                                        AND hora_registro > ?", array($horaAleatoria, $ultimaHoraRegistroFin->format('Y-m-d H:i:s')));
    }

    // Dormir durante el intervalo definido
    sleep($intervaloEjecucion);
}

// Este código debería ejecutarse solo si se detiene el bucle (por ejemplo, si se cierra el script)
sqlsrv_close($conn);

function generarHoraAleatoriaEnRango($horaInicio, $horaFin) {
    // Obtener la diferencia en minutos entre la horaInicio y la horaFin
    $diferenciaMinutos = $horaInicio->diff($horaFin)->format('%i');

    // Generar una hora aleatoria dentro del rango
    $horaAleatoria = clone $horaInicio; // Iniciar con la horaInicio
    $horaAleatoria->add(new DateInterval('PT' . rand(0, $diferenciaMinutos) . 'M'));

    return $horaAleatoria->format('Y-m-d H:i:s');
}
?>