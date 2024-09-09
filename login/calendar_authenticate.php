<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../db/open_connection.php';

if (isset($_GET['calendario']) && isset($_GET['p'])) {
    $calendario = $_GET['calendario'];
    $p = $_GET['p'];

    // Prevenir SQL Injection
    $stmt = $mysqli->prepare("SELECT id, nombre, email, password, tipo FROM usuario WHERE id = ? AND tipo = 2");
    $stmt->bind_param("s", $calendario);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $nombre, $email, $password, $tipo);
        $stmt->fetch();

        // Verificar la contraseña
        if ($p === $password) {
            // Guardar datos en la sesión
            $_SESSION['user_id'] = $id;
            $_SESSION['nombre'] = $nombre;
            $_SESSION['email'] = $email;
            $_SESSION['tipo'] = $tipo;

            // Obtener todos los parámetros de la URL actual
            $queryString = $_SERVER['QUERY_STRING'];

            // Redirigir con los parámetros originales
            header("Location: ../semana/index.php?" . $queryString);
            exit();
        } else {
            header("HTTP/1.0 404 Not Found");
            echo "<html><body><h1>404 Not Found</h1></body></html>";
            exit();
        }
    } else {
        // Enviar encabezado 404 y mostrar mensaje
        header("HTTP/1.0 404 Not Found");
        echo "<html><body><h1>404 Not Found</h1><p>No existe un usuario con ese email.</p></body></html>";
        exit();
    }

    $stmt->close();
    $mysqli->close();
} else {
    header("Location: login.php");
    exit();
}
?>
