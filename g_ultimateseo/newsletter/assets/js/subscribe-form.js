// newsletter/assets/subscribe-form.js
jQuery(document).ready(function($) {
    $('#gfseo-subscribe-form').on('submit', function(e) {
        e.preventDefault();

        let form = $(this);
        let responseDiv = $('#gfseo-form-response');

        // Disable submit button to prevent multiple submissions
        form.find('button[type="submit"]').prop('disabled', true).text('Subscribing...');

        let formData = {
            action: 'gfseo_subscribe_user',
            security: gfseoAjax.nonce,
            name: form.find('input[name="gfseo_name"]').val(),
            surname: form.find('input[name="gfseo_surname"]').val(),
            email: form.find('input[name="gfseo_email"]').val()
        };

        $.post(gfseoAjax.ajaxurl, formData, function(response) {
            if (response.status === 'success') {
                responseDiv.html('<span style="color:green;">' + response.message + '</span>');
                form.trigger('reset');
            } else {
                responseDiv.html('<span style="color:red;">' + response.message + '</span>');
            }

            form.find('button[type="submit"]').prop('disabled', false).text('Subscribe');
        }).fail(function() {
            responseDiv.html('<span style="color:red;">❌ An unexpected error occurred. Please try again later.</span>');
            form.find('button[type="submit"]').prop('disabled', false).text('Subscribe');
        });
    });
});