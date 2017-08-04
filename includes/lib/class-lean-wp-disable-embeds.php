<?php
/**
 * Based on Pascal Birchler's "Disable Embeds" plugin: https://wordpress.org/plugins/disable-embeds/
 * All credits for this plugin go to plugin author: https://pascalbirchler.com
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Disable embeds on init.
 *
 * - Removes the needed query vars.
 * - Disables oEmbed discovery.
 * - Completely removes the related JavaScript.
 *
 * @since 1.0.0
 */
add_action( 'init', 'lwp_disable_embeds_init', 9999 );

function lwp_disable_embeds_init() {
	/* @var WP $wp */
	global $wp;

	// Remove the embed query var.
	$wp->public_query_vars = array_diff( $wp->public_query_vars, array(
		'embed',
	) );

	// Remove the REST API endpoint.
	remove_action( 'rest_api_init', 'wp_oembed_register_route' );

	// Turn off oEmbed auto discovery.
	add_filter( 'embed_oembed_discover', '__return_false' );

	// Don't filter oEmbed results.
	remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );

	// Remove oEmbed discovery links.
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

	// Remove oEmbed-specific JavaScript from the front-end and back-end.
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	add_filter( 'tiny_mce_plugins', 'lwp_disable_embeds_tiny_mce_plugin' );

	// Remove all embeds rewrite rules.
	add_filter( 'rewrite_rules_array', 'lwp_disable_embeds_rewrites' );

	// Remove filter of the oEmbed result before any HTTP requests are made.
	remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );
}


/**
 * Removes the 'wpembed' TinyMCE plugin.
 *
 * @since 1.0.0
 *
 * @param array $plugins List of TinyMCE plugins.
 * @return array The modified list.
 */
function lwp_disable_embeds_tiny_mce_plugin( $plugins ) {
	return array_diff( $plugins, array( 'wpembed' ) );
}

/**
 * Remove all rewrite rules related to embeds.
 *
 * @since 1.0.0
 *
 * @param array $rules WordPress rewrite rules.
 * @return array Rewrite rules without embeds rules.
 */
function lwp_disable_embeds_rewrites( $rules ) {
	foreach ( $rules as $rule => $rewrite ) {
		if ( false !== strpos( $rewrite, 'embed=true' ) ) {
			unset( $rules[ $rule ] );
		}
	}

	return $rules;
}

/**
 * Remove embeds rewrite rules on plugin activation.
 *
 * @since 1.0.0
 */
function lwp_disable_embeds_remove_rewrite_rules() {
	add_filter( 'rewrite_rules_array', 'lwp_disable_embeds_rewrites' );
	flush_rewrite_rules();
}

//register_activation_hook( __FILE__, 'lwp_disable_embeds_remove_rewrite_rules' );

/**
 * Flush rewrite rules on plugin deactivation.
 *
 * @since 1.0.0
 */
function lwp_disable_embeds_flush_rewrite_rules() {
	remove_filter( 'rewrite_rules_array', 'lwp_disable_embeds_rewrites' );
	flush_rewrite_rules();
}

//register_deactivation_hook( __FILE__, 'lwp_disable_embeds_flush_rewrite_rules' );
