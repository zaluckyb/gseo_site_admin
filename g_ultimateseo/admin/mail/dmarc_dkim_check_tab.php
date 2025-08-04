<?php
if (!defined('ABSPATH')) {
    exit;
}

// admin/mail/dmarc_dkim_check_tab.php
?>

<form method="post">
    <?php wp_nonce_field('dmarc_dkim_check_action', 'dmarc_dkim_check_nonce'); ?>
    <table class="form-table">
        <tr>
            <th>Enter Your Domain:</th>
            <td>
                <input type="text" name="domain_to_check"
                       value="<?php echo esc_attr(parse_url(home_url(), PHP_URL_HOST)); ?>"
                       required style="width: 100%; max-width: 500px;">
            </td>
        </tr>
    </table>
    <?php submit_button('Check DMARC & DKIM'); ?>
</form>

<?php
if (isset($_POST['domain_to_check'])) {
    if (!isset($_POST['dmarc_dkim_check_nonce']) || !wp_verify_nonce($_POST['dmarc_dkim_check_nonce'], 'dmarc_dkim_check_action')) {
        wp_die('Security check failed');
    }

    $domain = sanitize_text_field($_POST['domain_to_check']);

    // DNS Records Section
    echo '<div style="border: 1px solid #ccd0d4; background-color: #f6f7f7; padding: 15px; border-radius: 4px; margin-top:20px;">';

    // Check DMARC records
    $dmarc_records = dns_get_record("_dmarc." . $domain, DNS_TXT);
    echo '<h3 style="margin-top:0;">DMARC Records:</h3>';
    if (!empty($dmarc_records)) {
        foreach ($dmarc_records as $record) {
            echo '<pre>' . esc_html($record['txt']) . '</pre>';
        }
    } else {
        echo '<p style="color:red;">❌ DMARC record not found for this domain.</p>';
    }

    // Check DKIM records (assuming selector "default")
    $dkim_selector = 'default';
    $dkim_domain = $dkim_selector . "._domainkey." . $domain;
    $dkim_records = dns_get_record($dkim_domain, DNS_TXT);
    echo '<h3>DKIM Records (selector: "default"):</h3>';
    if (!empty($dkim_records)) {
        foreach ($dkim_records as $record) {
            echo '<pre>' . esc_html($record['txt']) . '</pre>';
        }
    } else {
        echo '<p style="color:red;">❌ DKIM record not found using selector "default". Verify your selector name.</p>';
    }

    // Check SPF records
    $spf_records = dns_get_record($domain, DNS_TXT);
    echo '<h3>SPF Records:</h3>';
    $spf_found = false;
    foreach ($spf_records as $record) {
        if (strpos($record['txt'], 'v=spf1') !== false) {
            echo '<pre>' . esc_html($record['txt']) . '</pre>';
            $spf_found = true;
        }
    }
    if (!$spf_found) {
        echo '<p style="color:red;">❌ SPF record not found for this domain.</p>';
    }

    echo '</div>';

    // Quick Checks Section
    echo '<div style="border: 1px solid #ccd0d4; background-color: #f6f7f7; padding: 15px; border-radius: 4px; margin-top:20px;">';
    echo '<h3 style="margin-top:0;">🚀 Quick Checks Using Online Tools:</h3>';
    echo '<p>These external tools help verify your email deliverability settings quickly and reliably:</p>';
    echo '<ul style="list-style-type: disc; padding-left: 20px; margin-bottom:0;">';
    echo '<li><strong><a href="https://mxtoolbox.com/DMARC.aspx" target="_blank">DMARC Check</a></strong></li>';
    echo '<li><strong><a href="https://mxtoolbox.com/dkim.aspx" target="_blank">DKIM Check</a></strong></li>';
    echo '<li><strong><a href="https://mxtoolbox.com/spf.aspx" target="_blank">SPF Check</a></strong></li>';
    echo '</ul>';
    echo '</div>';
}