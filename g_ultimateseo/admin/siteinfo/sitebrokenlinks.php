<?php
if (!defined('ABSPATH')) exit;

// admin/siteinfo/sitebrokenlinks.php

// Register AJAX actions
add_action('wp_ajax_gfseo_start_broken_links_scan', 'gfseo_ajax_start_broken_links_scan');
add_action('wp_ajax_gfseo_get_broken_links_progress', 'gfseo_ajax_get_broken_links_progress');
add_action('wp_ajax_gfseo_clear_broken_links', 'gfseo_ajax_clear_broken_links');
add_action('wp_ajax_gfseo_remove_broken_link', 'gfseo_ajax_remove_broken_link');

// Main Display function
function gfseo_display_broken_links_checker() {
    global $wpdb;
    wp_enqueue_script('jquery');

    // Pagination settings
    $table_name = $wpdb->prefix . 'gfseo_broken_links';
    $per_page = 100;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $per_page;

    $total_broken = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $total_pages = ceil($total_broken / $per_page);

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name ORDER BY date_found DESC LIMIT %d OFFSET %d",
        $per_page, $offset
    ));
    ?>

    <div id="broken-links-status"></div>
    <button id="start-scan" class="button button-primary">Scan for Broken Links Now</button>
    <button id="clear-broken-links" class="button button-secondary">Clear All Broken Links</button>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#start-scan').click(function() {
                $('#broken-links-status').html('<p>Scan starting...</p>');
                $.post(ajaxurl, { action: 'gfseo_start_broken_links_scan' });

                var interval = setInterval(function() {
                    $.post(ajaxurl, { action: 'gfseo_get_broken_links_progress' }, function(response) {
                        $('#broken-links-status').html(response.html);
                        if(response.completed) {
                            clearInterval(interval);
                            location.reload();
                        }
                    });
                }, 3000);
            });

            $('#clear-broken-links').click(function() {
                if (confirm('Are you sure you want to clear all broken links?')) {
                    $.post(ajaxurl, { action: 'gfseo_clear_broken_links' }, function() {
                        location.reload();
                    });
                }
            });

            // Remove individual broken link
            $('.fixed-link').click(function() {
                const row = $(this).closest('tr');
                const id = $(this).data('id');
                $.post(ajaxurl, {
                    action: 'gfseo_remove_broken_link',
                    link_id: id
                }, function() {
                    row.fadeOut(500, function() { $(this).remove(); });
                });
            });
        });
    </script>

    <h2>🔗 Broken Links (<?php echo $total_broken; ?>)</h2>
    <?php if($results): ?>
        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th style="width:35%;">URL</th>
                    <th>Status Code</th>
                    <th>Found On (Post/Page)</th>
                    <th>Date Found</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td><a href="<?php echo esc_url($row->url); ?>" target="_blank"><?php echo esc_html($row->url); ?></a></td>
                        <td><?php echo esc_html($row->status_code); ?></td>
                        <td>
                            <a href="<?php echo get_edit_post_link($row->post_id); ?>" target="_blank">
                                <?php echo esc_html(get_the_title($row->post_id)); ?>
                            </a>
                        </td>
                        <td><?php echo esc_html($row->date_found); ?></td>
                        <td><button class="button fixed-link" data-id="<?php echo esc_attr($row->id); ?>">Fixed</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
    <nav class="navigation pagination" aria-label="Broken Links Pagination" style="margin-top:20px;">
        <?php
        $base_url = admin_url('admin.php?page=gseo-site-status&tab=broken_links');

        echo paginate_links([
            'base'      => add_query_arg('paged', '%#%', $base_url),
            'format'    => '',
            'current'   => max(1, isset($_GET['paged']) ? (int)$_GET['paged'] : 1),
            'total'     => $total_pages,
            'prev_text' => __('« Previous'),
            'next_text' => __('Next »'),
            'mid_size'  => 2,
            'end_size'  => 1,
            'type'      => 'list',
        ]);
        ?>
    </nav>

    <style>
        .navigation.pagination .page-numbers {
            display: inline-block;
            padding: 5px 12px;
            margin: 0 4px 10px 0;
            border: 1px solid #ddd;
            background: #f8f8f8;
            text-decoration: none;
            color: #555;
            border-radius: 3px;
        }
        .navigation.pagination .page-numbers.current {
            background: #007cba;
            color: #fff;
            border-color: #007cba;
        }
        .navigation.pagination .page-numbers:hover {
            background: #ddd;
        }
        .navigation.pagination ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .navigation.pagination ul li {
            display: inline-block;
            margin: 0;
        }
    </style>
<?php endif; ?>




    <?php else: ?>
        <p>No broken links found yet. Run a scan to detect issues.</p>
    <?php endif;
}

// AJAX: Start scan immediately
function gfseo_ajax_start_broken_links_scan() {
    if (!wp_next_scheduled('gfseo_broken_links_batch_scan')) {
        update_option('gfseo_broken_links_scan_offset', 0);
        update_option('gfseo_broken_links_scan_status', 'running');
        wp_schedule_single_event(time(), 'gfseo_broken_links_batch_scan', [20, 0]);
    }
    wp_die();
}

// AJAX: Progress update
function gfseo_ajax_get_broken_links_progress() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gfseo_broken_links';
    $total_broken = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $offset = get_option('gfseo_broken_links_scan_offset', 0);
    $status = get_option('gfseo_broken_links_scan_status', 'not_started');
    $total_posts = wp_count_posts('post')->publish + wp_count_posts('page')->publish;

    ob_start();
    ?>
    <div class="notice notice-info">
        Scan Status: <strong><?php echo ucfirst($status); ?></strong><br>
        Posts Scanned: <strong><?php echo $offset; ?> / <?php echo $total_posts; ?></strong><br>
        Broken Links Found: <strong><?php echo $total_broken; ?></strong>
    </div>
    <?php
    wp_send_json(['html' => ob_get_clean(), 'completed' => $status === 'completed']);
}

// AJAX: Clear all broken links
function gfseo_ajax_clear_broken_links() {
    global $wpdb;
    $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}gfseo_broken_links");
    wp_die();
}

// AJAX: Remove single broken link
function gfseo_ajax_remove_broken_link() {
    global $wpdb;
    $link_id = intval($_POST['link_id']);
    $wpdb->delete("{$wpdb->prefix}gfseo_broken_links", ['id' => $link_id]);
    wp_die();
}

// Scanner (no changes required)
add_action('gfseo_broken_links_batch_scan', 'gfseo_check_site_for_broken_links', 10, 2);

function gfseo_check_site_for_broken_links($batch_size = 20, $offset = 0) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gfseo_broken_links';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
        $wpdb->query("CREATE TABLE $table_name (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            url TEXT NOT NULL,
            status_code VARCHAR(10),
            post_id BIGINT UNSIGNED,
            date_found DATETIME DEFAULT CURRENT_TIMESTAMP
        ) {$wpdb->get_charset_collate()};");
    }

    $posts = get_posts(['post_type' => ['post','page'], 'post_status'=>'publish', 'posts_per_page'=>$batch_size, 'offset'=>$offset, 'fields'=>'ids']);
    foreach ($posts as $post_id) {
        if ($urls = wp_extract_urls(get_post_field('post_content', $post_id))) {
            foreach ($urls as $url) {
                if (strpos($url, '#') === 0 || strpos($url, 'mailto:') === 0) continue;
                $response = wp_remote_head($url,['timeout'=>5]);
                $status_code = is_wp_error($response) ? 'Error' : wp_remote_retrieve_response_code($response);
                if($status_code>=400 || is_wp_error($response)){
                    if(!$wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE url=%s AND post_id=%d",$url,$post_id))){
                        $wpdb->insert($table_name,['url'=>$url,'status_code'=>$status_code,'post_id'=>$post_id]);
                    }
                }
            }
        }
    }
    $total_posts = wp_count_posts('post')->publish+wp_count_posts('page')->publish;
    $next_offset = $offset+$batch_size;
    update_option('gfseo_broken_links_scan_offset',$next_offset);
    update_option('gfseo_broken_links_scan_status',$next_offset<$total_posts?'running':'completed');
    if($next_offset<$total_posts) wp_schedule_single_event(time()+10,'gfseo_broken_links_batch_scan',[$batch_size,$next_offset]);
}
