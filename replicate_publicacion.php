<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db/open_connection.php';

// Verificamos si se han enviado los parámetros necesarios por POST
if (isset($_POST['anio']) && isset($_POST['mes']) && isset($_POST['dia']) && isset($_POST['red_social']) && isset($_POST['calendario']) && isset($_POST['new_red_social'])) {
    // Obtenemos los parámetros del POST
    $anio = $_POST['anio'];
    $mes = $_POST['mes'];
    $dia = $_POST['dia'];
    $red_social = $_POST['red_social'];
    $calendario = $_POST['calendario'];
    $new_red_social = $_POST['new_red_social'];

    // Consulta para seleccionar los registros coincidentes
    $select_query = "SELECT * FROM calendario WHERE anio = ? AND mes = ? AND dia = ? AND red_social = ? AND calendario = ?";

    $ids_insertados = array(); // Array para almacenar los IDs insertados

    if ($stmt = $mysqli->prepare($select_query)) {
        $stmt->bind_param('iiiss', $anio, $mes, $dia, $red_social, $calendario);
        $stmt->execute();
        $result = $stmt->get_result();

        // Insertamos los registros encontrados con el nuevo valor de red_social
        $insert_query = "INSERT INTO calendario (calendario, anio, mes, dia, red_social, titulo, contenido, archivos, enlace, usuario, fecha) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        if ($insert_stmt = $mysqli->prepare($insert_query)) {
            while ($row = $result->fetch_assoc()) {
                $calendario_value = $row['calendario'];
                $anio_value = $row['anio'];
                $mes_value = $row['mes'];
                $dia_value = $row['dia'];
                $titulo_value = $row['titulo'];
                $contenido_value = $row['contenido'];
                $archivos_value = $row['archivos'];
                $enlace_value = $row['enlace'];
                $usuario_value = $row['usuario'];

                $insert_stmt->bind_param('iiisssssss', $calendario_value, $anio_value, $mes_value, $dia_value, $new_red_social, $titulo_value, $contenido_value, $archivos_value, $enlace_value, $usuario_value);
                $insert_stmt->execute();

                // Guardamos el ID insertado
                $ids_insertados[] = $mysqli->insert_id;
            }
            $insert_stmt->close();
        } else {
            echo json_encode(['error' => 'Error al preparar la consulta de inserción: ' . $mysqli->error]);
            exit;
        }

        $stmt->close();
    } else {
        echo json_encode(['error' => 'Error al preparar la consulta de selección: ' . $mysqli->error]);
        exit;
    }

    // Encontramos el menor ID insertado
    if (count($ids_insertados) > 0) {
        $min_id = min($ids_insertados);
        echo json_encode(['min_id' => $min_id]);
    } else {
        echo json_encode(['error' => 'No se insertaron registros.']);
    }
} else {
    echo json_encode(['error' => 'Faltan parámetros necesarios.']);
}

// Cerramos la conexión a la base de datos
$mysqli->close();
?>