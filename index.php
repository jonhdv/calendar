<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login/");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario HEY</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/chat.css">
</head>
<body>
<div class="container">
    <div class="calendar-header">
        <div class="calendar-header-logo">
            <img alt="Logo HEY" src="icon/logo_agencia_hey.svg">
        </div>

        <div class="calendar-header-date">
            <select class="calendar-client-select">
                <?php

                include 'db/open_connection.php';
                $query = "
                            SELECT * 
                            FROM usuario 
                            WHERE tipo = 2 
                            ORDER BY CASE WHEN favorito = ? THEN 0 ELSE 1 END, id DESC
                        ";

                if ($stmt = $mysqli->prepare($query)) {
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<option data-password="' . $row['password'] . '" value="' . $row['id'] . '">' . $row['nombre'] . '</option>';
                        }
                    }

                    $stmt->close();
                }
                ?>
            </select>

            <select class="calendar-month-select">
                <option value="1">Enero</option>
                <option value="2">Febrero</option>
                <option value="3">Marzo</option>
                <option value="4">Abril</option>
                <option value="5">Mayo</option>
                <option value="6">Junio</option>
                <option value="7">Julio</option>
                <option value="8">Agosto</option>
                <option value="9">Septiembre</option>
                <option value="10">Octubre</option>
                <option value="11">Noviembre</option>
                <option value="12">Diciembre</option>
            </select>

            <select class="calendar-year-select">
                <!-- Generar opciones de año dinámicamente -->
            </select>
        </div>

        <div class="calendar-header-login">
            <span><?php echo $_SESSION["nombre"] ?></span>
            <a title="Logout" href="login/logout.php"><img alt="Logout" src="icon/logout.svg"></a>
        </div>
    </div>

    <div class="calendar-content">
        <div class="calendar-form">
            <div class="calendar-numbers">
                <div class="calendar-number-add" title="Añadir nueva publicación para este día">+</div>
            </div>

            <div class="publicacion-form-comentarios">
                <img alt="Comentarios" src="icon/comentario-white.svg">
                <label>0</label>
            </div>

            <div class="publicacion-header"></div>
            <div class="publicacion-info"></div>

            <input type="hidden" class="publicacion-id" value="">

            <input type="hidden" class="publicacion-year" value="">
            <input type="hidden" class="publicacion-month" value="">
            <input type="hidden" class="publicacion-day" value="">
            <input type="hidden" class="publicacion-social" value="">

            <input type="text" class="publicacion-titulo" placeholder="Título">
            <textarea class="publicacion-contenido" placeholder="Contenido..."></textarea>
            <div class="publicacion-enlace-container">
                <input type="text" class="publicacion-enlace" placeholder="Enlace">
                <img title="Abrir en una pestaña nueva" class="publicacion-enlace-link" src="icon/external_link.svg">
            </div>

            <div class="publicacion-files-container">
                <label>Archivos adjuntos:</label>
                <div class="publicacion-files-uploaded"></div>
                <div class="publicacion-files-progress">
                    <div class="publicacion-files-progress-bar"></div>
                    <div class="percent">
                        Subiendo archivos <label>100</label>%
                    </div>
                </div>
                <input type="file" class="publicacion-files" name="files[]" id="files" multiple>
            </div>

            <div class="publicacion-replicate-container">
                <label>Replicar contenido:</label>

                <div class="publicacion-replicate">
                    <div class="publicacion-replicate-social" data-social="linkedin">
                        <img title="Replicar contenido en LinkedIn" src="/calendar/icon/linkedin.svg">
                    </div>

                    <div class="publicacion-replicate-social" data-social="facebook">
                        <img title="Replicar contenido en Facebook" src="/calendar/icon/facebook.svg">
                    </div>

                    <div class="publicacion-replicate-social" data-social="instagram">
                        <img title="Replicar contenido en Instagram" src="/calendar/icon/instagram.svg">
                    </div>

                    <div class="publicacion-replicate-social" data-social="equis">
                        <img title="Replicar contenido en X" src="/calendar/icon/equis.svg">
                    </div>

                    <div class="publicacion-replicate-social" data-social="tiktok">
                        <img title="Replicar contenido en Tik Tok" src="/calendar/icon/tiktok.svg">
                    </div>
                </div>
            </div>

            <div class="publicacion-buttons">
                <div class="upload-publicacion">ACTUALIZAR</div>
                <div class="delete-publicacion">BORRAR</div>
                <div class="create-publicacion">NUEVA PUBLICACIÓN</div>
            </div>
        </div>

        <table class="calendar">
            <thead>
            <tr>
                <th>L</th>
                <th>M</th>
                <th>X</th>
                <th>J</th>
                <th>V</th>
                <th>S</th>
                <th>D</th>
            </tr>
            </thead>
            <tbody>
            <!-- Días del calendario -->
            </tbody>
        </table>
    </div>
</div>

<div class="chat-close" id="chatContainer">
    <div id="chatHeader">
        <span>Mensajes</span>
        <img title="Cerrar chatbot" class="chat-header-close" src="icon/close.svg">
    </div>

    <div id="chatbox">
        <div id="messages">
            <div class="message bot waiting">
                <img src="icon/loading.gif">
            </div>
        </div>
    </div>

    <div id="userInputContainer">
        <textarea id="userInput" rows="1"></textarea>

        <div id="sendButton">
            <img src="icon/send.svg">
        </div>
    </div>
</div>

<script src="js/jquery-3.6.0.min.js"></script>
<script src="js/sweetalert2.js"></script>
<script src="js/create_calendar.js"></script>
<script src="js/chat.js"></script>

<script>
    let month, year, calendar, fileList, currentCreatePublication;
    let currentUrl = window.location.origin + window.location.pathname;
    let sesionId = <?php echo $_SESSION['user_id']?>;
    let calendarPass;
    let baseURL = ""

    $(document).ready(function () {
        month = $('.calendar-month-select').val();
        year = $('.calendar-year-select').val();
        calendar = $('.calendar-client-select').val();

        getPublicaciones(month, year, calendar);
    });

    $('.calendar-header select').change(function () {
        month = $('.calendar-month-select').val();
        year = $('.calendar-year-select').val();
        calendar = $('.calendar-client-select').val();

        getPublicaciones(month, year, calendar);
    });

    $(document).on('click', '.publicacion.active, .calendar-number', function () {
        getPublicacionById($(this).attr('data-id'))
            .then(function(jsonPublicacion) {
                fillPublicacionForm(jsonPublicacion);
            })
            .catch(function(error) {
                console.error('Error en la solicitud AJAX', error);
            });
    });

    $('.publicacion-files').on('change', function() {
        $('.publicacion-file.no-uploaded').remove();
        fileList = $('#fileList');
        fileList.empty();
        let $publicacionFilesUploaded = $(".publicacion-files-uploaded");

        let files = $(this)[0].files;
        for (var i = 0; i < files.length; i++) {
            fileList.append('<p>' + files[i].name + '</p>');
            $publicacionFilesUploaded.append('<div class="publicacion-file no-uploaded"><div class="publicacion-file-name">' + files[i].name + '</div></div>')

        }
    });

    $(".upload-publicacion").click(function () {
        let input = document.querySelector('.publicacion-files');
        let files = input.files;

        if (files.length === 0) {
            // Si no hay archivos, salta directamente a la función updatePublicacion
            updatePublicacion();
            return;
        }

        let formData = new FormData();
        for (var i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
        }

        formData.append('id', $('.publicacion-id').val());
        $('.publicacion-files-progress').show(200);

        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'upload_files.php', true);

        xhr.upload.onprogress = function (e) {
            if (e.lengthComputable) {
                let percentComplete = (e.loaded / e.total) * 100;
                $('.publicacion-files-progress .percent label').text(percentComplete.toFixed(0));
                $('.publicacion-files-progress-bar').css('width', percentComplete.toFixed(0) + "%");
            }
        };

        xhr.onload = function () {
            if (xhr.status === 200) {
                $('.publicacion-files-progress').hide(200);
                updatePublicacion();
            } else {
                console.log('Error: ' + xhr.status);
            }
        };

        xhr.onerror = function () {
            console.log("Error al subir los archivos.");
        };

        xhr.send(formData);
    });

    function getFileNames() {
        let arrayFileNames = [];

        //Sumamos los nombres de los archivos ya subidos
        $('.publicacion-file-name a').each(function () {
            arrayFileNames.push($(this).attr("data-file-name"));
        });

        //Sumamos los nombres de los archivos por subir
        let $publicacionId = $('.publicacion-id').val();

        for (var i = 0; i < $('.publicacion-files')[0].files.length; i++) {
            arrayFileNames.push($publicacionId + "_" + $('.publicacion-files')[0].files[i].name);
        }

        return (arrayFileNames.length === 0) ?  JSON.stringify("[]") : JSON.stringify(arrayFileNames);
    }

    function updatePublicacion() {
        let url = 'update_publicacion.php';
        let id = $('.publicacion-id').val();

        $.ajax({
            type: 'POST',
            url: url,
            data: {
                id: id,
                titulo: $('.publicacion-titulo').val(),
                contenido: $('.publicacion-contenido').val(),
                archivos: getFileNames(),
                enlace: $('.publicacion-enlace').val()
            },
            dataType: 'json',
            success: function (jsonData) {
                Swal.fire({
                    icon: 'success',
                    title: 'Publicación actualizada con éxito',
                    showConfirmButton: false,
                    timer: 1000
                });

                getPublicacionById(id)
                    .then(function(jsonPublicacion) {
                        fillPublicacionForm(jsonPublicacion);
                    })
                    .catch(function(error) {
                        console.error('Error en la solicitud AJAX', error);
                    });
            },
            error: function (error) {
                console.error('Error en la solicitud AJAX', error);
            }
        });
    }

    function cleanPublicacionForm(firstPublicationOfTheDay) {
        $('.calendar-form input, .calendar-form textarea').val('');
        $('.publicacion-info').text('');
        $('.publicacion-files-uploaded').html('');
        $('.publicacion-buttons').css('display', 'none');
        $('.publicacion.selected, .dia.selected').removeClass('selected');
        $('.publicacion-replicate-container, .publicacion-form-comentarios').hide();

        if (!firstPublicationOfTheDay) {
            $(".calendar-numbers").hide();
            $('.calendar-number').remove();
        }

        $('.chat-header-close').click();
    }

    $(document).on('click', '.publicacion:not(.active)', function () {
        currentCreatePublication = $(this);

        cleanPublicacionForm(false);
        fillNewPublicacionForm(currentCreatePublication);
    });

    $(document).on('click', '.calendar-number-add', function () {
        $('.calendar-number').removeClass('active')
        $(this).addClass('active');
        currentCreatePublication = $('.publicacion.active.selected');

        cleanPublicacionForm(true);
        fillNewPublicacionForm(currentCreatePublication);
    });

    function fillNewPublicacionForm($emptyPublicacion) {
        markDay($emptyPublicacion);

        let $dia = $emptyPublicacion.closest('.dia ');
        let dia = $dia.attr('data-dia');
        let social = $emptyPublicacion.attr('data-social');
        let month = getMesBuscado($dia);
        let year = getAnioBuscado($('.calendar-month-select').val(), $dia);

        $(".publicacion-header").text(social + ' | ' + dia + '/' + month + '/' + year);
        $('.publicacion-year').val(year);
        $('.publicacion-month').val(month);
        $('.publicacion-day').val(dia);
        $('.publicacion-social').val(social);

        $('.delete-publicacion, .upload-publicacion').hide();
        $('.create-publicacion').show();
        $('.publicacion-buttons').css('display', 'flex');
    }

    $('.create-publicacion').click(function () {
        let url = 'create_publicacion.php';
        let data = {
            calendario: $('.calendar-client-select').val(),
            anio: $('.publicacion-year').val(),
            mes: $('.publicacion-month').val(),
            dia: $('.publicacion-day').val(),
            red_social: $('.publicacion-social').val(),
            titulo: $('.publicacion-titulo').val(),
            contenido: $('.publicacion-contenido').val(),
            archivos: '"[]"',
            enlace: $('.publicacion-enlace').val()
        }

        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            dataType: 'json',
            success: function (jsonData) {
                if (!currentCreatePublication.attr('data-id')) {
                    currentCreatePublication.attr('data-id', jsonData.id);
                }

                currentCreatePublication.attr('data-publicacion', data.dia + '|' + data.mes + '|' + data.anio + '|' + data.calendario + '|' + data.red_social);
                currentCreatePublication.addClass('active');

                uploadFilesNewPublicacion(jsonData.id);
            },
            error: function (error) {
                console.error('Error en la solicitud AJAX', error);
            }
        });
    });

    function uploadFilesNewPublicacion(publicacionId) {
        let input = document.querySelector('.publicacion-files');
        let files = input.files;

        if (files.length === 0) {
            Swal.fire({
                icon: 'success',
                title: 'Publicación creada con éxito',
                showConfirmButton: false,
                timer: 1000
            });

            getPublicacionById(publicacionId)
                .then(function(jsonPublicacion) {
                    fillPublicacionForm(jsonPublicacion);
                })
                .catch(function(error) {
                    console.error('Error en la solicitud AJAX', error);
                });

            return;
        }

        let formData = new FormData();
        for (var i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
        }

        formData.append('id', publicacionId);
        $('.publicacion-files-progress').show(200);

        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'upload_files.php', true);

        xhr.upload.onprogress = function (e) {
            if (e.lengthComputable) {
                let percentComplete = (e.loaded / e.total) * 100;
                $('.publicacion-files-progress .percent label').text(percentComplete.toFixed(0));
                $('.publicacion-files-progress-bar').css('width', percentComplete.toFixed(0) + "%");
            }
        };

        xhr.onload = function () {
            if (xhr.status === 200) {
                $('.publicacion-files-progress').hide(200);

                updatePublicacionFiles(publicacionId);
            } else {
                console.log('Error: ' + xhr.status);
            }
        };

        xhr.onerror = function () {
            console.log("Error al subir los archivos.");
        };

        xhr.send(formData);
    }

    function updatePublicacionFiles(publicacionId) {
        $('.publicacion-id').val(publicacionId);

        let url = 'update_publicacion_files.php';
        let data = {
            id: publicacionId,
            archivos: getFileNames()
        }

        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            dataType: 'json',
            success: function (jsonData) {
                Swal.fire({
                    icon: 'success',
                    title: 'Publicación creada con éxito',
                    showConfirmButton: false,
                    timer: 1000
                });

                getPublicacionById(publicacionId)
                    .then(function(jsonPublicacion) {
                        fillPublicacionForm(jsonPublicacion);
                    })
                    .catch(function(error) {
                        console.error('Error en la solicitud AJAX', error);
                    });
            },
            error: function (error) {
                console.error('Error en la solicitud AJAX', error);
            }
        });
    }

    function getAnioBuscado(month, $dia) {
        month = parseInt(month);
        let year = parseInt($('.calendar-year-select').val());

        if (month === 12 && $dia.hasClass('dia-mes-siguiente')) {
            year += 1;
        } else if (month === 1 && $dia.hasClass('dia-mes-anterior')) {
            year -= 1;
        }

        return year;
    }

    function fillPublicacionForm(jsonPublicacion) {
        cleanPublicacionForm();
        markDay($('.publicacion[data-publicacion="' + (jsonPublicacion.publicacion.dia + '|' + jsonPublicacion.publicacion.mes + '|' + jsonPublicacion.publicacion.anio + '|' + jsonPublicacion.publicacion.calendario + '|' + jsonPublicacion.publicacion.red_social) + '"]'));

        $(".publicacion-header").text(jsonPublicacion.publicacion.red_social + ' | ' + jsonPublicacion.publicacion.dia + '/' + jsonPublicacion.publicacion.mes + '/' + jsonPublicacion.publicacion.anio);
        $(".publicacion-info").html("<b>" + jsonPublicacion.publicacion.nombre_usuario + "</b>" + " - " + jsonPublicacion.publicacion.fecha);
        $(".publicacion-id").val(jsonPublicacion.publicacion.id);
        $(".publicacion-year").val(jsonPublicacion.publicacion.anio);
        $(".publicacion-month").val(jsonPublicacion.publicacion.mes);
        $(".publicacion-day").val(jsonPublicacion.publicacion.dia);
        $(".publicacion-social").val(jsonPublicacion.publicacion.red_social);
        $(".publicacion-titulo").val(jsonPublicacion.publicacion.titulo);
        $(".publicacion-contenido").val(jsonPublicacion.publicacion.contenido);
        $(".publicacion-enlace").val(jsonPublicacion.publicacion.enlace);
        $(".publicacion-replicate-container, .calendar-numbers").css("display", "flex");

        $('.publicacion-replicate-social').css('display', 'flex');
        $('.publicacion-replicate-social').each(function () {
            if ($(this).data('social') === jsonPublicacion.publicacion.red_social) {
                $(this).hide();
            }
        });

        $(".publicacion-form-comentarios > label").text(jsonPublicacion.publicacion.num_comentarios);
        $(".publicacion-form-comentarios")
            .css('display', 'flex')
            .attr('data-id-publicacion', jsonPublicacion.publicacion.id)
            .attr('data-social', jsonPublicacion.publicacion.red_social)
            .attr('data-fecha', jsonPublicacion.publicacion.dia + '/' + jsonPublicacion.publicacion.mes + '/' + jsonPublicacion.publicacion.anio)
        ;

        let arrayficheros = (jsonPublicacion.publicacion.archivos === '"[]"') ? [] : JSON.parse(jsonPublicacion.publicacion.archivos);
        let $publicacionFilesUploaded = $(".publicacion-files-uploaded");

        // Recorre cada elemento del array
        $.each(arrayficheros, function(index, value) {
            $publicacionFilesUploaded.append('' +
                '<div class="publicacion-file">' +
                    '<div class="publicacion-file-type">' +
                        '<img src="icon/' + getFileType(value) + '">' +
                    '</div>' +
                    '<div class="publicacion-file-name">' +
                        '<a target="_blank" title="' + value + '" data-file-name="' + value + '" data-file-type="' + getFileType(value) + '" href="media/' + value + '">' + value + '</a>' +
                    '</div>' +
                    '<div class="publicacion-file-delete">' +
                        '<img title="Quitar archivo" src="icon/delete.svg">' +
                    '</div>' +
                '</div>'
            );
        });

        //Construimos páginación para cambiar entre publicaciones de un mismo día
        $(jsonPublicacion.ids_relacionados).each(function(index) {
            $('.calendar-number-add').before('<div class="calendar-number' +
                ((jsonPublicacion.publicacion.id === parseInt($(this)[0])) ? ' active' : '') +
                '" data-id="' + parseInt($(this)[0]) + '" title="Ir a publicación nº ' + (index + 1) + ' del día">' + (index + 1) + '</div>');
        });

        $('.calendar-number-add').removeClass('active');
        $('.delete-publicacion, .upload-publicacion').show();
        $('.create-publicacion').hide();
        $('.publicacion-buttons').css('display', 'flex');
    }

    function getJsonRow(mes, dia, jsonData) {
        var resultados = [];

        for (var i = 0; i < jsonData.length; i++) {
            if (jsonData[i].mes === mes && jsonData[i].dia === dia) {
                resultados.push(jsonData[i]);
            }
        }

        return resultados.length > 0 ? resultados : null;
    }

    function getMesBuscado($dia) {
        let mesBuscado;
        let wantedMonth = parseInt(month);

        if ($dia.hasClass('dia-mes-anterior')) {
            mesBuscado = wantedMonth - 1;
        } else if ($dia.hasClass('dia-mes-siguiente')) {
            mesBuscado = wantedMonth + 1;
        } else {
            mesBuscado = wantedMonth;
        }

        if (mesBuscado === 13) {
            mesBuscado = 1;
        }

        if (mesBuscado === 0) {
            mesBuscado = 12;
        }

        return parseInt(mesBuscado);
    }

    function getPublicaciones(month, year, calendar) {
        var url = 'get_publicaciones_by_mont.php';

        $.ajax({
            type: 'POST',
            url: url,
            data: {month: month, year: year, calendar: calendar},
            dataType: 'json',
            success: function (jsonData) {
                $('.dia').each(function () {
                    $this = $(this);

                    let publicaciones = getJsonRow(getMesBuscado($this), $this.data('dia'), jsonData);
                    $(publicaciones).each(function () {
                        publicacion = $(this)[0];

                        let $redSocialButton = $this.find('.publicacion[data-social=' + publicacion.red_social + ']');
                        $redSocialButton.addClass('active');
                        $redSocialButton.attr('data-id', publicacion.id);
                        $redSocialButton.attr('data-publicacion', publicacion.dia + '|' + publicacion.mes + '|' + publicacion.anio + '|' + publicacion.calendario + '|' + publicacion.red_social);

                        if (publicacion.num_comentarios > 0) {
                            $redSocialButton.append('<div class="publicacion-comentarios"><img alt="Comentarios" src="icon/comentario.svg"><label>' + publicacion.num_comentarios + '</label></div>');
                        }
                    });
                });
            },
            error: function (error) {
                console.error('Error en la solicitud AJAX', error);
            }
        });
    }

    function getPublicacionById(id) {
        return new Promise(function(resolve, reject) {
            // URL del archivo PHP
            var url = 'get_publicación_by_id.php';

            // Realizar la solicitud AJAX
            $.ajax({
                type: 'POST',
                url: url,
                data: {id_publicacion: id},
                dataType: 'json',
                success: function (jsonData) {
                    resolve(jsonData); // Resuelve la promesa con el valor de jsonData
                },
                error: function (error) {
                    // Rechaza la promesa con el error
                    reject(error);
                }
            });
        });
    }

    $('.delete-publicacion').click(function () {
        let url = 'delete_publicacion.php';
        let id = $('.publicacion-id').val();

        $.ajax({
            type: 'POST',
            url: url,
            data: {id: id},
            dataType: 'json',
            success: function (jsonData) {
                let $publicacion = $('.publicacion.active[data-id="' + id + '"]');

                Swal.fire({
                    icon: 'success',
                    title: 'Publicación eliminada con éxito',
                    showConfirmButton: false,
                    timer: 1000
                });

                cleanPublicacionForm();
                $publicacion.removeClass('active');
                $publicacion.removeAttr('data-id');
            },
            error: function (error) {
                console.error('Error en la solicitud AJAX', error);
            }
        });
    });

    function markDay($socialButton) {
        $socialButton.addClass('selected');
        $socialButton.closest('.dia').addClass('selected');
    }

    function getFileType(fileName) {
        var extension = fileName.split('.').pop().toLowerCase();

        var imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'];
        var videoExtensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm'];
        var audioExtensions = ['mp3', 'wav', 'aac', 'flac', 'ogg', 'm4a'];
        var documentExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
        var compressedExtensions = ['zip', 'rar', '7z', 'tar', 'gz'];

        if (imageExtensions.includes(extension)) {
            return 'image_file.svg';
        } else if (videoExtensions.includes(extension)) {
            return 'video_file.svg';
        } else if (audioExtensions.includes(extension)) {
            return 'audio_file.svg';
        } else if (documentExtensions.includes(extension)) {
            return 'document_file.svg';
        } else if (compressedExtensions.includes(extension)) {
            return 'other_file.svg';
        } else {
            return 'other_file.svg';
        }
    }
    
    $(".publicacion-enlace-link").click(function () {
        let publicacionEnlaceUrl = $('.publicacion-enlace').val();

        if (publicacionEnlaceUrl !== "") {
            if (!/^https?:\/\//i.test(publicacionEnlaceUrl)) {
                publicacionEnlaceUrl = 'http://' + publicacionEnlaceUrl;
            }

            window.open(publicacionEnlaceUrl, '_blank');
        }
    });

    $(document).on('click', '.publicacion-file-delete', function f() {
       $(this).closest('.publicacion-file').remove();
    });


    //Replicar contenido de publicación
    $(document).on('click', '.publicacion-replicate-social', function f() {
        Swal.fire({
            title: 'Replicar publicación en ' + $(this).data('social'),
            text: "Se sustituirá el contenido en caso de que ya exista",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'No, cancelar'
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }
            replicatePublicacion($(this).attr('data-social'));
        });
    });

    function replicatePublicacion(newRedSocial) {
        let anio = $('.publicacion-year').val();
        let mes = $('.publicacion-month').val();
        let dia = $('.publicacion-day').val();
        let red_social = $('.publicacion-social').val();

        $.ajax({
            url: 'replicate_publicacion.php', // Reemplaza con la ruta a tu script PHP
            type: 'POST',
            data: {
                anio: anio,
                mes: mes,
                dia: dia,
                red_social: red_social,
                calendario: calendar,
                new_red_social: newRedSocial
            },
            success: function(response) {
                console.log(data.min_id);
            },
            error: function(xhr, status, error) {
                alert('Error al enviar los datos: ' + error);
                console.log(xhr.responseText);
            }
        });
    }

    // Ver semana
    $(document).on('click', '.calendar-week-link', function f() {
        let $firstWeekDay = $(this).closest("tr").find("td").eq(0);
        let $lastWeekDay = $(this).closest("tr").find("td").eq(6)

        let firstDateDay = $firstWeekDay.attr('data-dia');
        let lastDateDay = $lastWeekDay.attr('data-dia');

        let firstDateMonth = getMesBuscado($firstWeekDay);
        let lastDateMonth = getMesBuscado($lastWeekDay);

        let firstDateYear = getAnioBuscado($('.calendar-month-select').val(), $firstWeekDay);
        let lastDateYear = getAnioBuscado($('.calendar-month-select').val(), $lastWeekDay);

        let password = $('.calendar-client-select').find(":selected").data('password');;

        window.open('semana/?fday=' + firstDateDay + '&fmonth=' + firstDateMonth + '&fyear=' + firstDateYear +
            '&lday=' + lastDateDay + '&lmonth=' + lastDateMonth + '&lyear=' + lastDateYear +
            '&calendario=' + calendar + '&p=' + password
        );
    });

</script>
</body>
</html>
