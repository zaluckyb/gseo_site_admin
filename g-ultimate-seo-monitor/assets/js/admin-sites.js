document.addEventListener('DOMContentLoaded', function () {

    // AJAX Sync Functionality
    document.querySelectorAll('.gseo-sync-site').forEach(button => {
        button.addEventListener('click', function () {
            const siteId = this.dataset.siteId;
            if (!siteId) return;

            const btn = this;
            btn.disabled = true;
            btn.textContent = 'Syncing...';

            fetch(gseo_ajax.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'gseo_manual_sync_site',
                    security: gseo_ajax.security,
                    site_id: siteId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Sync successful!');
                    location.reload();
                } else {
                    alert('⚠️ Sync failed: ' + (data.data || 'Unknown error'));
                }
                btn.disabled = false;
                btn.textContent = '🔄 Sync Site Info';
            })
            .catch(err => {
                alert('❌ AJAX error: ' + err);
                btn.disabled = false;
                btn.textContent = '🔄 Sync Site Info';
            });
        });
    });

    // Section Toggle Functionality
    jQuery(document).ready(function($) {

        $('.gseo-toggle-section').on('click', function() {
            var section = $(this).data('section');

            if (section === 'all') {
                $('.site-section').show();
            } else {
                $('.site-section').hide();
                $('.' + section).show();
            }

            // Highlight active button
            $('.gseo-toggle-section').removeClass('button-primary');
            $(this).addClass('button-primary');
        });

        // Initialize by showing all sections
        $('.gseo-toggle-section[data-section="all"]').trigger('click');
    });

});

jQuery(document).ready(function($) {
    $('#gusm-sync-authors-btn').click(function(e) {
        e.preventDefault();

        const button = $(this);
        button.prop('disabled', true).text('Syncing...');

        $.post(gseo_ajax.ajaxurl, {
            action: 'gusm_sync_all_authors',
            security: gseo_ajax.security,
        }, function(response) {
            if(response.success) {
                alert(response.data);
            } else {
                alert('Error: ' + response.data);
            }
            button.prop('disabled', false).text('Sync Authors from Sites');
            location.reload(); // Refresh the page to show changes
        });
    });
});