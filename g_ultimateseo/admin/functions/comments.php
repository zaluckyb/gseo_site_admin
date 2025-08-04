<?php 

// admin/functions/comments.php

function g_ultimateseo_toggle_comments() {
    $options = get_option('g_ultimateseo_options');
    if (isset($options['enable_comments']) && $options['enable_comments'] == 0) {
        // Disable comments
        add_filter('comments_open', '__return_false', 20, 2);
        add_filter('pings_open', '__return_false', 20, 2);
        // Hide existing comments
        add_filter('comments_array', '__return_empty_array', 10, 2);
        remove_action('wp_head', 'feed_links_extra', 3);
    }
}

add_action('wp', 'g_ultimateseo_toggle_comments');