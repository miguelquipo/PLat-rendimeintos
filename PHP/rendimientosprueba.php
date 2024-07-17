<?php
// Realiza la conexión a la base de datos (cambia estos valores por los tuyos)
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
        // Verifica si hora_inicio y hora_fin son null antes de usar format()
        $ultimaHoraRegistroInicio = isset($rowUltimoRango['hora_inicio']) ? new DateTime($rowUltimoRango['hora_inicio']->format('Y-m-d H:i:s')) : null;
        $ultimaHoraRegistroFin = isset($rowUltimoRango['hora_fin']) ? new DateTime($rowUltimoRango['hora_fin']->format('Y-m-d H:i:s')) : null;
    }

    // Si no hay registros de inicio y fin, redirigir con un mensaje de error
    if ($ultimaHoraRegistroInicio === null && $ultimaHoraRegistroFin === null) {
        header("Location: ../HTML/rendimientos.html?success=false&error=no_registro");
        exit();
    }

    // Determinar la última hora de registro según la última hora de inicio o fin obtenida
    $ultimaHoraRegistro = ($ultimaHoraRegistroInicio !== null) ? $ultimaHoraRegistroInicio : $ultimaHoraRegistroFin;

    // Calcular el tiempo de espera por cada producto
    $promedioIngresoPorHora = 3600 / obtenerRendimientoPorHora($conn, $codigoProducto); // 3600 segundos en una hora

    // Obtener el rendimiento por hora del producto
    $sql = "SELECT id_producto, rendimiento_producto_hora FROM productos WHERE id_producto = ?";
    $params = array($codigoProducto);
    $stmtProducto = sqlsrv_query($conn, $sql, $params);
    $resultProducto = sqlsrv_fetch_array($stmtProducto);

    if ($resultProducto) {
        $idProducto = $resultProducto['id_producto'];

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
        }

        // Ajustar la primera inserción para reflejar la lógica de acumulación desde la última hora de registro
        $ultimaHoraRegistroInicio = $ultimaHoraRegistro->add(new DateInterval('PT' . round($promedioIngresoPorHora) . 'S'));

        // Preparar la consulta de inserción
        $sql = "INSERT INTO rendimiento (cantidad_vendida, fecha_registro, id_producto, id_trabajador, hora_registro, id_tipo_ingreso) VALUES (1, ?, ?, ?, ?, 1)";
        $stmtInsert = sqlsrv_query($conn, $sql, array($formattedDateTime, $idProducto, $idTrabajador, $formattedDateTime));

        for ($i = 0; $i < $cantidadVeces; $i++) {
            // Calcular la nueva fecha y hora para la inserción
            $insertDateTime = clone $ultimaHoraRegistroInicio;
            $insertDateTime->add(new DateInterval('PT' . round($promedioIngresoPorHora * $i) . 'S'));
            $formattedDateTime = $insertDateTime->format('Y-m-d H:i:s');

            // Ejecutar la inserción
            $sql = "INSERT INTO rendimiento (cantidad_vendida, fecha_registro, id_producto, id_trabajador, hora_registro, id_tipo_ingreso) VALUES (1, ?, ?, ?, ?, 1)";
            $stmtInsert = sqlsrv_query($conn, $sql, array($formattedDateTime, $idProducto, $idTrabajador, $formattedDateTime));
        }

        header("Location: ../HTML/rendimientos.html?success=true");
        // Redirige a rendimientos.html si la inserción es exitosa
        exit();

    } else {
        // Manejar el caso en que el producto no exista en la base de datos
        header("Location: ../HTML/rendimientos.html?success=false&error=no_producto");
        exit();
    }
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
