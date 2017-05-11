<?php
/**
 * Based on Dave McHale's "Disable REST API" plugin: http://www.binarytemplar.com/disable-json-api
 * All credits for this plugin go to plugin author: http://www.binarytemplar.com/
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Forcibly raise an authentication error to the REST API if the user is not logged in
 */
add_filter( 'rest_authentication_errors', 'lwp_DRA_only_allow_logged_in_rest_access' );

/**
 * Returning an authentication error if a user who is not logged in tries to query the REST API
 * @param $access
 * @return WP_Error
 */
function lwp_DRA_only_allow_logged_in_rest_access( $access ) {

	if( ! is_user_logged_in() ) {
        return new WP_Error( 'rest_cannot_access', __( 'Only authenticated users can access the REST API.', 'lean-wp' ), array( 'status' => rest_authorization_required_code() ) );
    }

    return $access;
	
}