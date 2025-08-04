<?php
/**
 * newsletter/newsletter-subscribe.php
 * Secure newsletter subscription with email verification for WordPress.
 */

if (!defined('ABSPATH')) exit;

// Ensure custom role exists at activation
register_activation_hook(__FILE__, 'gfseo_create_pending_subscriber_role');
function gfseo_create_pending_subscriber_role() {
    if (!get_role('pending_subscriber')) {
        add_role('pending_subscriber', 'Pending Subscriber', ['read' => false]);
    }
}

// Check role exists on each load
add_action('init', 'gfseo_check_pending_subscriber_role');
function gfseo_check_pending_subscriber_role() {
    if (!get_role('pending_subscriber')) {
        add_role('pending_subscriber', 'Pending Subscriber', ['read' => false]);
    }
}

// Enqueue AJAX script
add_action('wp_enqueue_scripts', 'gfseo_subscribe_scripts');
function gfseo_subscribe_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script(
        'gfseo-subscribe-form',
        plugins_url('/assets/js/subscribe-form.js?v=1', __FILE__),
        ['jquery'],
        '1.0',
        true
    );

    wp_localize_script('gfseo-subscribe-form', 'gfseoAjax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('gfseo_subscribe_nonce')
    ]);
}

// Shortcode for subscription form
add_shortcode('gfseo_subscribe_form', 'gfseo_subscribe_form_shortcode');
function gfseo_subscribe_form_shortcode() {
    ob_start(); ?>

    <div class="gfseo-subscribe-form-container">
        <h2 class="gfseo-form-heading">Our Newsletter</h2>
        <p class="gfseo-form-subheading">Get our Free monthly newsletter on Virtual Reality</p>
        <form id="gfseo-subscribe-form" class="gfseo-subscribe-form">
            <input type="text" name="gfseo_name" placeholder="First Name" required />
            <input type="text" name="gfseo_surname" placeholder="Surname" required />
            <input type="email" name="gfseo_email" placeholder="Email Address" required />
            <button type="submit">Subscribe</button>
            <div id="gfseo-form-response"></div>
        </form>
    </div>

    <style>
        .gfseo-subscribe-form-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.05);
            font-family: "Inter", sans-serif;
            box-sizing: border-box;
        }

        .gfseo-form-heading {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 24px;
            color: #333333;
            text-align: center;
        }

        .gfseo-form-subheading {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 16px;
            color: #555555;
            text-align: center;
        }

        .gfseo-subscribe-form,
        .gfseo-subscribe-form * {
            box-sizing: border-box;
        }

        .gfseo-subscribe-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
        }

        .gfseo-subscribe-form input[type="text"],
        .gfseo-subscribe-form input[type="email"] {
            width: 100%;
            padding: 12px 15px;
            font-size: 16px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .gfseo-subscribe-form input[type="text"]:focus,
        .gfseo-subscribe-form input[type="email"]:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
            outline: none;
        }

        .gfseo-subscribe-form button {
            width: 100%;
            padding: 12px 20px;
            background-color: #2563eb;
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
        }

        .gfseo-subscribe-form button:hover {
            background-color: #1d4ed8;
            transform: translateY(-2px);
        }

        #gfseo-form-response {
            margin-top: 12px;
            font-size: 14px;
            text-align: center;
        }

        @media(max-width:600px) {
            .gfseo-subscribe-form-container {
                padding: 20px;
            }

            .gfseo-form-heading {
                font-size: 22px;
            }

            .gfseo-form-subheading {
                font-size: 14px;
            }
        }
    </style>

    <?php return ob_get_clean();
}



// AJAX actions for logged-in and logged-out users
add_action('wp_ajax_gfseo_subscribe_user', 'gfseo_subscribe_user_callback');
add_action('wp_ajax_nopriv_gfseo_subscribe_user', 'gfseo_subscribe_user_callback');

function gfseo_subscribe_user_callback() {
    check_ajax_referer('gfseo_subscribe_nonce', 'security');

    $name    = sanitize_text_field($_POST['name']);
    $surname = sanitize_text_field($_POST['surname']);
    $email   = sanitize_email($_POST['email']);

    if (!is_email($email)) {
        wp_send_json(['status' => 'error', 'message' => '❌ Invalid email address.']);
    }

    if (email_exists($email)) {
        wp_send_json(['status' => 'error', 'message' => '⚠️ Email already subscribed.']);
    }

    $username = sanitize_user(current(explode('@', $email)), true);
    if (username_exists($username)) {
        $username .= wp_generate_password(4, false);
    }

    $random_password = wp_generate_password(16, false);

    // Create user as pending subscriber
    $user_id = wp_insert_user([
        'user_login' => $username,
        'user_email' => $email,
        'first_name' => $name,
        'last_name'  => $surname,
        'role'       => 'pending_subscriber',
        'user_pass'  => $random_password
    ]);

    if (is_wp_error($user_id)) {
        wp_send_json(['status' => 'error', 'message' => '❌ Error: ' . $user_id->get_error_message()]);
    }

    // Generate verification link
    $verification_key = wp_generate_password(20, false);
    update_user_meta($user_id, 'gfseo_email_verification_key', $verification_key);

    $verification_link = add_query_arg([
        'gfseo_verify_email' => '1',
        'key' => $verification_key,
        'id' => $user_id
    ], home_url('/'));

    // Send verification email
    $subject = 'Please verify your subscription - ' . get_bloginfo('name');
    $message = "
    <p>Hi " . esc_html($name) . ",</p>
    <p>Thanks for subscribing to " . get_bloginfo('name') . ". Please click below to verify your subscription:</p>
    <p><a href='" . esc_url($verification_link) . "'>Verify My Subscription</a></p>
    <p>If you didn't subscribe, please ignore this email.</p>";

    wp_mail($email, $subject, $message, ['Content-Type: text/html; charset=UTF-8']);

    wp_send_json(['status' => 'success', 'message' => '📧 Check your inbox to verify your subscription!']);
}

// Email verification handler
add_action('init', 'gfseo_handle_email_verification');
function gfseo_handle_email_verification() {
    if (isset($_GET['gfseo_verify_email'], $_GET['key'], $_GET['id'])) {
        $user_id = intval($_GET['id']);
        $key = sanitize_text_field($_GET['key']);

        $stored_key = get_user_meta($user_id, 'gfseo_email_verification_key', true);
        if ($stored_key === $key) {
            $user = new WP_User($user_id);
            if ($user && $user->exists() && in_array('pending_subscriber', $user->roles)) {
                $user->set_role('subscriber');
                delete_user_meta($user_id, 'gfseo_email_verification_key');

                wp_safe_redirect(home_url('/?verified=1'));
                exit;
            }
        }
        wp_safe_redirect(home_url('/?verified=0'));
        exit;
    }
}

// Verification message in footer
add_action('wp_footer', 'gfseo_show_verification_message');
function gfseo_show_verification_message() {
    if (isset($_GET['verified'])) {
        if ($_GET['verified'] == '1') {
            echo '<script>alert("✅ Subscription verified successfully!");</script>';
        } else {
            echo '<script>alert("❌ Verification failed or already verified.");</script>';
        }
    }
}
