<?php
/**
 * Contact Form 7 reCAPTCHA Integrations.
 *
 * @since 1.0.0
 *
 * @package Recaptcha
 * @category Functions
 * @author Astoundify
 */

/**
 * Remove Default CF7 Recaptcha Tab
 *
 * @since 1.0.0
 */
function astoundify_recaptcha_wpcf7_remove_form_tag() {
	remove_action( 'wpcf7_init', 'wpcf7_recaptcha_add_form_tag_recaptcha' );
}
add_action( 'init', 'astoundify_recaptcha_wpcf7_remove_form_tag', 9 );

/**
 * Add Our Own Recaptcha Implementation for CF7
 *
 * @since 1.0.0
 */
function astoundify_recaptcha_wpcf7_add_form_tag() {
	$recaptcha = WPCF7_RECAPTCHA::get_instance();

	if ( $recaptcha->is_active() ) {
		wpcf7_add_form_tag(
			'recaptcha',
			'astoundify_recaptcha_wpcf7_form_tag_handler',
			array(
				'display-block' => true,
			)
		);
	}
}
add_action( 'wpcf7_init', 'astoundify_recaptcha_wpcf7_add_form_tag' );

/**
 * Recaptcha Form Tag Handler
 *
 * @since 1.0.0
 *
 * @param array $tag CF7 Tags.
 * @return string
 */
function astoundify_recaptcha_wpcf7_form_tag_handler( $tag ) {
	$options = array();

	// Theme:
	$theme = $tag->get_option( 'theme', '(dark|light)', true );
	if ( $theme ) {
		$options['theme'] = $theme;
	}

	// Size:
	$size = $tag->get_option( 'size', '(normal|compact)', true );
	if ( $size ) {
		$options['size'] = $size;
	}

	return astoundify_recaptcha_field( false, 'wpcf7', $options );
}
