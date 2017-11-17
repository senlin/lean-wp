<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LEAN_WP {

	/**
	 * The single instance of LEAN_WP.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $version;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.2.0' ) {
		$this->_version = $version;
		$this->_token = 'lean_wp';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load admin CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		// Handle localisation
		add_action( 'plugins_loaded', array( $this, 'i18n' ), 0 );

		/*** PLUGIN FUNCTIONS ***/

		/***
		 *** ACTIONS AND FILTERS RELATED TO BACKEND
		 ***/

		// removes redundant items from adminbar
		add_action( 'admin_bar_menu', array( $this, 'remove_redundant_items_adminbar' ), 99 );

		// removes howdy "greeting"
		add_filter( 'admin_bar_menu', array( $this, 'remove_howdy' ) );

		// remove welcome panel
		remove_action( 'welcome_panel', 'wp_welcome_panel' );

		// removes dashboard widgets and removes certain admin sidebar (sub)menus
		add_action( 'admin_menu', array( $this, 'edit_admin' ), 9999 );

		// removes a number of default widgets
		add_action( 'widgets_init', array( $this, 'remove_wp_default_widgets' ) );

		// Disable XML-RPC - //plugins.svn.wordpress.org/disable-xml-rpc/tags/1.0.1/disable-xml-rpc.php
		add_filter( 'xmlrpc_enabled', '__return_false' );
		// Remove call for XML-RPC from HEAD - @ZenPress //wordpress.stackexchange.com/questions/219643/best-way-to-eliminate-xmlrpc-php#comment350490_219666
		add_filter( 'pings_open', '__return_false', PHP_INT_MAX );

		// Disable scrollfree editor
		add_action( 'after_setup_theme', array( $this, 'scrollfree_editor_off' ) );

		// Reorder Pages, Posts and Media
		add_action( 'admin_menu', array( $this, 'reorder_pages_posts_media' ) );

		// Prevents plugins from injecting themselves as top level menus (example: Jetpack)
		add_filter( 'custom_menu_order', '__return_true' );
		add_filter( 'menu_order', array( $this, 'menu_order' ), 99 );

		// Move Gravity Forms menu down if plugin is active
		add_filter( 'gform_menu_position', array( $this, 'gform_under_settings' ) );

		// Set Reading Settings "Front page displays" to Page
		// @since 1.2.0 make function pluggable so it can be overridden
		if ( ! function_exists( 'show_page_on_front' ) ) {
			add_filter( 'pre_option_show_on_front', array( $this, 'show_page_on_front' ) );
		}

		// Disable file editors
		if ( ! defined( 'DISALLOW_FILE_EDIT' ) )
			define( 'DISALLOW_FILE_EDIT', true );

		// Set different defaults for certain options
		add_action( 'init', array( $this, 'update_options' ) );

		// Remove contextual help //and screen options
		add_filter( 'contextual_help', array( $this, 'remove_contextual_help' ), 999, 3 );

		// Remove admin footer text and WP version
		add_filter( 'admin_footer_text', '__return_empty_string', 11 );
		add_filter( 'update_footer',     '__return_empty_string', 11 );

		/***
		 *** ACTIONS AND FILTERS RELATED TO FRONTEND
		 ***/
		add_action( 'init', array( $this, 'clean_head' ) );

		// remove WP version from RSS, styles and scripts
		add_filter( 'the_generator', array( $this, 'remove_wp_version_from_rss' ) );

		// remove WP version from enqueued styles and scripts
		add_filter( 'style_loader_src', array( $this, 'remove_wp_version_styles_scripts' ) );
		add_filter( 'script_loader_src', array( $this, 'remove_wp_version_styles_scripts' ) );

		// remove all meta generators
		add_action( 'get_header', array( $this, 'clean_meta_generators' ), 100 );
		add_action( 'wp_footer', function() { ob_end_flush(); }, 100 );

		// set default display name new user registrations to first_name last_name
		add_action( 'user_register', array( $this, 'set_default_display_name' ) );

		// changing the author URL
		// handle incoming links with the author first_name instead of the author slug
		// generate author post urls with the sitename instead of the standard username-slug
		add_filter( 'request', array ( $this, 'author_firstname_request' ) );
		add_filter( 'author_link', array ( $this, 'sitename_author_link' ), 10, 3 );

		// disable author archives
		remove_filter( 'template_redirect', 'redirect_canonical' );
		add_action( 'template_redirect', array( $this, 'disable_author_archives' ) );

		// block user-enumeration
		if ( ! is_admin() ) {
			// default URL format
			if (preg_match( '/author=([0-9]*)/i', $_SERVER[ 'QUERY_STRING' ] ) ) die();
			add_filter( 'redirect_canonical', array( $this, 'check_enum' ), 10, 2 );
		}

		// change default category name
		// @modified 1.0.1 - add check if Uncategorized term_exists
		$term = term_exists( 'Uncategorized', 'category' );
		if ( $term !== 0 && $term !== null ) {
			wp_update_term(

				1, 'category', array(
					'name' => __( 'General', 'lean-wp' ),
					'slug' => 'general',
					'description' => __( 'The default Category for the Posts post type', 'lean-wp' )
				)
			);
		}

	} // End __construct ()

	/**
	 * Wrapper function to register a new dashboard widget
	 * @param  string $widget_id   Taxonomy name
	 * @param  string $widget_name     Taxonomy single name
	 * @param  string $callback  control_callbackpost_types Post types to which this taxonomy applies
	 * @return object             Dashboard Widget class object
	 */
    public function init() {
        //Register the widget...
		wp_add_dashboard_widget( 'leanwp-dashboard-widget', esc_html__( 'Site information', 'lean-wp'), array( 'LEAN_WP_Dashboard_Widget', 'widget' ) );
    }

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Loads the translation file.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function i18n() {
		load_plugin_textdomain( 'lean-wp', false, false );
	} // End i18n ()

	/**
	 * Remove items from adminbar.
	 *
	 * @since 1.0.0
	 */
	public function remove_redundant_items_adminbar( $wp_admin_bar ) {

		global $wp_admin_bar;

		/*** BACKEND ***/
		// remove WP logo and subsequent drop-down menu
		$wp_admin_bar->remove_node( 'wp-logo' );

		// remove View Site text
		$wp_admin_bar->remove_node( 'view-site' );

		// remove "+ New" drop-down menu
		$wp_admin_bar->remove_node( 'new-content' );

		// remove Comments
		$wp_admin_bar->remove_node( 'comments' );

		// remove plugin updates count
		$wp_admin_bar->remove_node( 'updates' );

		/*** FRONTEND ***/
		// remove Dashboard link
		$wp_admin_bar->remove_node( 'dashboard' );

		// remove Themes, Widgets, Menus, Header links
		$wp_admin_bar->remove_node( 'appearance' );
	}

	/**
	 * Remove Howdy.
	 *
	 * @since 1.0.0
	 * @modified 1.1.0
	 */
	public function remove_howdy( $wp_admin_bar ) {

	    $my_account = $wp_admin_bar->get_node( 'my-account' );
	    $newtitle = '';
	    $wp_admin_bar->add_node(
	    	array(
	        	'id' => 'my-account',
				'title' => $newtitle,
			)
		);

	}

	/**
	 * Edit the backend.
	 *
	 * dashboard widgets
	 * sidebar menu items
	 * sidebar sub menu items
	 *
	 * @since 1.0.0
	 */
	public function edit_admin() {

		// remove all WordPress default dashboard widgets
		remove_meta_box( 'dashboard_activity', 'dashboard', 'core' );
		remove_meta_box( 'dashboard_right_now', 'dashboard', 'core' );
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'core' );
		remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'core' );
		remove_meta_box( 'dashboard_plugins', 'dashboard', 'core' );
		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'core' );
		remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'core' );
		remove_meta_box( 'dashboard_primary', 'dashboard', 'core' );
		remove_meta_box( 'dashboard_secondary', 'dashboard', 'core' );

		// remove Tools menu
		remove_menu_page( 'tools.php' );

		// remove "Header" and "Custom Background" sub-menus from "Appearance"
		remove_submenu_page( 'themes.php', 'custom-header' );
		remove_submenu_page( 'themes.php', 'custom-background' );
		// remove Edit CSS sub-menu from "Appearance" (since WP 4.8)
		remove_submenu_page( 'themes.php', 'editcss-customizer-redirect' );

		if ( is_plugin_active( 'jetpack/jetpack.php' ) ) {
			add_submenu_page( 'jetpack', __( 'Lean Settings', 'lean-wp' ), __( 'Lean Settings', 'lean-wp' ), 'manage_options', 'admin.php?page=jetpack_modules' );
		}

	}

	/**
	 * Remove default widgets.
	 *
	 * @since 1.0.0
	 */
	public function remove_wp_default_widgets() {

		unregister_widget( 'WP_Widget_Pages' );
		unregister_widget( 'WP_Widget_Calendar' );
		unregister_widget( 'WP_Widget_Archives' );
		if ( get_option( 'link_manager_enabled' ) )
			unregister_widget( 'WP_Widget_Links' );
		unregister_widget( 'WP_Widget_Meta' );
		unregister_widget( 'WP_Widget_Categories' );
		unregister_widget( 'WP_Widget_Recent_Comments' );
		unregister_widget( 'WP_Widget_RSS' );
		unregister_widget( 'WP_Widget_Tag_Cloud' );

	}

	/**
	 * Disable scroll-free editor.
	 *
	 * @since 1.0.0
	 */
	public function scrollfree_editor_off() {

		set_user_setting( 'editor_expand', 'off' );

	}

	/**
	 * Reorder Pages, Posts and Media admin menus
	 *
	 * - Pages gets position 5
	 * - Posts gets position 50
	 * - Media gets position 58 (before separator of Appearance)
	 *
	 * This means that there is ample space to position Custom Post Types
	 *
	 * @source: //randyhoyt.com/wordpress/admin/
	 * @source: //cdn.tutsplus.com/wp/uploads/legacy/228_Customizing_Your_WordPress_Admin/chart.gif
	 *
	 * @since 1.0.0
	 */
	function reorder_pages_posts_media() {
		/**
		 * plugins that disable blogging do something similar which might be conflicting
		 * therefore so only execute this function when those plugins are not installed
		 *
		 * plugins we know and which we have tested for, are:
		 * - [Disable Blogging](https://wordpress.org/plugins/disable-blogging/) by Fact Maven
		 * - [Disable Blog](https://wordpress.org/plugins/disable-blog/) by Joshua Nelson
		 *
		 */
		if ( ! is_plugin_active( 'disable-blogging/disable-blogging.php' ) && ! is_plugin_active( 'disable-blog/disable-blog.php' ) ) {
			global $menu;
			$menu[50] = $menu[5]; // Posts goes from position 5 to position 50
			$menu[58] = $menu[10]; // Media goes from position 10 to position 58 (right above Appearance-separator)
			$menu[5] = $menu[20]; // Pages goes from position 20 to position 5 (originally Posts)
			unset($menu[10]); //remove Media from original position
			unset($menu[20]); //remove Pages from original position
		}
	}

	/**
	 * Prevents plugins - for example Jetpack - from injecting themselves in the top part
	 * of the admin sidebar menu, right under "Dashboard"
	 *
	 * @source: based on Menu Humility plugin - //wordpress.org/plugins/menu-humility/
	 *
	 * @since 1.0.0
	 */
	public function menu_order( $menu ) {

		if ( ! $menu ) return true;

		$plugins_area = array();

		foreach ( $menu as $key => $item ) {

			if ( 'separator1' == $item ) {

				break;

			} elseif ( 'index.php' !== $item ) {

				// remove it and put it in the plugins area instead
				$plugins_area[] = $item;

				unset( $menu[$key] );
			}
		}

		// Move the items in the plugins area to the end of the menu
		return array_merge( $menu, $plugins_area );

	}

	/**
	 * Move Gravity Forms admin menu under Settings
	 *
	 * @source: //gravityhelp.com/documentation/article/gform_menu_position/
	 *
	 * @since 1.0.0
	 */
	public function gform_under_settings( $position ) {
	    if ( is_plugin_active( 'gravityforms/gravityforms.php' ) ) {
	    	return 80;
	    }
	}

	/**
	 * Change default reading setting  "Front page displays"
	 *
	 * @source: //wordpress.stackexchange.com/q/259054/2015
	 *
	 * @since 1.0.0
	 */
	public function show_page_on_front() {
	    return 'page';
	}

	/**
	 * Set different defaults for certain options
	 *
	 * @since 1.0.0
	 */
	public function update_options() {
		// Set reading option "For each article in a feed, show" to Summary
		update_option( 'rss_use_excerpt', 1 );

		// Turn off Gravatars (in Dashboard and for comments)
		update_option( 'show_avatars', 0 );

		// Uncheck boxes Settings > Discussion: Default article settings
		update_option( 'default_pingback_flag', 0 );
		update_option( 'default_ping_status', 0 );
	}

	/**
	 * Remove contextual help tabs
	 *
	 * @since 1.0.0
	 */
	public function remove_contextual_help( $old_help, $screen_id, $screen ) {
	    $screen->remove_help_tabs();
	    return $old_help;
	}

	/**
	 * WP_HEAD Cleanup.
	 *
	 * contains all cleaning related functions (frontend)
	 *
	 * @since 1.0.0
	 */
	public function clean_head() {

		// EditURI link
		remove_action( 'wp_head', 'rsd_link' );

		// windows live writer
		remove_action( 'wp_head', 'wlwmanifest_link' );

		// links for adjacent posts
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );

		// wp shortlink
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );

	} // end clean_head()

	/**
	 * Remove WP version from RSS.
	 *
	 * @since 1.0.0
	 */
	public function remove_wp_version_from_rss() {
		return '';
	}

	/**
	 * Remove WP version from enqueued styles and scripts.
	 *
	 * @since 1.0.0
	 */
	function remove_wp_version_styles_scripts( $src ) {
	    if ( strpos( $src, 'ver=' . get_bloginfo( 'version' ) ) )
	        $src = remove_query_arg( 'ver', $src );
	    return $src;
	}

	/**
	 * Remove all meta generators.
	 *
	 * @source: //stackoverflow.com/a/42380747/1381553
	 *
	 * @since 1.0.0
	 */
	public function remove_meta_generators( $html ) {

	    $pattern = '/<meta name(.*)=(.*)"generator"(.*)>/i';

	    $html = preg_replace( $pattern, '', $html );

	    return $html;

	}
	public function clean_meta_generators( $html ) {

		ob_start( array( $this, 'remove_meta_generators' ) );

	}

	/**
	 * Set default display name New User Registrations to first_name last_name
	 * or to site name if first name is not filled in
	 *
	 * Best used on 'user_register'
	 * @param int $user_id The user ID
	 * @return void
	 * @uses get_userdata()
	 * @uses wp_update_user()
	 *
	 * @source: //stevegrunwell.com/blog/quick-tip-set-the-default-display-name-for-wordpress-users/
	 */
	public function set_default_display_name( $user_id ) {

		$user = get_userdata( $user_id );

		if ( ! empty ( $user->first_name ) ) {
			// if first_name field is not empty set display name to first_name last_name
			$name = sprintf( '%s %s', $user->first_name, $user->last_name );
		} else {
			// if first_name field is empty set display name to site name
			$name = get_bloginfo( 'name' );
		}

		$args = array(
			'ID' => $user_id,
			'display_name' => $name,
			'nickname' => $name
		);

		wp_update_user( $args );
	}

	/**
	 * Changing the author URL
	 *
	 * handle incoming links with the author first_name instead of the author slug
	 *
	 * @source: //wordpress.stackexchange.com/a/6527/2015 (adapted to use first_name instead of nickname)
	 */
	public function author_firstname_request( $query_vars ) {
	    if ( array_key_exists( 'author_name', $query_vars ) ) {
	        global $wpdb;
	        $author_id = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key='first_name' AND meta_value = %s", $query_vars['author_name'] ) );
	        if ( $author_id ) {
	            $query_vars['author'] = $author_id;
	            unset( $query_vars['author_name'] );
	        }
	    }
	    return $query_vars;
	}

	/**
	 * Changing the author URL
	 *
	 * generate author post urls with the sitename instead of the standard username-slug
	 *
	 * @source: //wordpress.stackexchange.com/a/6527/2015 (adapted to use sitename instead of nickname)
	 */
	public function sitename_author_link( $link, $author_id, $author_nicename ) {

	    $link = str_replace( $author_nicename, get_bloginfo( 'name' ), $link );

	    return $link;
	}

	/**
	 * Disable author archives.
	 *
	 * @source: //wp-mix.com/wordpress-disable-author-archives/
	 *
	 * @since 1.0.0
	 */
	public function disable_author_archives() {
		if ( is_author() ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
		} else {
			redirect_canonical();
		}
	}
	/**
	 * Block WP enum scans (user-enumeration).
	 *
	 * @source: //perishablepress.com/stop-user-enumeration-wordpress/
	 *
	 * @since 1.0.0
	 */
	public function check_enum( $redirect, $request ) {
		// permalink URL format
		if ( preg_match( '/\?author=([0-9]*)(\/*)/i', $request ) ) die();
		else return $redirect;
	}

	/**
	 * Main LEAN_WP Instance
	 *
	 * Ensures only one instance of LEAN_WP is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LEAN_WP()
	 * @return Main LEAN_WP instance
	 */
	public static function instance ( $file = '', $version = '1.1.1' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}
