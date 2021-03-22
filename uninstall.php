<?php
// If uninstall not called from WordPress, then exit.
if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

if (current_user_can('delete_plugins') && current_user_can('manage_options')) {
    //Plugin slug.
    $slug = 'subscriber_login_for_youtube';
    
    //Delete rows on usermeta table.
    global $wpdb;

    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM `{$wpdb->usermeta}` WHERE `meta_key` IN ('%1\$s', '%2\$s', '%3\$s', '%4\$s')",
            $slug . '_picture',
            $slug . '_token',
            $slug . '_refresh_token',
            $slug . '_expires_in'
        )
    );

    //Delete options.
    delete_option($slug);

    //Destroy PHP Session.
    session_destroy();
}
