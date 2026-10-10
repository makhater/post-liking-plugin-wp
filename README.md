# Khater Post Liking

A WordPress plugin project that lets logged-in users like or dislike a post. It uses AJAX to update counts and stores one current reaction per user and post.

## Features

- Like and dislike buttons.
- AJAX-based reaction updates without a full page reload.
- Per-post reaction counts.
- Reaction records stored in a custom WordPress database table.
- Shortcode output for singular posts.
- Admin page for viewing and deleting recent reaction records.
- Nonce checks and server-side user identity for authenticated AJAX requests.
- Prepared SQL queries for database reads.

## Installation

1. Download or clone this repository.
2. Place the plugin folder in `wp-content/plugins/`.
3. In WordPress Admin, open **Plugins** and activate **Khater Post Liking**.
4. Add the shortcode below to a post or a suitable template area.

## Shortcode

Use this shortcode in post content:

```text
[mak-post-liking_shortcode]
```

The buttons are available to logged-in users. Visitors who are not logged in see a login-required message.

## Admin page

After activation, administrators can open **Post Reactions** in the WordPress dashboard to review the most recent reaction records and delete individual records.

## Technical notes

- Main plugin file: `khater-post-liking-ajax-db.php`
- Database helper: `post-like-db.php`
- Reaction UI: `liking-form.php`
- Admin table: `post-liking-admin-page.php`
- AJAX script: `assets/js/ajax-script.js`
- Styles: `assets/css/style.css`

The plugin uses the current WordPress user ID on the server rather than trusting a user ID submitted by the browser. Database queries use prepared statements where values are inserted into SQL.

## Testing checklist

Test in a disposable WordPress installation before using on a production website:

1. Activate the plugin and confirm the custom table is created.
2. Add the shortcode to a published post.
3. As a logged-in user, click Like and confirm the count updates without reloading.
4. Click Like again and confirm the reaction is removed.
5. Switch from Like to Dislike and confirm the user's reaction changes rather than creating a second reaction.
6. Repeat with a second account and confirm each account has its own reaction.
7. Confirm logged-out visitors cannot submit reactions.
8. Open **Post Reactions** as an administrator and verify the reaction type, post, user, and date.
9. Delete a reaction and confirm the row is removed.
10. Check the browser console and WordPress/PHP logs for errors.

## Current status

This is a development project. The source has received an initial security and correctness pass, but it has not been confirmed through a live WordPress integration test in this repository workflow. Test it on a staging site before production use.

## License

GPL-2.0-or-later.
