<?php
/**
 * This file is to fill the LWP Dashboard Widget with site information.
 */

if (!defined('ABSPATH')) exit;

/*

	Thanks to:

		Jeff Starr's Dashboard Widgets Suite @ https://wordpress.org/plugins/dashboard-widgets-suite/

*/

?>

	<p><?php 
		printf ( __( 'The backend of the <b>%1$s</b> website has been simplified and optimised for company use by %2$s.', 'lean-wp' ),
			esc_attr( get_bloginfo( 'name' ) ),
			__( 'the <b><a href="https://wordpress.org/plugins/lean-wp/" target="_blank">LEAN WP</a></b> plugin', 'lean-wp' )
		);
		?>
	</p>
	<ul>
		<li><?php esc_html_e( 'WP version: ',		'dashboard-widgets-suite' ); echo '<b>' . lwp_get_wp_version() . '</b>'; ?></li>
		<li><?php esc_html_e( 'Active theme: ',		'dashboard-widgets-suite' ); lwp_get_theme_info(); ?></li>
		<li><?php esc_html_e( 'Your IP address: ',	'dashboard-widgets-suite' ); echo '<b>' . lwp_get_user_ip() . '</b>'; ?></li>
	</ul>
	<p><?php 
		printf ( __( 'If you have any questions, please read the documentation first. If that doesn\'t answer your question, you can open an issue on Github.', 'lean-wp' ),
			__( 'the <b><a href="https://so-wp.com/plugin/lean-wp/#premium" target="_blank">premium version</a></b>', 'lean-wp' )
		);
		?>
	</p>
	<p><?php /* will be added once we have released the premium version
		printf ( __( 'If you would like to control the output and settings, we recommend to purchase %s for only &euro;39 per site.', 'lean-wp' ),
			__( 'the <b><a href="https://so-wp.com/plugin/lean-wp/#premium" target="_blank">premium version</a></b>', 'lean-wp' )
		); */
		?>
	</p>
	<?php

// display current WP version number
function lwp_get_wp_version() {

	return get_bloginfo( 'version' );

}

function lwp_get_theme_info() {

	$theme_data = wp_get_theme();

	$theme_info = '<b>' . $theme_data->Name . '</b>' . esc_html__( ', version ', 'lean-wp' ) . '<b>' . $theme_data->Version . '</b>';
	$theme_info = printf( __( '%1$s, version %2$s', 'lean-wp' ),
		'<b>' . $theme_data->Name . '</b>',
		'<b>' . $theme_data->Version . '</b>'
	);

	return $theme_info;

}


function lwp_get_user_ip() {

	if (isset($_SERVER)) {

		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];

		elseif (isset($_SERVER['HTTP_CLIENT_IP'])) $ip_address = $_SERVER['HTTP_CLIENT_IP'];

		elseif (isset($_SERVER['REMOTE_ADDR'])) $ip_address = $_SERVER['REMOTE_ADDR'];

	} else {

		if (getenv('HTTP_X_FORWARDED_FOR')) $ip_address = getenv('HTTP_X_FORWARDED_FOR');

		elseif (getenv('HTTP_CLIENT_IP')) $ip_address = getenv('HTTP_CLIENT_IP');

		elseif (getenv('REMOTE_ADDR')) $ip_address = getenv( 'REMOTE_ADDR' );

	}

	return sanitize_text_field( $ip_address );

}

