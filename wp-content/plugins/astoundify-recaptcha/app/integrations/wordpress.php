<?php
/**
 * WordPress reCAPTCHA Integrations.
 *
 * @since 1.0.0
 *
 * @package Recaptcha
 * @category Functions
 * @author Astoundify
 */

/**
 * Add WordPress Locations.
 *
 * @since 1.0.0
 *
 * @param array $locations Locations.
 * @return array
 */
function astoundify_recaptcha_wordpress_locations( $locations ) {
	$locations['wordpress'] = array(
		'label' => esc_html__( 'WordPress', 'astoundify-recaptcha' ),
		'forms' => array(
			'login'    => esc_html__( 'Login form', 'astoundify-recaptcha' ),
			'register' => esc_html__( 'Register form', 'astoundify-recaptcha' ),
			'comment'  => esc_html__( 'Comment form', 'astoundify-recaptcha' ),
		),
	);
	return $locations;
}
add_filter( 'astoundify_recaptcha_locations', 'astoundify_recaptcha_wordpress_locations' );

/**
 * Add Captcha to wp-login.php Login Form
 *
 * @since 1.0.0
 */
function astoundify_recaptcha_wordpress_login_form() {
	$is_active = astoundify_recaptcha_is_active( 'WordPress', 'login' );
	if ( ! $is_active ) {
		return;
	}
	$css = '#login{width:350px;}.astoundify-recaptcha{margin-bottom:16px;}';
	echo astoundify_recaptcha_field( $css, 'wp-login.php' );
}
add_action( 'login_form', 'astoundify_recaptcha_wordpress_login_form' );

/**
 * Add Captcha to Custom WordPress Login Form.
 *
 * @since 1.0.0
 *
 * @param string $middle WP Login Form Middle Area.
 * @return string
 */
function astoundify_recaptcha_wordpress_login_form_custom( $middle ) {
	$is_active = astoundify_recaptcha_is_active( 'wordpress', 'login' );
	if ( ! $is_active ) {
		return $middle;
	}
	return $middle . astoundify_recaptcha_field( false, ' wp_login_form' );
}
add_filter( 'login_form_middle', 'astoundify_recaptcha_wordpress_login_form_custom' );

/**
 * Login Auth. If fail, need to return WP_Error.
 *
 * @since 1.0.0
 *
 * @param null|WP_User|WP_Error $user     User.
 * @param string                $username User name.
 * @param string                $password User password.
 * @return string
 */
function astoundify_recaptcha_wordpress_login_authenticate( $user, $username, $password ) {
	// Bail if not active.
	$is_active = astoundify_recaptcha_is_active( 'wordpress', 'login' );
	if ( ! $is_active ) {
		return $user;
	}

	// Verify captcha.
	if ( isset( $_POST['log'] ) ) {
		if ( ! isset( $_POST['g-recaptcha-response'] ) || false === astoundify_recaptcha_verify( $_POST['g-recaptcha-response'] ) ) {
			return new WP_Error( 'recaptcha', astoundify_recaptcha_error_notice() );
		}
	}
	return $user;
}
add_filter( 'authenticate', 'astoundify_recaptcha_wordpress_login_authenticate', 99, 3 );

/**
 * Add Captcha to wp-login.php Register Form
 *
 * @since 1.0.0
 */
function astoundify_recaptcha_wordpress_register_form() {
	$is_active = astoundify_recaptcha_is_active( 'wordpress', 'register' );
	if ( ! $is_active ) {
		return;
	}
	$css = '#login{width:350px;}.astoundify-recaptcha{margin-bottom:16px;}';
	echo astoundify_recaptcha_field( $css, 'wp-login.php?action=register' );
}
add_action( 'register_form', 'astoundify_recaptcha_wordpress_register_form' );

/**
 * Add registration error if captcha fail.
 *
 * @since 1.0.0
 *
 * @param WP_Error $errors Error.
 * @return WP_Error
 */
function astoundify_recaptcha_wordpress_register_authenticate( $errors ) {
	// Bail if not active.
	$is_active = astoundify_recaptcha_is_active( 'wordpress', 'register' );
	if ( ! $is_active ) {
		return $errors;
	}

	// Verify captcha.
	if ( ! isset( $_POST['g-recaptcha-response'] ) || false === astoundify_recaptcha_verify( $_POST['g-recaptcha-response'] ) ) {
		$errors->add( 'recaptcha', astoundify_recaptcha_error_notice() );
	}
	return $errors;
}
add_filter( 'registration_errors', 'astoundify_recaptcha_wordpress_register_authenticate', 99 );


/**
 * Add Captcha Comment Form
 *
 * @since 1.0.0
 */
function astoundify_recaptcha_wordpress_comment_form() {
	$is_active = astoundify_recaptcha_is_active( 'wordpress', 'comment' );
	if ( ! $is_active || is_user_logged_in() ) {
		return;
	}
	echo astoundify_recaptcha_field( false, 'comment' );
}
add_action( 'comment_form_after_fields', 'astoundify_recaptcha_wordpress_comment_form' );

/**
 * Comment Form Authenticate
 *
 * @since 1.0.0
 *
 * @param bool|string $approved    Comment auto approved.
 * @param array       $commentdata Comment data.
 * @return bool|string
 */
function astoundify_recaptcha_wordpress_comment_authenticate( $approved, $commentdata ) {
	// Bail if not active.
	$is_active = astoundify_recaptcha_is_active( 'wordpress', 'comment' );
	if ( ! $is_active || is_user_logged_in() ) {
		return $approved;
	}

	// Verify captcha.
	if ( ! isset( $_POST['g-recaptcha-response'] ) || false === astoundify_recaptcha_verify( $_POST['g-recaptcha-response'] ) ) {
		wp_die(
			astoundify_recaptcha_error_notice(), astoundify_recaptcha_error_notice(), array(
				'back_link' => true,
			)
		);
	}
	return $approved;
}
add_filter( 'pre_comment_approved', 'astoundify_recaptcha_wordpress_comment_authenticate', 99, 2 );
