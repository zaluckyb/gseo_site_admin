<?php
    // Exit if accessed directly
    if (!defined('ABSPATH')) {
        exit;
    }

    // menu_items/g_seo_settings.php

    // Registers the settings for the plugin
    // Registers plugin settings

    
    function g_ultimateseo_register_settings() {
        // Check if the option already exists. If not, add the default value.
        if (false === get_option('g_ultimateseo_options')) {
            $default_options = array(
                'enable_images' => 1 // Enable the images functionality by default
            );
            add_option('g_ultimateseo_options', $default_options);
        }
        register_setting('g-ultimate-seo-settings', 'g_ultimateseo_all_settings');

    }
    add_action('admin_init', 'g_ultimateseo_register_settings');
    

    function g_ultimateseo_settings_fields() {
        $fields = [
            ['g_ultimateseo_organization_name', 'Organization Name', 'text'],
            ['g_ultimateseo_organization_logo', 'Organization Logo', 'text'],
            ['g_ultimateseo_organization_telephone', 'Organization Telephone', 'text'],
            ['g_ultimateseo_email', 'Organization Email', 'email'],
            ['g_ultimateseo_description', 'Organization Description', 'textarea']
        ];
        
    
        foreach ($fields as $field) {
            add_settings_field(
                $field[0],
                $field[1],
                function() use ($field) { g_ultimateseo_generic_field_callback($field[0], $field[2]); },
                'g-ultimate-seo-settings',
                'g_ultimateseo_main_section'
            );
        }
    }
    function g_ultimateseo_address_fields() {
        $address_fields = [
            ['g_ultimateseo_areaserved', 'Areas Served', 'text'],
            ['g_ultimateseo_street_address', 'Street Address', 'text'],
            ['g_ultimateseo_city_address', 'City Address', 'text'],
            ['g_ultimateseo_state_province_address', 'State/Province Address', 'text'],
            ['g_ultimateseo_country_address', 'Country Address', 'text'],
            ['g_ultimateseo_zip_code_address', 'Zip Code Address', 'text'],
            ['g_ultimateseo_geo_latitude', 'Geo Latitude', 'text'],
            ['g_ultimateseo_geo_longitude', 'Geo Longitude', 'text']
        ];
    
        foreach ($address_fields as $field) {
            add_settings_field(
                $field[0],
                $field[1],
                function() use ($field) { g_ultimateseo_generic_field_callback($field[0], $field[2]); },
                'g-ultimate-seo-settings',
                'g_ultimateseo_address_section'
            );
        }
    }

    function g_ultimateseo_social_media_fields() {
        $social_media_fields = [
            ['g_ultimateseo_facebook_link', 'Facebook Link', 'text'],
            ['g_ultimateseo_twitter_link', 'Twitter Link', 'text'],
            ['g_ultimateseo_instagram_link', 'Instagram Link', 'text'],
            ['g_ultimateseo_linkedin_link', 'Linkedin Link', 'text'],
            ['g_ultimateseo_youtube_link', 'YouTube Link', 'text'],
            ['g_ultimateseo_pinterest_link', 'Pinterest Link', 'text'],
            ['g_ultimateseo_tiktok_link', 'TikTok Link', 'text'],
            ['g_ultimateseo_whatsapp_link', 'WhatsApp Link', 'text'],
            ['g_ultimateseo_telegram_link', 'Telegram Link', 'text'],
            ['g_ultimateseo_snapchat_link', 'Snapchat Link', 'text'],
            ['g_ultimateseo_reddit_link', 'Reddit Link', 'text'],
            ['g_ultimateseo_tumblr_link', 'Tumblr Link', 'text'],
            ['g_ultimateseo_quora_link', 'Quora Link', 'text'],
            ['g_ultimateseo_vimeo_link', 'Vimeo Link', 'text']
        ];
        foreach ($social_media_fields as $field) {
            add_settings_field(
                $field[0],
                $field[1],
                function() use ($field) { g_ultimateseo_generic_field_callback($field[0], $field[2]); },
                'g-ultimate-seo-settings',
                'g_ultimateseo_social_media_section'
            );
        }
    }

    function g_ultimateseo_ceo_fields() {
        $ceo_fields = [
            ['g_ultimateseo_ceo_name', 'CEO Name', 'text'],
            ['g_ultimateseo_ceo_job_title', 'CEO Job Title', 'text'],
            ['g_ultimateseo_ceo_description', 'CEO Description', 'textarea'],
            ['g_ultimateseo_ceo_url', 'CEO URL', 'text'],
            ['g_ultimateseo_ceo_facebook', 'CEO Facebook', 'text'],
            ['g_ultimateseo_ceo_twitter', 'CEO Twitter', 'text'],
            ['g_ultimateseo_ceo_linkedin', 'CEO LinkedIn', 'text'],
            ['g_ultimateseo_ceo_image', 'CEO Image URL', 'text'],
            ['g_ultimateseo_ceo_birthdate', 'CEO Birth Date', 'text'],
            ['g_ultimateseo_ceo_nationality', 'CEO Nationality', 'text'],
            ['g_ultimateseo_ceo_contact', 'CEO Contact Information', 'text'],
            ['g_ultimateseo_ceo_phone', 'CEO Phone Number', 'text'],
            ['g_ultimateseo_ceo_email', 'CEO Email Address', 'email']
        ];
    
        foreach ($ceo_fields as $field) {
            add_settings_field(
                $field[0],
                $field[1],
                function() use ($field) { g_ultimateseo_generic_field_callback($field[0], $field[2]); },
                'g-ultimate-seo-settings',
                'g_ultimateseo_ceo_section'
            );
        }
    }

    function g_ultimateseo_operating_hours_fields() {
        $operating_hours_fields = [
            ['g_ultimateseo_operating_hours', 'Operating Hours', 'text'],
            ['g_ultimateseo_hours_monday', 'Monday Hours', 'text'],
            ['g_ultimateseo_hours_tuesday', 'Tuesday Hours', 'text'],
            ['g_ultimateseo_hours_wednesday', 'Wednesday Hours', 'text'],
            ['g_ultimateseo_hours_thursday', 'Thursday Hours', 'text'],
            ['g_ultimateseo_hours_friday', 'Friday Hours', 'text'],
            ['g_ultimateseo_hours_saturday', 'Saturday Hours', 'text'],
            ['g_ultimateseo_hours_sunday', 'Sunday Hours', 'text']
        ];
    
        foreach ($operating_hours_fields as $field) {
            add_settings_field(
                $field[0],
                $field[1],
                function() use ($field) { g_ultimateseo_generic_field_callback($field[0], $field[2]); },
                'g-ultimate-seo-settings',
                'g_ultimateseo_operating_hours_section'
            );
        }
    }

    function g_ultimateseo_generic_field_callback($option_name, $type) {
        $options = get_option('g_ultimateseo_all_settings');
        $value = isset($options[$option_name]) ? $options[$option_name] : '';
    
        if ($type === 'textarea') {
            echo "<textarea id='{$option_name}' name='g_ultimateseo_all_settings[{$option_name}]' style='width: 100%; max-width: 100%; height: auto; min-height: 100px;'>" . esc_textarea($value) . "</textarea>";
        } elseif ($type === 'checkbox') {
            echo "<input type='checkbox' id='{$option_name}' name='g_ultimateseo_all_settings[{$option_name}]' " . checked(1, $value, false) . " value='1' />";
            // Add a description next to the checkbox
            if ($option_name === 'g_ultimateseo_enable_ga') {
                echo "<label for='{$option_name}' style='margin-left: 10px;'>Only enable this if other plugins like Google Sitekit are not already adding Google Analytics.</label>";
            }
        } else {
            echo "<input type='{$type}' id='{$option_name}' name='g_ultimateseo_all_settings[{$option_name}]' value='" . esc_attr($value) . "' style='width: 100%; max-width: 100%;' />";
        }
    }
    
    function g_ultimateseo_external_seo_fields() {
        $external_seo_fields = [
            ['g_ultimateseo_google_analytics_id', 'Google Analytics ID', 'text'],
            ['g_ultimateseo_enable_ga', 'Enable Google Analytics', 'checkbox'],
            ['g_ultimateseo_google_my_business_link', 'Google My Business Link', 'text'],
            ['g_ultimateseo_bing_webmaster_link', 'Bing Verification Code', 'text'],
            ['g_ultimateseo_google_webmaster_verification_link', 'Google Webmaster Verification Code', 'text'],
            ['g_ultimateseo_twitter_handle', 'Twitter Handle', 'text'],
            ['g_ultimateseo_facebook_app_id', 'Facebook App ID', 'text']
        ];
    
        foreach ($external_seo_fields as $field) {
            add_settings_field(
                $field[0],
                $field[1],
                function() use ($field) { g_ultimateseo_generic_field_callback($field[0], $field[2]); },
                'g-ultimate-seo-settings',
                'g_ultimateseo_external_seo_section'
            );
        }
    }
    
    

    // Function to add the settings page in the WordPress admin area
    function g_ultimate_seo_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
    
        settings_errors('g-ultimate-seo-settings');
    
        // Check if the settings form has been submitted
        if (isset($_POST['g_ultimateseo_all_settings']) && is_array($_POST['g_ultimateseo_all_settings'])) {
            $options = array_map('sanitize_text_field', $_POST['g_ultimateseo_all_settings']);
            update_option('g_ultimateseo_all_settings', $options);
            g_ultimateseo_get_organization_schema();
    
            // Also update individual social media options
            foreach ($options as $key => $value) {
                if (strpos($key, 'g_ultimateseo_') === 0) {
                    update_option($key, $value);
                }
            }
    
            add_settings_error('g-ultimate-seo-settings', 'settings_updated', 'Settings saved.', 'updated');
        }
    
        $options = get_option('g_ultimateseo_all_settings');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="" method="post">
                <?php
                settings_fields('g-ultimate-seo-settings');
                do_settings_sections('g-ultimate-seo-settings');
                submit_button('Save Settings');
                ?>
            </form>
        </div>
        <?php
    }
    
    


    add_action('admin_init', function() {
        add_settings_section(
            'g_ultimateseo_main_section',  // Section ID
            'Main Settings',              // Section title
            'g_ultimateseo_section_callback', // Callback function
            'g-ultimate-seo-settings'     // Page it belongs to
        );
    
        g_ultimateseo_settings_fields(); // Add settings fields

        // Add a new section for external SEO settings
        add_settings_section(
            'g_ultimateseo_external_seo_section',    // External SEO Section ID
            'External SEO Settings',                 // External SEO Section title
            'g_ultimateseo_external_seo_callback',   // Callback function for external SEO section
            'g-ultimate-seo-settings'                // Page the external SEO section belongs to
        );

         // Add external SEO settings fields
        g_ultimateseo_external_seo_fields();

        // Add a new section for address settings
        add_settings_section(
            'g_ultimateseo_address_section',  // Address Section ID
            'Address Settings',              // Address Section title
            'g_ultimateseo_address_callback', // Callback function for address section
            'g-ultimate-seo-settings'        // Page the address section belongs to
        );
        // Add address settings fields
        g_ultimateseo_address_fields();

        // Add a new section for social media
        add_settings_section(
            'g_ultimateseo_social_media_section',  // Social Media Section ID
            'Social Media',                        // Social Media Section title
            'g_ultimateseo_social_media_callback', // Callback function for social media section
            'g-ultimate-seo-settings'              // Page the social media section belongs to
        );
        // Add social media settings fields
        g_ultimateseo_social_media_fields();

        // Add a new section for CEO settings
        add_settings_section(
            'g_ultimateseo_ceo_section',  // CEO Section ID
            'Founder Settings',               // CEO Section title
            'g_ultimateseo_ceo_callback', // Callback function for CEO section
            'g-ultimate-seo-settings'     // Page the CEO section belongs to
        );
        // Add CEO settings fields
        g_ultimateseo_ceo_fields();

        // Add a new section for operating hours settings
        add_settings_section(
            'g_ultimateseo_operating_hours_section',  // Operating Hours Section ID
            'Operating Hours',                       // Operating Hours Section title
            'g_ultimateseo_operating_hours_callback', // Callback function for operating hours section
            'g-ultimate-seo-settings'                // Page the operating hours section belongs to
        );

        // Add operating hours settings fields
        g_ultimateseo_operating_hours_fields();

});

    function g_ultimateseo_section_callback() {
        echo '<hr><p>Main settings for the G Ultimate SEO plugin.</p>';
    }

    function g_ultimateseo_address_callback() {
        echo '<p>Configure address related settings for the G Ultimate SEO plugin.</p>';
    }
    function g_ultimateseo_social_media_callback() {
        echo '<hr><p>Configure social media links for the G Ultimate SEO plugin.</p>';
    }

    function g_ultimateseo_ceo_callback() {
        echo '<hr><p>Configure Founder related settings for the G Ultimate SEO plugin.</p>';
    }
    function g_ultimateseo_operating_hours_callback() {
        echo '<p>Configure operating hours for the G Ultimate SEO plugin.</p>';
    }
    
    function g_ultimateseo_external_seo_callback() {
        echo '<p>Configure external SEO related settings for the G Ultimate SEO plugin.</p>';
    }

    function g_ultimateseo_add_ga_script() {
        $options = get_option('g_ultimateseo_all_settings');
    
        // Check if the Enable Google Analytics option is checked
        if (empty($options['g_ultimateseo_enable_ga'])) {
            return; // Exit the function if GA is not enabled
        }
    
        // Retrieve the Google Analytics ID from the WordPress options table.
        $ga_id = get_option('g_ultimateseo_google_analytics_id');
    
        // Check if the Google Analytics ID is set and is not empty.
        if (!empty($ga_id)) {
            // The script tag for Google Analytics
            ?>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($ga_id); ?>"></script>
<script>
window.dataLayer = window.dataLayer || [];

function gtag() {
    dataLayer.push(arguments);
}
gtag('js', new Date());
gtag('config', '<?php echo esc_attr($ga_id); ?>');
</script>
<?php
        }
    }
    
    add_action('wp_body_open', 'g_ultimateseo_add_ga_script');
    

function g_ultimateseo_add_bing_webmaster_meta_tag() {
    // Retrieve the Bing Verification Code from the WordPress options table
    // $bing_link = get_option('g_ultimateseo_bing_webmaster_link');
    $options = get_option('g_ultimateseo_all_settings');
    $bing_link = isset($options['g_ultimateseo_bing_webmaster_link']) ? $options['g_ultimateseo_bing_webmaster_link'] : '';    
    $google_webmaster_verification_link = isset($options['g_ultimateseo_google_webmaster_verification_link']) ? $options['g_ultimateseo_google_webmaster_verification_link'] : '';


    // Check if the Bing Verification Code is set and not empty
    if (!empty($bing_link)) {
        // Output the meta tag for Bing Webmaster Tools verification
        echo '<meta name="msvalidate.01" content="' . esc_attr($bing_link) . '" />' . "\n";
    }
        // Check if the Google Webmaster link is set and not empty
    if (!empty($google_webmaster_verification_link )) {
        // Output the meta tag for Google Webmaster Tools verification
        echo '<meta name="google-site-verification" content="' . esc_attr($google_webmaster_verification_link) . '" />' . "\n";
    }
}
// Hook the above function into the wp_head action
add_action('wp_head', 'g_ultimateseo_add_bing_webmaster_meta_tag', 10);