document.addEventListener('DOMContentLoaded', function() {
    let mediaFrame;

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('gso-copy-shortcode')) {
            const shortcode = e.target.getAttribute('data-shortcode');

            navigator.clipboard.writeText(shortcode).then(() => {
                const originalText = e.target.textContent;
                e.target.textContent = 'Copied!';
                setTimeout(() => {
                    e.target.textContent = originalText;
                }, 1200);
            });

            return;
        }

        if (e.target.classList.contains('gso-logo-upload')) {
            e.preventDefault();

            const field = e.target.closest('.gso-logo-field');
            const input = field.querySelector('#gso_logo_id');
            const preview = field.querySelector('.gso-logo-preview');
            const removeButton = field.querySelector('.gso-logo-remove');

            if (mediaFrame) {
                mediaFrame.off('select');
            }

            mediaFrame = wp.media({
                title: 'Select Logo',
                button: {
                    text: 'Use this logo'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            mediaFrame.on('select', function() {
                const attachment = mediaFrame.state().get('selection').first().toJSON();
                const imageUrl = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;

                input.value = attachment.id;
                preview.classList.remove('is-empty');
                preview.innerHTML = '<img src="' + imageUrl + '" alt="Logo preview" class="gso-logo-preview-image">';
                removeButton.hidden = false;
            });

            mediaFrame.open();
            return;
        }

        if (e.target.classList.contains('gso-logo-remove')) {
            e.preventDefault();

            const field = e.target.closest('.gso-logo-field');
            const input = field.querySelector('#gso_logo_id');
            const preview = field.querySelector('.gso-logo-preview');

            input.value = '';
            preview.classList.add('is-empty');
            preview.innerHTML = '<span class="gso-logo-placeholder">No logo selected</span>';
            e.target.hidden = true;
        }
    });
});
