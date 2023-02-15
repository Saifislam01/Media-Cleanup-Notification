<?php
/*
Plugin Name: Media Cleanup Notification
Plugin URI: http://portfolio.dainikajkermeghna.com
Description: Provides notifications for deleting unused images from the media folder.
Version: 1.0
Author: Saiful Islam
Author URI: http://portfolio.dainikajkermeghna.com
*/

// Activate the plugin
function media_cleanup_notification_activate() {
    // add activation code here
}
register_activation_hook( __FILE__, 'media_cleanup_notification_activate' );



function media_cleanup_notification_activate() {
    add_option( 'media_cleanup_notification_last_sent', time() );
}


function media_cleanup_notification_schedule() {
    if ( ! wp_next_scheduled( 'media_cleanup_notification_cron' ) ) {
        wp_schedule_event( time(), 'daily', 'media_cleanup_notification_cron' );
    }
}
add_action( 'wp', 'media_cleanup_notification_schedule' );


function media_cleanup_notification_cron() {
    // get the last time the notification was sent
    $last_sent = get_option( 'media_cleanup_notification_last_sent' );

    // check if it's been more than a week since the last notification was sent
    if ( time() - $last_sent >= 7 * 24 * 60 * 60 ) {
        // get all the images in the media folder
        $images = get_posts( array(
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => -1,
        ) );

        // check each image to see if it's being used
        foreach ( $images as $image ) {
            if ( ! get_post_meta( $image->ID, '_wp_attachment_wpse1234', true ) ) {
                // the image is not being used, send the cleanup notification
                $subject = 'Unused images in your media folder';
                $message = 'There are unused images in your media folder that can be safely deleted.';
                wp_mail( get_option( 'admin_email' ), $subject, $message );
                // update the last time the notification was sent
                update_option( 'media_cleanup_notification_last_sent', time() );
                break; // stop checking images once an unused image is found
            }
        }
    }
}

// Deactivate the plugin
function media_cleanup_notification_deactivate() {
    wp_clear_scheduled_hook( 'media_cleanup_notification_cron' );
    delete_option( 'media_cleanup_notification_last_sent' );
}
register_deactivation_hook( __FILE__, 'media_cleanup_notification_de



