<?php

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/");
    exit();
}

include '../db/open_connection.php';

// Verificar si los parámetros GET están presentes
if (!isset($_GET['fday'], $_GET['fmonth'], $_GET['fyear'], $_GET['lday'], $_GET['lmonth'], $_GET['lyear'], $_GET['calendario'])) {
    die(json_encode(["error" => "Parámetros insuficientes."]));
}

// Recibir los parámetros GET
$fday = (int)$_GET['fday'];
$fmonth = (int)$_GET['fmonth'];
$fyear = (int)$_GET['fyear'];
$lday = (int)$_GET['lday'];
$lmonth = (int)$_GET['lmonth'];
$lyear = (int)$_GET['lyear'];
$calendario = $_GET['calendario'];

// Crear las fechas de inicio y fin
$startDate = DateTime::createFromFormat('j-n-Y', "$fday-$fmonth-$fyear");
$endDate = DateTime::createFromFormat('j-n-Y', "$lday-$lmonth-$lyear");

// Validar que las fechas son válidas y que el intervalo es de 7 días
if (!$startDate || !$endDate || $startDate > $endDate) {
    die(json_encode(["error" => "Fechas inválidas."]));
}

$interval = $startDate->diff($endDate)->days + 1;

if ($interval != 7) {
    die(json_encode(["error" => "URL no valida"]));
}

// Crear un array con las fechas de la semana
$dates = [];
for ($i = 0; $i < 7; $i++) {
    $currentDate = clone $startDate;
    $currentDate->modify("+$i days");
    $dates[] = [
        'day' => (int)$currentDate->format('j'),
        'month' => (int)$currentDate->format('n'),
        'year' => (int)$currentDate->format('Y'),
        'dayOfWeek' => $currentDate->format('l') // Nombre del día de la semana
    ];
}

// Construir la consulta SQL usando consultas preparadas
$query = "SELECT 
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
WHERE calendario = ? AND (";
$conditions = [];
$types = "s";  // Tipo para el primer parámetro de calendario

foreach ($dates as $date) {
    $conditions[] = "(anio = ? AND mes = ? AND dia = ?)";
    $types .= "iii";  // Tipos para los parámetros de año, mes y día
}

$query .= implode(' OR ', $conditions);
$query .= ")
 GROUP BY 
    p.id, 
    p.calendario, 
    u1.nombre, 
    p.anio, 
    p.mes, 
    p.dia, 
    p.red_social, 
    p.titulo, 
    p.contenido, 
    p.archivos, 
    p.enlace, 
    p.usuario, 
    u2.nombre, 
    p.fecha 
 ORDER BY anio, mes, dia";


// Preparar la consulta
$stmt = $mysqli->prepare($query);
if (!$stmt) {
    die(json_encode(["error" => "Error en la preparación de la consulta SQL."]));
}

// Vincular los parámetros
$params = [$calendario];
foreach ($dates as $date) {
    $params[] = $date['year'];
    $params[] = $date['month'];
    $params[] = $date['day'];
}
$stmt->bind_param($types, ...$params);

// Ejecutar la consulta
$stmt->execute();
$result = $stmt->get_result();

// Preparar los resultados en formato JSON
$publicaciones = [];
while ($row = $result->fetch_assoc()) {
    $publicaciones[] = $row;
}

// Cerrar el resultado de la consulta y la declaración
$result->close();
$stmt->close();

// Cerrar la conexión a la base de datos
include '../db/close_connection.php';

// Agrupar las publicaciones por día de la semana
$diasDeLaSemana = [
    "Monday" => [],
    "Tuesday" => [],
    "Wednesday" => [],
    "Thursday" => [],
    "Friday" => [],
    "Saturday" => [],
    "Sunday" => []
];

// Asignar las publicaciones a los días de la semana correspondientes
foreach ($publicaciones as $publicacion) {
    $fecha = DateTime::createFromFormat('j-n-Y', "{$publicacion['dia']}-{$publicacion['mes']}-{$publicacion['anio']}");
    $diaSemana = $fecha->format('l');
    $diasDeLaSemana[$diaSemana][] = $publicacion;
}

function traducirDia($diaSemana) {
    switch(strtolower($diaSemana)) {
        case 'monday':
            return 'lunes';
        case 'tuesday':
            return 'martes';
        case 'wednesday':
            return 'miércoles';
        case 'thursday':
            return 'jueves';
        case 'friday':
            return 'viernes';
        case 'saturday':
            return 'sábado';
        case 'sunday':
            return 'domingo';
        default:
            return 'Día no válido';
    }
}

function getFileType($fileName) {
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'];
    $videoExtensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm'];
    $audioExtensions = ['mp3', 'wav', 'aac', 'flac', 'ogg', 'm4a'];
    $documentExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
    $compressedExtensions = ['zip', 'rar', '7z', 'tar', 'gz'];

    if (in_array($extension, $imageExtensions)) {
        return 'image_file.svg';
    } else if (in_array($extension, $videoExtensions)) {
        return 'video_file.svg';
    } else if (in_array($extension, $audioExtensions)) {
        return 'audio_file.svg';
    } else if (in_array($extension, $documentExtensions)) {
        return 'document_file.svg';
    } else if (in_array($extension, $compressedExtensions)) {
        return 'other_file.svg';
    } else {
        return 'other_file.svg';
    }
}

?>
