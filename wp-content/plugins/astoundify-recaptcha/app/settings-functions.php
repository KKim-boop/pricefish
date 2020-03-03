<?php
/**
 * Settings functions.
 *
 * @since 1.0.0
 *
 * @package Recaptcha
 * @category Functions
 * @author Astoundify
 */

/**
 * Register Settings
 *
 * @since 1.0.0
 */
function astoundify_recaptcha_settings_page() {

	add_options_page(
		$page_title = __( 'reCAPTCHA Settings', 'astoundify-recaptcha' ),
		$menu_title = __( 'reCAPTCHA', 'astoundify-recaptcha' ),
		$capability = 'manage_options',
		$menu_slug  = 'astoundify-recaptcha',
		$function   = 'astoundify_recaptcha_settings_output'
	);
}
add_action( 'admin_menu', 'astoundify_recaptcha_settings_page' );

/**
 * Settings Output
 *
 * @since 1.0.0
 */
function astoundify_recaptcha_settings_output() {
?>
<div class="wrap">
	<h1><?php _e( 'Astoundify reCAPTCHA Settings', 'astoundify-recaptcha' ); ?></h1>
	<form method="post" action="options.php">
		<?php do_settings_sections( 'astoundify-recaptcha' ); ?>
		<?php settings_fields( 'astoundify-recaptcha' ); ?>
		<?php submit_button(); ?>
	</form>
</div><!-- wrap -->
<?php
}

/**
 * Register Settings.
 *
 * @since 1.0.0
 */
function astoundify_recaptcha_settings_register() {

	// Settings.
	register_setting(
		$option_group      = 'astoundify-recaptcha',
		$option_name       = 'astoundify-recaptcha',
		$sanitize_callback = 'astoundify_recaptcha_settings_sanitize'
	);

	// Section: API.
	add_settings_section(
		$section_id        = 'astoundify_recaptcha_api',
		$section_title     = '',
		$callback_function = function () {
			echo wpautop( sprintf( __( 'Get your reCAPTCHA API keys at <a href="%s" target="_blank">Google reCAPTCHA Website</a>.', 'astoundify-recaptcha' ), 'https://www.google.com/recaptcha' ) );
		},
		$settings_slug = 'astoundify-recaptcha'
	);

	// Field: Site Key.
	add_settings_field(
		$field_id          = 'astoundify_recaptcha_api_site_key',
		$field_title       = __( 'Site Key', 'astoundify-recaptcha' ),
		$callback_function = function() {
			?>
			<p>
				<input class="regular-text" type="text" name="astoundify-recaptcha[site_key]" value="<?php echo sanitize_text_field( astoundify_recaptcha_get_option( 'site_key' ) ); ?>">
			</p>
			<?php
		},
		$settings_slug = 'astoundify-recaptcha',
		$section_id    = 'astoundify_recaptcha_api'
	);

	// Field: Secret Key.
	add_settings_field(
		$field_id          = 'astoundify_recaptcha_api_secret_key',
		$field_title       = __( 'Secret Key', 'astoundify-recaptcha' ),
		$callback_function = function() {
			?>
			<p>
				<input class="regular-text" type="text" name="astoundify-recaptcha[secret_key]" value="<?php echo sanitize_text_field( astoundify_recaptcha_get_option( 'secret_key' ) ); ?>">
			</p>
			<?php
		},
		$settings_slug = 'astoundify-recaptcha',
		$section_id    = 'astoundify_recaptcha_api'
	);

	// Field: Error Notice
	add_settings_field(
		$field_id          = 'astoundify_recaptcha_error_notice',
		$field_title       = __( 'Error Notice', 'astoundify-recaptcha' ),
		$callback_function = function() {
			?>
			<p>
				<input class="regular-text" type="text" name="astoundify-recaptcha[error_notice]" value="<?php echo sanitize_text_field( wp_kses_post( astoundify_recaptcha_get_option( 'error_notice', __( '', 'astoundify-recaptcha' ) ) ) ); ?>">
			</p>
			<?php
		},
		$settings_slug = 'astoundify-recaptcha',
		$section_id    = 'astoundify_recaptcha_api'
	);

	// Field: Captcha Label
	add_settings_field(
		$field_id          = 'astoundify_recaptcha_field_label',
		$field_title       = __( 'Field Label', 'astoundify-recaptcha' ),
		$callback_function = function() {
			?>
			<p>
				<input class="regular-text" type="text" name="astoundify-recaptcha[field_label]" value="<?php echo sanitize_text_field( wp_kses_post( astoundify_recaptcha_get_option( 'field_label', __( 'Captcha', 'astoundify-recaptcha' ) ) ) ); ?>">
			</p>
			<p class="description"><?php _e( 'Only when applicable.', 'astoundify-recaptcha' ); ?></p>
			<?php
		},
		$settings_slug = 'astoundify-recaptcha',
		$section_id    = 'astoundify_recaptcha_api'
	);

	// Field: Enqueue JS
	add_settings_field(
		$field_id          = 'astoundify_recaptcha_enqueue_js',
		$field_title       = __( 'Load Script', 'astoundify-recaptcha' ),
		$callback_function = function() {
			?>
			<p>
				<label><input type="checkbox" name="astoundify-recaptcha[enqueue_js]" value="1" <?php checked( astoundify_recaptcha_get_option( 'enqueue_js', false ), true ); ?>> <?php _e( 'Auto load reCAPTCHA script in all pages for non logged-in user.', 'astoundify-recaptcha' ); ?></label>
			</p>
			<p class="description"><?php _e( 'Useful if the form is loaded via AJAX.', 'astoundify-recaptcha' ); ?></p>
			<?php
		},
		$settings_slug = 'astoundify-recaptcha',
		$section_id    = 'astoundify_recaptcha_api'
	);

	// Section: Locations.
	$locations = apply_filters( 'astoundify_recaptcha_locations', array() );

	// Add section if not empty.
	if ( $locations ) {
		add_settings_section(
			$section_id        = 'astoundify_recaptcha_locations',
			$section_title     = __( 'Output', 'astoundify-recaptcha' ),
			$callback_function = '__return_false',
			$settings_slug     = 'astoundify-recaptcha'
		);
	}

	// Add location fields.
	foreach ( $locations as $location_id => $location_data ) {

		// Register setting.
		register_setting(
			$option_group      = 'astoundify-recaptcha',
			$option_name       = "astoundify-recaptcha-{$location_id}",
			$sanitize_callback = 'astoundify_recaptcha_settings_sanitize_locations'
		);

		// Create field for each form.
		add_settings_field(
			$field_id          = "astoundify_recaptcha_{$location_id}",
			$field_title       = $location_data['label'],
			$callback_function = function() use ( $location_id, $location_data ) {
				foreach ( $location_data['forms'] as $form_id => $form_label ) {
					$is_active = astoundify_recaptcha_is_active( $location_id, $form_id );
					?>
					<p>
						<label><input type="checkbox" name="astoundify-recaptcha-<?php echo esc_attr( $location_id ); ?>[]" value="<?php echo esc_attr( $form_id ); ?>" <?php checked( $is_active, true ); ?>> <?php echo $form_label; ?></label>
					</p>
					<?php
				}
			},
			$settings_slug = 'astoundify-recaptcha',
			$section_id    = 'astoundify_recaptcha_locations'
		);
	}

}
add_action( 'admin_init', 'astoundify_recaptcha_settings_register' );

/**
 * Settings Sanitize Callback
 *
 * @since 1.0.0
 *
 * @param array $data Posted data.
 * @return array Clean data.
 */
function astoundify_recaptcha_settings_sanitize( $data ) {
	$data['site_key']   = esc_attr( $data['site_key'] );
	$data['secret_key'] = esc_attr( $data['secret_key'] );
	return $data;
}

/**
 * Sanitize Locations.
 *
 * @since 1.0.0
 *
 * @param array $data Posted data.
 * @return array Clean data.
 */
function astoundify_recaptcha_settings_sanitize_locations( $data ) {
	return is_array( $data ) ? $data : array();
}
