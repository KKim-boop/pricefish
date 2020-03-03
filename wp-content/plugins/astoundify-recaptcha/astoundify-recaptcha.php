<?php
/**
 * Plugin Name: Google reCAPTCHA by Astoundify
 * Plugin URI: https://astoundify.com/products/recaptcha
 * Description: Google reCAPTCHA for WordPress.
 * Version: 1.0.1
 * Author: Astoundify
 * Author URI: https://astoundify.com/
 * Requires at least: 4.9.0
 * Tested up to: 4.9.1
 * Requires PHP: 5.6.0
 * Text Domain: astoundify-recaptcha
 * Domain Path: resources/languages/
 *
 *    Copyright: 2017 Astoundify
 *    License: GNU General Public License v3.0
 *    License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Recaptcha
 * @category Core
 * @author Astoundify
 */

// Do not access this file directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activation PHP Notice
 *
 * @since 1.0.0
 */
function astoundify_recaptcha_php_notice() {
	// Translators: %1$s minimum PHP version, %2$s current PHP version.
	$notice = sprintf( __( 'Astoundify reCAPTCHA plugin requires at least PHP %1$s. You are running PHP %2$s. Please upgrade and try again.', 'astoundify-recaptcha' ), '<code>5.6.0</code>', '<code>' . PHP_VERSION . '</code>' );
?>

<div class="notice notice-error">
	<p><?php echo wp_kses_post( $notice, array( 'code' ) ); ?></p>
</div>

<?php
}

// Check for PHP version..
if ( version_compare( PHP_VERSION, '5.6', '<' ) ) {
	add_action( 'admin_notices', 'astoundify_recaptcha_php_notice' );

	return;
}

// Plugin can be loaded... define some constants.
define( 'ASTOUNDIFY_RECAPTCHA_VERSION', '1.0.0' );
define( 'ASTOUNDIFY_RECAPTCHA_FILE', __FILE__ );
define( 'ASTOUNDIFY_RECAPTCHA_PLUGIN', plugin_basename( __FILE__ ) );
define( 'ASTOUNDIFY_RECAPTCHA_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'ASTOUNDIFY_RECAPTCHA_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );

/**
 * Start the application.
 *
 * @since 1.0.0
 */
require_once( ASTOUNDIFY_RECAPTCHA_PATH . 'bootstrap/app.php' );
