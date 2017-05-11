<?php
/**
 * Based on Kei Nomura's "mypace Remove Comments Feed Link" plugin: https://github.com/mypacecreator/mypace-remove-comments-feed-link
 * All credits for this plugin go to plugin author: http://mypacecreator.net/
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

add_filter( 'feed_links_show_comments_feed', '__return_false' );

add_filter( 'post_comments_feed_link', 'lwp_remove_single_comments_feed_link' );

add_action( 'parse_query', 'lwp_comments_feed_404' );

function lwp_remove_single_comments_feed_link(){
	return;
}

function lwp_comments_feed_404( $object ) {
	if ( $object->is_comment_feed ) {
		wp_die( __( 'Page not found.', 'lean-wp' ), '', array(
			'response'  => 404,
			'back_link' => true,
		));
	}
}
