<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db/open_connection.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Primero, obtener la lista de archivos
    $query = "SELECT archivos FROM publicacion WHERE id = ?";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $archivos = json_decode($row['archivos'], true); // Decodificar como array asociativo
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al preparar la consulta para obtener archivos']);
        include 'db/close_connection.php';
        exit();
    }

    // Preparar la consulta de eliminación
    $query = "DELETE FROM publicacion WHERE id = ?";

    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param('s', $id);

        if ($stmt->execute()) {
            // Si la eliminación fue exitosa, borrar los archivos si existen
            if (is_array($archivos) && !empty($archivos)) {
                foreach ($archivos as $archivo) {
                    $file_path = 'media/' . $archivo;
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                }
            }
            // Devolver un JSON con un 'okey' si la operación fue exitosa
            echo json_encode(['status' => 'okey']);
        } else {
            // Devolver un JSON con un error si la ejecución falló
            echo json_encode(['status' => 'error', 'message' => 'Error al ejecutar la consulta']);
        }
    } else {
        // Devolver un JSON con un error si la preparación falló
        echo json_encode(['status' => 'error', 'message' => 'Error al preparar la consulta']);
    }

    include 'db/close_connection.php';

} else {
    // Devolver un JSON con un error si faltan parámetros
    echo json_encode(['status' => 'error', 'message' => 'Faltan parámetros']);
}

?>
