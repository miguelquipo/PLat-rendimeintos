<?php
  // Incluir archivo de conexión a la base de datos
  include('db.php'); // Asegúrate de que esta ruta sea correcta

  if (isset($_GET['id'])) {
    $id_producto = $_GET['id'];

    // Verificar que la conexión a la base de datos es válida
    if ($conn) {
      $query = "DELETE FROM productos WHERE id_producto = ?";
      $params = array($id_producto);
      $stmt = sqlsrv_prepare($conn, $query, $params);
      
      if ($stmt === false) {
          die(print_r(sqlsrv_errors(), true));
      }
      
      if (sqlsrv_execute($stmt)) {
        echo 'success';
      } else {
        echo 'Error executing query: ' . print_r(sqlsrv_errors(), true);
      }

      sqlsrv_free_stmt($stmt);
      sqlsrv_close($conn);
    } else {
      echo 'Error: No se pudo conectar a la base de datos.';
    }
  } else {
    echo 'Error: ID no establecido.';
  }
?>
