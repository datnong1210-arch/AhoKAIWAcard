<?php
/**
 * Nihongo Flashcard Uninstall
 *
 * Fired when the plugin is uninstalled.
 *
 * @package NihongoFlashcard
 */

// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Delete all nihongo_deck posts and their meta
$deck_posts = get_posts(
    array(
        'post_type'      => 'nihongo_deck',
        'posts_per_page' => -1,
        'post_status'    => 'any',
        'fields'         => 'ids',
    )
);

foreach ( $deck_posts as $post_id ) {
    wp_delete_post( $post_id, true );
}

// Delete all nihongo_course posts and their meta
$course_posts = get_posts(
    array(
        'post_type'      => 'nihongo_course',
        'posts_per_page' => -1,
        'post_status'    => 'any',
        'fields'         => 'ids',
    )
);

foreach ( $course_posts as $post_id ) {
    wp_delete_post( $post_id, true );
}

// Drop the progress table
$table_name = $wpdb->prefix . 'nihongo_flashcard_progress';
$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $table_name ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

// Delete options
delete_option( 'nihongo_flashcard_version' );

// Delete user meta for all users
$users = get_users( array( 'fields' => 'ids' ) );
foreach ( $users as $user_id ) {
    delete_user_meta( $user_id, 'nihongo_flashcard_progress' );
}

// Clear any cached data
wp_cache_flush();
