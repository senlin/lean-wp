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
			__( 'the <b><a href="https://wordpress.org/plugins/lean-wp/" target="_blank">Lean WP</a></b> plugin', 'lean-wp' )
		);
		?>
	</p>
	<ul>
		<li><?php echo lwp_get_cms_version(); ?></li>
		<li><?php esc_html_e( 'Active theme: ', 'lean-wp' ); lwp_get_theme_info(); ?></li>
		<li><?php esc_html_e( 'Your IP address: ', 'lean-wp' ); echo '<b>' . lwp_get_user_ip() . '</b>'; ?></li>
	</ul>
	<p><?php
		printf ( __( 'If you have any questions, please read the <a href="%1$s" target="_blank"><strong>documentation</strong></a> first. If that doesn\'t answer your question, you can open an issue on <a href="%2$s" target="_blank"><strong>Github</strong></a>.', 'lean-wp' ),
			'https://so-wp.com/lean-wp-docs/',
			'https://github.com/senlin/lean-wp/issues/new'
		);
		?>
	</p>
	<?php

// display current WP version number
function lwp_get_cms_version() {

	if ( function_exists( 'classicpress_version' ) ) {
		return esc_html_e( 'ClassicPress version: ', 'lean-wp' ) . '<b>' . classicpress_version() . '</b>';
	} else {
		return esc_html_e( 'WordPress version: ', 'lean-wp' ) . '<b>' . get_bloginfo( 'version' ) . '</b>';
	}

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

