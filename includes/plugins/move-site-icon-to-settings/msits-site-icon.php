<?php
/*
Plugin Name: Move Site Icon To Settings
Plugin URL: http://gregreindel.com/
Description:  Moves the site icon from the customizer to settings
Version: 1.1
Author: Greg Reindel

Note: This is a modified version of Jetpack's site icon module.

Released under the GPL v.2 license.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/


class GR_Site_Icon_Plugin {

	function __construct() {

        $this->plugin               = new stdClass;
        $this->plugin->name 		= 'msits-site-icon';
        $this->plugin->folder       = plugin_dir_path( __FILE__ );
        $this->plugin->url          = plugin_dir_url( __FILE__ );

		if ( version_compare( get_bloginfo('version'), '4.3', '>=' ) && ! class_exists( 'Jetpack_Site_Icon' ) ) {
			require_once( $this->plugin->folder.'/class-msits-site-icon.php' );
			Msits_Site_Icon::init();
		}
		
		// Anything else?
	}

}

new GR_Site_Icon_Plugin;