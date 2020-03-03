<?php
/**
 * WooCommerce reCAPTCHA Integrations.
 *
 * @since 1.0.0
 *
 * @package Recaptcha
 * @category Functions
 * @author Astoundify
 */

/**
 * Add WooCommerce Locations.
 *
 * @since 1.0.0
 *
 * @param array $locations Locations.
 * @return array
 */
function astoundify_recaptcha_woocommerce_locations( $locations ) {
	$locations['woocommerce'] = array(
		'label' => esc_html__( 'WooCommerce', 'astoundify-recaptcha' ),
		'forms' => array(
			'login'    => esc_html__( 'Login form', 'astoundify-recaptcha' ),
			'register' => esc_html__( 'Register form', 'astoundify-recaptcha' ),
		),
	);
	return $locations;
}
add_filter( 'astoundify_recaptcha_locations', 'astoundify_recaptcha_woocommerce_locations' );


/**
 * Add Captcha to WooCommerce Login Form
 *
 * @since 1.0.0
 */
function astoundify_recaptcha_woocommerce_login_form() {
	$is_active = astoundify_recaptcha_is_active( 'woocommerce', 'login' );
	if ( ! $is_active ) {
		return;
	}
	echo astoundify_recaptcha_field( false, 'woocommerce_login_form' );
}
add_action( 'woocommerce_login_form', 'astoundify_recaptcha_woocommerce_login_form' );


/**
 * Add Captcha to WooCommerce Register Form
 *
 * @since 1.0.0
 */
function astoundify_recaptcha_woocommerce_register_form() {
	$is_active = astoundify_recaptcha_is_active( 'woocommerce', 'register' );
	if ( ! $is_active ) {
		return;
	}
	echo astoundify_recaptcha_field( false, 'woocommerce_register_form' );
}
add_action( 'woocommerce_register_form', 'astoundify_recaptcha_woocommerce_register_form' );

/**
 * Authenticate Login
 *
 * @since 1.0.0
 *
 * @param WP_Error $errors Error.
 * @return WP_Error
 */
function astoundify_recaptcha_woocommerce_login_authenticate( $errors ) {
	$is_active = astoundify_recaptcha_is_active( 'woocommerce', 'login' );
	if ( ! $is_active ) {
		return $errors;
	}

	// Verify captcha.
	if ( ! isset( $_POST['g-recaptcha-response'] ) || false === astoundify_recaptcha_verify( $_POST['g-recaptcha-response'] ) ) {
		$errors->add( 'recaptcha', astoundify_recaptcha_error_notice() );
	}
	return $errors;
}
add_filter( 'woocommerce_process_login_errors', 'astoundify_recaptcha_woocommerce_login_authenticate', 99 );

/**
 * Authenticate Registration
 *
 * @since 1.0.0
 *
 * @param WP_Error $errors Error.
 * @return WP_Error
 */
function astoundify_recaptcha_woocommerce_register_authenticate( $errors ) {
	$is_active = astoundify_recaptcha_is_active( 'woocommerce', 'register' );
	if ( ! $is_active ) {
		return $errors;
	}

	// Verify captcha.
	if ( ! isset( $_POST['g-recaptcha-response'] ) || false === astoundify_recaptcha_verify( $_POST['g-recaptcha-response'] ) ) {
		$errors->add( 'recaptcha', astoundify_recaptcha_error_notice() );
	}
	return $errors;
}
add_filter( 'woocommerce_registration_errors', 'astoundify_recaptcha_woocommerce_register_authenticate', 99 );
