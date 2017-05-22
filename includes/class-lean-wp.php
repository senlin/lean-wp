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
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	//public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	//public $_token;

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
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	//public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	//public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	//public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'lean_wp';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		//$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		//$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend JS & CSS
		//add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		//add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS
		//add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		//add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		// Load API for generic admin functions
/*
		if ( is_admin() ) {
			$this->admin = new LEAN_WP_Admin_API();
		}
*/

		// Handle localisation
		add_action( 'plugins_loaded', array( $this, 'i18n' ), 0 );

		/*** PLUGIN FUNCTIONS ***/

		/***
		 *** ACTIONS AND FILTERS RELATED TO BACKEND
		 ***/

		// removes redundant items from adminbar
		add_action( 'admin_bar_menu', array( $this, 'remove_redundant_adminbar' ), 99 );

		add_filter( 'admin_bar_menu', array( $this, 'remove_howdy' ) );

		// remove welcome panel
		remove_action( 'welcome_panel', 'wp_welcome_panel' );

		// removes dashboard widgets and removes certain admin sidebar (sub)menus
		add_action( 'admin_menu', array( $this, 'edit_admin' ), 11 );

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
		add_filter( 'pre_option_show_on_front', array( $this, 'show_page_on_front' ) );

		// Disable file editors
		if ( ! defined( 'DISALLOW_FILE_EDIT' ) )
			define( 'DISALLOW_FILE_EDIT', true );

		// Set different defaults for certain options
		add_action( 'init', array( $this, 'update_options' ) );

		// Remove contextual help and screen options
		add_filter( 'screen_options_show_screen', '__return_false' );
		add_filter( 'contextual_help', array( $this, 'remove_contextual_help' ), 999, 3 );

		// Remove admin footer text and WP version
		add_filter( 'admin_footer_text', '__return_empty_string', 11 );
		add_filter( 'update_footer',     '__return_empty_string', 11 );

		/***
		 *** ACTIONS AND FILTERS RELATED TO FRONTEND
		 ***/
		add_action( 'init', array( $this, 'clean_head' ) );

		// remove WP version from RSS
		add_filter( 'the_generator', array( $this, 'remove_wp_version_from_rss' ) );

		// disable author archives
		add_action( 'template_redirect', array( $this, 'disable_author_archives' ) );
		remove_filter( 'template_redirect', 'redirect_canonical' );

		if ( ! is_admin() ) {
			// default URL format
			if (preg_match( '/author=([0-9]*)/i', $_SERVER[ 'QUERY_STRING' ] ) ) die();
			add_filter( 'redirect_canonical', array( $this, 'check_enum' ), 10, 2 );
		}

	} // End __construct ()

	/**
	 * Wrapper function to register a new post type
	 * @param  string $post_type   Post type name
	 * @param  string $plural      Post type item plural name
	 * @param  string $single      Post type item single name
	 * @param  string $description Description of post type
	 * @return object              Post type class object
	 */
/*
	public function register_post_type ( $post_type = '', $plural = '', $single = '', $description = '', $options = array() ) {

		if ( ! $post_type || ! $plural || ! $single ) return;

		$post_type = new LEAN_WP_Post_Type( $post_type, $plural, $single, $description, $options );

		return $post_type;
	}
*/

	/**
	 * Wrapper function to register a new taxonomy
	 * @param  string $taxonomy   Taxonomy name
	 * @param  string $plural     Taxonomy single name
	 * @param  string $single     Taxonomy plural name
	 * @param  array  $post_types Post types to which this taxonomy applies
	 * @return object             Taxonomy class object
	 */
/*
	public function register_taxonomy ( $taxonomy = '', $plural = '', $single = '', $post_types = array(), $taxonomy_args = array() ) {

		if ( ! $taxonomy || ! $plural || ! $single ) return;

		$taxonomy = new LEAN_WP_Taxonomy( $taxonomy, $plural, $single, $post_types, $taxonomy_args );

		return $taxonomy;
	}
*/

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
/*
	public function enqueue_styles () {
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );
	} // End enqueue_styles ()
*/

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
/*
	public function enqueue_scripts () {
		wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-frontend' );
	} // End enqueue_scripts ()
*/

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
/*
	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()
*/

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
/*
	public function admin_enqueue_scripts ( $hook = '' ) {
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-admin' );
	} // End admin_enqueue_scripts ()
*/

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
	public function remove_redundant_adminbar( $wp_admin_bar ) {

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
	 */
	public function remove_howdy( $wp_admin_bar ) {

	    $my_account = $wp_admin_bar->get_node( 'my-account' );
	    $newtitle = str_replace( 'Howdy,', '', $my_account->title );
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
	 * * Pages gets position 5
	 * * Posts gets position 50
	 * * Media gets position 58 (before separator of Appearance)
	 *
	 * This means that there is ample space to position Custom Post Types
	 *
	 * @source: //randyhoyt.com/wordpress/admin/
	 * @source: //cdn.tutsplus.com/wp/uploads/legacy/228_Customizing_Your_WordPress_Admin/chart.gif
	 *
	 * @since 1.0.0
	 */
	function reorder_pages_posts_media() {
		global $menu;
		$menu[50] = $menu[5]; // Posts goes from position 5 to position 50
		$menu[58] = $menu[10]; // Media goes from position 10 to position 58 (right above Appearance-separator)
		$menu[5] = $menu[20]; // Pages goes from position 20 to position 5 (originally Posts)
		unset($menu[10]); //remove Media from original position
		unset($menu[20]); //remove Pages from original position
	}

	/**
	 * Prevents plugins from injecting themselves as top level menus (example: Jetpack)
	 *
	 * @source: based on Menu Humility plugin - //wordpress.org/plugins/menu-humility/
	 *
	 * @since 1.0.0
	 */
	public function menu_order( $menu ) {

		if ( ! $menu ) return true;

		$penalty_box = array();

		foreach ( $menu as $key => $item ) {
			if ( 'separator1' == $item ) {
				// Have reached the content area. We're done.
				break;
			} elseif ( 'index.php' !== $item ) {
				// Yank it out and put it in the penalty box.
				$penalty_box[] = $item;
				unset( $menu[$key] );
			}
		}

		// Shove the penalty box items onto the end
		return array_merge( $menu, $penalty_box );

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
	 * Block WP enum scans.
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
	public static function instance ( $file = '', $version = '1.0.0' ) {
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
