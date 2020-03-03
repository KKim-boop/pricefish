<?php
/**
 * Helper functions.
 *
 * @since 1.0.0
 *
 * @package Recaptcha
 * @category Functions
 * @author Astoundify
 */

// Do not access this file directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Option helper function
 * To get option easier when merging multiple option in single option name.
 *
 * @since 1.0.0
 *
 * @param string       $option      Option Key.
 * @param string|array $default     Default output.
 * @param string       $option_name Option name.
 */
function astoundify_recaptcha_get_option( $option, $default = '', $option_name = 'astoundify-recaptcha' ) {

	// Bail early if no option defined.
	if ( ! $option ) {
		return false;
	}

	// Get option from db.
	$get_option = get_option( $option_name );

	// Return false if invalid format (not array).
	if ( ! is_array( $get_option ) ) {
		return $default;
	}

	// Get data if set.
	if ( isset( $get_option[ $option ] ) ) {
		return $get_option[ $option ];
	} else {
		return $default;
	}
}

/**
 * Site Key
 *
 * @since 1.0.0
 *
 * @return string|false
 */
function astoundify_recaptcha_site_key() {
	return astoundify_recaptcha_get_option( 'site_key', false );
}

/**
 * Secret Key
 *
 * @since 1.0.0
 *
 * @return string|false
 */
function astoundify_recaptcha_secret_key() {
	return astoundify_recaptcha_get_option( 'secret_key', false );
}

/**
 * Error Notice.
 *
 * @since 1.0.0
 *
 * @return string
 */
function astoundify_recaptcha_error_notice() {
	return wp_kses_post( astoundify_recaptcha_get_option( 'error_notice', __( 'Error. Captcha failed! please try again.', 'astoundify-recaptcha' ) ) );
}

/**
 * Field Label.
 * Only used when applicable.
 *
 * @since 1.0.0
 *
 * @return string
 */
function astoundify_recaptcha_field_label() {
	return wp_kses_post( astoundify_recaptcha_get_option( 'field_label', __( 'Captcha', 'astoundify-recaptcha' ) ) );
}

/**
 * Authenticate Captcha
 *
 * @since 1.0.0
 *
 * @param string $response Posted captcha response from the form.
 * @return bool True if verified.
 */
function astoundify_recaptcha_verify( $response ) {
	global $astoundify_recaptcha_verify_instance;
	if ( isset( $astoundify_recaptcha_verify_instance ) && $astoundify_recaptcha_verify_instance ) {
		return true;
	}

	// Only if secret key is set & response not empty.
	$secret_key = astoundify_recaptcha_secret_key();
	if ( ! $secret_key || ! $response ) {
		return false;
	}

	// Send data to google.
	$raw_response = wp_remote_post(
		esc_url_raw( 'https://www.google.com/recaptcha/api/siteverify' ),
		array(
			'body' => array(
				'secret'   => $secret_key,
				'response' => $response,
				'remoteip' => $_SERVER['REMOTE_ADDR'],
			),
		)
	);

	// Response error, fail.
	if ( is_wp_error( $raw_response ) || 200 != wp_remote_retrieve_response_code( $raw_response ) ) {
		return false;
	}

	// Get data.
	$results = json_decode( trim( wp_remote_retrieve_body( $raw_response ) ), true );

	// If success (return "1"), pass.
	$astoundify_recaptcha_verify_instance = isset( $results['success'] ) && $results['success'] ? true : false;
	return $astoundify_recaptcha_verify_instance;
}

/**
 * Is Active for a Form.
 *
 * @since 1.0.0
 *
 * @param string $location_id Location Group ID.
 * @param string $form_id     Location Form ID.
 * @return bool
 */
function astoundify_recaptcha_is_active( $location_id, $form_id ) {
	$option = get_option( "astoundify-recaptcha-{$location_id}", array() );
	return apply_filters( 'astoundify_recaptcha_is_active', in_array( $form_id, (array) $option ), $location_id, $form_id );
}

/**
 * Register Script to All WordPress Context for easy enqueue.
 *
 * @since 1.0.0
 */
function astoundify_recaptcha_register_script() {
	// Only load if site key is set.
	$site_key = astoundify_recaptcha_site_key();
	if ( ! $site_key ) {
		return;
	}

	// Google reCAPTCHA scripts. Add prefix to make sure it's loaded if other recaptcha plugin also load it.
	wp_register_script( 'astoundify-google-recaptcha', 'https://www.google.com/recaptcha/api.js?onload=astoundifyRecaptcha&render=explicit', array(), ASTOUNDIFY_RECAPTCHA_VERSION, false );

	// Loader callback.
	wp_register_script( 'astoundify-recaptcha', ASTOUNDIFY_RECAPTCHA_URL . 'resources/assets/js/recaptcha.js', array( 'astoundify-google-recaptcha', 'jquery' ), ASTOUNDIFY_RECAPTCHA_VERSION );
	wp_localize_script(
		'astoundify-recaptcha', 'astoundifyRecaptchaData', array(
			'sitekey' => esc_attr( $site_key ),
		)
	);

	// Auto load script if enabled.
	if ( ! is_user_logged_in() && ! is_admin() && astoundify_recaptcha_get_option( 'enqueue_js', false ) ) {
		wp_enqueue_script( 'astoundify-recaptcha' );
	}
}
add_action( 'wp_enqueue_scripts', 'astoundify_recaptcha_register_script', 1 );
add_action( 'admin_enqueue_scripts', 'astoundify_recaptcha_register_script', 1 );
add_action( 'login_enqueue_scripts', 'astoundify_recaptcha_register_script', 1 );

/**
 * Add Async and Defer to reCAPTCHA Script Tag.
 *
 * @since 1.0.0
 *
 * @param string $tag    Script tag.
 * @param string $handle Script handle.
 * @return string
 */
function astoundify_recaptcha_script_tag( $tag, $handle ) {
	if ( 'astoundify-google-recaptcha' === $handle ) {
		$tag = str_replace( 'src=', 'async defer src=', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'astoundify_recaptcha_script_tag', 10, 2 );

/**
 * reCAPTCHA Field
 *
 * @since 1.0.0
 *
 * @param string|false $css     Add inline CSS to fix recaptcha display (Quick and dirty way).
 * @param string       $context Location context for filter purpose. Optional.
 * @param array        $options Recaptcha options. Optional.
 * @return string
 */
function astoundify_recaptcha_field( $css = false, $context = '', $options = array() ) {
	// Only load if site key is set.
	$site_key = astoundify_recaptcha_site_key();
	if ( ! $site_key ) {
		return null;
	}

	// Defaults options.
	$defaults = array(
		'html_id'    => false,
		'html_class' => '',
		'theme'      => 'light',  // Options: "light", "normal".
		'size'       => 'normal', // Options: "normal", "compact".
	);
	$options  = wp_parse_args( $options, $defaults );
	$options  = apply_filters( 'astoundify_recaptcha_field_options', $options, $context );

	// Enqueue Script.
	wp_enqueue_script( 'astoundify-recaptcha' );

	// HTML Attr.
	$attr_str = '';
	$attr     = array(
		'class'      => trim( "astoundify-recaptcha {$options['html_class']}" ),
		'data-theme' => $options['theme'],
		'data-size'  => $options['size'],
	);
	if ( $options['html_id'] ) {
		$attr['id'] = $options['html_id'];
	}
	foreach ( $attr as $name => $value ) {
		$attr_str .= false !== $value ? sprintf( ' %s="%s"', esc_html( $name ), esc_attr( $value ) ) : esc_html( " {$name}" );
	}

	// Field HTML.
	$field = "<div {$attr_str}></div>";

	// Inline CSS: Print in footer of the page so it will not get in the way.
	$css = apply_filters( 'astoundify_recaptcha_field_css', $css, $context );
	if ( $css ) {
		$print_css = function() use ( $css ) {
			echo sprintf( '<style>%s</style>', trim( $css ) );
		};
		add_action( 'login_footer', $print_css );
		add_action( 'admin_footer', $print_css );
		add_action( 'wp_footer', $print_css );
	}

	return apply_filters( 'astoundify_recaptcha_field', $field, $css, $context );
}
