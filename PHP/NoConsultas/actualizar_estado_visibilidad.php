<?php
// Incluir el archivo de conexión a la base de datos
include '../db.php';

// Verifica si se recibieron datos en la solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica si se recibió un cuerpo en formato JSON
    $data = json_decode(file_get_contents("php://input"), true);

    // Verifica si se recibió el ID del trabajador
    if (isset($data['id_trabajador'])) {
        // Obtén el ID del trabajador desde los datos recibidos
        $id_trabajador = $data['id_trabajador'];

        // Consulta SQL para obtener el estado actual de visibilidad del trabajador
        $sql_estado = "SELECT viewTrab FROM trabajadores WHERE id_trabajador = ?";
        $stmt_estado = sqlsrv_query($conn, $sql_estado, array($id_trabajador));

        if ($stmt_estado !== false) {
            // Obtiene el estado actual de visibilidad del trabajador
            if (sqlsrv_fetch($stmt_estado)) {
                $estado_actual = sqlsrv_get_field($stmt_estado, 0);
            }

            // Determina el nuevo estado de visibilidad
            $nuevo_estado = ($estado_actual == 1) ? 0 : 1;

            // Ejecuta la consulta SQL para actualizar el estado de visibilidad del trabajador
            $sql_update = "UPDATE trabajadores SET viewTrab = ? WHERE id_trabajador = ?";
            $stmt_update = sqlsrv_prepare($conn, $sql_update, array(&$nuevo_estado, &$id_trabajador));
            if (sqlsrv_execute($stmt_update) === true) {
                // Si la consulta se ejecutó correctamente, envía una respuesta de éxito al cliente
                echo json_encode(array("message" => "Estado de visibilidad actualizado correctamente."));
            } else {
                // Si hay un error en la consulta SQL, envía una respuesta de error al cliente
                http_response_code(500);
                echo json_encode(array("message" => "Error al actualizar el estado de visibilidad."));
            }
        } else {
            // Si no se encuentra el trabajador con el ID proporcionado, envía una respuesta de error al cliente
            http_response_code(404);
            echo json_encode(array("message" => "Trabajador no encontrado."));
        }
    } else {
        // Si no se recibió el ID del trabajador, envía una respuesta de error al cliente
        http_response_code(400);
        echo json_encode(array("message" => "ID de trabajador no proporcionado."));
    }
} else {
    // Si la solicitud no es POST, envía una respuesta de error al cliente
    http_response_code(405);
    echo json_encode(array("message" => "Método no permitido."));
}
?>
