<?php
// add_action('rest_api_init', function () {
//     register_rest_route('gsecurity/v1', '/site-info', [
//         'methods'             => 'GET',
//         'callback'            => 'gseo_get_site_info',
//         'permission_callback' => 'gsecurity_api_permission_check',
//     ]);
// });

// function gseo_get_site_info(WP_REST_Request $request) {
//     global $wp_roles, $wpdb;

//     return rest_ensure_response([
//         'server_info' => [
//             'PHP Version'        => phpversion(),
//             'WordPress Version'  => get_bloginfo('version'),
//             'Server Software'    => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
//             'MySQL Version'      => $wpdb->db_version(),
//             'Memory Limit'       => ini_get('memory_limit'),
//             'Max Execution Time' => ini_get('max_execution_time') . ' sec',
//         ],
//         'user_roles' => array_keys($wp_roles->roles),
//         'post_types' => array_values(get_post_types(['public' => true], 'names')),
//     ]);
// }
