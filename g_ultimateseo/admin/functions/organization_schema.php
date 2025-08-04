<?php

// admin/functions/organization_schema.php

function g_ultimateseo_display_schema_table($schema, $level = 0) {
    foreach ($schema as $key => $value) {
        echo '<tr>';
        echo '<td style="border: 1px solid #ddd; padding: 8px;">' . str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level) . htmlspecialchars($key) . '</td>';

        if (is_array($value)) {
            echo '<td style="border: 1px solid #ddd; padding: 8px;">';
            if (array_is_associative($value)) {
                // For associative arrays, print nested properties in a new table
                echo '<table style="width: 100%;">';
                g_ultimateseo_display_schema_table($value, $level + 1);
                echo '</table>';
            } else {
                // For non-associative arrays (simple lists), check if they contain associative arrays
                if (all_elements_are_arrays($value)) {
                    // If all elements are arrays, print each in a new table
                    foreach ($value as $sub_array) {
                        echo '<table style="width: 100%;">';
                        g_ultimateseo_display_schema_table($sub_array, $level + 1);
                        echo '</table><br>';
                    }
                } else {
                    // Otherwise, print the array as a JSON string
                    echo htmlspecialchars(json_encode($value, JSON_PRETTY_PRINT));
                }
            }
            echo '</td>';
        } else {
            echo '<td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($value) . '</td>';
        }
        echo '</tr>';
    }
}

// Helper function to check if an array is associative
function array_is_associative($array) {
    return count(array_filter(array_keys($array), 'is_string')) > 0;
}

// Helper function to check if all elements in an array are arrays
function all_elements_are_arrays($array) {
    return count(array_filter($array, 'is_array')) === count($array);
}



function g_ultimateseo_organization_schema_page() {
    // Security checks
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'g-ultimate-seo'));
    }

    // Retrieve the saved Organization Schema markup
    $schema = get_option('g_ultimateseo_organization_schema');

    // Remove <script> tags and decode the JSON for formatting
    $schema = str_replace(array('<script type="application/ld+json">', '</script>'), '', $schema);
    $schemaArray = json_decode($schema, true); // Decode to array for easy handling
    $formattedSchema = json_encode($schemaArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    // Page layout and styling
    echo '<div style="margin: 20px;">';
    echo '<h1 style="color: #333; font-size: 24px;">' . __('Organization Schema Settings', 'g-ultimate-seo') . '</h1>';

    // Check if the schema is set and display it
    if (!empty($schemaArray)) {
        echo '<table style="border-collapse: collapse; width: 100%;">';
        echo '<tr style="background-color: #f2f2f2;"><th style="border: 1px solid #ddd; padding: 8px;">Property</th><th style="border: 1px solid #ddd; padding: 8px;">Value</th></tr>';

        g_ultimateseo_display_schema_table($schemaArray);

        echo '</table>';

        // Display the schema as preformatted text
        echo '<h2 style="color: #333; font-size: 20px;">' . __('Formatted Schema Markup', 'g-ultimate-seo') . '</h2>';
        echo '<div style="background-color: #f9f9f9; border: 1px solid #ddd; padding: 10px; margin-top: 10px; overflow: auto; white-space: pre-wrap; font-family: monospace;">';
        echo esc_html($formattedSchema);
        echo '</div>';
    } else {
        echo '<p>' . __('No Organization Schema Markup has been set.', 'g-ultimate-seo') . '</p>';
    }
    echo '</div>'; // Close the main container div
}


// Code to ADD Organization Schema
function g_ultimateseo_get_organization_schema() {
    // Retrieve all settings from the single option
    $options = get_option('g_ultimateseo_all_settings');

    // Extract individual settings from the options array
    $organization_name = isset($options['g_ultimateseo_organization_name']) ? $options['g_ultimateseo_organization_name'] : '';
    $organization_logo = isset($options['g_ultimateseo_organization_logo']) ? $options['g_ultimateseo_organization_logo'] : '';
    $organization_telephone = isset($options['g_ultimateseo_organization_telephone']) ? $options['g_ultimateseo_organization_telephone'] : '';
    $email = isset($options['g_ultimateseo_email']) ? $options['g_ultimateseo_email'] : '';
    $description = isset($options['g_ultimateseo_description']) ? $options['g_ultimateseo_description'] : '';
    $facebook_link = isset($options['g_ultimateseo_facebook_link']) ? $options['g_ultimateseo_facebook_link'] : '';
    $twitter_link = isset($options['g_ultimateseo_twitter_link']) ? $options['g_ultimateseo_twitter_link'] : '';
    $linkedin_link = isset($options['g_ultimateseo_linkedin_link']) ? $options['g_ultimateseo_linkedin_link'] : '';
    $instagram_link = isset($options['g_ultimateseo_instagram_link']) ? $options['g_ultimateseo_instagram_link'] : '';
    $areas_served = isset($options['g_ultimateseo_areaserved']) ? $options['g_ultimateseo_areaserved'] : '';
    $street_address = isset($options['g_ultimateseo_street_address']) ? $options['g_ultimateseo_street_address'] : '';
    $operating_hours = isset($options['g_ultimateseo_operating_hours']) ? $options['g_ultimateseo_operating_hours'] : '';
    $latitude = isset($options['g_ultimateseo_geo_latitude']) ? $options['g_ultimateseo_geo_latitude'] : '';
    $longitude = isset($options['g_ultimateseo_geo_longitude']) ? $options['g_ultimateseo_geo_longitude'] : '';
    $city_address = isset($options['g_ultimateseo_city_address']) ? $options['g_ultimateseo_city_address'] : '';
    $state_province_address = isset($options['g_ultimateseo_state_province_address']) ? $options['g_ultimateseo_state_province_address'] : '';
    $country_address = isset($options['g_ultimateseo_country_address']) ? $options['g_ultimateseo_country_address'] : '';
    $zip_code_address = isset($options['g_ultimateseo_zip_code_address']) ? $options['g_ultimateseo_zip_code_address'] : '';
    $ceo_name = isset($options['g_ultimateseo_ceo_name']) ? $options['g_ultimateseo_ceo_name'] : '';
    $ceo_job_title = isset($options['g_ultimateseo_ceo_job_title']) ? $options['g_ultimateseo_ceo_job_title'] : '';
    $ceo_description = isset($options['g_ultimateseo_ceo_description']) ? $options['g_ultimateseo_ceo_description'] : '';
    $ceo_url = isset($options['g_ultimateseo_ceo_url']) ? $options['g_ultimateseo_ceo_url'] : '';
    $ceo_facebook = isset($options['g_ultimateseo_ceo_facebook']) ? $options['g_ultimateseo_ceo_facebook'] : '';
    $ceo_twitter = isset($options['g_ultimateseo_ceo_twitter']) ? $options['g_ultimateseo_ceo_twitter'] : '';
    $ceo_linkedin = isset($options['g_ultimateseo_ceo_linkedin']) ? $options['g_ultimateseo_ceo_linkedin'] : '';
    $ceo_image = isset($options['g_ultimateseo_ceo_image']) ? $options['g_ultimateseo_ceo_image'] : '';
    $ceo_birthdate = isset($options['g_ultimateseo_ceo_birthdate']) ? $options['g_ultimateseo_ceo_birthdate'] : '';
    $ceo_nationality = isset($options['g_ultimateseo_ceo_nationality']) ? $options['g_ultimateseo_ceo_nationality'] : '';
    $ceo_contact = isset($options['g_ultimateseo_ceo_contact']) ? $options['g_ultimateseo_ceo_contact'] : '';
    $social_media_urls = array_filter([$ceo_facebook, $ceo_twitter, $ceo_linkedin]);
    $ceo_phone = isset($options['g_ultimateseo_ceo_phone']) ? $options['g_ultimateseo_ceo_phone'] : '';
    $ceo_email = isset($options['g_ultimateseo_ceo_email']) ? $options['g_ultimateseo_ceo_email'] : '';
    $hours = [
        'Mo' => isset($options['g_ultimateseo_hours_monday']) ? $options['g_ultimateseo_hours_monday'] : '',
        'Tu' => isset($options['g_ultimateseo_hours_tuesday']) ? $options['g_ultimateseo_hours_tuesday'] : '',
        'We' => isset($options['g_ultimateseo_hours_wednesday']) ? $options['g_ultimateseo_hours_wednesday'] : '',
        'Th' => isset($options['g_ultimateseo_hours_thursday']) ? $options['g_ultimateseo_hours_thursday'] : '',
        'Fr' => isset($options['g_ultimateseo_hours_friday']) ? $options['g_ultimateseo_hours_friday'] : '',
        'Sa' => isset($options['g_ultimateseo_hours_saturday']) ? $options['g_ultimateseo_hours_saturday'] : '',
        'Su' => isset($options['g_ultimateseo_hours_sunday']) ? $options['g_ultimateseo_hours_sunday'] : ''
    ];

    $formattedHours = array_filter($hours, function($value) { return !empty($value); });
    foreach ($formattedHours as $day => &$time) {
        $time = "{$day} {$time}";
    }
    unset($time); // Break the reference with the last element

    $localBusinessSchema = '';
if (!empty($formattedHours)) {
    $localBusinessSchema = '"department": {';
    $localBusinessSchema .= '"@type": "LocalBusiness",';
    $localBusinessSchema .= '"openingHours": ["' . implode('", "', $formattedHours) . '"]';
    $localBusinessSchema .= '}';
}

    // Start building the schema markup
    $schema = '<script type="application/ld+json">';
    $schema .= '{';
    $schema .= '"@context": "https://schema.org",';
    $schema .= '"@type": "Organization",';

    if (!empty($organization_name)) {
        $schema .= '"name": "' . esc_attr($organization_name) . '",';
    }
    $schema .= '"url": "' . esc_url(get_site_url()) . '",';
    if (!empty($organization_logo)) {
        $schema .= '"logo": "' . esc_url($organization_logo) . '",';
    }
    if (!empty($email)) {
        $schema .= '"email": "' . esc_attr($email) . '",';
    }
    if (!empty($organization_telephone)) {
        $schema .= '"contactPoint": [{' . PHP_EOL;
        $schema .= '"@type": "ContactPoint",';
        $schema .= '"telephone": "' . esc_attr($organization_telephone) . '",';
        $schema .= '"contactType": "customer service",';
        if (!empty($areas_served)) {
            $areas_served_array = explode(",", $areas_served);
            $schema .= '"areaServed": ' . json_encode($areas_served_array);
        }
        $schema .= '}],';
    }
    if (!empty($description)) {
        $schema .= '"description": "' . esc_attr($description) . '",';
    }

    $schema .= '"address": {';
    $schema .= '"@type": "PostalAddress",';
    $schema .= '"streetAddress": "' . esc_attr($street_address) . '",';
    $schema .= '"addressLocality": "' . esc_attr($city_address) . '",';
    $schema .= '"addressRegion": "' . esc_attr($state_province_address) . '",';
    $schema .= '"postalCode": "' . esc_attr($zip_code_address) . '",';
    $schema .= '"addressCountry": "' . esc_attr($country_address) . '"';
    $schema .= '},';

    if (!empty($latitude) && !empty($longitude)) {
        $schema .= '"location": {';
        $schema .= '"@type": "Place",';
        $schema .= '"geo": {';
        $schema .= '"@type": "GeoCoordinates",';
        $schema .= '"latitude": ' . esc_attr($latitude) . ',';
        $schema .= '"longitude": ' . esc_attr($longitude);
        $schema .= '}';
        $schema .= '}';
    }


    
    // $schema .= '"sameAs": [';
    // Initialize an array to hold social links
    $social_links = [];
    if (!empty($facebook_link)) {
        $social_links[] = esc_url($facebook_link);
    }
    if (!empty($twitter_link)) {
        $social_links[] = esc_url($twitter_link);
    }
    if (!empty($linkedin_link)) {
        $social_links[] = esc_url($linkedin_link);
    }
    if (!empty($instagram_link)) {
        $social_links[] = esc_url($instagram_link);
    }

    // Add 'sameAs' property only if there are social links
    if (!empty($social_links)) {
        $schema .= ', "sameAs": ["' . implode('", "', $social_links) . '"]';
    }
    else {
    }
    
        // Add 'department' only if there are formatted hours
        if (!empty($formattedHours)) {
            $schema .= ', "department": {';
            $schema .= '"@type": "LocalBusiness",';
            // Include name, image, telephone, and address in LocalBusiness
            $schema .= '"name": "' . esc_attr($organization_name) . '",';
            if (!empty($organization_logo)) {
                $schema .= '"image": "' . esc_url($organization_logo) . '",';
            }
            if (!empty($organization_telephone)) {
                $schema .= '"telephone": "' . esc_attr($organization_telephone) . '",';
            }
            $schema .= '"address": {';
            $schema .= '"@type": "PostalAddress",';
            $schema .= '"streetAddress": "' . esc_attr($street_address) . '",';
            $schema .= '"addressLocality": "' . esc_attr($city_address) . '",';
            $schema .= '"addressRegion": "' . esc_attr($state_province_address) . '",';
            $schema .= '"postalCode": "' . esc_attr($zip_code_address) . '",';
            $schema .= '"addressCountry": "' . esc_attr($country_address) . '"';
            $schema .= '},';
            $schema .= '"openingHours": ["' . implode('", "', $formattedHours) . '"]';
            $schema .= '}';
        }

        // Adding Founder/CEO information with URL, social media links, and additional details
        $schema .= ', "founder": {';
            $schema .= '"@type": "Person",';
            $schema .= '"name": "' . esc_attr($ceo_name) . '",';
            $schema .= '"jobTitle": "' . esc_attr($ceo_job_title) . '",';
            $schema .= '"description": "' . esc_attr($ceo_description) . '",';
            if (!empty($ceo_image)) {
                $schema .= '"image": "' . esc_url($ceo_image) . '",';
            }
            if (!empty($ceo_birthdate)) {
                $schema .= '"birthDate": "' . esc_attr($ceo_birthdate) . '",';
            }
            if (!empty($ceo_nationality)) {
                $schema .= '"nationality": "' . esc_attr($ceo_nationality) . '",';
            }
            if (!empty($ceo_contact) || !empty($ceo_phone) || !empty($ceo_email)) {
                $schema .= '"contactPoint": {';
                $schema .= '"@type": "ContactPoint",';
                if (!empty($ceo_contact)) {
                    $schema .= '"name": "' . esc_attr($ceo_contact) . '",';
                }
                if (!empty($ceo_phone)) {
                    $schema .= '"telephone": "' . esc_attr($ceo_phone) . '",';
                }
                if (!empty($ceo_email)) {
                    $schema .= '"email": "' . esc_attr($ceo_email) . '"';
                }
                $schema .= '},';
            }
            $schema .= '"url": "' . esc_url($ceo_url) . '",';
            $schema .= '"sameAs": ' . json_encode(array_map('esc_url', $social_media_urls));
        $schema .= '}';


    $schema .= '}'; // Close the main JSON object
    $schema .= '</script>';
    error_log('Schema before saving: ' . $schema);

    update_option('g_ultimateseo_organization_schema', $schema);

    return $schema;
}

// Code to ADD Organization Schema
function g_ultimateseo_add_organization_schema() {
    // Retrieve the saved schema
    $schema = get_option('g_ultimateseo_organization_schema');
    if (!empty($schema)) {
        echo $schema;
    }
}

add_action('wp_head', 'g_ultimateseo_add_organization_schema', 5);