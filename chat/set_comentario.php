<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../db/open_connection.php';

if (isset($_POST['comentario']) && isset($_POST['id_publicacion'])) {
    $id_usuario = $_SESSION['user_id'];
    $comentario = $mysqli->real_escape_string($_POST['comentario']);
    $id_publicacion = (int)$_POST['id_publicacion'];

    $query = "INSERT INTO comentario (id_usuario, comentario, fecha, id_publicacion) VALUES (?, ?, NOW(), ?)";
    $stmt = $mysqli->prepare($query);

    if ($stmt) {
        $stmt->bind_param('isi', $id_usuario, $comentario, $id_publicacion);

        if ($stmt->execute()) {
            // Consulta para contar los comentarios
            $count_query = "SELECT COUNT(*) AS num_comentarios FROM comentario WHERE id_publicacion = ?";
            $count_stmt = $mysqli->prepare($count_query);

            $count_stmt->bind_param('i', $id_publicacion);
            $count_stmt->execute();
            $count_stmt->bind_result($num_comentarios);
            $count_stmt->fetch();

            // Construir el array de respuesta
            $response = array(
                'status' => 'success',
                'num_comentarios' => $num_comentarios
            );

            if ($_SESSION['tipo'] === 2) {
                require __DIR__ . '/../db/enviar_correo.php';

                $asunto = "Mensaje de ". $_SESSION['nombre'] . " en su publicación de " . $_POST['calendario_social'] . " fecha " . $_POST['fecha_publicacion'];
                $cuerpo = $comentario;

                enviarCorreoSMTP($asunto, $cuerpo);
            }

            echo json_encode($response);

            $count_stmt->close();
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Error al insertar el comentario: ' . $stmt->error));
        }

        $stmt->close();
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Error en la preparación de la consulta: ' . $mysqli->error));
    }

    $mysqli->close();
} else {
    echo json_encode(array('status' => 'error', 'message' => 'No se recibieron todos los parámetros necesarios.'));
}
?>
