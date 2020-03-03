<?php
/**
 * WP Job Manager reCAPTCHA Integrations.
 *
 * @since 1.0.0
 *
 * @package Recaptcha
 * @category Functions
 * @author Astoundify
 */


/**
 * Add WP Job Manager Locations.
 *
 * @since 1.0.0
 *
 * @param array $locations Locations.
 * @return array
 */
function astoundify_recaptcha_wpjm_locations( $locations ) {
	$locations['wp_job_manager'] = array(
		'label' => esc_html__( 'WP Job Manager', 'astoundify-recaptcha' ),
		'forms' => array(
			'submit_job' => esc_html__( 'Submit job form', 'astoundify-recaptcha' ),
		),
	);
	return $locations;
}
add_filter( 'astoundify_recaptcha_locations', 'astoundify_recaptcha_wpjm_locations' );


/**
 * Add Captcha to Submit Job Form
 *
 * @since 1.0.0
 */
function astoundify_recaptcha_wpjm_submit_job_form() {
	$is_active = astoundify_recaptcha_is_active( 'wp_job_manager', 'submit_job' );
	if ( ! $is_active || is_user_logged_in() ) {
		return;
	}
?>
<fieldset class="astoundify-recaptcha-wpjm-submit-job-field">
	<label><?php echo astoundify_recaptcha_field_label(); ?></label>
	<div class="field">
		<?php echo astoundify_recaptcha_field( false, 'wpjm_submit_job' ); ?>
	</div>
</fieldset>
<?php
}
add_action( 'submit_job_form_company_fields_end', 'astoundify_recaptcha_wpjm_submit_job_form' );

/**
 * Validate Submi Job Field.
 *
 * @since 1.0.0
 *
 * @param bool|WP_Error $success True if validation success.
 * @return bool|WP_Error
 */
function astoundify_recaptcha_wpjm_submit_job_authenticate( $success ) {
	$is_active = astoundify_recaptcha_is_active( 'wp_job_manager', 'submit_job' );
	if ( ! $is_active || ! $success || is_user_logged_in() ) {
		return $success;
	}

	if ( ! isset( $_POST['g-recaptcha-response'] ) || false === astoundify_recaptcha_verify( $_POST['g-recaptcha-response'] ) ) {
		return new WP_Error( 'validation-error', astoundify_recaptcha_error_notice() );
	}
	return $success;
}
add_filter( 'submit_job_form_validate_fields', 'astoundify_recaptcha_wpjm_submit_job_authenticate', 99 );
