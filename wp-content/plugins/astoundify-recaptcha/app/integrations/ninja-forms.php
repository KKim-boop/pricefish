<?php
/**
 * Ninja Forms reCAPTCHA Integrations.
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
function astoundify_recaptcha_nf_scripts( $form_id ) {

	// Remove recaptcha script.
	wp_deregister_script( 'nf-google-recaptcha' );

	// Add our own.
	wp_register_script( 'nf-google-recaptcha', ASTOUNDIFY_RECAPTCHA_URL . 'resources/assets/js/ninja-forms-recaptcha.js', array( 'astoundify-google-recaptcha', 'jquery' ), ASTOUNDIFY_RECAPTCHA_VERSION );
}
add_action( 'ninja_forms_enqueue_scripts', 'astoundify_recaptcha_nf_scripts' );

/**
 * Add recaptcha template
 *
 * @since 1.0.0
 */
function astoundify_recaptcha_nf_template( $paths ) {
	$new_path = array( ASTOUNDIFY_RECAPTCHA_PATH . 'resources/templates/ninja-forms/' );
	$paths    = array_merge( $new_path, $paths );
	return $paths;
}
add_filter( 'ninja_forms_field_template_file_paths', 'astoundify_recaptcha_nf_template' );
