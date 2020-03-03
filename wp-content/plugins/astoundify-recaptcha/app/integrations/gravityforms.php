<?php
/**
 * Gravity reCAPTCHA Integrations.
 *
 * @since 1.0.0
 *
 * @package Recaptcha
 * @category Functions
 * @author Astoundify
 */

/**
 * Deregister Recaptcha Script
 *
 * @since 1.0.0
 */
function astoundify_recaptcha_gfforms_scripts( $form_id ) {

	// Remove duplicate recaptcha script.
	if ( wp_script_is( 'astoundify-google-recaptcha', 'enqueued' ) ) {
		wp_deregister_script( 'gform_recaptcha' );
	}
}
add_action( 'wp_footer', 'astoundify_recaptcha_gfforms_scripts' );
