<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario en jQuery</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container">
    <div class="calendar-header">
        <select class="calendar-client-select">
            <option value="MOTORTEC">MOTORTEC</option>
            <option value="SALON%20VO">SALON VO</option>
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

    <div class="calendar-content">
        <h1 class="calendar-content-title"></h1>

        <div class="calendar-form">
            <input type="text" class="publicacion-titulo" placeholder="Título">
            <textarea class="publicacion-contenido" placeholder="Contenido..."></textarea>
            <input type="text" class="publicacion-titulo" placeholder="Enlace">
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

<script src="js/create_calendar.js"></script>
<script>
    let month, year, calendar;
    let currentUrl = window.location.origin + window.location.pathname;

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

        if ($dia.hasClass('dia-mes-anterior')) {
            mesBuscado = month - 1;
        } else if ($dia.hasClass('dia-mes-siguiente')) {
            mesBuscado = month + 1;
        } else {
            mesBuscado = month;
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
        //Limpiamos calendario

        // URL del archivo PHP
        var url = 'http://192.168.1.62/calendar/c';

        // Realizar la solicitud AJAX
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

                        $this.find('.dia-content').append('<img src="/calendar/icon/' + publicacion.red_social + '.svg" class="publicacion" data-id="' + publicacion.id + '">');
                    });
                });
            },
            error: function (error) {
                // Manejar errores
                console.error('Error en la solicitud AJAX', error);
            }
        });
    }
    $(document).ready(function () {
     
    });
</script>
</body>
</html>
