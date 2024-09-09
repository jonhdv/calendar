<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    require __DIR__ . '/../login/calendar_authenticate.php';
}

require 'get_week_publicaciones.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario HEY</title>
    <link rel="stylesheet" href="../css/week.css">
    <link rel="stylesheet" href="../css/chat.css">
</head>

<body>
<div class="container">
    <div class="container-header">
        <div class="container-header-title">
            <h1>
                <?php

                include '../db/open_connection.php';
                $query = "SELECT nombre FROM usuario WHERE tipo = 2 and id = ?";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param('i', $calendario);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        echo $row['nombre'];
                    }
                }

                $stmt->close();
                $mysqli->close();
                ?>
            </h1>
            <label><?php echo $fday . '/' . $fmonth . '/' . $fyear ?> - <?php echo $lday . '/' . $lmonth . '/' . $lyear ?></label>
        </div>

        <div class="container-header-login">
            <span><?php echo $_SESSION["nombre"] ?></span>
            <img alt="Logout" src="../icon/user.svg">
        </div>
    </div>


    <?php foreach ($dates as $date) {
        $diaSemana = $date['dayOfWeek'];
        $fecha = $date['day'] . "/" . $date['month'] . "/" . $date['year'];
        ?>
        <h2 class="dia-header"><?php echo traducirDia($diaSemana) . " - " . $fecha ?></h2>

        <div class="publicaciones">
            <?php
            if (!empty($diasDeLaSemana[$diaSemana])) {
                foreach ($diasDeLaSemana[$diaSemana] as $publicacion) { ?>
                    <div class="publicacion" data-id="<?php echo $publicacion['id'] ?>">
                        <div class="publicacion-header">
                            <img alt="<?php echo $publicacion["red_social"]?>" src="../icon/<?php echo $publicacion["red_social"] ?>.svg">
                            <label class="publicacion-header-nombre"><?php echo $publicacion["titulo"] !== "" ? $publicacion["titulo"] : "<span style='color: grey'>Sin título</span>"; ?></label>

                            <div class="publicacion-comentario" title="Abrir chat para esta publicación" data-fecha="<?php echo $publicacion['dia'] . "/" . $publicacion['mes'] . "/" .$publicacion['anio'] ?>" data-social="<?php echo $publicacion['red_social'] ?>" data-id-publicacion="<?php echo $publicacion["id"]?>">
                                <img alt="Comentarios" src="../icon/comentario.svg">

                                <div class="publicacion-comentario-numero">
                                    <?php echo $publicacion["num_comentarios"] > 0 ? $publicacion["num_comentarios"] : "0" ?>
                                </div>
                            </div>
                        </div>

                        <div class="publicacion-body">
                            <div class="publicacion-carrousel">
                                <div class="publicacion-carrousel-elements">
                                    <?php if ($publicacion["archivos"] !== '"[]"') { ?>
                                        <?php foreach (json_decode($publicacion["archivos"]) as $archivo) { ?>
                                            <div class="publicacion-carrousel-element">
                                                <?php if (getFileType($archivo) === "image_file.svg") {
                                                    echo '<img class="publicacion-carrousel-file publicacion-carrousel-image" src="../media/' . trim($archivo, '"') . '">';
                                                } else if (getFileType($archivo) === "video_file.svg") {
                                                    echo '<video class="publicacion-carrousel-file publicacion-carrousel-video" controls>' .
                                                        '<source src="../media/' . $archivo . '" type="video/mp4">' .
                                                        '</video>';
                                                } else {
                                                    echo '<div class="publicacion-carrousel-file publicacion-carrousel-file-default">
                                                        <img src="../icon/document_file_2.svg"> 
                                                        <a target="_blank" title="' . $archivo . '" href="../media/' . $archivo . '">' . $archivo . '</a>
                                                        </div>';
                                                } ?>
                                            </div>
                                        <?php }
                                    } else { ?>
                                        <div class="publicacion-carrousel-element">
                                            <div class="publicacion-carrousel-file publicacion-carrousel-file-empty">
                                                Sin archivos adjuntos
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>

                                <?php if ($publicacion["archivos"] !== '"[]"' && count(json_decode($publicacion["archivos"])) > 1) { ?>
                                    <div class="publicacion-carrousel-counter"><label>1</label>/<label><?php echo count(json_decode($publicacion["archivos"])) ?></label></div>
                                    <img class="publicacion-carrousel-arrow-left" src="../icon/arrow-left.svg">
                                    <img class="publicacion-carrousel-arrow-right" src="../icon/arrow-right.svg">
                                <?php } ?>
                            </div>

                            <div class="publicacion-usuario">
                                <?php echo $publicacion["nombre_usuario"] ?> - <?php echo date('d/m/Y', strtotime(substr($publicacion["fecha"], 0, 10))); ?>
                            </div>

                            <div class="publicacion-contenido">
                                <?php echo $publicacion["contenido"] !== "" ? $publicacion["contenido"] : "<span style='color: grey'>Sin contenido</span>"; ?>
                            </div>

                            <?php if ($publicacion["enlace"] !== "") { ?>
                                <div class="publicacion-enlace">
                                    <label>Enlace: </label>
                                    <a target="_blank" title="<?php echo $publicacion["enlace"] ?>" href="<?php echo $publicacion["enlace"] ?>"><?php echo $publicacion["enlace"] ?></a>
                                    <img title="Copiar enlace" class="publicacion-enlace-copiar" src="../icon/copy.svg" data-enlace="<?php echo $publicacion["enlace"] ?>">
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php }
            } ?>
        </div>
    <?php } ?>
</div>

<div class="chat-close" id="chatContainer">
    <div id="chatHeader">
        <span>Mensajes</span>
        <img title="Cerrar chatbot" class="chat-header-close" src="../icon/close.svg">
    </div>

    <div id="chatbox">
        <div id="messages">
            <div class="message bot waiting">
                <img src="../icon/loading.gif">
            </div>
        </div>
    </div>

    <div id="userInputContainer">
        <textarea id="userInput" rows="1"></textarea>

        <div id="sendButton">
            <img src="../icon/send.svg">
        </div>
    </div>
</div>

<script src="../js/jquery-3.6.0.min.js"></script>
<script src="../js/sweetalert2.js"></script>
<script src="../js/chat.js"></script>

<script>
    let sesionId = <?php echo $_SESSION['user_id']?>;
    let baseURL = "../"

    $(document).ready(function() {
        $('.publicacion-enlace-copiar').on('click', function() {
            let enlace = $(this).attr('data-enlace');
            let $tempElement = $("<textarea>");
            
            $("body").append($tempElement);
            $tempElement.val(enlace).select();
            document.execCommand("copy");
            $tempElement.remove();

            Swal.fire({
                icon: 'success',
                title: enlace + ' copiado al portapapeles',
                showConfirmButton: false,
                timer: 1000
            });
        });

        let isFunctionBlocked = false;

        $('.publicacion-carrousel-arrow-right, .publicacion-carrousel-arrow-left').on('click', function() {
            if (isFunctionBlocked) return; // Si la función está bloqueada, salimos

            isFunctionBlocked = true; // Bloqueamos la función

            let $publicacionCarrouselElements = $(this).siblings('.publicacion-carrousel-elements');
            let transformValue = $publicacionCarrouselElements.css('transform');
            let carrouselElementsNumber = $publicacionCarrouselElements.children().length;

            // Extraer el valor de translateX del string obtenido
            let translateX = 0;
            if (transformValue !== 'none') {
                let matrixValues = transformValue.match(/matrix\(([^)]+)\)/)[1].split(', ');
                translateX = parseInt(matrixValues[4]);
            }

            let currentPosition = Math.abs(translateX / 440);

            if ($(this).hasClass('publicacion-carrousel-arrow-right')) {
                // Mover a la derecha
                if (carrouselElementsNumber === currentPosition + 1) {
                    $publicacionCarrouselElements.css("transform", "translateX(0px)");
                    currentPosition = -1;
                } else {
                    $publicacionCarrouselElements.css("transform", "translateX(" + (translateX - 440) + "px)");
                }

                $(this).siblings(".publicacion-carrousel-counter").find('label:first-of-type').text(currentPosition + 2);
            } else if ($(this).hasClass('publicacion-carrousel-arrow-left')) {
                // Mover a la izquierda
                if (currentPosition === 0) {
                    $publicacionCarrouselElements.css("transform", "translateX(" + -(carrouselElementsNumber - 1) * 440 + "px)");
                    currentPosition = carrouselElementsNumber;
                } else {
                    $publicacionCarrouselElements.css("transform", "translateX(" + (translateX + 440) + "px)");
                }

                $(this).siblings(".publicacion-carrousel-counter").find('label:first-of-type').text(currentPosition);
            }

            // Desbloqueamos la función después de 200ms
            setTimeout(function() {
                isFunctionBlocked = false;
            }, 300);
        });

        //Poner URLs a todos los enlaces de los copys
        $('.publicacion-contenido').each(function() {
            var contenido = $(this).html().trim(); // Elimina espacios en blanco al inicio y al final
            var regexUrl = /((http|https):\/\/\S+)/g;

            contenido = contenido.replace(regexUrl, function(url) {
                return '<a href="' + url + '" target="_blank">' + url + '</a>';
            });

            contenido = contenido.replace(/\n/g, '<br>');

            $(this).html(contenido);
        });

    });
</script>

</body>
</html>