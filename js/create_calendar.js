$(document).ready(function () {
    const $monthSelect = $('.calendar-month-select');
    const $yearSelect = $('.calendar-year-select');
    const $calendarSelect = $('.calendar-client-select');
    const $calendarBody = $('.calendar tbody');

    // Generar opciones de año dinámicamente
    const currentYear = new Date().getFullYear();
    for (let i = currentYear + 5; i >= 2015; i--) {
        $yearSelect.append(new Option(i, i));
    }

    // Función para generar el calendario
    function generateCalendar(month, year) {
        const firstDay = new Date(year, month - 1, 1);
        const lastDay = new Date(year, month, 0);
        const lastDayPrevMonth = new Date(year, month - 1, 0);

        const startDay = firstDay.getDay() === 0 ? 7 : firstDay.getDay(); // Ajustar para que domingo sea 7
        const daysInMonth = lastDay.getDate();
        const daysInPrevMonth = lastDayPrevMonth.getDate();

        let html = '';
        let dayCounter = 1 - startDay + 1; // Ajuste del contador de días

        for (let row = 0; row < 6; row++) {
            html += '<tr>';
            for (let col = 1; col <= 7; col++) {
                if (dayCounter < 1) {
                    html += `<td data-dia="${daysInPrevMonth + dayCounter}" class="dia dia-mes-anterior">
                                <div class="dia-number">${daysInPrevMonth + dayCounter}</div>
                                <div class="dia-content">
                                    <div class="publicacion" data-social="linkedin"><img title="LinkedIn" src="/calendar/icon/linkedin.svg"></div>
                                    <div class="publicacion" data-social="facebook"><img title="Facebook" src="/calendar/icon/facebook.svg"></div>
                                    <div class="publicacion" data-social="instagram"><img title="Instagram" src="/calendar/icon/instagram.svg"></div>
                                    <div class="publicacion" data-social="equis"><img title="X" src="/calendar/icon/equis.svg"></div>
                                    <div class="publicacion" data-social="tiktok"><img title="Tik Tok" src="/calendar/icon/tiktok.svg"></div>
                                </div>`;
                } else if (dayCounter > daysInMonth) {
                    html += `<td data-dia="${dayCounter - daysInMonth}" class="dia dia-mes-siguiente">
                                <div class="dia-number">${dayCounter - daysInMonth}</div>
                                <div class="dia-content">
                                    <div class="publicacion" data-social="linkedin"><img title="LinkedIn" src="/calendar/icon/linkedin.svg"></div>
                                    <div class="publicacion" data-social="facebook"><img title="Facebook" src="/calendar/icon/facebook.svg"></div>
                                    <div class="publicacion" data-social="instagram"><img title="Instagram" src="/calendar/icon/instagram.svg"></div>
                                    <div class="publicacion" data-social="equis"><img title="X" src="/calendar/icon/equis.svg"></div>
                                    <div class="publicacion" data-social="tiktok"><img title="Tik Tok" src="/calendar/icon/tiktok.svg"></div>
                                </div>`;
                } else {
                    html += `<td data-dia="${dayCounter}" class="dia dia-mes-actual">
                                <div class="dia-number">${dayCounter}</div>
                                <div class="dia-content">
                                    <div class="publicacion" data-social="linkedin"><img title="LinkedIn" src="/calendar/icon/linkedin.svg"></div>
                                    <div class="publicacion" data-social="facebook"><img title="Facebook" src="/calendar/icon/facebook.svg"></div>
                                    <div class="publicacion" data-social="instagram"><img title="Instagram" src="/calendar/icon/instagram.svg"></div>
                                    <div class="publicacion" data-social="equis"><img title="X" src="/calendar/icon/equis.svg"></div>
                                    <div class="publicacion" data-social="tiktok"><img title="Tik Tok" src="/calendar/icon/tiktok.svg"></div>
                                </div>`;
                }

                if (col === 7 ) {
                    html += '<div title="Ver semana" class="calendar-week-link"><img src="icon/week.svg"></div>';
                }

                html += '</td>';

                dayCounter++;
            }

            html += '</tr>';
            if (dayCounter > daysInMonth) break; // Salir del bucle si hemos completado el mes actual
        }
        $calendarBody.html(html);
    }

    // Evento para actualizar el calendario cuando se cambie el mes o el año
    $monthSelect.add($yearSelect).add($calendarSelect).on('change', function () {
        cleanPublicacionForm();
        const month = $monthSelect.val();
        const year = $yearSelect.val();
        generateCalendar(month, year);
    });

    // Inicializar el calendario con el mes y año actual
    $monthSelect.val(new Date().getMonth() + 1);
    $yearSelect.val(new Date().getFullYear());
    generateCalendar($monthSelect.val(), $yearSelect.val());
});
