<?php
// Realiza la conexión a la base de datos
include 'db.php';

// Verifica si se ha enviado una solicitud POST y si los campos del formulario están definidos
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search-cedula']) && isset($_POST['search-producto']) && isset($_POST['insert-cantidad'])) {
    // Obtén los valores de los campos del formulario
    $cedula = $_POST['search-cedula'];
    $codigoProducto = $_POST['search-producto'];
    $cantidadVeces = $_POST['insert-cantidad'];

    // Función para buscar el ID del trabajador por la cédula
    $sql = "SELECT id_trabajador FROM trabajadores WHERE cedula = ?";
    $params = array($cedula);
    $stmtTrabajador = sqlsrv_query($conn, $sql, $params);
    $resultTrabajador = sqlsrv_fetch_array($stmtTrabajador);

    if ($resultTrabajador) {
        $idTrabajador = $resultTrabajador['id_trabajador'];
    } else {
        // Manejar el caso en que el trabajador no exista en la base de datos
        header("Location: ../HTML/rendimientos.html?success=false&error=no_trabajador");
        exit();
    }

    // Obtener el último rango de horas registrado (inicio y fin)
    $sql = "SELECT MAX(CASE WHEN id_tipo_ingreso = 2 THEN hora_registro END) AS hora_inicio,
                    MAX(CASE WHEN id_tipo_ingreso = 3 THEN hora_registro END) AS hora_fin
            FROM rendimiento
            WHERE CAST(fecha_registro AS DATE) = CAST(GETDATE() AS DATE)";
    $stmtUltimoRango = sqlsrv_query($conn, $sql);
    $rowUltimoRango = sqlsrv_fetch_array($stmtUltimoRango);

    $ultimaHoraRegistroInicio = null;
    $ultimaHoraRegistroFin = null;

    if ($rowUltimoRango) {
        $ultimaHoraRegistroInicio = isset($rowUltimoRango['hora_inicio']) ? new DateTime($rowUltimoRango['hora_inicio']->format('Y-m-d H:i:s')) : null;
        $ultimaHoraRegistroFin = isset($rowUltimoRango['hora_fin']) ? new DateTime($rowUltimoRango['hora_fin']->format('Y-m-d H:i:s')) : null;
    }

    // Si no hay registros de inicio y fin, redirigir con un mensaje de error
    if ($ultimaHoraRegistroInicio === null && $ultimaHoraRegistroFin === null) {
        header("Location: ../HTML/rendimientos.html?success=false&error=no_registro");
        exit();
    }

    // Obtener el rendimiento por hora del producto
    $rendimientoPorHora = obtenerRendimientoPorHora($conn, $codigoProducto);
    if ($rendimientoPorHora == 0) {
        // Si no se encuentra el rendimiento del producto, manejar el error
        header("Location: ../HTML/rendimientos.html?success=false&error=no_producto");
        exit();
    }

    // Calcular el tiempo de espera por cada producto
    $promedioIngresoPorHora = 3600 / $rendimientoPorHora; // 3600 segundos en una hora

    // Verificar si el trabajador tiene registros en el rango actual
    $sql = "SELECT COUNT(*) AS num_registros
            FROM rendimiento
            WHERE id_trabajador = ? AND hora_registro >= ? AND hora_registro <= ?";
    $params = array($idTrabajador, $ultimaHoraRegistroInicio->format('Y-m-d H:i:s'), $ultimaHoraRegistroFin->format('Y-m-d H:i:s'));
    $stmtValidarRango = sqlsrv_query($conn, $sql, $params);
    $resultValidarRango = sqlsrv_fetch_array($stmtValidarRango);

    $numRegistrosEnRango = 0;

    if ($resultValidarRango) {
        $numRegistrosEnRango = $resultValidarRango['num_registros'];
    }

    if ($numRegistrosEnRango > 0) {
        // Si el trabajador tiene registros en el rango, usar la última hora de registro en el rango como inicio
        $sql = "SELECT MAX(hora_registro) AS ultima_hora_registro
                FROM rendimiento
                WHERE id_trabajador = ? AND hora_registro >= ? AND hora_registro <= ?";
        $params = array($idTrabajador, $ultimaHoraRegistroInicio->format('Y-m-d H:i:s'), $ultimaHoraRegistroFin->format('Y-m-d H:i:s'));
        $stmtUltimaHoraRegistro = sqlsrv_query($conn, $sql, $params);
        $resultUltimaHoraRegistro = sqlsrv_fetch_array($stmtUltimaHoraRegistro);

        if ($resultUltimaHoraRegistro) {
            $ultimaHoraRegistro = new DateTime($resultUltimaHoraRegistro['ultima_hora_registro']->format('Y-m-d H:i:s'));
        }
    } else {
        // Si no hay registros en el rango, iniciar desde la última hora de inicio o fin
        $ultimaHoraRegistro = ($ultimaHoraRegistroInicio !== null) ? $ultimaHoraRegistroInicio : $ultimaHoraRegistroFin;
    }

    // Insertar los registros en la tabla rendimiento
    for ($i = 0; $i < $cantidadVeces; $i++) {
        $insertDateTime = clone $ultimaHoraRegistro;
        $insertDateTime->add(new DateInterval('PT' . round($promedioIngresoPorHora * $i) . 'S'));
        $formattedDateTime = $insertDateTime->format('Y-m-d H:i:s');

        $sql = "INSERT INTO rendimiento (cantidad_vendida, fecha_registro, id_producto, id_trabajador, hora_registro, id_tipo_ingreso)
                VALUES (1, ?, ?, ?, ?, 1)";
        $params = array($formattedDateTime, $codigoProducto, $idTrabajador, $formattedDateTime);
        $stmtInsert = sqlsrv_query($conn, $sql, $params);

        // Actualizar la última hora de registro para la siguiente inserción
        $ultimaHoraRegistro = $insertDateTime;
    }

    header("Location: ../HTML/rendimientos.html?success=true");
    exit();
}

// Cerrar conexión
sqlsrv_close($conn);

function obtenerRendimientoPorHora($conexion, $codigoProducto) {
    // Obtener el rendimiento por hora del producto
    $sql = "SELECT rendimiento_producto_hora FROM productos WHERE id_producto = ?";
    $params = array($codigoProducto);
    $stmtRendimiento = sqlsrv_query($conexion, $sql, $params);
    $resultRendimiento = sqlsrv_fetch_array($stmtRendimiento);

    if ($resultRendimiento) {
        return $resultRendimiento['rendimiento_producto_hora'];
    } else {
        return 0;
    }
}
?>
