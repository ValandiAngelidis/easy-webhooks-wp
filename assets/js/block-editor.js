/**
 * Block Editor (Gutenberg) Sidebar
 */
(function(wp) {
    const { registerPlugin } = wp.plugins || {};
    if (!registerPlugin) return;

    const { Fragment, useState } = wp.element;
    const { PanelBody, Button, ToggleControl } = wp.components;
    const { PluginSidebar } = wp.editPost || {};
    const { select } = wp.data;

    const Panel = () => {
        const [includeAuthor, setIncludeAuthor] = useState(EW_WP_BLOCK_EDITOR.defaultAuthor);
        const [sending, setSending] = useState(false);
        const [msg, setMsg] = useState('');

        const sendNow = () => {
            const post = select('core/editor')?.getCurrentPost();
            const postId = post?.id;
            
            if (!postId) {
                alert('Please save the post first.');
                return;
            }

            setMsg('Sending…');
            setSending(true);

            jQuery.post(ajaxurl, {
                action: EW_WP_BLOCK_EDITOR.action,
                [EW_WP_BLOCK_EDITOR.nonce_field]: EW_WP_BLOCK_EDITOR.nonce,
                post_id: postId,
                include_author: includeAuthor ? 1 : 0
            }, function(resp) {
                if (resp && resp.ok) {
                    setMsg('✅ Success: ' + (resp.message || 'Completed.'));
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    setMsg('⚠️ ' + (resp && resp.message ? resp.message : 'Unknown issue.'));
                }
            }).fail(function(xhr) {
                var m = (xhr && xhr.responseJSON && xhr.responseJSON.message) || 
                       (xhr && xhr.responseText) || 
                       'Request failed.';
                setMsg('❌ Error: ' + m);
            }).always(function() {
                setSending(false);
            });
        };

        return wp.element.createElement(Fragment, null,
            PluginSidebar ? wp.element.createElement(PluginSidebar, {
                name: 'ew-wp-sidebar',
                title: 'Easy Webhooks',
                icon: 'share'
            },
                wp.element.createElement(PanelBody, {
                    title: 'Send to Webhook',
                    initialOpen: true
                },
                    wp.element.createElement(ToggleControl, {
                        label: 'Include post author info',
                        checked: includeAuthor,
                        onChange: setIncludeAuthor
                    }),
                    wp.element.createElement(Button, {
                        isPrimary: true,
                        isBusy: sending,
                        onClick: sendNow
                    }, sending ? 'Sending…' : 'Send to Webhook'),
                    msg ? wp.element.createElement('div', {
                        style: { marginTop: '10px', whiteSpace: 'pre-line' }
                    }, msg) : null
                )
            ) : null
        );
    };

    registerPlugin('easy-webhooks-wp', { render: Panel });
})(window.wp || {});
