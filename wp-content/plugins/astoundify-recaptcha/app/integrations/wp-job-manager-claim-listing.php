<?php
/**
 * Claim Listing for WP Job Manager reCAPTCHA Integrations.
 *
 * @since 1.0.0
 *
 * @package Recaptcha
 * @category Functions
 * @author Astoundify
 */


/**
 * Add Claim Listing for WP Job Manager Locations.
 *
 * @since 1.0.0
 *
 * @param array $locations Locations.
 * @return array
 */
function astoundify_recaptcha_wpjm_claim_listing_locations( $locations ) {
	$locations['wp_job_manager_claim_listing'] = array(
		'label' => esc_html__( 'Claim Listing for WP Job Manager', 'astoundify-recaptcha' ),
		'forms' => array(
			'claim_form' => esc_html__( 'Claim form', 'astoundify-recaptcha' ),
		),
	);
	return $locations;
}
add_filter( 'astoundify_recaptcha_locations', 'astoundify_recaptcha_wpjm_claim_listing_locations' );


/**
 * Add Captcha to Claim Listing Form
 *
 * @since 1.0.0
 */
function astoundify_recaptcha_wpjm_claim_form() {
	$is_active = astoundify_recaptcha_is_active( 'wp_job_manager_claim_listing', 'claim_form' );
	if ( ! $is_active || is_user_logged_in() ) {
		return;
	}
?>
<fieldset class="astoundify-recaptcha-wpjm-claim-field">
	<label><?php echo astoundify_recaptcha_field_label(); ?></label>
	<div class="field">
		<?php echo astoundify_recaptcha_field( false, 'wpjm_claim_form' ); ?>
	</div>
</fieldset>
<?php
}
add_action( 'wpjmcl_submit_claim_form_login_register_view_close', 'astoundify_recaptcha_wpjm_claim_form' );

/**
 * Validate Submi Job Field.
 *
 * @since 1.0.0
 */
function astoundify_recaptcha_wpjm_claim_form_authenticate() {
	$is_active = astoundify_recaptcha_is_active( 'wp_job_manager_claim_listing', 'claim_form' );
	if ( ! $is_active || is_user_logged_in() || ! isset( $_POST['create_account_email'] ) ) {
		return;
	}

	if ( ! isset( $_POST['g-recaptcha-response'] ) || false === astoundify_recaptcha_verify( $_POST['g-recaptcha-response'] ) ) {
		wp_die(
			astoundify_recaptcha_error_notice(), astoundify_recaptcha_error_notice(), array(
				'back_link' => true,
			)
		);
	}
}
add_action( 'wpjmcl_submit_claim_form_login_register_handler_before', 'astoundify_recaptcha_wpjm_claim_form_authenticate', 99 );
