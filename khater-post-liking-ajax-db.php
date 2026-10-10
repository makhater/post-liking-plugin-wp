<?php
/**
 * Plugin Name: Khater Post Liking
 * Plugin URI: https://github.com/makhater/post-liking-plugin-wp
 * Description: Logged-in WordPress users can like or dislike posts, with AJAX count updates and an admin activity table.
 * Version: 1.1.0
 * Author: Mohammed Khater
 * Text Domain: mak-post-liking
 * License: GPL-2.0-or-later
 */

if (!defined('ABSPATH')) {
    exit;
}

define('MAK_POST_LIKING_VERSION', '1.1.0');
define('MAK_POST_LIKING_FILE', __FILE__);
define('MAK_POST_LIKING_TABLE_SUFFIX', 'mak_postliking');

require_once __DIR__ . '/post-like-db.php';

function mak_post_liking_table_name() {
    global $wpdb;
    return $wpdb->prefix . MAK_POST_LIKING_TABLE_SUFFIX;
}

function mk_post_liking_activation() {
    global $wpdb;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $table_name = mak_post_liking_table_name();
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table_name} (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        post_id bigint(20) unsigned NOT NULL,
        user_id bigint(20) unsigned NOT NULL,
        like_type tinyint(1) NOT NULL DEFAULT 1,
        like_date datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY post_id (post_id),
        KEY user_id (user_id),
        KEY post_user (post_id,user_id)
    ) {$charset_collate};";

    dbDelta($sql);
}
register_activation_hook(MAK_POST_LIKING_FILE, 'mk_post_liking_activation');

function mak_post_liking_form_shortcode() {
    $post_id = get_the_ID();
    $user_id = get_current_user_id();

    if (!$post_id || !is_singular() || !is_user_logged_in()) {
        return '<p class="unuser">' . esc_html__('Please log in to like or dislike this post.', 'mak-post-liking') . '</p>';
    }

    ob_start();
    include __DIR__ . '/liking-form.php';
    return ob_get_clean();
}
add_shortcode('mak-post-liking_shortcode', 'mak_post_liking_form_shortcode');

function the_liking_form($post_id = 0) {
    $post_id = $post_id ? absint($post_id) : get_the_ID();
    if (!$post_id || !is_user_logged_in()) {
        return;
    }

    $user_id = get_current_user_id();
    include __DIR__ . '/liking-form.php';
}

function mak_post_liking_ajax() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => __('Please log in first.', 'mak-post-liking')], 401);
    }

    check_ajax_referer('post-liking', 'nonce');

    $post_id = isset($_POST['post_id']) ? absint(wp_unslash($_POST['post_id'])) : 0;
    $reaction = isset($_POST['btn']) ? sanitize_key(wp_unslash($_POST['btn'])) : '';
    $active = isset($_POST['btn_liking']) ? sanitize_text_field(wp_unslash($_POST['btn_liking'])) : '';

    if (!$post_id || !get_post($post_id) || !is_string($reaction) || !in_array($reaction, ['like', 'dislike'], true) || !in_array($active, ['true', 'false'], true)) {
        wp_send_json_error(['message' => __('Invalid reaction request.', 'mak-post-liking')], 400);
    }

    if (!is_post_publicly_viewable(get_post($post_id))) {
        wp_send_json_error(['message' => __('This post is not available for reactions.', 'mak-post-liking')], 403);
    }

    $user_id = get_current_user_id();
    $like_type = ($reaction === 'like') ? 1 : 0;

    if ($active === 'true') {
        $saved = mak_upsert_post_reaction($post_id, $user_id, $like_type);
    } else {
        $saved = mak_delete_post_reaction($post_id, $user_id, $like_type);
    }

    if (!$saved) {
        wp_send_json_error(['message' => __('Could not save your reaction. Please try again.', 'mak-post-liking')], 500);
    }

    wp_send_json_success([
        'post_like' => (int) get_post_like_count($post_id, 1),
        'post_dislike' => (int) get_post_like_count($post_id, 0),
        'user_reaction' => mak_get_user_reaction($post_id, $user_id),
    ]);
}
add_action('wp_ajax_post-liking', 'mak_post_liking_ajax');

function mak_post_liking_enqueue_assets() {
    if (!is_singular()) {
        return;
    }

    wp_enqueue_style(
        'mak-post-liking-style',
        plugins_url('assets/css/style.css', __FILE__),
        [],
        MAK_POST_LIKING_VERSION
    );

    wp_enqueue_script(
        'mak-post-liking-script',
        plugins_url('assets/js/ajax-script.js', __FILE__),
        ['jquery'],
        MAK_POST_LIKING_VERSION,
        true
    );

    wp_localize_script('mak-post-liking-script', 'data_ajax', [
        'nonce' => wp_create_nonce('post-liking'),
        'ajax_url' => admin_url('admin-ajax.php'),
        'login_required' => __('Please log in to react to this post.', 'mak-post-liking'),
        'error_message' => __('Something went wrong. Please try again.', 'mak-post-liking'),
    ]);
}
add_action('wp_enqueue_scripts', 'mak_post_liking_enqueue_assets');

function mak_post_liking_admin_menu() {
    add_menu_page(
        __('Post Reactions', 'mak-post-liking'),
        __('Post Reactions', 'mak-post-liking'),
        'manage_options',
        'mak-post-liking',
        'mak_post_liking_admin_page',
        'dashicons-thumbs-up',
        30
    );
}
add_action('admin_menu', 'mak_post_liking_admin_menu');

function mak_post_liking_admin_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to view this page.', 'mak-post-liking'));
    }

    global $wpdb;
    $table_name = mak_post_liking_table_name();
    $rows = $wpdb->get_results("SELECT id, post_id, user_id, like_type, like_date FROM {$table_name} ORDER BY like_date DESC LIMIT 500");

    include __DIR__ . '/post-liking-admin-page.php';
}

function mak_post_liking_delete_reaction() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to perform this action.', 'mak-post-liking'));
    }

    $reaction_id = isset($_POST['reaction_id']) ? absint(wp_unslash($_POST['reaction_id'])) : 0;
    check_admin_referer('mak_delete_reaction_' . $reaction_id);

    if ($reaction_id) {
        global $wpdb;
        $wpdb->delete(mak_post_liking_table_name(), ['id' => $reaction_id], ['%d']);
    }

    wp_safe_redirect(admin_url('admin.php?page=mak-post-liking&deleted=1'));
    exit;
}
add_action('admin_post_mak_delete_post_liking', 'mak_post_liking_delete_reaction');
