<?php
if (!defined('ABSPATH')) {
    exit;
}

function mak_get_user_reaction($post_id, $user_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mak_postliking';
    $reaction = $wpdb->get_var($wpdb->prepare(
        "SELECT like_type FROM {$table_name} WHERE post_id = %d AND user_id = %d ORDER BY id DESC LIMIT 1",
        absint($post_id),
        absint($user_id)
    ));
    if ($reaction === null) {
        return null;
    }
    return ((int) $reaction === 1) ? 'like' : 'dislike';
}

function mak_upsert_post_reaction($post_id, $user_id, $like_type) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mak_postliking';
    $post_id = absint($post_id);
    $user_id = absint($user_id);
    $like_type = (int) $like_type;

    if (!$post_id || !$user_id || !in_array($like_type, [0, 1], true)) {
        return false;
    }

    $existing_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT id FROM {$table_name} WHERE post_id = %d AND user_id = %d ORDER BY id ASC",
        $post_id,
        $user_id
    ));

    if (!empty($existing_ids)) {
        $primary_id = absint(array_shift($existing_ids));
        $updated = $wpdb->update(
            $table_name,
            ['like_type' => $like_type, 'like_date' => current_time('mysql')],
            ['id' => $primary_id],
            ['%d', '%s'],
            ['%d']
        );
        foreach ($existing_ids as $duplicate_id) {
            $wpdb->delete($table_name, ['id' => absint($duplicate_id)], ['%d']);
        }
        return $updated !== false;
    }

    return $wpdb->insert(
        $table_name,
        [
            'post_id' => $post_id,
            'user_id' => $user_id,
            'like_type' => $like_type,
            'like_date' => current_time('mysql'),
        ],
        ['%d', '%d', '%d', '%s']
    ) !== false;
}

function mak_delete_post_reaction($post_id, $user_id, $like_type = null) {
    global $wpdb;
    $where = ['post_id' => absint($post_id), 'user_id' => absint($user_id)];
    $formats = ['%d', '%d'];
    if ($like_type !== null) {
        $where['like_type'] = (int) $like_type;
        $formats[] = '%d';
    }
    return $wpdb->delete($wpdb->prefix . 'mak_postliking', $where, $formats) !== false;
}

function get_post_like_count($post_id, $type) {
    global $wpdb;
    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}mak_postliking WHERE post_id = %d AND like_type = %d",
        absint($post_id),
        (int) $type
    ));
}

function select_post_like_user($post_id, $user_id, $type) {
    global $wpdb;
    return $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}mak_postliking WHERE post_id = %d AND user_id = %d AND like_type = %d ORDER BY id DESC LIMIT 1",
        absint($post_id),
        absint($user_id),
        (int) $type
    ));
}
