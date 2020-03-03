/**
 * NinjaForms ReCaptcha
 *
 * @since 1.0.0
 */

var astoundifyRecaptchaNF = function() {
	jQuery( '.astoundify-recaptcha-nf' ).each( function(i) {
		grecaptcha.render( jQuery( this )[0], {
			sitekey: astoundifyRecaptchaData.sitekey,
			theme: jQuery( this ).data( 'theme' ),
			size: jQuery( this ).data( 'size' ),
		} );
	} );
};

jQuery( document ).on( 'nfFormReady', function() {
	astoundifyRecaptchaNF();
});
