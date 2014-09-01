<?php

/*
Plugin Name: Pico Slider
Plugin URI: http://www.sennza.com.au/
Description: A slider that uses a Custom Post Type and Sequence.js for the slide transitions
Author: Sennza Pty Ltd, Bronson Quick, Tarei King, Lachlan MacPherson
Author URI: http://www.sennza.com.au/
Version: 1.1
License: GPL2
GitHub Plugin URI: https://github.com/sennza/pico-slider
*/

// Exit if this file is directly accessed
if ( !defined( 'ABSPATH' ) ) exit;

class Pico_Slider {
	private static $instance;

	static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new Pico_Slider;
		}

		return self::$instance;
	}

	public function __construct() {
		add_action( 'init',                      array( $this, 'register_slider' ) );
		add_action( 'wp_enqueue_scripts',        array( $this, 'slider_scripts' ) );
		add_action( 'save_post',                 array( $this, 'save_slider_meta' ) );
		add_action( 'add_meta_boxes',            array( $this, 'add_slider_meta_boxes' ) );
		add_filter( 'enter_title_here',          array( $this, 'change_slider_title' ) );
		add_filter( 'admin_post_thumbnail_html', array( $this, 'slider_post_thumbnail_html' ) );
		register_activation_hook( __FILE__,      array( $this, 'slider_rewrite_flush' ) );
		register_deactivation_hook( __FILE__,    array( $this, 'slider_rewrite_flush' ) );
	}

	/**
	 * Register our slider custom post type
	 */
	public function register_slider() {
		$labels = array(
			'name'               => _x( 'Slide', 'slider' ),
			'singular_name'      => _x( 'Slide', 'slider' ),
			'add_new'            => _x( 'Add New', 'slider' ),
			'add_new_item'       => _x( 'Add New Slide', 'slider' ),
			'edit_item'          => _x( 'Edit Slide', 'slider' ),
			'new_item'           => _x( 'New Slide', 'slider' ),
			'view_item'          => _x( 'View Slide', 'slider' ),
			'search_items'       => _x( 'Search Slides', 'slider' ),
			'not_found'          => _x( 'No slides found', 'slider' ),
			'not_found_in_trash' => _x( 'No slides found in Trash', 'slider' ),
			'parent_item_colon'  => _x( 'Parent Slide:', 'slider' ),
			'menu_name'          => _x( 'Slider', 'slider' ),
			'all_items'          => _x( 'All Slides', 'slider' ),
		);

		$cpt_defaults = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			'description'         => _x( 'A custom post type to easily generate slideshows', 'slider' ),
			'supports'            => array( 'title', 'editor', 'thumbnail', 'revisions' ),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-slides',
			'menu_position'       => 20,
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => true,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => true,
			'capability_type'     => 'post',
		);

		$cpt_args = apply_filters( 'pico_slider_cpt_args', $cpt_args = array() );

		$cpt_args = wp_parse_args( $cpt_args, $cpt_defaults );

		register_post_type( 'slider', $cpt_args );

		$image_defaults = array(
			'image_width'         => 970, 		// Height in Pixels: 9999 == any height
			'image_height'        => 9999, 		// Height in Pixels: 9999 == any height
		);

		$image_args = apply_filters( 'pico_slider_image_args', $image_args = array() );

		$image_args = wp_parse_args( $image_args, $image_defaults );

		add_image_size( 'slider-thumb', $image_args['image_width'], $image_args['image_height'] );
	}

	/**
	 * Find the slider template and generate the file. This checks inside our theme then falls back to the template
	 * inside the plugin
	 *
	 * @param $args
	 */
	public function do_slider( $args ) {
		$plugindir        = dirname( __FILE__ );
		$templatefilename = 'slider-template.php';
		if ( file_exists( TEMPLATEPATH . '/' . $templatefilename ) ) {
			$return_template = TEMPLATEPATH . '/' . $templatefilename;
			require_once( $return_template );
		} else {
			$return_template = $plugindir . '/templates/' . $templatefilename;
			require_once( $return_template );
		}
	}

	/**
	 * Enqueue our scripts
	 */
	public function slider_scripts() {
		if ( is_front_page() ):
			wp_enqueue_script( 'pico-flexslider', plugins_url( '/js/jquery.flexslider-min.js', __FILE__ ), array( 'jquery' ), '2.1', true );
			wp_enqueue_script( 'slider-main', plugins_url( '/js/jquery.slider-main.js', __FILE__ ), array(
					'jquery',
					'pico-flexslider'
				), '0.8', true );
			wp_enqueue_style( 'pico-flexslider-styles', plugins_url( '/assets/css/flexslider.css', __FILE__ ), array(), 1.0 );
		endif;
	}

	/**
	 * Add our meta boxes
	 */
	public function add_slider_meta_boxes() {
		$meta_defaults = array (
			'box_1'           => true, 	// Boolean, true or false
			'box_2'           => true, 	// Boolean, true or false
			'video'           => true, 	// Boolean, true or false
		);

		$meta_args = apply_filters( 'pico_slider_meta_args', $meta_args = array() );

		$meta_args = wp_parse_args( $meta_args, $meta_defaults );

		if ( $meta_args['video'] ) {
			add_meta_box( 'slider_video_meta_box', __( 'Video Url' ), array(
					$this,
					'slider_video_meta_box'
				), 'slider', 'side', 'core'
			);
		};

		if ( $meta_args['box_1'] || $meta_args['box_2'] ) {
			add_meta_box( 'cta_meta_box', __( 'Calls To Action' ), array(
					$this,
					'cta_meta_box'
				), 'slider', 'side', 'core'
			);
		}
		remove_meta_box( 'postimagediv', 'slider', 'side' );
		add_meta_box( 'postimagediv', 'Slider Image', 'post_thumbnail_meta_box', 'slider', 'side' );
	}

	/**
	 * Generate our Video meta box
	 */
	public function slider_video_meta_box() {
		global $post_ID;
		?>

		<div id="video_meta">
			<?php
			$slider_video_url = get_post_meta( $post_ID, 'slider_video_url', true );

			// Video Url
			?>
			<p>
				<label for="slider_video_url" style="width:25%; display:inline-block;"><?php _e( 'Video URL:' ); ?></label>
				<input type="text" id="slider_video_url" name="slider_video_url" value="<?php echo $slider_video_url; ?>" style="width:73%; display:inline-block;" />

			<p>The link to your video on YouTube or Vimeo.</p>
			</p>
		</div>
	<?php
	}

	/**
	 * Generate our custom meta boxes for the Call To Actions
	 */
	public function cta_meta_box() {
		global $post_ID;
		$meta_defaults = array (
			'box_1'           => true, 	// Boolean, true or false
			'box_2'           => true, 	// Boolean, true or false
			'video'           => true, 	// Boolean, true or false
		);

		$meta_args = apply_filters( 'pico_slider_meta_args', $meta_args = array() );

		$meta_args = wp_parse_args( $meta_args, $meta_defaults ); ?>

		<div id="slider_meta">
			<?php
			wp_nonce_field( plugin_basename( __FILE__ ), 'slider_nonce' );

			// Button 1 Link
			if ( $meta_args['box_1'] ) {
				$button_1_link  = get_post_meta( $post_ID, 'button_1_link', true );
				$button_1_title = get_post_meta( $post_ID, 'button_1_title', true );
				?>
				<p>
					<label for="button_1_link" style="width:80px; display:inline-block;"><?php _e( "Button 1 Link:" ); ?></label>
					<input type="text" id="button_1_link" name="button_1_link" value="<?php echo wptexturize( esc_html( $button_1_link ) ); ?>" size="25" />
				</p>
				<?php
				// Button 1 Title ?>
				<p>
					<label for="button_1_title" style="width:80px; display:inline-block;"><?php _e( "Button 1 Title:" ); ?></label>
					<input type="text" id="button_1_title" name="button_1_title" value="<?php echo wptexturize( esc_html( $button_1_title ) ); ?>" size="25" />
				</p>
				<?php
			}
			// End Button 1

			// Button 2 Link
			if ( $meta_args['box_2'] ) {
				$button_2_link  = get_post_meta( $post_ID, 'button_2_link', true );
				$button_2_title = get_post_meta( $post_ID, 'button_2_title', true );
				?>
				<p>
					<label for="button_2_link" style="width:80px; display:inline-block;"><?php _e( "Button 2 Link:" ); ?></label>
					<input type="text" id="button_2_link" name="button_2_link" value="<?php echo wptexturize( esc_html( $button_2_link ) ); ?>" size="25" />
				</p>
				<?php
				// Button 2 Title ?>
				<p>
					<label for="button_2_title" style="width:80px; display:inline-block;"><?php _e( "Button 2 Title:" ); ?></label>
					<input type="text" id="button_2_title" name="button_2_title" value="<?php echo wptexturize( esc_html( $button_2_title ) ); ?>" size="25" />
				</p>
				<?php
			}
			// End Button 2
			?>
		</div>
	<?php
	}


	/**
	 * Save the meta associated with a testimonial
	 *
	 * @since 1.0
	 */
	public function save_slider_meta() {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( empty( $_POST['slider_nonce'] ) || ! wp_verify_nonce( $_POST['slider_nonce'], plugin_basename( __FILE__ ) ) ) {
			return;
		}
		$meta_defaults = array (
			'box_1'           => true, 	// Boolean, true or false
			'box_2'           => true, 	// Boolean, true or false
			'video'           => true, 	// Boolean, true or false
		);

		$meta_args = apply_filters( 'pico_slider_meta_args', $meta_args = array() );

		$meta_args = wp_parse_args( $meta_args, $meta_defaults );

		$valid = array(
			'alignleft'   => 'alignleft',
			'alignright'  => 'alignright',
			'aligncenter' => 'aligncenter',
			'alignnone'   => 'alignnone',
		);
		if ( ! array_key_exists( $_POST['imagealignment'], $valid ) ) {
			$_POST['imagealignment'] = 'alignnone';
		}
		update_post_meta( $_POST['ID'], 'imagealignment', $_POST['imagealignment'] );

		// Video Box
		if ( $meta_args['video'] ) {
			update_post_meta( $_POST['ID'], 'slider_video_url', esc_url( $_POST['slider_video_url'] ) );
		}

		// CTA Button 1
		if ( $meta_args['box_1'] ) {
			update_post_meta( $_POST['ID'], 'button_1_link', esc_url( $_POST['button_1_link'] ) );
			update_post_meta( $_POST['ID'], 'button_1_title', esc_html( $_POST['button_1_title'] ) );
		}

		// CTA Button 2
		if ( $meta_args['box_1'] ) {
			update_post_meta( $_POST['ID'], 'button_2_link', esc_url( $_POST['button_2_link'] ) );
			update_post_meta( $_POST['ID'], 'button_2_title', esc_html( $_POST['button_2_title'] ) );
		}
	}

	/**
	 * Flush the rewrite rules on activation and deactivation
	 */
	public function slider_rewrite_flush() {
		Pico_Slider::get_instance();
		flush_rewrite_rules();
	}

	/**
	 * Filter the title placeholder text
	 *
	 * @param $title
	 * @return string|void
	 */
	public function change_slider_title( $title ) {
		$screen = get_current_screen();

		if ( 'slider' == $screen->post_type ) {
			$title = __( 'Add Slider Title', 'slider' );
		}

		return $title;
	}

	/**
	 * Add some alignment options for our slider
	 *
	 * @param $output
	 * @return mixed|string
	 */
	function slider_post_thumbnail_html( $output ) {
		global $post_type, $post_ID;

		// beware of translated admin
		if ( ! empty ( $post_type ) && 'slider' == $post_type ) {
			$image_alignment = get_post_meta( $post_ID, 'imagealignment', true );
			$output          = str_replace( 'Set featured image', 'Select / Upload a slider image', $output );
			$output          = str_replace( 'Remove featured image', 'Remove slider image', $output );

			if ( has_post_thumbnail( $post_ID ) ) {
				$output .= "<p>Choose the image alignment:</p>";
				$output .= '<label for="alignleft"><input type="radio" name="imagealignment" value="alignleft" id="alignleft"' . checked( $image_alignment, 'alignleft', false ) . '> Left</input></label><br>';
				$output .= '<label for="aligncenter"><input type="radio" name="imagealignment" value="aligncenter" id="aligncenter"' . checked( $image_alignment, 'aligncenter', false ) . '> Center</input></label><br>';
				$output .= '<label for="alignright"><input type="radio" name="imagealignment" value="alignright" id="alignright"' . checked( $image_alignment, 'alignright', false ) . '> Right</input></label><br>';
				$output .= '<label for="alignnone"><input type="radio" name="imagealignment" value="alignnone" id="alignnone"' . checked( $image_alignment, 'alignnone', false ) . '> None</input></label><br>';
			}
		}

		return $output;
	}

}

Pico_Slider::get_instance();