<?php


/* ACTIONS AND FILTERS RELATED TO BACKEND */
add_action( 'admin_bar_menu', 'lwp_remove_redundant_adminbar', 99 );

add_filter( 'admin_bar_menu', 'lwp_replace_howdy' );

remove_action( 'welcome_panel', 'wp_welcome_panel' );

// removes dashboard widgets and removes admin sidebar (sub)menus
add_action( 'admin_menu', 'lwp_edit_admin', 11 );


add_action( 'widgets_init', 'lwp_remove_wp_default_widgets' );

// Disable XML-RPC - //plugins.svn.wordpress.org/disable-xml-rpc/tags/1.0.1/disable-xml-rpc.php
add_filter( 'xmlrpc_enabled', '__return_false' );
// Remove call for XML-RPC from HEAD - @ZenPress //wordpress.stackexchange.com/questions/219643/best-way-to-eliminate-xmlrpc-php#comment350490_219666
add_filter( 'pings_open', '__return_false', PHP_INT_MAX );

// Disable scrollfree editor
add_action( 'after_setup_theme', 'lwp_scrollfree_editor_off' );

add_filter( 'custom_menu_order', 'lwp_custom_menu_order' ); // Activate custom_menu_order
add_filter( 'menu_order', 'lwp_custom_menu_order' );

// Set Reading Settings "Front page displays" to Page
add_filter( 'pre_option_show_on_front', 'lwp_show_page_on_front' );

// disable file editors
if ( ! defined( 'DISALLOW_FILE_EDIT' ) )
	define( 'DISALLOW_FILE_EDIT', true );



/* ACTIONS AND FILTERS RELATED TO FRONTEND */
add_action( 'init', 'lwp_clean_head' );

// remove WP version from RSS
add_filter( 'the_generator', 'lwp_remove_wp_version_from_rss' );
    
add_action( 'template_redirect', 'lwp_disable_author_archives' );
remove_filter( 'template_redirect', 'redirect_canonical' );



// Remove items from adminbar
function lwp_remove_redundant_adminbar( $wp_admin_bar ) {

	global $wp_admin_bar;

	// remove WP logo
	$wp_admin_bar->remove_node( 'wp-logo' );    

	// remove "New Post"
	$wp_admin_bar->remove_node( 'new-post' );

	// remove "New Media"
	$wp_admin_bar->remove_node( 'new-media' );

	// remove "New User"
	$wp_admin_bar->remove_node( 'new-user' );

	// remove Comments
	$wp_admin_bar->remove_node( 'comments' );    

	// remove plugin updates count
	$wp_admin_bar->remove_node( 'updates' );

}

// Replace "Howdy" with "Hello, welcome back"
function lwp_replace_howdy( $wp_admin_bar ) {

    $my_account = $wp_admin_bar->get_node( 'my-account' );
    $newtitle = str_replace( 'Howdy,', '', $my_account->title );
    $wp_admin_bar->add_node(
    	array(
        	'id' => 'my-account',
			'title' => $newtitle,
		)
	);

}

function lwp_edit_admin() {

	// WordPress default dashboard widgets
	remove_meta_box( 'dashboard_activity', 'dashboard', 'core' );
	remove_meta_box( 'dashboard_right_now', 'dashboard', 'core' );
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'core' );
	remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'core' );
	remove_meta_box( 'dashboard_plugins', 'dashboard', 'core' );
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'core' );
	remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'core' );
	remove_meta_box( 'dashboard_primary', 'dashboard', 'core' );
	remove_meta_box( 'dashboard_secondary', 'dashboard', 'core' );
	
	remove_menu_page( 'tools.php' );
	
	remove_submenu_page( 'themes.php', 'custom-header' );
}

function lwp_remove_wp_default_widgets() {

	unregister_widget( 'WP_Widget_Pages' );
	unregister_widget( 'WP_Widget_Calendar' );
	unregister_widget( 'WP_Widget_Archives' );
	if ( get_option( 'link_manager_enabled' ) )
		unregister_widget( 'WP_Widget_Links' );
	unregister_widget( 'WP_Widget_Meta' );
	//unregister_widget( 'WP_Widget_Search' );
	//unregister_widget( 'WP_Widget_Text' );
	unregister_widget( 'WP_Widget_Categories' );
	//unregister_widget( 'WP_Widget_Recent_Posts' );
	unregister_widget( 'WP_Widget_Recent_Comments' );
	unregister_widget( 'WP_Widget_RSS' );
	unregister_widget( 'WP_Widget_Tag_Cloud' );
	//unregister_widget( 'WP_Nav_Menu_Widget' );

}

if ( ! function_exists( 'lwp_scrollfree_editor_off' ) ) {
	function lwp_scrollfree_editor_off(){
		
		set_user_setting( 'editor_expand', 'off' );
	
	}
}

/**
 * Function to customise the admin menu sidebar order
 *
 * @source: //code.tutsplus.com/articles/customizing-your-wordpress-admin--wp-24941
 */
function lwp_custom_menu_order( $menu_ord ) {

	if ( ! $menu_ord ) return true;

	return array(
		'index.php', // Dashboard
		'separator1', // First separator
		'edit.php?post_type=page', // Pages
	);
}

/**
 * Change default reading setting  "Front page displays"
 *
 * @source: //wordpress.stackexchange.com/q/259054/2015
 */
function lwp_show_page_on_front() {
    return 'page';
}

// Set reading option "For each article in a feed, show" to Summary
update_option( 'rss_use_excerpt', 1 );

// Turn off Gravatars (in Dashboard and for comments)
update_option( 'show_avatars', 0 );

update_option( 'default_pingback_flag', 0 );
update_option( 'default_ping_status', 0 );


/**
 * WP_HEAD CLEANUP
 * all cleaning related functions
 */
function lwp_clean_head() {

	// EditURI link
	remove_action( 'wp_head', 'rsd_link' );

	// windows live writer
	remove_action( 'wp_head', 'wlwmanifest_link' );

	// index link
	remove_action( 'wp_head', 'index_rel_link' );

	// previous link
	remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );

	// start link
	remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );

	// links for adjacent posts
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );

	// wp shortlink
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );

	// WP version
	remove_action( 'wp_head', 'wp_generator' );

} // end lwp_clean_head()

// remove WP version from RSS
function lwp_remove_wp_version_from_rss() {
	return '';
}

// disable author archives - //wp-mix.com/wordpress-disable-author-archives/
function lwp_disable_author_archives() {
	if ( is_author() ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
	} else {
		redirect_canonical();
	}

}
// block WP enum scans - //perishablepress.com/stop-user-enumeration-wordpress/
if ( ! is_admin() ) {
	// default URL format
	if (preg_match( '/author=([0-9]*)/i', $_SERVER[ 'QUERY_STRING' ] ) ) die();
	add_filter( 'redirect_canonical', 'lwp_check_enum', 10, 2 );
}
function lwp_check_enum( $redirect, $request ) {
	// permalink URL format
	if ( preg_match( '/\?author=([0-9]*)(\/*)/i', $request ) ) die();
	else return $redirect;
}