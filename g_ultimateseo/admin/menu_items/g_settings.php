<?php
    // Exit if accessed directly
    if (!defined('ABSPATH')) {
        exit;
    }

    // admin/menu_items/g_settings.php

    function g_settings_page() {
        ?>
        <div class="wrap">
            <h1>Activation Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('g_ultimateseo_settings');
                do_settings_sections('g_ultimateseo_settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    function g_ultimateseo_register_general_settings() {

        register_setting('g_ultimateseo_settings', 'g_ultimateseo_options');
        register_setting('g_ultimateseo_settings', 'g_ultimateseo_organization_schema');
    
        add_settings_section(
            'g_ultimateseo_main_section',
            'Main Settings',
            'g_ultimateseo_section_text',
            'g_ultimateseo_settings'
        );
    
        add_settings_field(
            'g_ultimateseo_enable_images',
            'Enable Image Functions',
            'g_ultimateseo_enable_images_callback',
            'g_ultimateseo_settings',
            'g_ultimateseo_main_section'
        );
        add_settings_field(
            'g_ultimateseo_enable_comments',
            'Disable Comments',
            'g_ultimateseo_enable_comments_callback',
            'g_ultimateseo_settings',
            'g_ultimateseo_main_section'
        );
        add_settings_field(
            'g_ultimateseo_enable_schema',
            'Enable JSON-LD Schema for Posts',
            'g_ultimateseo_enable_schema_callback',
            'g_ultimateseo_settings',
            'g_ultimateseo_main_section'
        );
        add_settings_field(
            'g_ultimateseo_enable_organization_schema',
            'Enable JSON-LD Schema for Organization',
            'g_ultimateseo_enable_organization_schema_callback',
            'g_ultimateseo_settings',
            'g_ultimateseo_main_section'
        );
        add_settings_field(
            'g_ultimateseo_enable_emoji',
            'Disable Emojis',
            'g_ultimateseo_enable_emoji_callback',
            'g_ultimateseo_settings',
            'g_ultimateseo_main_section'
        );
        add_settings_field(
            'g_ultimateseo_enable_page_visits',
            'Enable Page Visits',
            'g_ultimateseo_enable_page_visits_callback',
            'g_ultimateseo_settings',
            'g_ultimateseo_main_section'
        );
    }
    add_action('admin_init', 'g_ultimateseo_register_general_settings');

    
    function g_ultimateseo_section_text() {
        echo '<p>Here you can enable or disable specific functionalities of the G_UltimateSEO plugin.</p>';
    }
    
    function g_ultimateseo_enable_images_callback() {
        $options = get_option('g_ultimateseo_options');
        $checked = isset($options['enable_images']) && $options['enable_images'] == 1 ? 'checked' : '';
        echo "<input type='checkbox' id='enable_images' name='g_ultimateseo_options[enable_images]' value='1' $checked />";
    }
    // Callback for the comments toggle
    function g_ultimateseo_enable_comments_callback() {
        $options = get_option('g_ultimateseo_options');
        $checked = isset($options['enable_comments']) && $options['enable_comments'] == 1 ? 'checked' : '';
        echo "<input type='checkbox' id='enable_comments' name='g_ultimateseo_options[enable_comments]' value='1' $checked />";
    }

    function g_ultimateseo_enable_schema_callback() {
        $options = get_option('g_ultimateseo_options');
        $checked = isset($options['enable_schema']) && $options['enable_schema'] == 1 ? 'checked' : '';
        echo "<input type='checkbox' id='enable_schema' name='g_ultimateseo_options[enable_schema]' value='1' $checked />";
    }
    function g_ultimateseo_enable_organization_schema_callback() {
        $options = get_option('g_ultimateseo_options');
        $checked = isset($options['enable_organization_schema']) && $options['enable_organization_schema'] == 1 ? 'checked' : '';
        echo "<input type='checkbox' id='enable_organization_schema' name='g_ultimateseo_options[enable_organization_schema]' value='1' $checked />";
    }

    function g_ultimateseo_enable_emoji_callback() {
        $options = get_option('g_ultimateseo_options');
        $checked = isset($options['enable_emoji']) && $options['enable_emoji'] == 1 ? 'checked' : '';
        echo "<input type='checkbox' id='enable_emoji' name='g_ultimateseo_options[enable_emoji]' value='1' $checked />";
    }
    function g_ultimateseo_enable_page_visits_callback() {
        $options = get_option('g_ultimateseo_options');
        $checked = isset($options['enable_page_visits']) && $options['enable_page_visits'] == 1 ? 'checked' : '';
        echo "<input type='checkbox' id='enable_page_visits' name='g_ultimateseo_options[enable_page_visits]' value='1' $checked />";
    }


    add_action('admin_init', 'g_ultimateseo_register_settings');
    
    