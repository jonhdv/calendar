<?php

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'calendario';

// Conexión a la base de datos
$mysqli = new mysqli($host, $user, $pass, $db);
$mysqli->set_charset("utf8mb4");

// Verificar la conexión
if ($mysqli->connect_error) {
    die('Error de conexión: ' . $mysqli->connect_error);
}