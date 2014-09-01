<?php
$defaults  = array(
	'post_type'       => 'simpleslide',
	'posts_per_page'  => -1,
	'call_to_actions' => false,
	'has_video'       => false,
	'secondary_nav'   => true,
	'alignment'      => false,
);

$args = wp_parse_args( $args, $defaults );
$slider_items = new WP_Query( $args );

if ( $slider_items->have_posts() ): ?>
	<?php
	$button_1_link       = get_post_meta( $slider_items->post->ID, 'button_1_link', true );
	$button_1_title      = get_post_meta( $slider_items->post->ID, 'button_1_title', true );
	$button_2_link       = get_post_meta( $slider_items->post->ID, 'button_2_link', true );
	$button_2_title      = get_post_meta( $slider_items->post->ID, 'button_2_title', true ); ?>


<div class="slick-widget-area">
	<div class="slick-slider">
		<?php while ( $slider_items->have_posts() ) : $slider_items->the_post(); ?>

			<div class="slick-content">

				<h3><?php echo the_title(); ?></h3>

				<?php if ( get_the_content() != '' ): ?>
					<?php the_content(); ?>
				<?php endif ?>

				<?php if ( has_post_thumbnail() ): ?>
					<div class="slick-image-wrap">
						<?php the_post_thumbnail( 'slider-thumb', array( 'class' => 'slick-image' ) ); ?>
					</div>
				<?php endif; ?>

				<a href="<?php echo $button_1_link; ?>" class="button slick-button"><?php echo $button_1_title; ?></a>

			</div>
		<?php endwhile; ?>
	</div>
</div>
<?php else: ?>
	<p>There aren't any sliders.</p>
<?php endif; ?>
<?php wp_reset_query(); ?>