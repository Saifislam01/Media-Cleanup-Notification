jQuery(document).ready(function ($) {
    $('#unused-images-scan-button').on('click', function () {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'unused_images_cleanup_scan',
                _wpnonce: '<?php echo wp_create_nonce('unused-images-cleanup-nonce'); ?>'
            },
            beforeSend: function () {
                $('#unused-images-scan-results').html('Scanning...');
            },
            success: function (response) {
                var resultHtml = '<p>Total Images: ' + response.data.total_images + '</p>';
                resultHtml += '<p>Unused Images: ' + response.data.unused_images + '</p>';
                if (response.data.unused_images > 0) {
                    resultHtml += '<p><strong>Unused Images:</strong></p>';
                    resultHtml += '<ul>';
                    $.each(response.data.unused_list, function (index, image) {
                        resultHtml += '<li>' + image + '</li>';
                    });
                    resultHtml += '</ul>';
                    resultHtml += '<button id="unused-images-delete-button" class="button button-primary">Delete Unused Images</button>';
                }
                $('#unused-images-scan-results').html(resultHtml);
            },
            error: function (xhr) {
                $('#unused-images-scan-results').html('Error: ' + xhr.responseText);
            }
        });
    });

    $('#unused-images-scan-results').on('click', '#unused-images-delete-button', function () {
        var imagesToDelete = [];
        $('#unused-images-scan-results ul li').each(function () {
            imagesToDelete.push($(this).text());
        });

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'unused_images_cleanup_delete',
                images: imagesToDelete,
                _wpnonce: '<?php echo wp_create_nonce('unused-images-cleanup-nonce'); ?>'
            },
            beforeSend: function () {
                $('#unused-images-scan-results').append('<p>Deleting unused images...</p>');
            },
            success: function (response) {
                $('#unused-images-scan-results').append('<p>Deleted ' + response.data.deleted_count + ' unused images.</p>');
                $('#unused-images-delete-button').remove();
            },
            error: function (xhr) {
                $('#unused-images-scan-results').append('<p>Error: ' + xhr.responseText + '</p>');
            }
        });
    });
});
