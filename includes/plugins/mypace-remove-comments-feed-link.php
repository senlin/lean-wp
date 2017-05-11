<?php
/*
Plugin Name: mypace Remove Comments Feed Link
Plugin URI: https://github.com/mypacecreator/mypace-remove-comments-feed-link
Description: This plugin will remove comments feed link from header, output only posts feed.
Author: Kei Nomura (mypacecreator)
Version: 1.1
Author URI: http://mypacecreator.net/
*/

$wp_version = get_bloginfo( 'version' );

if ( $wp_version >= '4.4' ) {

	add_filter( 'feed_links_show_comments_feed', '__return_false' ); 

} else { // if ( $wp_version < '4.3.x' )

	if ( !function_exists( 'mypace_output_posts_feed' ) ){
		remove_action( 'wp_head', 'feed_links', 2 );
		function mypace_output_posts_feed( ) {
	?>
	<link rel="alternate" type="<?php echo feed_content_type( 'rss2' ); ?>" title="<?php bloginfo( 'name' ); ?> &raquo; RSS 2.0 Feed" href="<?php bloginfo( 'rss2_url' ); ?>" />
	<?php }
		add_filter( 'wp_head','mypace_output_posts_feed' );
	}
}

if ( !function_exists( 'mypace_remove_single_comments_feed' ) ){
	function mypace_remove_single_comments_feed(){
		return;
	}
	add_filter( 'post_comments_feed_link', 'mypace_remove_single_comments_feed' );
}

if( !function_exists( 'mypace_comments_feed_404' ) ){
	function mypace_comments_feed_404( $object ) {
		if ( $object->is_comment_feed ) {
			wp_die( 'Page not found.', '', array(
				'response'  => 404,
				'back_link' => true,
			));
		}
	}
	add_action( 'parse_query', 'mypace_comments_feed_404' );
}