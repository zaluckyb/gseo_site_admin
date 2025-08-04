<?php
// admin/functions/posts_schema.php
function g_ultimateseo_post_schema_page() {
    // Security checks
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'g-ultimate-seo'));
    }

    // Fetch the latest post
    $args = array(
        'posts_per_page' => 1,
        'post_type' => 'post',
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
    );
    $latest_posts = get_posts($args);

    // Start page layout
    echo '<div style="margin: 20px;">';
    echo '<h1 style="color: #333; font-size: 24px;">' . __('G Ultimate SEO - Post Schema Settings', 'g-ultimate-seo') . '</h1>';
    echo '<p>You can use this to validate your structure markup: <a href="https://developers.google.com/search/docs/appearance/structured-data" target="_blank">Structured Data</a></p>';

    if (count($latest_posts) > 0) {
        $post = $latest_posts[0];

        // Get essential details
        $author_name = get_the_author_meta('display_name', $post->post_author);
        $author_url = get_author_posts_url($post->post_author);
        $post_title = get_the_title($post->ID);
        $post_url = get_permalink($post->ID);
        $post_content = apply_filters('the_content', $post->post_content);
        $post_description = wp_trim_words(wp_strip_all_tags($post_content), 55, '...');

        // Check if the post has a featured image
        $featured_image_url = '';
        if (has_post_thumbnail($post->ID)) {
            $featured_img_array = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
            $featured_image_url = $featured_img_array[0] ?? '';
        }

        // Schema data
        $schema_data = array(
            "@context" => "https://schema.org",
            "@type" => "Article",
            "mainEntityOfPage" => array(
                "@type" => "WebPage",
                "@id" => $post_url
            ),
            "headline" => $post_title,
            "description" => $post_description,
            "datePublished" => get_the_date('c', $post->ID),
            "dateModified" => get_the_modified_date('c', $post->ID),
            "author" => array(
                "@type" => "Person",
                "name" => $author_name,
                "url" => $author_url
            ),
            "image" => $featured_image_url,
            "articleBody" => wp_strip_all_tags($post_content)
        );

        // Display the schema in a readable format
        echo '<h2 style="color: #333; font-size: 20px;">Post Schema for the Latest Post</h2>';
        echo '<table style="border-collapse: collapse; width: 100%;">';
        foreach ($schema_data as $key => $value) {
            echo '<tr style="background-color: #f2f2f2;"><th style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($key) . '</th>';
            if (is_array($value)) {
                echo '<td style="border: 1px solid #ddd; padding: 8px;"><table style="border-collapse: collapse; width: 100%;">';
                foreach ($value as $sub_key => $sub_value) {
                    echo '<tr><td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($sub_key) . '</td>';
                    echo '<td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($sub_value) . '</td></tr>';
                }
                echo '</table></td>';
            } else {
                echo '<td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($value) . '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';

        echo '<h2 style="color: #333; font-size: 20px;">This is an example of what the Schema Markup looks like for the Latest Post</h2>';
        echo '<div style="background-color: #f9f9f9; border: 1px solid #ddd; padding: 10px; margin-top: 10px; overflow: auto; white-space: pre-wrap; font-family: monospace;">';
        echo htmlspecialchars(json_encode($schema_data, JSON_PRETTY_PRINT));
        echo '</div>';
    } else {
        echo '<p>No posts found to display schema.</p></div>';
    }

    echo '</div>'; // Close the main container div
}

function add_schema_to_wp_head() {
    if (is_single() && 'post' == get_post_type()) {  // Ensure it's a single post page
        global $post;
        setup_postdata($post);

        // Basic post information
        $author_name = get_the_author_meta('display_name', $post->post_author);
        $author_url = get_author_posts_url($post->post_author);
        $post_title = get_the_title($post->ID);
        $post_url = get_permalink($post->ID);

        // Featured image processing
        $featured_image_url = '';
        if (has_post_thumbnail($post->ID)) {
            $featured_img_array = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
            $featured_image_url = $featured_img_array[0] ?? '';
        }

        // Cleaned post content for description
        $post_content = apply_filters('the_content', $post->post_content);
        $post_content_clean = wp_strip_all_tags($post_content);
        $post_description = wp_trim_words($post_content_clean, 55, '...');

        // Get the Twitter handle from the plugin settings
        $options = get_option('g_ultimateseo_all_settings');
        $twitter_handle = isset($options['g_ultimateseo_twitter_handle']) ? $options['g_ultimateseo_twitter_handle'] : '@default_handle';

        // JSON-LD Schema
        $schema_data = array(
            "@context" => "https://schema.org",
            "@type" => "Article",
            "headline" => $post_title,
            "datePublished" => get_the_date('c', $post->ID),
            "dateModified" => get_the_modified_date('c', $post->ID),
            "author" => array(
                "@type" => "Person",
                "name" => $author_name,
                "url" => $author_url
            ),
            "image" => $featured_image_url,
            "articleBody" => $post_content_clean
        );
        echo '<script type="application/ld+json">' . json_encode($schema_data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';

        // Open Graph Tags
        echo '<meta property="og:title" content="' . esc_attr($post_title) . '" />';
        echo '<meta property="og:type" content="article" />';
        echo '<meta property="og:url" content="' . esc_url($post_url) . '" />';
        echo '<meta property="og:image" content="' . esc_url($featured_image_url) . '" />';
        echo '<meta property="og:description" content="' . esc_attr($post_description) . '" />';

        // Twitter Card Tags
        echo '<meta name="twitter:card" content="summary_large_image">';
        echo '<meta name="twitter:site" content="' . esc_attr($twitter_handle) . '">';  // Dynamically set Twitter handle
        echo '<meta name="twitter:title" content="' . esc_attr($post_title) . '">';
        echo '<meta name="twitter:description" content="' . esc_attr($post_description) . '">';
        echo '<meta name="twitter:image" content="' . esc_url($featured_image_url) . '">';

        wp_reset_postdata();
    }
}
add_action('wp_head', 'add_schema_to_wp_head');

