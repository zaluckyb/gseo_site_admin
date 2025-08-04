<?php
// admin/siteinfo/siteemailinfo.php

if (!defined('ABSPATH')) {
    exit;
}

function gfseo_display_email_info() {
    $smtp_options = get_option('g_ultimateseo_smtp_options');
    $smtp_email = isset($smtp_options['from_email']) ? $smtp_options['from_email'] : 'Not Configured';

    $domain = parse_url(site_url(), PHP_URL_HOST);

    $checks = [
        'SMTP Email Configured'   => $smtp_email !== 'Not Configured',
        'SPF Record'              => gfseo_check_dns_record($domain, 'spf'),
        'DKIM Record'             => gfseo_check_dns_record('default._domainkey.' . $domain, 'dkim'),
        'DMARC Record'            => gfseo_check_dns_record('_dmarc.' . $domain, 'dmarc'),
        'Reverse DNS (rDNS)'      => check_rdns($domain),
        'SMTP Authentication'     => !empty($smtp_options['smtp_auth']),
        'SMTP TLS/SSL Encryption' => !empty($smtp_options['encryption']) && in_array(strtolower($smtp_options['encryption']), ['tls', 'ssl']),
        'IP Blacklist Check'      => check_blacklists(gethostbyname($domain)) === 'Clean ✅',
        'Email Domain Consistency'=> parse_url(site_url(), PHP_URL_HOST) === explode('@', $smtp_email)[1],
    ];

    $total_checks = count($checks);
    $passed_checks = count(array_filter($checks));
    $score_percentage = round(($passed_checks / $total_checks) * 100);

    $status_color = $score_percentage >= 90 ? 'green' : ($score_percentage >= 70 ? 'orange' : 'red');
    ?>

    <h2>Email Deliverability Score: <span style="color: <?php echo $status_color; ?>;"><?php echo $score_percentage; ?>%</span></h2>

    <table class="wp-list-table widefat striped">
        <thead>
            <tr>
                <th>Setting</th>
                <th>Status</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($checks as $setting => $result): ?>
                <tr>
                    <td><?php echo esc_html($setting); ?></td>
                    <td><?php echo $result ? '<span style="color:green;">Configured ✅</span>' : '<span style="color:red;">Not Configured ❌</span>'; ?></td>
                    <td>
                        <?php
                        switch ($setting) {
                            case 'SMTP Email Configured':
                                echo 'The email address configured to send emails via SMTP.';
                                break;
                            case 'SPF Record':
                                echo 'Helps prevent email spoofing by specifying allowed mail servers.';
                                break;
                            case 'DKIM Record':
                                echo 'Verifies sender identity by digitally signing emails. <a href="https://dnschecker.org/#TXT/default._domainkey.' . esc_attr($domain) . '" target="_blank">Check DKIM</a>';
                                break;
                            case 'DMARC Record':
                                echo 'Provides policies for handling emails failing SPF/DKIM checks.';
                                break;
                            case 'Reverse DNS (rDNS)':
                                echo 'Ensures the IP address resolves correctly back to the domain.';
                                break;
                            case 'SMTP Authentication':
                                echo 'Prevents email spoofing and ensures deliverability.';
                                break;
                            case 'SMTP TLS/SSL Encryption':
                                echo 'Encrypts emails during sending to improve trust and security.';
                                break;
                            case 'IP Blacklist Check':
                                echo 'Checks if your IP address is listed on major spam blacklists.';
                                break;
                            case 'Email Domain Consistency':
                                echo 'Ensures the email domain matches your sending domain.';
                                break;
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}

function gfseo_check_dns_record($domain, $type) {
    $records = dns_get_record($domain, DNS_TXT);
    if (empty($records)) return false;

    $checks = [
        'spf'   => 'v=spf1',
        'dkim'  => 'k=rsa',
        'dmarc' => 'v=DMARC1'
    ];

    foreach ($records as $record) {
        if (isset($record['txt']) && stripos($record['txt'], $checks[$type]) !== false) {
            return true;
        }
    }
    return false;
}

function check_rdns($domain) {
    $ip = gethostbyname($domain);
    $rdns = gethostbyaddr($ip);
    return ($rdns && strpos($rdns, $domain) !== false);
}

function check_blacklists($ip) {
    $blacklists = ['zen.spamhaus.org', 'bl.spamcop.net', 'b.barracudacentral.org'];
    $listed = [];

    foreach ($blacklists as $bl) {
        if (checkdnsrr(implode('.', array_reverse(explode('.', $ip))) . '.' . $bl . '.', 'A')) {
            $listed[] = $bl;
        }
    }

    return empty($listed) ? 'Clean ✅' : 'Listed: ' . implode(', ', $listed) . ' ❌';
}
