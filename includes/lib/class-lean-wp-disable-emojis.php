<?php
/**
 * Based on Ryan Heller's "Disable Emojis" plugin: https://geek.hellyer.kiwi/plugins/disable-emojis/
 * All credits for this plugin go to plugin author: https://geek.hellyer.kiwi/
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

	add_action( 'init', array( $this, 'disable_emojis' ) );

	/**
	 * Disable the emoji's
	 */
	function lwp_disable_emojis() {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );	
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );	
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'tiny_mce_plugins', 'lwp_disable_emojis_tinymce' );
		add_filter( 'wp_resource_hints', 'lwp_disable_emojis_remove_dns_prefetch', 10, 2 );
	}

	/**
	 * Filter function used to remove the tinymce emoji plugin.
	 * 
	 * @param    array  $plugins  
	 * @return   array             Difference betwen the two arrays
	 */
	function lwp_disable_emojis_tinymce( $plugins ) {
		if ( is_array( $plugins ) ) {
			return array_diff( $plugins, array( 'wpemoji' ) );
		} else {
			return array();
		}
	}

	/**
	 * Remove emoji CDN hostname from DNS prefetching hints.
	 *
	 * @param  array  $urls          URLs to print for resource hints.
	 * @param  string $relation_type The relation type the URLs are printed for.
	 * @return array                 Difference betwen the two arrays.
	 */
	function lwp_disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
		if ( 'dns-prefetch' == $relation_type ) {
			/** This filter is documented in wp-includes/formatting.php */
			$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2.2.1/svg/' );
	
			$urls = array_diff( $urls, array( $emoji_svg_url ) );
		}

		return $urls;
	}
