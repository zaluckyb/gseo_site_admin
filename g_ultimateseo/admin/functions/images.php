<?php

// admin/functions/images.php
function g_ultimateseo_images_page() {
        // Security checks
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'g-ultimate-seo'));
        }
    echo '<h1>Image Settings</h1>';
    echo "The G_UltimateSEO plugin optimizes images for your website in three key ways:";
    echo "<br>";
    echo "1. It automatically resizes any uploaded image to a maximum width of 1920 pixels if it's too large, helping to speed up your website's load time. ";
    echo "<br>";
    echo "2. It cleans up image file names by removing special characters, converting spaces and multiple hyphens to a single hyphen, and making the names lowercase, ensuring more organized and standardized filenames. ";
    echo "<br>";
    echo "3. It sets meaningful alternative text, captions, and descriptions for each uploaded image, using a cleaner version of the file name. This enhances website accessibility and SEO, making your site more user- and search engine-friendly.";        
    echo "<h3>g_ultimateseo_resize_image_on_upload():</h3>";
    echo "<p>This function hooks into the <code>wp_handle_upload_prefilter</code> to resize images upon upload.<br>";
    echo "It checks if the image functionality is enabled in the plugin's options.<br>";
    echo "The maximum width for images is set to 1920 pixels. Images wider than this are resized proportionally.<br>";
    echo "It uses WordPress's <code>wp_get_image_editor()</code> to handle the image and perform the resize.<br>";
    echo "This function is a good way to ensure that uploaded images are not excessively large, which is beneficial for page load times and overall site performance.</p>";

    echo "<h3>g_ultimateseo_sanitize_file_name():</h3>";
    echo "<p>This function sanitizes file names of uploaded images.<br>";
    echo "It hooks into the <code>sanitize_file_name</code> filter.<br>";
    echo "Special characters are removed, spaces and multiple hyphens are replaced with a single hyphen, and the file name is converted to lowercase.<br>";
    echo "This is useful for ensuring clean and standardized file names, which can help with file organization and URL readability.</p>";

    echo "<h3>g_ultimateseo_set_image_properties_on_upload():</h3>";
    echo "<p>Hooks into <code>add_attachment</code> and <code>update_attached_file</code> actions.<br>";
    echo "It checks if the image functionality is enabled in the plugin's options.<br>";
    echo "When an image is uploaded or its file is updated, this function sets the image's alt text, caption, and description.<br>";
    echo "The alt text, caption, and description are derived from a sanitized version of the file name, with hyphens replaced with spaces and converted to title case.<br>";
    echo "This is a great feature for SEO and accessibility, as it ensures that every image has meaningful and descriptive alt text.</p>";
}

function g_ultimateseo_optimize_image_on_upload($file) {
    $options = get_option('g_ultimateseo_options');
    if (!isset($options['enable_images']) || $options['enable_images'] != 1) {
        return $file; // Exit the function if image functionality is disabled
    }

    // Sanitize file name
    $filename = $file['name'];
    $file_parts = pathinfo($filename);
    $name = preg_replace('/[^A-Za-z0-9-\s]/', '', $file_parts['filename']);
    $name = preg_replace('/\s+|-+/', '-', $name);
    $name = strtolower($name);
    $file['name'] = $name . (isset($file_parts['extension']) ? '.' . $file_parts['extension'] : '');

    // Resize image if necessary
    $max_width = 1920;
    if (strpos($file['type'], 'image') === 0) {
        $image = wp_get_image_editor($file['tmp_name']);
        if (!is_wp_error($image)) {
            $size = $image->get_size();
            if ($size['width'] > $max_width) {
                $new_height = (int) (($max_width / $size['width']) * $size['height']);
                $image->resize($max_width, $new_height, false);
                $resized = $image->save();
                if (!is_wp_error($resized) && isset($resized['path'])) {
                    $file['tmp_name'] = $resized['path'];
                }
            }
        }
    }

    return $file;
}
add_filter('wp_handle_upload_prefilter', 'g_ultimateseo_optimize_image_on_upload');

function g_ultimateseo_set_image_properties($attachment_id) {
    if (!wp_attachment_is_image($attachment_id)) return; // Only proceed if it's an image

    $file_path = get_attached_file($attachment_id);
    $file_name = pathinfo($file_path, PATHINFO_FILENAME);
    $sanitized_file_name = preg_replace('/[^A-Za-z0-9-\s]/', '', $file_name);
    $sanitized_file_name = preg_replace('/\s+|-+/', '-', $sanitized_file_name);
    $sanitized_file_name = strtolower($sanitized_file_name);
    $display_name = ucwords(str_replace('-', ' ', $sanitized_file_name));

    update_post_meta($attachment_id, '_wp_attachment_image_alt', sanitize_text_field($display_name));
    $image_post = [
        'ID'           => $attachment_id,
        'post_title'   => sanitize_text_field($display_name),
        'post_excerpt' => sanitize_text_field($display_name),
        'post_content' => sanitize_text_field($display_name),
    ];

    wp_update_post($image_post);
}
add_action('add_attachment', 'g_ultimateseo_set_image_properties');

