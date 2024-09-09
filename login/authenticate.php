<?php
session_start();
include '../db/open_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prevenir SQL Injection
    $stmt = $mysqli->prepare("SELECT id, nombre, email, password, tipo FROM usuario WHERE email = ? AND tipo = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $nombre, $email, $hashed_password, $tipo);
        $stmt->fetch();

        // Verificar la contraseña
        if (password_verify($password, $hashed_password)) {
            // Guardar datos en la sesión
            $_SESSION['user_id'] = $id;
            $_SESSION['nombre'] = $nombre;
            $_SESSION['email'] = $email;
            $_SESSION['tipo'] = $tipo;

            header("Location: ../index.php");
            exit();
        } else {
            echo "Contraseña incorrecta.";
        }
    } else {
        echo "No existe un usuario con ese email.";
    }

    $stmt->close();
    $mysqli->close();
} else {
    header("Location: login.php");
    exit();
}
?>
