<?php
/*
Plugin Name: Media Cleanup Notification
Plugin URI: https://saifuldevs.com/
Description: Provides notifications for deleting unused images from the media folder.
Version: 1.0
Author: Saiful Islam
Author URI: https://saifuldevs.com/
*/

// Enqueue JavaScript and CSS files
function unused_images_cleanup_enqueue_scripts() {
    wp_enqueue_script('unused-images-cleanup-script', plugin_dir_url(__FILE__) . 'js/script.js', array('jquery'), '1.0', true);
    wp_enqueue_style('unused-images-cleanup-style', plugin_dir_url(__FILE__) . 'css/style.css', array(), '1.0');
}
add_action('admin_enqueue_scripts', 'unused_images_cleanup_enqueue_scripts');

// Add settings page
function unused_images_cleanup_add_settings_page() {
    add_options_page('Unused Images Cleanup', 'Unused Images Cleanup', 'manage_options', 'unused-images-cleanup', 'unused_images_cleanup_settings_page');
}
add_action('admin_menu', 'unused_images_cleanup_add_settings_page');

// Display settings page
function unused_images_cleanup_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1>Unused Images Cleanup</h1>
        <p>Click the "Scan" button to scan your media folder for unused images.</p>
        <button id="unused-images-scan-button" class="button">Scan</button>
        <div id="unused-images-scan-results"></div>
    </div>
    <?php
}

// AJAX callback function for scanning unused images
function unused_images_cleanup_scan_callback() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    $media_folder = wp_upload_dir()['basedir'];

    $attachments = get_posts(array(
        'post_type'      => 'attachment',
        'posts_per_page' => -1,
        'post_status'    => 'inherit',
    ));

    $used_images = array();
    foreach ($attachments as $attachment) {
        $attachment_metadata = wp_get_attachment_metadata($attachment->ID);
        if ($attachment_metadata) {
            if (isset($attachment_metadata['file'])) {
                $file = $attachment_metadata['file'];
                $used_images[] = $media_folder . '/' . $file;
            }
            if (isset($attachment_metadata['sizes'])) {
                foreach ($attachment_metadata['sizes'] as $size) {
                    if (isset($size['file'])) {
                        $file = $size['file'];
                        $used_images[] = $media_folder . '/' . $file;
                    }
                }
            }
        }
    }

    $all_images = glob($media_folder . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    $unused_images = array_diff($all_images, $used_images);

    wp_send_json_success(array(
        'total_images'   => count($all_images),
        'unused_images'  => count($unused_images),
        'unused_list'    => $unused_images,
    ));
}
add_action('wp_ajax_unused_images_cleanup_scan', 'unused_images_cleanup_scan_callback');

// AJAX callback function for deleting unused images
function unused_images_cleanup_delete_callback() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    $images_to_delete = $_POST['images'];

    $deleted_count = 0;
    foreach ($images_to_delete as $image) {
        if (file_exists($image)) {
            if (unlink($image)) {
                $deleted_count++;
            }
        }
    }

    wp_send_json_success(array('deleted_count' => $deleted_count));
}
add_action('wp_ajax_unused_images_cleanup_delete', 'unused_images_cleanup_delete_callback');
