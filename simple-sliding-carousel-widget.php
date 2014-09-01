<?php

/**
 * Simple Sliding Carousel Widget
 */

// Exit if this file is directly accessed
if ( !defined( 'ABSPATH' ) ) exit;

class SZ_Simple_Sliding_Carousel_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'simple_sliding_carousel-widget', 'description' => __('Simple Sliding Carousel Widget'));
		$control_ops = array('width' => 400, 'height' => 350);
		parent::__construct('text', __('Simple Sliding Carousel'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		extract($args);

		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		/**
		 * Filter the content of the Text widget.
		 *
		 * @since 2.3.0
		 *
		 * @param string    $widget_text The widget content.
		 * @param WP_Widget $instance    WP_Widget instance.
		 */
		$text = apply_filters( 'widget_text', empty( $instance['text'] ) ? '' : $instance['text'], $instance );

		echo $before_widget;

		/**
		 * Display the slider template
		 */
		if ( class_exists( 'SZ_Simple_Sliding_Carousel' ) ) {
				$slider= SZ_Simple_Sliding_Carousel::get_instance();

				$slider->do_slider( array() );

		}

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '' ) );
		$title = strip_tags($instance['title']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>


<?php
	}
}

function register_sz_simple_sliding_carousel_widget() {
	register_widget( 'SZ_Simple_Sliding_Carousel_Widget' );
}
add_action( 'widgets_init', 'register_sz_simple_sliding_carousel_widget' );
