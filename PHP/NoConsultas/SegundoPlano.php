<?php
include '../db.php';

$intervaloEjecucion = 5; // 5 segundos (ajústalo según tus necesidades)

while (true) {
    $currentDateTime = new DateTime();
    $currentDateTime->setTimezone(new DateTimeZone('America/Chicago'));
    $formattedDate = $currentDateTime->format('Y-m-d');

    $stmtSelectLastType3 = sqlsrv_query($conn, "SELECT TOP 1 hora_registro FROM rendimiento WHERE id_tipo_ingreso = 3 AND CONVERT(DATE, fecha_registro) = ? ORDER BY fecha_registro DESC, hora_registro DESC", array($formattedDate));
    $lastStartTimeType3 = null;
    if ($stmtSelectLastType3 !== false) {
        $row = sqlsrv_fetch_array($stmtSelectLastType3, SQLSRV_FETCH_ASSOC);
        if ($row !== null) {
            $lastStartTimeType3 = new DateTime($row['hora_registro']);
        }
        sqlsrv_free_stmt($stmtSelectLastType3);
    }

    if (!empty($lastStartTimeType3) && $currentDateTime->format('H:i:s') >= $lastStartTimeType3->format('H:i:s')) {
        $stmtSelectLastType2 = sqlsrv_query($conn, "SELECT TOP 1 hora_registro FROM rendimiento WHERE id_tipo_ingreso = 2 AND CONVERT(DATE, fecha_registro) = ? ORDER BY fecha_registro DESC, hora_registro DESC", array($formattedDate));
        $lastStartTimeType2 = null;
        if ($stmtSelectLastType2 !== false) {
            $row = sqlsrv_fetch_array($stmtSelectLastType2, SQLSRV_FETCH_ASSOC);
            if ($row !== null) {
                $lastStartTimeType2 = new DateTime($row['hora_registro']);
            }
            sqlsrv_free_stmt($stmtSelectLastType2);
        }

        if (!empty($lastStartTimeType2)) {
            $nextHourType2 = (new DateTime($lastStartTimeType2->format('Y-m-d H:i:s')))->add(new DateInterval('PT1H2S'))->format('H:i:s');
            $stmtInsertType2 = sqlsrv_query($conn, "INSERT INTO rendimiento (cantidad_vendida, fecha_registro, id_tipo_ingreso, hora_registro) VALUES (1, ?, 2, ?)", array($formattedDate, $nextHourType2));
        }

        $nextHourType3 = (new DateTime($lastStartTimeType3->format('Y-m-d H:i:s')))->add(new DateInterval('PT1H2S'))->format('H:i:s');
        $stmtInsertType3 = sqlsrv_query($conn, "INSERT INTO rendimiento (cantidad_vendida, fecha_registro, id_tipo_ingreso, hora_registro) VALUES (1, ?, 3, ?)", array($formattedDate, $nextHourType3));
    }

    $stmtUltimoRango = sqlsrv_query($conn, "SELECT MAX(CASE WHEN id_tipo_ingreso = 2 THEN hora_registro END) AS hora_inicio,
                                            MAX(CASE WHEN id_tipo_ingreso = 3 THEN hora_registro END) AS hora_fin
                                            FROM rendimiento
                                            WHERE CONVERT(DATE, fecha_registro) = ?", array($formattedDate));
    $resultUltimoRango = sqlsrv_fetch_array($stmtUltimoRango);
    $ultimaHoraRegistroInicio = null;
    $ultimaHoraRegistroFin = null;

    if ($resultUltimoRango) {
        $ultimaHoraRegistroInicio = new DateTime($resultUltimoRango['hora_inicio']);
        $ultimaHoraRegistroFin = new DateTime($resultUltimoRango['hora_fin']);
    }

    if ($ultimaHoraRegistroInicio === null && $ultimaHoraRegistroFin === null) {
        $ultimaHoraRegistroInicio = new DateTime();
        $ultimaHoraRegistroInicio->setTime($ultimaHoraRegistroInicio->format('H'), 0, 0);
        $ultimaHoraRegistroFin = new DateTime();
        $ultimaHoraRegistroFin->setTime($ultimaHoraRegistroFin->format('H') + 1, 0, 0);
    }

    $ultimaHoraRegistro = ($ultimaHoraRegistroInicio !== null) ? $ultimaHoraRegistroInicio : $ultimaHoraRegistroFin;

    $stmtIngresosFueraDeRango = sqlsrv_query($conn, "SELECT id_tipo_ingreso, hora_registro
                                                     FROM rendimiento
                                                     WHERE id_tipo_ingreso = 1
                                                     AND CONVERT(DATE, fecha_registro) = ?
                                                     AND hora_registro > ?", array($formattedDate, $ultimaHoraRegistroFin->format('H:i:s')));
    while ($rowIngresoFueraDeRango = sqlsrv_fetch_array($stmtIngresosFueraDeRango, SQLSRV_FETCH_ASSOC)) {
        $horaRegistro = new DateTime($rowIngresoFueraDeRango['hora_registro']);

        $horaAleatoria = generarHoraAleatoriaEnRango($ultimaHoraRegistroInicio, $ultimaHoraRegistroFin);

        $stmtActualizarIngreso = sqlsrv_query($conn, "UPDATE rendimiento
                                                     SET id_tipo_ingreso = 5,
                                                         hora_registro = ?
                                                     WHERE id_tipo_ingreso = 1
                                                     AND hora_registro = ?", array($horaAleatoria->format('H:i:s'), $rowIngresoFueraDeRango['hora_registro']));
    }

    sleep($intervaloEjecucion);
}

sqlsrv_close($conn);

function generarHoraAleatoriaEnRango($horaInicio, $horaFin) {
    $diferenciaMinutos = ($horaFin->getTimestamp() - $horaInicio->getTimestamp()) / 60;
    $horaAleatoria = clone $horaInicio;
    $horaAleatoria->add(new DateInterval('PT' . rand(0, $diferenciaMinutos) . 'M'));
    return $horaAleatoria;
}
?>
