<?php
if (!defined('ABSPATH')) exit;

// admin/pages/siteauthors.php

function gseo_render_authors_page() {
    global $wpdb;
    $authors_table = $wpdb->prefix . 'gusm_authors';
    $author_content_table = $wpdb->prefix . 'gusm_author_content';
    $sites_table = $wpdb->prefix . 'gusm_sites';

    // Handle form submissions (Add Author)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gusm_add_author'])) {
        check_admin_referer('gusm_add_author');

        $name = sanitize_text_field($_POST['author_name']);
        $emails = sanitize_textarea_field($_POST['author_emails']);

        if (!empty($name) && !empty($emails)) {
            $wpdb->insert($authors_table, [
                'name' => $name,
                'emails' => $emails
            ]);

            echo '<div class="notice notice-success"><p>Author successfully added!</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Please provide both Name and Emails.</p></div>';
        }
    }

    // Handle Delete Author
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_author'])) {
        check_admin_referer('gusm_delete_author');

        $author_id = intval($_POST['author_id']);
        $wpdb->delete($authors_table, ['id' => $author_id]);
        $wpdb->delete($author_content_table, ['author_id' => $author_id]);

        echo '<div class="notice notice-success"><p>Author deleted successfully!</p></div>';
    }

    // Fetch authors along with their associated sites and roles
    $authors = $wpdb->get_results("
        SELECT DISTINCT
            a.id as author_id,
            s.url as site_url,
            a.name as author_name,
            a.emails as author_emails,
            ac.role as author_role
        FROM $author_content_table ac
        INNER JOIN $authors_table a ON ac.author_id = a.id
        INNER JOIN $sites_table s ON ac.site_id = s.id
        ORDER BY s.url, a.name ASC
    ");
    ?>

    <div class="wrap">
        <h1>👥 Manage Authors</h1>
        <button id="gusm-sync-authors-btn" class="button button-primary">
            Sync Authors from Sites
        </button>

        <!-- Add Author Form -->
        <form method="post" style="margin-bottom: 20px; padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 5px;">
            <?php wp_nonce_field('gusm_add_author'); ?>
            <h2>Add New Author</h2>
            <table class="form-table">
                <tr>
                    <th><label for="author_name">Author Name</label></th>
                    <td><input type="text" id="author_name" name="author_name" required class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="author_emails">Emails (comma-separated)</label></th>
                    <td><textarea id="author_emails" name="author_emails" required class="regular-text" rows="3"></textarea></td>
                </tr>
            </table>
            <?php submit_button('Add Author', 'primary', 'gusm_add_author'); ?>
        </form>

        <!-- Existing Authors with associated sites and roles -->
        <h2>Current Authors (By Site)</h2>
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th style="width:5%">ID</th>
                    <th style="width:30%">Site URL</th>
                    <th style="width:20%">Name</th>
                    <th style="width:20%">Emails</th>
                    <th style="width:15%">Role</th>
                    <th style="width:10%">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($authors): ?>
                    <?php foreach ($authors as $author): ?>
                        <tr>
                            <td><?php echo esc_html($author->author_id); ?></td>
                            <td><?php echo esc_html($author->site_url); ?></td>
                            <td><?php echo esc_html($author->author_name); ?></td>
                            <td><?php echo esc_html($author->author_emails); ?></td>
                            <td><?php echo esc_html(ucfirst($author->author_role ?? '')); ?></td>
                            <td>
                                <form method="post" onsubmit="return confirm('Are you sure you want to delete this author?');">
                                    <?php wp_nonce_field('gusm_delete_author'); ?>
                                    <input type="hidden" name="author_id" value="<?php echo esc_attr($author->author_id); ?>">
                                    <?php submit_button('Delete', 'small', 'delete_author', false, ['style'=>'color:#a00;']); ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">No authors added yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php
}