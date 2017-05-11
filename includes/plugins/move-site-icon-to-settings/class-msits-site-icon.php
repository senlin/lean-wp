<?php

class Msits_Site_Icon {

	public static $version     = 1.0;
	public static $assets_version = 1;

	public static $min_size  = 512; 
	public static $page_crop = 512;
	
	public static $accepted_file_types = array(
		'image/jpg',
		'image/jpeg',
		'image/gif',
		'image/png'
	);

	public static $site_icon_sizes = array(
		256,
		128,
		80,
		64,
		32,
		16,
	);

	static $instance = false;

	/**
	 * Control type.
	 *
	 * @since 4.3.0
	 * @access public
	 * @var string
	 */
	public $type = 'cropped_image';

	/**
	 * Suggested width for cropped image.
	 *
	 * @since 4.3.0
	 * @access public
	 * @var int
	 */
	public $width = 150;

	/**
	 * Suggested height for cropped image.
	 *
	 * @since 4.3.0
	 * @access public
	 * @var int
	 */
	public $height = 150;

	/**
	 * Whether the width is flexible.
	 *
	 * @since 4.3.0
	 * @access public
	 * @var bool
	 */
	public $flex_width = false;

	/**
	 * Whether the height is flexible.
	 *
	 * @since 4.3.0
	 * @access public
	 * @var bool
	 */
	public $flex_height = false;

	/**
	 * Enqueue control related scripts/styles.
	 *
	 * @since 4.3.0
	 * @access public
	 */
	public function enqueue() {
		wp_enqueue_script( 'customize-views' );

		parent::enqueue();
	}

	/**
	 * Refresh the parameters passed to the JavaScript via JSON.
	 *
	 * @since 4.3.0
	 * @access public
	 *
	 * @see WP_Customize_Control::to_json()
	 */
	public function to_json() {
		parent::to_json();

		$this->json['width']       = absint( $this->width );
		$this->json['height']      = absint( $this->height );
		$this->json['flex_width']  = absint( $this->flex_width );
		$this->json['flex_height'] = absint( $this->flex_height );
	}
	
	/**
	 * Singleton
	 */
	public static function init() {
		if ( ! self::$instance ){
			self::$instance = new Msits_Site_Icon;
			self::$instance->register_hooks();
		}

		return self::$instance;
	}


	/**
	 * Register our actions and filters
	 * @return null
	 */
	public function register_hooks(){
		add_action( 'admin_menu',             array( $this, 'admin_menu_upload_site_icon' ) );
		add_filter( 'display_media_states',   array( $this, 'add_media_state' ) );
		add_action( 'admin_init',             array( $this, 'admin_init' ) );
		add_action( 'admin_init',             array( $this, 'delete_site_icon_hook' ) );
		add_action( 'admin_enqueue_scripts',  array( $this, 'enqueue_media_scripts' ) );

		add_action( 'admin_print_styles-options-general.php', array( $this, 'add_general_options_styles' ) );

		add_action( 'delete_option',     array( 'Msits_Site_Icon', 'delete_temp_data' ), 10, 1); // used to clean up after itself.
		add_action( 'delete_attachment', array( 'Msits_Site_Icon', 'delete_attachment_data' ), 10, 1); // in case user deletes the attachment via
		add_filter( 'get_post_metadata', array( 'Msits_Site_Icon', 'delete_attachment_images' ), 10, 4 );
	}


	/**
	 * Add a hidden upload page from people
	 */
	public function admin_menu_upload_site_icon() {
 		$page_hook = add_submenu_page(
 			null,
 			__( 'Site Icon Upload', 'msits-site-icon' ),
 			'',
 			'manage_options',
 			'msits-site_icon-upload',
 			array( $this, 'upload_site_icon_page' )
 		);

 		add_action( "admin_head-$page_hook", array( $this, 'upload_balavatar_head' ) );
	}


	/**
	 * Add styles to the General Settings Screen
	 */
	public function add_general_options_styles() {
		wp_enqueue_style( 'site-icon-admin' );
	}


	/**
	 * Add Styles to the Upload UI Page
	 *
	 */
	public function upload_balavatar_head() {

		wp_register_script( 'msits-site-icon-crop',  plugin_dir_url( __FILE__ ). "js/site-icon-crop.js"  , array( 'jquery', 'jcrop' ) ,  self::$assets_version, false);
		if ( isset( $_REQUEST['step'] )  && $_REQUEST['step'] == 2 ) {
			wp_enqueue_script( 'msits-site-icon-crop' );
			wp_enqueue_style( 'jcrop' );
		}
		wp_enqueue_style( 'site-icon-admin' );
	}

	public function add_media_state( $media_states ) {

		if ( has_site_icon() ) {
			global $post;

			if( $post->ID == get_option( 'site_icon' ) ) {
				$media_states[] = __( 'Site Icon', 'msits-site-icon' );
			}

		}
		return $media_states;
	}


	/**
	 * Load on when the admin is initialized
	 */
	public function admin_init() {
		/* register the styles and scripts */
		wp_register_style( 'site-icon-admin' , plugin_dir_url( __FILE__ ). "css/site-icon-admin.css", array(), self::$assets_version );
		// register the settings
		add_settings_section(
		  'site-icon',
		  '',
		  array( $this, 'site_icon_settings' ),
		  'general'
		);
	}


	/**
	 * Checks for permission to delete the site_icon
	 */
	public function delete_site_icon_hook() {
		// Delete the site_icon
		if ( isset( $GLOBALS['plugin_page'] ) && 'msits-site_icon-upload' == $GLOBALS['plugin_page'] ) {
			if ( isset( $_GET['action'] )
					&& 'remove' == $_GET['action']
					&& isset( $_GET['nonce'] )
					&& wp_verify_nonce( $_GET['nonce'], 'remove_site_icon' ) ) {

				$site_icon_id = get_option( 'site_icon' );
				// Delete the previous Blavatar
				self::delete_site_icon( $site_icon_id, true );
				wp_safe_redirect( admin_url( 'options-general.php#site-icon' ) );
			}
		}
	}

	public function enqueue_media_scripts() {
		if(function_exists( 'wp_enqueue_media' )){
		    wp_enqueue_media();
		}else{
		    wp_enqueue_style('thickbox');
		    wp_enqueue_script('media-upload');
		    wp_enqueue_script('thickbox');
		}
      }

	/**
	 * Add HTML to the General Settings
	 */
	public function site_icon_settings() {
		$upload_blavatar_url = admin_url( 'options-general.php?page=msits-site_icon-upload' );

		// lets delete the temp data that we might he holding on to
		self::delete_temporay_data();

		?><table class="form-table">
		<tr id="site-icon" class="site-icon-shell">
		<th>
			<label><?php echo esc_html_e( 'Site Icon', 'msits-site-icon' ); ?></label>
		</th>
		<td>
			<div class="site-icon-content">


				<div class="site-icon-image"> 
				<?php if( has_site_icon() ) {
					echo gr_get_site_icon( null, 128 );
					} ?>
				</div>
				<div class="site-icon-meta">

				<?php if ( has_site_icon() ) {
					$remove_blavatar_url = admin_url( 'options-general.php?page=msits-site_icon-upload' )."&action=remove&nonce=".wp_create_nonce( 'remove_site_icon' ); // this could be an ajax url
					?>
					<p><a href="<?php echo esc_url( $upload_blavatar_url ); ?>" id="site-icon-update" class="button"><?php echo esc_html_e( 'Update Site Icon', 'msits-site-icon'  ); ?></a>
					<a href="<?php echo esc_url( $remove_blavatar_url ); ?>" id="site-icon-remove" ><?php echo esc_html_e( 'Remove Icon', 'msits-site-icon'  ); ?></a> </p>

				<?php } else { ?>

					<a href="<?php echo esc_url( $upload_blavatar_url ); ?>" id="site-icon-update" class="button"><?php echo esc_html_e( 'Add a Site Icon', 'msits-site-icon' ); ?></a>

				<?php } ?>

					<div class="site-icon-info">
						<p><?php echo esc_html_e( 'Site Icon creates a favicon for your site and more.', 'msits-site-icon' ); ?></p>
					</div>

				</div>
			</div>
			</td>
		</tr></table>
		<?php
	}


	/**
	 * Hidden Upload Blavatar page for people that don't like modals
	 */
	public function upload_site_icon_page() { ?>
		<div class="wrap">
			<?php require_once( dirname( __FILE__ ) . '/upload-site-icon.php' ); ?>
		</div>
		<?php
	}


	/**
	 * Select a file admin view
	 */
	public static function select_page() {
		// Display the site_icon form to upload the image
		 ?>
		<form action="<?php echo esc_url( admin_url( 'options-general.php?page=msits-site_icon-upload' ) ); ?>" method="post" enctype="multipart/form-data">

			<h2 class="site-icon-title">
			<?php if( has_site_icon() ) {
				esc_html_e( 'Update Site Icon', 'msits-site-icon' );
			} else {
				esc_html_e( 'Add Site Icon', 'msits-site-icon' );
			} ?> <span class="small"><?php esc_html_e( 'select a file', 'msits-site-icon' ); ?></span></h2>
			<p><?php esc_html_e( 'Upload an image that you want to use as your site icon. You will be asked to crop it in the next step.', 'msits-site-icon' ); ?></p>


			<p><input name="site-iconfile" id="site-iconfile" type="file" /></p>
			<p><strong><?php esc_html_e( 'The image needs to be at least', 'msits-site-icon' ); ?> <?php echo self::$min_size; ?>px <?php esc_html_e( 'in both width and height.', 'msits-site-icon' ); ?></strong></p>
			<p class="submit site-icon-submit-form">
				<input name="submit" value="<?php esc_attr_e( 'Upload Image' , 'msits-site-icon' ); ?>" type="submit" class="button button-primary button-large" /><?php printf( __( ' or <a href="%s">Cancel</a> and go back to the settings.' , 'msits-site-icon' ), esc_url( admin_url( 'options-general.php' ) ) ); ?>
				<input name="step" value="2" type="hidden" />

				<?php wp_nonce_field( 'update-site_icon-2', '_nonce' ); ?>
			</p>
		</form>
		<?php
	}


	/**
	 * Crop a the image admin view
	 */
	public static function crop_page() {
		// handle the uploaded image
		$image = self::handle_file_upload( $_FILES['site-iconfile'] );

		// display the image image cropping functionality
		if( is_wp_error( $image ) ) { ?>
			<div id="message" class="updated error below-h2"><p><?php echo esc_html( $image->get_error_message() ); ?></p></div>
			<?php
			// back to step one
			$_POST = array();
			self::delete_temporay_data();
			self::select_page();
			return;
		}

		$crop_data = get_option( 'site_icon_temp_data' );
		$crop_ration = $crop_data['large_image_data'][0] / $crop_data['resized_image_data'][0]; // always bigger then 1

		// lets make sure that the Javascript ia also loaded
		wp_localize_script( 'msits-site-icon-crop', 'Site_Icon_Crop_Data', self::initial_crop_data( $crop_data['large_image_data'][0] , $crop_data['large_image_data'][1], $crop_data['resized_image_data'][0], $crop_data['resized_image_data'][1] ) );
		?>

		<h2 class="site-icon-title"><?php esc_html_e( 'Site Icon', 'msits-site-icon' ); ?> <span class="small"><?php esc_html_e( 'crop the image', 'msits-site-icon' ); ?></span></h2>
		<div class="site-icon-crop-shell">
			<form action="" method="post" enctype="multipart/form-data">
			<div class="site-icon-crop-preview-shell">

			<h3><?php esc_html_e( 'Preview', 'msits-site-icon' ); ?></h3>

				<strong><?php esc_html_e( 'As your favicon', 'msits-site-icon' ); ?></strong>
				<div class="site-icon-crop-favicon-preview-shell">
					<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ). "browser.png" ); ?>" class="site-icon-browser-preview" width="172" height="79" alt="<?php esc_attr_e( 'Browser Chrome' , 'msits-site-icon' ); ?>" />
					
					<div class="site-icon-crop-preview-favicon">
						<img src="<?php echo esc_url( $image[0] ); ?>" id="preview-favicon" alt="<?php esc_attr_e( 'Preview Favicon' , 'msits-site-icon' ); ?>" />
					</div>
					<span class="site-icon-browser-title"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
				</div>
			

				<strong><?php esc_html_e( 'As a mobile icon', 'msits-site-icon' ); ?></strong>
				<div class="site-icon-crop-preview-homeicon">
					<img src="<?php echo esc_url( $image[0] ); ?>" id="preview-homeicon" alt="<?php esc_attr_e( 'Preview Home Icon' , 'msits-site-icon' ); ?>" />
				</div>
			</div>
			<img src="<?php echo esc_url( $image[0] ); ?>" id="crop-image" class="site-icon-crop-image"
				width="<?php echo esc_attr( $crop_data['resized_image_data'][0] ); ?>"
				height="<?php echo esc_attr( $crop_data['resized_image_data'][1] ); ?>"
				alt="<?php esc_attr_e( 'Image to be cropped', 'msits-site-icon' ); ?>" />
				
			<p class="site-icon-submit-form"><input name="submit" value="<?php esc_attr_e( 'Save Image', 'msits-site-icon' ); ?>" type="submit" class="button button-primary button-large" /><?php printf( __( ' or <a href="%s">Cancel</a> and go back to the settings.' , 'msits-site-icon' ), esc_url( admin_url( 'options-general.php' ) ) ); ?></p>

			<input name="step" value="3" type="hidden" />
			<input type="hidden" id="crop-x" name="crop-x" />
			<input type="hidden" id="crop-y" name="crop-y" />
			<input type="hidden" id="crop-width" name="crop-w" />
			<input type="hidden" id="crop-height" name="crop-h" />

			<?php wp_nonce_field( 'update-site_icon-3', '_nonce' ); ?>

			</form>
		</div>
		<?php
	}


	/**
	 * All done page admin view
	 *
	 */
	public static function all_done_page() {

		$temp_image_data = get_option( 'site_icon_temp_data' );
		if( ! $temp_image_data ) {
			// start again
			self::select_page();
			return;
		}
		$crop_ration = $temp_image_data['large_image_data'][0] / $temp_image_data['resized_image_data'][0]; // always bigger then 1

		$crop_data = self::convert_coodiantes_from_resized_to_full( $_POST['crop-x'], $_POST['crop-y'], $_POST['crop-w'], $_POST['crop-h'], $crop_ration );

		$image_edit =  wp_get_image_editor( _load_image_to_edit_path( $temp_image_data['large_image_attachment_id'] ) );

		if ( is_wp_error( $image_edit ) ) {
			return $image_edit;
		}

		// Delete the previous site_icon
		$previous_site_icon_id =  get_option( 'site_icon' );
		self::delete_site_icon( $previous_site_icon_id );

		// crop the image
		$image_edit->crop( $crop_data['crop_x'], $crop_data['crop_y'],$crop_data['crop_width'], $crop_data['crop_height'], self::$min_size, self::$min_size );

		$dir = wp_upload_dir();

		$site_icon_filename = $image_edit->generate_filename( dechex ( time() ) . 'v' . self::$version . '_site_icon', null, 'png' );

		// If the attachment is a URL, then change it to a local file name to allow us to save and then upload the cropped image
		$check_url = parse_url( $site_icon_filename );
		if ( isset( $check_url['host'] ) ) {
			$upload_dir = wp_upload_dir();
			$site_icon_filename = $upload_dir['path'] . '/' . basename( $site_icon_filename );
		}

		$image_edit->save( $site_icon_filename );

		add_filter( 'intermediate_image_sizes_advanced', array( 'Msits_Site_Icon', 'additional_sizes' ) );

		$site_icon_id = self::save_attachment(
			__( 'Large Blog Image', 'msits-site-icon' ) ,
			$site_icon_filename,
			'image/png'
		);

		remove_filter( 'intermediate_image_sizes_advanced', array( 'Msits_Site_Icon', 'additional_sizes' ) );

		// Save the site_icon data into option
		update_option( 'site_icon', $site_icon_id );

		//Get the site icon URL ready to sync
		// update_option( 'site_icon_url', site_icon_url( get_current_blog_id(), 512 ) );
		
		
		?>
		<h2 class="site-icon-title"><?php esc_html_e( 'Site Icon', 'msits-site-icon' ); ?> <span class="small"><?php esc_html_e( 'All Done', 'msits-site-icon' ); ?></span></h2>
		<div id="message" class="updated below-h2"><p><?php esc_html_e( 'Your site icon has been uploaded!', 'msits-site-icon' ); ?> <a href="<?php echo esc_url( admin_url( 'options-general.php' ) ); ?>" ><?php esc_html_e( 'Back to General Settings' , 'msits-site-icon' ); ?></a></p></div>
		<?php echo gr_get_site_icon( null, $size = '128' ); ?>
		<?php echo gr_get_site_icon( null, $size = '48' ); ?>
		<?php echo gr_get_site_icon( null, $size = '16' ); ?>

		<?php
	}


	/**
	 * This function is used to pass data to the localize script
	 * so that we can center the cropper and also set the minimum
	 * cropper if we still want to show the
	 *
	 * @param  int $large_width
	 * @param  int $large_height
	 * @param  int $resized_width
	 * @param  int $resized_height
	 * @return array
	 */
	public static function initial_crop_data( $large_width, $large_height, $resized_width, $resized_height ) {
		$init_x = 0;
		$init_y = 0;

		$ration = $large_width / $resized_width;
		$min_crop_size = ( self::$min_size / $ration );

		// Landscape format ( width > height )
		if( $resized_width > $resized_height ) {
			$init_x = ( self::$page_crop - $resized_height ) / 2;
			$init_size = $resized_height;
		}

		// Portrait format ( height > width )
		if( $resized_width < $resized_height ) {
			$init_y = ( self::$page_crop - $resized_width ) / 2;
			$init_size = $resized_height;
		}

		// Square height == width
		if( $resized_width = $resized_height ) {
			$init_size = $resized_height;
		}

		return array(
			'init_x'    => $init_x,
			'init_y'    => $init_y,
			'init_size' => $init_size,
			'min_size'  => $min_crop_size
		);
	}


	/**
	 * Delete the temporary created data and attachments
	 *
	 * @return null
	 */
	public static function delete_temporay_data() {
		// This should automatically delete the temporary files as well
		delete_option( 'site_icon_temp_data' );
	}


	/**
	 * Function gets fired when delete_option( 'site_icon_temp_data' ) is run.
	 *
	 * @param  $option string
	 * @return null
	 */
 	public static function delete_temp_data( $option ) {
		if( 'site_icon_temp_data' == $option ) {
			$temp_image_data = get_option( 'site_icon_temp_data' );

			remove_action( 'delete_attachment', array( 'Msits_Site_Icon', 'delete_attachment_data' ), 10, 1);

			wp_delete_attachment( $temp_image_data['large_image_attachment_id'] , true );
			wp_delete_attachment( $temp_image_data['resized_image_attacment_id'] , true );
		}
		return null;
	}


	/**
	 * @param $post_id
	 */
	public static function delete_attachment_data( $post_id ) {
		// The user could be deleting the site_icon image
		$site_icon_id = get_option( 'site_icon' );
		if( $site_icon_id &&  $post_id == $site_icon_id ) {
			delete_option( 'site_icon' );
			// delete_option( 'site_icon_url' );
		}
		// The user could be deleting the temporary images
	}


	/**
	 * @param $check
	 * @param $post_id
	 * @param $meta_key
	 * @param $single
	 *
	 * @return mixed
	 */
	public static function delete_attachment_images( $check, $post_id, $meta_key, $single ) {
		$site_icon_id = get_option( 'site_icon' );
		if( $post_id == $site_icon_id && '_wp_attachment_backup_sizes' == $meta_key && true == $single )
			add_filter( 'intermediate_image_sizes', array( 'Msits_Site_Icon', 'intermediate_image_sizes' ) );
		return $check;
	}

	/**
	 * Delete the blavatar and all the attached data
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	public static function delete_site_icon( $id ) {
		// We add the filter to make sure that we also delete all the added images
		add_filter( 'intermediate_image_sizes', 	array( 'Msits_Site_Icon', 'intermediate_image_sizes' ) );
		wp_delete_attachment( $id , true );
		remove_filter( 'intermediate_image_sizes', 	array( 'Msits_Site_Icon', 'intermediate_image_sizes' ) );
		// for good measure also
		self::delete_temporay_data();

		return delete_option( 'site_icon' );
	}


	/**
	 * @param $crop_x
	 * @param $crop_y
	 * @param $crop_width
	 * @param $crop_height
	 * @param $ratio
	 *
	 * @return array
	 */
	public static function convert_coodiantes_from_resized_to_full( $crop_x, $crop_y, $crop_width, $crop_height, $ratio ) {
		return array(
			'crop_x' 	  => floor( $crop_x * $ratio ),
			'crop_y' 	  => floor( $crop_y * $ratio ),
			'crop_width'  => floor( $crop_width * $ratio ),
			'crop_height' => floor( $crop_height * $ratio ),
			);
	}


	/**
	 * Handle the uploaded image
	 *
	 * @param $uploaded_file
	 *
	 * @return mixed
	 */
	public static function handle_file_upload( $uploaded_file ) {

		// check that the image actually is a file with size
		if( !isset( $uploaded_file ) || ($uploaded_file['size'] <= 0 ) ) {
			return new WP_Error( 'broke', __( 'Please upload a file.', 'msits-site-icon' ) );
		}

		$arr_file_type = wp_check_filetype( basename( $uploaded_file['name'] ) );
		$uploaded_file_type = $arr_file_type['type'];
		if( ! in_array( $uploaded_file_type, self::$accepted_file_types ) ) {
			// Create a temp file which should be deleted at when the scipt stops
			return new WP_Error( 'broke', __( 'The file that you uploaded is not an accepted file type. Please try again.', 'msits-site-icon' ) );
		}

		$image = wp_handle_upload( $uploaded_file, array( 'test_form' => false ) );

		if(  is_wp_error( $image ) ) {
  			// this should contain the error message returned from wp_handle_upload
  			unlink( $image['file'] ); // Lets delete the file since we are not going to be using it
			return $image;
		}

		// Lets try to crop the image into smaller files.
		// We will be doing this later so it is better if it fails now.
		$image_edit = wp_get_image_editor( $image['file'] );
		if ( is_wp_error( $image_edit ) ) {
			// this should contain the error message from WP_Image_Editor
			unlink( $image['file'] ); // lets delete the file since we are not going to be using it
			return $image_edit;
		}

		$image_size = getimagesize( $image['file'] );

		if( $image_size[0] < self::$min_size || $image_size[1] < self::$min_size ) {

			if( $image_size[0] < self::$min_size ) {
				return new WP_Error( 'broke', sprintf( __( 'The image that you uploaded is smaller than %upx in width.', 'msits-site-icon' ), self::$min_size ) );
			}

			if( $image_size[1] < self::$min_size ) {
				return new WP_Error( 'broke', sprintf( __( 'The image that you uploaded is smaller than %upx in height.', 'msits-site-icon' ), self::$min_size ) );
			}
		}

		// Save the image as an attachment for later use.
		$large_attachment_id = self::save_attachment(
			__( 'Temporary Large Image for Blog Image', 'msits-site-icon' ) ,
			$image['file'],
			$uploaded_file_type,
			false
		);

		// Let's resize the image so that the user can easier crop an image that in the admin view
		$image_edit->resize( self::$page_crop, self::$page_crop, false );
		$dir = wp_upload_dir();

		$resized_filename = $image_edit->generate_filename( 'temp', null, null );
		$image_edit->save( $resized_filename );

		$resized_attach_id = self::save_attachment(
			__( 'Temporary Resized Image for Blog Image', 'msits-site-icon' ),
			$resized_filename,
			$uploaded_file_type,
			false
		);

		$resized_image_size = getimagesize( $resized_filename );
		// Save all of this into the the database for that we can work with it later.
		update_option( 'site_icon_temp_data', array(
			'large_image_attachment_id'  => $large_attachment_id,
			'large_image_data'           => $image_size,
			'resized_image_attacment_id' => $resized_attach_id,
			'resized_image_data'         => $resized_image_size
		) );

		return wp_get_attachment_image_src( $resized_attach_id, 'full' );
	}


	/**
	 * Save Blavatar files to Media Library
	 *
	 * @param  string  	$title
	 * @param  string  	$filename
	 * @param  string  	$file_type
	 * @param  boolean 	$generate_meta
	 * @return int 		$attactment_id
	 */
	public static function save_attachment( $title, $file, $file_type, $generate_meta = true ) {

		$filename =  _wp_relative_upload_path( $file );

		$wp_upload_dir = wp_upload_dir();
		$attachment = array(
		 	'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
			'post_mime_type' => $file_type,
			'post_title' 	 => $title,
			'post_content' 	 => '',
			'post_status' 	 => 'inherit'
		);
		$attachment_id = wp_insert_attachment( $attachment, $filename );

		if( ! function_exists( 'wp_generate_attachment_metadata' ) )  {
			// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
		}
		if( !$generate_meta )
			add_filter( 'intermediate_image_sizes_advanced', array( 'Msits_Site_Icon', 'only_thumbnail_size' ) );

		// Generate the metadata for the attachment, and update the database record.
		$attach_data = wp_generate_attachment_metadata( $attachment_id, $file );
		wp_update_attachment_metadata( $attachment_id, $attach_data );

		if( !$generate_meta ) {
			remove_filter( 'intermediate_image_sizes_advanced', array( 'Msits_Site_Icon', 'only_thumbnail_size' ) );
		}

		return $attachment_id;
	}


	/**
	 * Add additional sizes to be made when creating the site_icon images
	 *
	 * @param  array $sizes
	 * @return array
	 */
	public static function additional_sizes( $sizes ) {
		/**
		 * Filter the different dimensions that a site icon is saved in.
		 *
		 * @module site-icon
		 *
		 * @since 3.2.0
		 *
		 * @param array $site_icon_sizes Sizes available for the Site Icon.  Default is array(256, 128, 80, 64, 32, 16).
		 */
		self::$site_icon_sizes = apply_filters( 'site_icon_image_sizes', self::$site_icon_sizes );
		// use a natural sort of numbers
		natsort( self::$site_icon_sizes );
		self::$site_icon_sizes = array_reverse ( self::$site_icon_sizes );

		// ensure that we only resize the image into
		foreach( $sizes as $name => $size_array ) {
			if( $size_array['crop'] ){
				$only_crop_sizes[ $name ] = $size_array;
			}
		}

		foreach( self::$site_icon_sizes as $size ) {
			if( $size < self::$min_size ) {

				$only_crop_sizes['site_icon-'.$size] =  array(
					"width" => $size,
					"height"=> $size,
					"crop"  => true,
				);
			}
		}

		return $only_crop_sizes;
	}


	/**
	 * Helps us delete site_icon images that
	 *
	 * @param  [type] $sizes [description]
	 *
	 * @return array
	 */
	public static function intermediate_image_sizes( $sizes ) {
		self::$site_icon_sizes = apply_filters( 'site_icon_image_sizes', self::$site_icon_sizes );
		foreach( self::$site_icon_sizes as $size ) {
			$sizes[] = 'site_icon-'.$size;
		}
		return $sizes;
	}


	/**
	 * Only resize the image to thumbnail so we can use
	 * Use when resizing temporary images. This way we can see the temp image in Media Gallery.
	 *
	 * @param  array $sizes
	 * @return array
	 */
	public static function only_thumbnail_size( $sizes ){
		foreach( $sizes as $name => $size_array ) {
			if( 'thumbnail' == $name ) {
				$only_thumb['thumbnail'] = $size_array;
			}
		}
		return $only_thumb;
	}



} // end Msits_Site_Icon



if( ! function_exists( 'gr_get_site_icon' ) ) :
function gr_get_site_icon( $blog_id = null, $size = '512', $default = '', $alt = false ) {

	if( ! is_int( $blog_id ) )
		$blog_id = get_current_blog_id();

	$size  = esc_attr( $size );
	$class = "avatar avatar-$size";
	$alt = ( $alt ? esc_attr( $alt ) : __( 'Site Icon', 'msits-site-icon' ) );
	$src = esc_url( gr_site_icon_url( $blog_id, $size, $default ) );
	$avatar = "<img alt='{$alt}' src='{$src}' class='$class' height='{$size}' width='{$size}' />";
	/**
	 * Filters the display options for the Site Icon.
	 *
	 * @module site-icon
	 *
	 * @since 3.2.0
	 *
	 * @param string $avatar The Site Icon in an html image tag.
	 * @param int    $blog_id The local site Blog ID.
	 * @param string $size The size of the Site Icon, default is 512.
	 * @param string $default The default URL for the Site Icon.
	 * @param string $alt The alt tag for the avatar.
	 */
	return apply_filters( 'msits-get_site_icon', $avatar, $blog_id, $size, $default, $alt );
}
endif;


if( ! function_exists( 'gr_site_icon_url' ) ) :
function gr_site_icon_url( $blog_id = null, $size = '512', $default = false ) {
	$url = '';
	if( ! is_int( $blog_id ) )
		$blog_id = get_current_blog_id();

		$site_icon_id = get_option( 'site_icon' );


	if( ! $site_icon_id  ) {
		if( $default === false && defined( 'SITE_ICON_DEFAULT_URL' ) )
			$url =  SITE_ICON_DEFAULT_URL;
		else
			$url = $default;
	} else {
		if( $size >= 512 ) {
			$size_data = 'full';
		} else {
			$size_data = array( $size, $size );
		}
		$url_data = wp_get_attachment_image_src( $site_icon_id, $size_data );
		$url = $url_data[0];
	}

	return $url;
}
endif;