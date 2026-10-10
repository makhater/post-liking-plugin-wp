<?php
if (!defined('ABSPATH')) {
    exit;
}

$post_id = absint($post_id);
$user_id = get_current_user_id();
$like_count = get_post_like_count($post_id, 1);
$dislike_count = get_post_like_count($post_id, 0);
$user_reaction = mak_get_user_reaction($post_id, $user_id);
?>
<div class="mak-plugin" id="mak-post-liking-<?php echo esc_attr($post_id); ?>" data-post-id="<?php echo esc_attr($post_id); ?>">
    <h4><?php esc_html_e('React to this post', 'mak-post-liking'); ?></h4>
    <input type="hidden" class="mak-post-liking-nonce" value="<?php echo esc_attr(wp_create_nonce('post-liking')); ?>">
    <button class="btn likebtn mak-reaction-button <?php echo $user_reaction === 'like' ? 'green' : 'gray'; ?>"
            type="button" data-reaction="like" aria-pressed="<?php echo $user_reaction === 'like' ? 'true' : 'false'; ?>">
        <span class="like-count mak-like-count"><?php echo esc_html(number_format_i18n($like_count)); ?></span>
        <span class="mak-reaction-label"><?php esc_html_e('Like', 'mak-post-liking'); ?></span>
    </button>
    <button class="btn dislikebtn mak-reaction-button <?php echo $user_reaction === 'dislike' ? 'red' : 'gray'; ?>"
            type="button" data-reaction="dislike" aria-pressed="<?php echo $user_reaction === 'dislike' ? 'true' : 'false'; ?>">
        <span class="like-count mak-dislike-count"><?php echo esc_html(number_format_i18n($dislike_count)); ?></span>
        <span class="mak-reaction-label"><?php esc_html_e('Dislike', 'mak-post-liking'); ?></span>
    </button>
    <p class="mak-post-liking-message" role="status" aria-live="polite"></p>
</div>
