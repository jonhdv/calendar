<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db/open_connection.php';

// Obtener parámetros de la URL
$id_publicacion = (int)$_POST['id_publicacion'];

// Construir la consulta SQL para obtener la publicación
$query_publicacion = "SELECT 
    p.id, 
    p.calendario, 
    u1.nombre AS nombre_calendario, 
    p.anio, 
    p.mes, 
    p.dia, 
    p.red_social, 
    p.titulo, 
    p.contenido, 
    p.archivos, 
    p.enlace, 
    p.usuario, 
    u2.nombre AS nombre_usuario, 
    p.fecha,
    COUNT(c.id) AS num_comentarios
FROM 
    publicacion p
JOIN 
    usuario u1 ON p.calendario = u1.id
JOIN 
    usuario u2 ON p.usuario = u2.id
LEFT JOIN 
    comentario c ON p.id = c.id_publicacion
WHERE 
    p.id = ?
GROUP BY 
    p.id, 
    p.calendario, 
    p.anio, 
    p.mes, 
    p.dia, 
    p.red_social, 
    p.titulo, 
    p.contenido, 
    p.archivos, 
    p.enlace, 
    p.usuario, 
    p.fecha 
";

// Preparar la consulta
$stmt_publicacion = $mysqli->prepare($query_publicacion);
$stmt_publicacion->bind_param('i', $id_publicacion);

// Ejecutar la consulta
$stmt_publicacion->execute();

// Obtener los resultados
$result_publicacion = $stmt_publicacion->get_result();
$publicacion = $result_publicacion->fetch_assoc();

// Si no se encuentra la publicación, devolver un error
if (!$publicacion) {
    echo json_encode(['error' => 'Publicación no encontrada']);
    exit();
}

// Construir la consulta SQL para obtener los IDs relacionados
$query_ids_relacionados = "SELECT 
    id 
FROM 
    publicacion 
WHERE 
    calendario = ? 
    AND anio = ? 
    AND mes = ? 
    AND dia = ? 
    AND red_social = ? 
ORDER BY 
    id ASC";

// Preparar la consulta
$stmt_ids = $mysqli->prepare($query_ids_relacionados);
$stmt_ids->bind_param('iiiis',
    $publicacion['calendario'],
    $publicacion['anio'],
    $publicacion['mes'],
    $publicacion['dia'],
    $publicacion['red_social']
);

// Ejecutar la consulta
$stmt_ids->execute();

// Obtener los resultados
$result_ids = $stmt_ids->get_result();

// Crear un array para los IDs
$ids_relacionados = [];
while ($row = $result_ids->fetch_assoc()) {
    $ids_relacionados[] = $row['id'];
}

// Preparar la respuesta en JSON
$response = [
    'publicacion' => $publicacion,
    'ids_relacionados' => $ids_relacionados
];

// Enviar los resultados como JSON
header('Content-Type: application/json');
echo json_encode($response);

include 'db/close_connection.php';
?>
