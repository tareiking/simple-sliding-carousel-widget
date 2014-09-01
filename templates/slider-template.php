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
<div class="slick-widget-area">
	<div class="slick-slider">
		<?php while ( $slider_items->have_posts() ) : $slider_items->the_post(); ?>

			<div class="slick-content ">

				<h3><?php echo the_title(); ?></h3>

				<?php if ( has_post_thumbnail() ): ?>
					<?php the_post_thumbnail( 'slider-thumb' ); ?> <br>
				<?php endif; ?>

				<a href="#" class="button">Visit Link</a><br>

			</div>
		<?php endwhile; ?>
	</div>
</div>
<?php else: ?>
	<p>There aren't any sliders.</p>
<?php endif; ?>
<?php wp_reset_query(); ?>