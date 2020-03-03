/**
 * Astoundify ReCaptcha
 *
 * @since 1.0.0
 */
var astoundifyRecaptcha = function() {
	jQuery( '.astoundify-recaptcha' ).each( function(i) {
		grecaptcha.render( jQuery( this )[0], {
			sitekey: astoundifyRecaptchaData.sitekey,
			theme: jQuery( this ).data( 'theme' ),
			size: jQuery( this ).data( 'size' ),
		} );
	} );
};