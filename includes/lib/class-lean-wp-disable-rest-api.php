<?php
/**
 * Based on Jeff Star's "Disable WP REST API" plugin: https://perishablepress.com/disable-wp-rest-api/
 * All credits for this plugin go to plugin author: https://perishablepress.com/
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/*
	Disable REST API link in HTTP headers
	Link: <https://example.com/wp-json/>; rel="https://api.w.org/"
*/
remove_action('template_redirect', 'rest_output_link_header', 11);

/*
	Disable REST API links in HTML <head>
	<link rel='https://api.w.org/' href='https://example.com/wp-json/' />
*/
remove_action('wp_head', 'rest_output_link_wp_head', 10);
remove_action('xmlrpc_rsd_apis', 'rest_output_rsd');

/*
	Disable REST API
*/
if (version_compare(get_bloginfo('version'), '4.7', '>=')) {

	add_filter('rest_authentication_errors', 'lwp_disable_wp_rest_api');

} else {

	lwp_disable_wp_rest_api_legacy();

}

function lwp_disable_wp_rest_api($access) {

	if (!is_user_logged_in()) {

		$message = apply_filters('disable_wp_rest_api_error', __('Only authenticated users can access the REST API.', 'lean-wp'));

		return new WP_Error('rest_login_required', $message, array('status' => rest_authorization_required_code()));

	}

	return $access;

}

function lwp_disable_wp_rest_api_legacy() {

    // REST API 1.x
    add_filter('json_enabled', '__return_false');
    add_filter('json_jsonp_enabled', '__return_false');

    // REST API 2.x
    add_filter('rest_enabled', '__return_false');
    add_filter('rest_jsonp_enabled', '__return_false');

}