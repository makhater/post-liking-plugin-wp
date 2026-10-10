<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <?php if (isset($_GET['deleted']) && '1' === sanitize_text_field(wp_unslash($_GET['deleted']))) : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Reaction deleted.', 'mak-post-liking'); ?></p></div>
    <?php endif; ?>

    <p><?php esc_html_e('Showing up to the 500 most recent reactions.', 'mak-post-liking'); ?></p>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e('Post', 'mak-post-liking'); ?></th>
                <th><?php esc_html_e('User', 'mak-post-liking'); ?></th>
                <th><?php esc_html_e('Reaction', 'mak-post-liking'); ?></th>
                <th><?php esc_html_e('Date', 'mak-post-liking'); ?></th>
                <th><?php esc_html_e('Action', 'mak-post-liking'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($rows)) : ?>
            <tr><td colspan="5"><?php esc_html_e('No reactions have been recorded yet.', 'mak-post-liking'); ?></td></tr>
        <?php else : foreach ($rows as $row) : ?>
            <tr>
                <td><?php echo esc_html(get_the_title($row->post_id) ?: sprintf(__('Post #%d', 'mak-post-liking'), $row->post_id)); ?></td>
                <td><?php echo esc_html(get_the_author_meta('display_name', $row->user_id) ?: sprintf(__('User #%d', 'mak-post-liking'), $row->user_id)); ?></td>
                <td><?php echo (int) $row->like_type === 1 ? esc_html__('Like', 'mak-post-liking') : esc_html__('Dislike', 'mak-post-liking'); ?></td>
                <td><?php echo esc_html($row->like_date); ?></td>
                <td>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <input type="hidden" name="action" value="mak_delete_post_liking">
                        <input type="hidden" name="reaction_id" value="<?php echo esc_attr($row->id); ?>">
                        <?php wp_nonce_field('mak_delete_reaction_' . absint($row->id)); ?>
                        <?php submit_button(__('Delete', 'mak-post-liking'), 'delete small', 'submit', false); ?>
                    </form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
