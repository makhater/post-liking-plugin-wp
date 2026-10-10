(function ($) {
    'use strict';

    $(document).on('click', '.mak-reaction-button', function () {
        var $button = $(this);
        var $container = $button.closest('.mak-plugin');
        var $buttons = $container.find('.mak-reaction-button');
        var $message = $container.find('.mak-post-liking-message');
        var reaction = $button.data('reaction');
        var currentlyActive = $button.attr('aria-pressed') === 'true';
        var requestedState = currentlyActive ? 'false' : 'true';

        if (!window.data_ajax || !data_ajax.ajax_url || !data_ajax.nonce) {
            $message.text('Unable to send the reaction right now.');
            return;
        }

        $buttons.prop('disabled', true);
        $message.text('');

        $.ajax({
            url: data_ajax.ajax_url,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'post-liking',
                nonce: $container.find('.mak-post-liking-nonce').val() || data_ajax.nonce,
                post_id: $container.data('post-id'),
                btn: reaction,
                btn_liking: requestedState
            }
        }).done(function (response) {
            if (!response || response.success !== true || !response.data) {
                $message.text((response && response.data && response.data.message) || data_ajax.error_message || 'Could not save reaction.');
                return;
            }

            $container.find('.mak-like-count').text(response.data.post_like);
            $container.find('.mak-dislike-count').text(response.data.post_dislike);

            $buttons.each(function () {
                var $current = $(this);
                var currentReaction = $current.data('reaction');
                var isActive = response.data.user_reaction === currentReaction;
                $current.attr('aria-pressed', isActive ? 'true' : 'false');
                $current.toggleClass('green', currentReaction === 'like' && isActive);
                $current.toggleClass('red', currentReaction === 'dislike' && isActive);
                $current.toggleClass('gray', !isActive);
            });
        }).fail(function (xhr) {
            var message = data_ajax.error_message || 'Something went wrong. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                message = xhr.responseJSON.data.message;
            }
            $message.text(message);
        }).always(function () {
            $buttons.prop('disabled', false);
        });
    });
})(jQuery);
