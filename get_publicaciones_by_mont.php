<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db/open_connection.php';

// Obtener parámetros de la URL
$month = $_POST['month'];
$year = $_POST['year'];
$calendar = $_POST['calendar'];

// Obtener el mes anterior y el mes siguiente
$monthAnterior = calcularMesAnio($month, $year, 'restar', 'month');
$yearAnterior = calcularMesAnio($month, $year, 'restar', 'year');
$monthSiguiente = calcularMesAnio($month, $year, 'sumar', 'month');
$yearSiguiente = calcularMesAnio($month, $year, 'sumar', 'year');

// Construir la consulta SQL
$query = "SELECT 
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
    p.fecha,
    u.password,
    COUNT(c.id) AS num_comentarios
FROM 
    publicacion p
LEFT JOIN 
    comentario c ON p.id = c.id_publicacion
JOIN
    usuario u ON p.usuario = u.id
WHERE 
    ((p.mes = ? AND p.anio = ?)
    OR (p.mes = ? AND p.anio = ?)
    OR (p.mes = ? AND p.anio = ?))
    AND p.calendario = ?
    AND p.id IN (
        SELECT MIN(p2.id)
        FROM publicacion p2
        WHERE ((p2.mes = ? AND p2.anio = ?)
            OR (p2.mes = ? AND p2.anio = ?)
            OR (p2.mes = ? AND p2.anio = ?))
            AND p2.calendario = p.calendario
        GROUP BY p2.calendario, p2.anio, p2.mes, p2.dia, p2.red_social
    )
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

$stmt = $mysqli->prepare($query);
$stmt->bind_param('sssssssssssss', $month, $year, $monthAnterior, $yearAnterior, $monthSiguiente, $yearSiguiente, $calendar, $month, $year, $monthAnterior, $yearAnterior, $monthSiguiente, $yearSiguiente);
if (!$stmt->execute()) {
    die('Error en la ejecución de la consulta: ' . $stmt->error);
}

// Obtener los resultados
$result = $stmt->get_result();

// Crear un array para almacenar los resultados
$data = array();
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

function calcularMesAnio($month, $year, $operacion, $tipoResultado) {
    // Validar la operación (sumar o restar)
    if ($operacion !== 'sumar' && $operacion !== 'restar') {
        die('Error: La operación debe ser "sumar" o "restar".');
    }

    // Validar el tipo de resultado (mes o año)
    if ($tipoResultado !== 'month' && $tipoResultado !== 'year') {
        die('Error: El tipo de resultado debe ser "mes" o "anio".');
    }

    // Realizar la operación
    $nuevaFecha = date('Y-m', strtotime(($operacion === 'sumar' ? '+' : '-') . '1 month', strtotime("$year-$month-01")));

    // Obtener el mes y el año del resultado
    $nuevoMes = date('m', strtotime($nuevaFecha));
    $nuevoAnio = date('Y', strtotime($nuevaFecha));

    // Devolver el resultado según el tipo especificado
    return ($tipoResultado === 'month') ? $nuevoMes : $nuevoAnio;
}

// Enviar los resultados como JSON
header('Content-Type: application/json');
echo json_encode($data);

include 'db/close_connection.php';
