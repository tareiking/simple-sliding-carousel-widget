<?php
$defaults  = array(
	'post_type'       => 'slider',
	'posts_per_page'  => -1,
	'call_to_actions' => false,
	'has_video'       => false,
	'secondary_nav'   => true,
	'alignment'      => false,
);

$args = wp_parse_args( $args, $defaults );
$slider_items = new WP_Query( $args );

if ( $slider_items->have_posts() ): ?>

	<div class="flexslider">
		<ul class="slides">
			<?php while ( $slider_items->have_posts() ) : $slider_items->the_post(); ?>
				<li>
					<?php if ( has_post_thumbnail() ): ?>
						<?php the_post_thumbnail( 'slider-thumb' ); ?>
					<?php endif; ?>
				</li>
			<?php endwhile; ?>
		</ul>
	</div>
<?php else: ?>
	<p>There aren't any sliders.</p>
<?php endif; ?>
<?php wp_reset_query(); ?>