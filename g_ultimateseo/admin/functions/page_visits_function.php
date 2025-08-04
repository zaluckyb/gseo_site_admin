<?php

// admin/functions/page_visits_function.php
// Function to update page visits
function g_ultimateseo_update_page_visits() {
    global $post, $wpdb;

    // Exit early if not a valid post or in case of revision/autosave
    if (empty($post) || !is_a($post, 'WP_Post') || wp_is_post_revision($post->ID) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
        return;
    }

    // Ensure we're on a singular page view
    if (is_singular()) {
        // Prepare data for insertion
        $data = [
            'post_id' => $post->ID,
            'date' => current_time('mysql'),
            'ip_address' => g_ultimateseo_get_user_ip(),
            'referrer' => g_ultimateseo_get_referrer()
        ];

        // Add data to the queue for batch processing
        g_ultimateseo_queue_data_for_insert($data);
    }
}

// Get user IP with anonymization
function g_ultimateseo_get_user_ip() {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unavailable';
    // Anonymize IP (example method, modify as needed)
    return wp_hash($ip_address);
}

// Get the HTTP referrer
function g_ultimateseo_get_referrer() {
    return isset($_SERVER['HTTP_REFERER']) ? esc_url_raw($_SERVER['HTTP_REFERER']) : 'N/A';
}

// Queue data for batch processing
function g_ultimateseo_queue_data_for_insert($data) {
    $queue = get_transient('g_ultimateseo_data_queue') ?: [];
    array_push($queue, $data);
    set_transient('g_ultimateseo_data_queue', $queue, HOUR_IN_SECONDS);
}

// Batch process the queued data
function g_ultimateseo_batch_process_data() {
    global $wpdb;
    $queue = get_transient('g_ultimateseo_data_queue');

    if (!empty($queue)) {
        foreach ($queue as $data) {
            // Use prepared statements for secure database insertion
            $wpdb->insert(
                $wpdb->prefix . 'g_ultimateseo_analytics',
                $data,
                ['%d', '%s', '%s', '%s']
            );
        }
        // Clear the transient after processing
        delete_transient('g_ultimateseo_data_queue');
    }
}

// Hook the function to wp_head
add_action('wp_head', 'g_ultimateseo_update_page_visits');

// Schedule the batch processing
if (!wp_next_scheduled('g_ultimateseo_batch_process_data')) {
    wp_schedule_event(time(), 'hourly', 'g_ultimateseo_batch_process_data');
}

add_action('g_ultimateseo_batch_process_data', 'g_ultimateseo_batch_process_data');
