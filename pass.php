<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    echo "Contraseña Hasheada: " . $hashed_password;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Hash de Contraseña</title>
</head>
<body>
<form action="pass.php" method="POST">
    <label for="password">Contraseña:</label>
    <input type="password" name="password" id="password" required>
    <br>
    <button type="submit">Crear Hash</button>
</form>
</body>
</html>
