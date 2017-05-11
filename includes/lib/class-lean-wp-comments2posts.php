<?php
/**
 * Based on John James Jacoby's "WP Comment Humility" plugin: https://wordpress.org/plugins/wp-comment-humility/
 * Original plugin has not been updated for > 2 years
 * All credits for this plugin go to plugin author: https://profiles.wordpress.org/johnjamesjacoby/
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Actions
add_action( 'admin_menu',             'lwp_comments2posts' );
add_action( 'admin_head-comment.php', 'lwp_comments2posts_modify_admin_menu_highlight' );

/**
 * Unset top level "Comments" menu and related submenus, then recreate it under
 * the "Posts" top level menu item.
 *
 * @since 1.0.0
 */
function lwp_comments2posts() {

	// Look for
	$comments_menu = lwp_comments2posts_get_menu_index_by_slug( 'edit-comments.php' );

	// No comments
	if ( false !== $comments_menu ) {

		// Unset top level menu
		unset( $GLOBALS['menu'][ $comments_menu ], $GLOBALS['submenu'][ 'edit-comments.php' ] );

		// Move comments to underneath "Posts"
		$awaiting_mod = wp_count_comments();
		$awaiting_mod = $awaiting_mod->moderated;
		$GLOBALS['submenu']['edit.php'][9] = array( sprintf( __( 'Comments %s' ), "<span class='awaiting-mod count-{$awaiting_mod}'><span class='pending-count'>" . number_format_i18n( $awaiting_mod ) . '</span></span>' ), 'edit_posts', 'edit-comments.php' );
	}
}

/**
 * Get a global menu index
 *
 * @since 1.0.0
 *
 * @param type $location
 *
 * @return mixed
 */
function lwp_comments2posts_get_menu_index_by_slug( $location = '' ) {
	foreach ( $GLOBALS['menu'] as $index => $menu_item ) {
		if ( $location === $menu_item[2] ) {
			return $index;
		}
	}
	return false;
}

/**
 * This tells WP to highlight the Posts > Comments menu when editing a comment.
 *
 * @since 1.0.0
 */
function lwp_comments2posts_modify_admin_menu_highlight() {
	$GLOBALS['plugin_page']  = 'edit.php';
}
