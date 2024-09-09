let idPublicacionChat

$(document).ready(function() {
    let messages = [];
    let $userInput = $('#userInput');

    $(".publicacion-comentario, .publicacion-form-comentarios").on("click", function() {
        let $chatBox = $('#chatbox');
        let $this = $(this);
        idPublicacionChat = $(this).attr("data-id-publicacion");

        $('#messages').html('');
        $('#chatHeader span').html($this.attr('data-social') + ' - ' + $this.attr('data-fecha'));

        $.ajax({
            url: baseURL + 'chat/get_comentarios.php',
            type: 'POST',
            data: {
                id_publicacion: idPublicacionChat
            },
            success: function(response) {
                let comentarios = JSON.parse(response);

                // Aquí puedes manejar los comentarios, por ejemplo, mostrarlos en un div
                let comentariosHtml = "";
                $.each(comentarios, function(index, comentario) {
                    comentariosHtml +=
                        '<div class="message show ' + (comentario.id_usuario === sesionId ? "user" : "bot") +  '">' +
                        '<label class="message-user">' + comentario.nombre_usuario + '</label>' +
                        '<label class="message-text">' + comentario.comentario.replace(/\\n/g, '\n').replace(/\n/g, '<br>') + '</label>' +
                        '<label class="message-date">' + formatDateString(comentario.fecha) + '</label>' +
                        '</div>';

                    $('#messages').html(comentariosHtml);
                    $chatBox.scrollTop($chatBox[0].scrollHeight);
                });

                $('#chatContainer').removeClass('chat-close');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Error al obtener los comentarios:", textStatus, errorThrown);
            }
        });
    });

    function sendComentario() {
        let comentario = $userInput.val();
        let $openComentariosButton = $('.publicacion-comentario[data-id-publicacion="' + idPublicacionChat + '"], .publicacion-form-comentarios[data-id-publicacion="' + idPublicacionChat + '"]');

        $.ajax({
            url: baseURL + 'chat/set_comentario.php',
            type: 'POST',
            dataType: 'json',
            data: {
                comentario: comentario,
                id_publicacion: idPublicacionChat,
                fecha_publicacion: $openComentariosButton.attr('data-fecha'),
                calendario_social: $openComentariosButton.attr('data-social')
            },
            success: function(response) {
                if (response.status === 'success') {
                    $openComentariosButton.click();
                    $openComentariosButton.find('.publicacion-comentario-numero').text(response.num_comentarios);
                    $('.publicacion-form-comentarios[data-id-publicacion="' + idPublicacionChat + '"] > label, .publicacion[data-id="' + idPublicacionChat + '"] .publicacion-comentarios > label').text(response.num_comentarios);

                    $userInput.val('').css('height', "45px");
                } else {
                    console.error('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la petición AJAX: ' + error);
            }
        });
    }

    function formatDateString(dateString) {
        const [datePart, timePart] = dateString.split(' ');
        const [year, month, day] = datePart.split('-');
        const [hours, minutes] = timePart.split(':');

        return `${day}/${month} - ${hours}:${minutes}`;
    }

    $('#sendButton').on('click', sendComentario);

    $userInput.on('input', function() {
        $(this).css('height', 'auto');
        $(this).css('height', this.scrollHeight + 'px');
    });

    $userInput.on('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendComentario();
        }
    });

    $(document).on('keydown', function(e) {
        if (e.which === 27) {
            $('.chat-header-close').click();
        }
    });

    $('.chat-header-close').click(function () {
        $('#chatContainer').addClass('chat-close');
    });
});