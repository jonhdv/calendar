<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db/open_connection.php';

if (isset($_POST['id']) && isset($_POST['titulo']) && isset($_POST['contenido']) && isset($_POST['archivos']) && isset($_POST['enlace'])) {
    $id = $_POST['id'];
    $titulo = $_POST['titulo'];
    $contenido = $_POST['contenido'];
    $archivos = $_POST['archivos'];
    $enlace = $_POST['enlace'];
    $usuario = $_SESSION['user_id'];

    // Preparar la consulta de actualización
    $query = "UPDATE publicacion SET titulo = ?, contenido = ?, archivos = ?, enlace = ?, usuario = ?, fecha=now() WHERE id = ?";

    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param('sssssi', $titulo, $contenido, $archivos, $enlace, $usuario, $id);

        if ($stmt->execute()) {
            // Devolver un JSON con un 'okey' si la operación fue exitosa
            echo json_encode(['status' => 'okey']);
        } else {
            // Devolver un JSON con un error si la ejecución falló
            echo json_encode(['status' => 'error', 'message' => 'Error al ejecutar la consulta']);
        }

        include 'db/close_connection.php';
    } else {
        // Devolver un JSON con un error si la preparación falló
        echo json_encode(['status' => 'error', 'message' => 'Error al preparar la consulta']);
    }

} else {
    // Devolver un JSON con un error si faltan parámetros
    echo json_encode(['status' => 'error', 'message' => 'Faltan parámetros']);
}

