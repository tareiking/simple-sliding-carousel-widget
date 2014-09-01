jQuery(document).ready(function ($) {

	if( $( '.slick-slider' ).length )
	{
		$('.slick-slider').slick({
			slidesToShow: 1,
			slidesToScroll: 1,
			autoplay: true,
			autoplaySpeed: 3000,
			dots: true,
			arrows: false
		});
	}

});