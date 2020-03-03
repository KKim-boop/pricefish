<?php
/**
 * Load the application.
 *
 * @since 1.0.0
 *
 * @package Recaptcha
 * @category Bootstrap
 * @author Astoundify
 */

namespace Astoundify\Recaptcha;

// Load helper functions.
require_once( ASTOUNDIFY_RECAPTCHA_PATH . 'app/functions.php' );
require_once( ASTOUNDIFY_RECAPTCHA_PATH . 'app/settings-functions.php' );

/**
 * Initialize plugin.
 *
 * @since 1.0.0
 */
add_action(
	'plugins_loaded', function() {

		// Load text domain.
		load_plugin_textdomain( dirname( ASTOUNDIFY_RECAPTCHA_PATH ), false, dirname( ASTOUNDIFY_RECAPTCHA_PATH ) . '/resources/languages/' );

		// Integrations.
		$path = trailingslashit( ASTOUNDIFY_RECAPTCHA_PATH . 'app/integrations' );

		// WordPress.
		require_once( $path . 'wordpress.php' );

		// WooCommerce.
		if ( class_exists( 'WooCommerce' ) ) {
			require_once( $path . 'woocommerce.php' );
		}

		// WP Job Manager.
		if ( class_exists( 'WP_Job_Manager' ) ) {
			require_once( $path . 'wp-job-manager.php' );

			// Claim Listing for WP Job Manager.
			if ( function_exists( 'wpjmcl_init' ) ) {
				require_once( $path . 'wp-job-manager-claim-listing.php' );
			}
		}

		// Contact Form 7.
		if ( defined( 'WPCF7_VERSION' ) ) {
			if ( \WPCF7::get_option( 'recaptcha' ) ) {
				require_once( $path . 'contact-form-7.php' );
			}
		}

		// Ninja Forms.
		if ( class_exists( 'Ninja_Forms' ) ) {
			require_once( $path . 'ninja-forms.php' );
		}

		// Gravity Forms.
		if ( class_exists( 'GFForms' ) ) {
			require_once( $path . 'gravityforms.php' );
		}

	}
);
