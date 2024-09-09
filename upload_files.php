<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = 'media/';
    $uploadedFiles = [];
    $errors = [];

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    foreach ($_FILES['files']['name'] as $key => $name) {
        $tmpName = $_FILES['files']['tmp_name'][$key];
        $fileName = basename($name);

        // Concatenar $_POST["id"] al principio del nombre del archivo si existe y tiene algún contenido
        if (isset($_POST["id"]) && !empty($_POST["id"])) {
            $fileName = $_POST["id"] . "_" . $fileName;
        }

        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($tmpName, $targetFilePath)) {
            $uploadedFiles[] = $fileName;
        } else {
            $errors[] = "Error al subir el archivo: $fileName";
        }
    }

    if (!empty($uploadedFiles)) {
        echo 'Archivos subidos con éxito: ' . implode(', ', $uploadedFiles);
    }

    if (!empty($errors)) {
        echo '<br>Errores: ' . implode(', ', $errors);
    }
} else {
    echo 'Método de solicitud no permitido';
}
?>
