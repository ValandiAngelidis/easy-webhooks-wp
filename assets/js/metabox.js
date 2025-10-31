/**
 * Classic Editor Meta Box JavaScript
 */
(function($) {
    $(function() {
        var $btn = $('#ew-wp-send');
        var $st = $('#ew-wp-status');
        
        if (!$btn.length) return;

        $btn.on('click', function(e) {
            e.preventDefault();
            
            var id = parseInt($('#post_ID').val(), 10) || 0;
            if (!id) {
                alert('Please save the post first.');
                return;
            }

            var includeAuthor = $('#ew-wp-include-author').is(':checked') ? 1 : 0;
            
            $st.text('Sending…');
            $btn.prop('disabled', true).text('Sending…');

            $.post(ajaxurl, {
                action: EW_WP_AJAX.action,
                [EW_WP_AJAX.nonce_field]: $('#' + EW_WP_AJAX.nonce_field).val(),
                post_id: id,
                include_author: includeAuthor
            }, function(resp) {
                if (resp && resp.ok) {
                    $st.text('✅ Success: ' + (resp.message || 'Completed.'));
                    setTimeout(function() {
                        location.reload();
                    }, 1200);
                } else {
                    $st.text('⚠️ ' + (resp && resp.message ? resp.message : 'Unknown issue.'));
                    $btn.prop('disabled', false).text('Send to Webhook');
                }
            }).fail(function(xhr) {
                var msg = (xhr && xhr.responseJSON && xhr.responseJSON.message) || 
                         (xhr && xhr.responseText) || 
                         'Request failed.';
                $st.text('❌ Error: ' + msg);
                $btn.prop('disabled', false).text('Send to Webhook');
            });
        });
    });
})(jQuery);
