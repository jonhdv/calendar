<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Incluye el archivo para abrir la conexión a la base de datos
include '../db/open_connection.php';

// Verifica si se ha recibido el id_publicacion por el método POST
if (isset($_POST['id_publicacion'])) {
    $id_publicacion = $_POST['id_publicacion'];

    // Prepara la consulta SQL para obtener los comentarios y las fechas
    $stmt = $mysqli->prepare("
        SELECT c.*, u.nombre as nombre_usuario 
        FROM comentario c join usuario u on c.id_usuario = u.id 
        WHERE id_publicacion = ? 
        ORDER BY fecha;
    ");
    $stmt->bind_param("i", $id_publicacion);
    $stmt->execute();
    $result = $stmt->get_result();

    $comentarios = array();
    while ($row = $result->fetch_assoc()) {
        $comentarios[] = $row;
    }

    $stmt->close();

    echo json_encode($comentarios);
} else {
    echo json_encode(array("error" => "No se recibió el id_publicacion"));
}

// Cierra la conexión a la base de datos
$mysqli->close();

