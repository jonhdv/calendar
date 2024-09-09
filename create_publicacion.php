<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db/open_connection.php';

// Establecer encabezados para la respuesta JSON
header('Content-Type: application/json');

// Verificar si se han recibido todos los parámetros necesarios
if (
    isset($_POST['calendario']) && isset($_POST['anio']) && isset($_POST['mes']) &&
    isset($_POST['dia']) && isset($_POST['red_social']) && isset($_POST['titulo']) &&
    isset($_POST['contenido']) && isset($_POST['archivos']) && isset($_POST['enlace'])
) {
    $calendario = $_POST['calendario'];
    $anio = intval($_POST['anio']);
    $mes = intval($_POST['mes']);
    $dia = intval($_POST['dia']);
    $red_social = $_POST['red_social'];
    $titulo = $_POST['titulo'];
    $contenido = $_POST['contenido'];
    $archivos = $_POST['archivos'];
    $enlace = $_POST['enlace'];
    $usuario = $_SESSION['user_id'];

    // Preparar y ejecutar la consulta
    $sql = "INSERT INTO publicacion (calendario, anio, mes, dia, red_social, titulo, contenido, archivos, enlace, usuario, fecha)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("siiissssss", $calendario, $anio, $mes, $dia, $red_social, $titulo, $contenido, $archivos, $enlace, $usuario);

        if ($stmt->execute()) {
            // Obtener el ID autoincrementado
            $last_id = $stmt->insert_id;

            // Respuesta JSON de éxito
            echo json_encode(array("status" => "success", "id" => $last_id));
        } else {
            // Error en la ejecución de la consulta
            echo json_encode(array("status" => "error", "message" => $stmt->error));
        }
    } else {
        // Error al preparar la consulta
        echo json_encode(array("status" => "error", "message" => $mysqli->error));
    }

    // Cerrar la conexión
    include 'db/close_connection.php';
} else {
    // Parámetros faltantes en la solicitud
    echo json_encode(array("status" => "error", "message" => "Faltan parámetros en la solicitud"));
}
?>
