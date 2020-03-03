<?php
/**
 * Plugin Name: WooCommerce Realex Gateway
 * Plugin URI: http://www.woocommerce.com/products/realex-payment-gateway/
 * Description: Adds the Realex Remote Gateway to your WooCommerce website. Requires an SSL certificate.
 * Author: SkyVerge
 * Author URI: http://www.woocommerce.com/
 * Version: 1.10.5
 * Text Domain: woocommerce-gateway-realex
 * Domain Path: /i18n/languages/
 *
 * Copyright: (c) 2012-2017, SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Gateway-Realex
 * @author    SkyVerge
 * @category  Gateway
 * @copyright Copyright (c) 2012-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 * Woo: 18703:15138c7a53ac0b09f18555feb580125f
 * WC requires at least: 2.5.5
 * WC tested up to: 3.2.5
 */

defined( 'ABSPATH' ) or exit;

// Required functions
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'woo-includes/woo-functions.php' );
}

// Plugin updates
woothemes_queue_update( plugin_basename( __FILE__ ), '15138c7a53ac0b09f18555feb580125f', '18703' );

// WC active check
if ( ! is_woocommerce_active() ) {
	return;
}

// Required library class
if ( ! class_exists( 'SV_WC_Framework_Bootstrap' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'lib/skyverge/woocommerce/class-sv-wc-framework-bootstrap.php' );
}

SV_WC_Framework_Bootstrap::instance()->register_plugin( '4.6.0', __( 'WooCommerce Realex Gateway', 'woocommerce-gateway-realex' ), __FILE__, 'init_woocommerce_gateway_realex', array(
	'minimum_wc_version'   => '2.5.5',
	'minimum_wp_version'   => '4.1',
	'backwards_compatible' => '4.4',
) );

function init_woocommerce_gateway_realex() {

/**
 * The main class for the Realex gateway.  This class handles all the
 * non-gateway tasks such as verifying dependencies are met, loading the text
 * domain, etc.  It also loads the Realex Gateway when needed now that the
 * gateway is only created on the checkout and settings page.  The gateway is
 * also loaded in the following instances:
 *
 * * The order admin page when the order transaction was handled by Realex,
 *   to render a link to the realex transaction page
 * * The credit card vault form on the My Account page
 */
class WC_Realex extends SV_WC_Plugin {


	/** version number */
	const VERSION = '1.10.5';

	/** @var WC_Realex single instance of this plugin */
	protected static $instance;

	/** gateway id */
	const PLUGIN_ID = 'realex';

	/** plugin text domain, DEPRECATED since 1.8.0 */
	const TEXT_DOMAIN = 'woocommerce-gateway-realex';

	/** @var string class to load as gateway, can be base or subscriptions class */
	public $gateway_class_name = 'WC_Gateway_Realex';


	/**
	 * Initilize the plugin
	 *
	 * @see SV_WC_Plugin::__construct()
	 */
	public function __construct() {

		parent::__construct(
			self::PLUGIN_ID,
			self::VERSION,
			array(
				'text_domain'  => 'woocommerce-gateway-realex',
				'dependencies' => array( 'SimpleXML', 'dom' ),
			)
		);

		// Load the gateway
		add_action( 'sv_wc_framework_plugins_loaded', array( $this, 'load_classes' ) );

		// add the 'my account' cc vault if on the My Account page
		add_action( 'woocommerce_after_my_account', array( $this, 'account_cc' ) );

		// process our mini checkout form on the Pay Page for 3D Secure transactions
		add_action( 'wp_ajax_wc-realex-checkout',                  array( $this, 'process_checkout' ) );
		add_action( 'wp_ajax_nopriv_wc-realex-checkout',           array( $this, 'process_checkout' ) );
	}


	/**
	 * Loads Gateway class once parent class is available
	 */
	public function load_classes() {

		// Realex gateway
		require_once( $this->get_plugin_path() . '/classes/class-wc-gateway-realex.php' );

		// load Subscriptions class if available
		if ( $this->is_subscriptions_active() ) {

			require_once( $this->get_plugin_path() . '/classes/class-wc-gateway-realex-subscriptions.php' );

			$this->gateway_class_name = 'WC_Gateway_Realex_Subscriptions';
		}

		// Add class to WC Payment Methods
		add_filter( 'woocommerce_payment_gateways', array( $this, 'load_gateway' ) );
	}


	/**
	 * Adds gateway to the list of available payment gateways
	 *
	 * @param array $gateways array of gateway names or objects
	 * @return array $gateways array of gateway names or objects
	 */
	public function load_gateway( $gateways ) {

		$gateways[] = $this->gateway_class_name;

		return $gateways;
	}


	/**
	 * Checks if the configure-complus message needs to be rendered
	 *
	 * @since 1.5.3
	 * @see SV_WC_Plugin::add_admin_notices()
	 */
	public function add_admin_notices() {

		parent::add_admin_notices();

		// on the plugin settings page render a notice if 3DSecure is enabled and mcrypt is not installed
		if ( $this->is_plugin_settings() ) {

			$wc_gateway_realex = new WC_Gateway_Realex();
			if ( $wc_gateway_realex->is_enabled() ) {
				$threedsecure = $wc_gateway_realex->get_threedsecure();
				if ( $threedsecure->is_enabled() && ! extension_loaded( 'mcrypt' ) ) {
					$message = sprintf(
						/* translators: Placeholders: %1$s - <strong>, %2$s - </strong>, %3$s - <a>, %4$s - </a> */
						__( 'WooCommerce Realex Payment Gateway: 3DSecure: PHP extension %1$smcrypt%2$s not detected, 3DSecure will not be active and no liability shift will occur until you install the %3$smcrypt%4$s PHP extension.  Contact your host or server administrator to configure and install PHP mcrypt.', 'woocommerce-gateway-realex' ),
						'<strong>',
						'</strong>',
						'<a href="http://www.php.net/manual/en/mcrypt.setup.php">',
						'</a>'
					);
					$this->get_admin_notice_handler()->add_admin_notice( $message, 'mcrypt-missing' );
				}
			}
		}

		// check ssl dependency
		$this->check_ssl();
	}


	/**
	 * Check if SSL is enabled and notify the admin user.  The gateway can technically still
	 * function without SSL, so this isn't a fatal dependency, not to mention users might
	 * not bother to configure SSL for their test server.
	 */
	private function check_ssl() {
		if ( get_option( 'woocommerce_force_ssl_checkout' ) != 'yes' ) {
			$message = "Realex: WooCommerce is not being forced over SSL; your customer's credit card data could be at risk.";
			$this->get_admin_notice_handler()->add_admin_notice( $message, 'ssl-required', array(
				'notice_class' => 'error',
			) );
		}
	}


	/**
	 * Checks is WooCommerce Subscriptions is active
	 *
	 * @since 1.4
	 * @return bool true if WooCommerce Subscriptions is active, false if not active
	 */
	public function is_subscriptions_active() {
		return $this->is_plugin_active( 'woocommerce-subscriptions.php' );
	}


	/** Frontend methods ******************************************************/


	/**
	 * Add the credit card vault to the My Account page if needed
	 */
	public function account_cc() {

		$wc_gateway_realex = new WC_Gateway_Realex();
		$wc_gateway_realex->account_cc();
	}


	/**
	 * Pay page tokenized payment method checkout process, adapted from
	 * WooCommerce core
	 *
	 * @since 1.7.1
	 */
	public function process_checkout() {

		$wc_gateway_realex = new WC_Gateway_Realex();

		// Validate
		$wc_gateway_realex->validate_fields();

		// Process
		if ( SV_WC_Helper::wc_notice_count( 'error' ) == 0 ) {

			// Process Payment
			$result = $wc_gateway_realex->process_payment( $_POST['order_id'] );

			// Redirect to success/confirmation/payment page
			if ( 'success' == $result['result'] ) {

				$result = apply_filters( 'woocommerce_payment_successful_result', $result );

				if ( is_ajax() ) {
					echo '<!--WC_START-->' . json_encode( $result ) . '<!--WC_END-->';
					exit;
				} else {
					wp_redirect( $result['redirect'] );
					exit;
				}
			}
		}

		// If we reached this point then there were errors
		if ( is_ajax() ) {

			ob_start();
			wc_print_notices();
			$messages = ob_get_clean();

			$response = array(
				'result'   => 'failure',
				'messages' => isset( $messages ) ? $messages : '',
			);

			wp_send_json( $response );
			exit;
		}
	}

	/** Helper methods ******************************************************/


	/**
	 * Main Realex Instance, ensures only one instance is/can be loaded
	 *
	 * @since 1.6.0
	 * @see wc_realex()
	 * @return WC_Realex
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Gets the plugin documentation url
	 *
	 * @since 1.5
	 * @see SV_WC_Plugin::get_documentation_url()
	 * @return string documentation URL
	 */
	public function get_documentation_url() {
		return 'http://docs.woocommerce.com/document/realex-payment-gateway/';
	}


	/**
	 * Gets the plugin support URL
	 *
	 * @since 1.7.0
	 * @see SV_WC_Plugin::get_support_url()
	 * @return string
	 */
	public function get_support_url() {

		return 'https://woocommerce.com/my-account/marketplace-ticket-form/';
	}


	/**
	 * Gets the gateway configuration URL
	 *
	 * @since 1.5
	 * @see SV_WC_Plugin::get_settings_url()
	 * @param string $plugin_id the plugin identifier.  Note that this can be a
	 *        sub-identifier for plugins with multiple parallel settings pages
	 *        (ie a gateway that supports both credit cards and echecks)
	 * @return string plugin settings URL
	 */
	public function get_settings_url( $plugin_id = null ) {
		return $this->get_payment_gateway_configuration_url();
	}


	/**
	 * Returns true if on the gateway settings page
	 *
	 * @since 1.5
	 * @see SV_WC_Plugin::is_plugin_settings()
	 * @return boolean true if on the admin gateway settings page
	 */
	public function is_plugin_settings() {
		return $this->is_payment_gateway_configuration_page();
	}


	/**
	 * Returns the admin configuration url for the gateway with class name
	 * $gateway_class_name
	 *
	 * @since 1.5.3
	 * @return string admin configuration url for the gateway
	 */
	public function get_payment_gateway_configuration_url() {

		return admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $this->get_payment_gateway_configuration_section() );
	}


	/**
	 * Returns true if the current page is the admin configuration page for the
	 * gateway with class name $gateway_class_name
	 *
	 * @since 1.5.3
	 * @return boolean true if the current page is the admin configuration page for the gateway
	 */
	public function is_payment_gateway_configuration_page() {

		return isset( $_GET['page'] ) && 'wc-settings' == $_GET['page'] &&
		isset( $_GET['tab'] ) && 'checkout' == $_GET['tab'] &&
		isset( $_GET['section'] ) && $this->get_payment_gateway_configuration_section() == $_GET['section'];
	}


	/**
	 * Get the gateway's settings screen section ID.
	 *
	 * @since 1.5.0-1
	 * @return string
	 */
	public function get_payment_gateway_configuration_section() {

		// WC 2.6+ uses the gateway ID instead of class name
		if ( SV_WC_Plugin_Compatibility::is_wc_version_lt_2_6() ) {
			$section = $this->gateway_class_name;
		} else {
			$section = self::PLUGIN_ID;
		}

		return strtolower( $section );
	}


	/**
	 * Returns the plugin name, localized
	 *
	 * @since 1.5
	 * @see SV_WC_Plugin::get_plugin_name()
	 * @return string the plugin name
	 */
	public function get_plugin_name() {
		return __( 'WooCommerce Realex Gateway', 'woocommerce-gateway-realex' );
	}


	/**
	 * Returns __FILE__
	 *
	 * @since 1.5
	 * @see SV_WC_Plugin::get_file()
	 * @return string the full path and filename of the plugin file
	 */
	protected function get_file() {
		return __FILE__;
	}


} // end WC_Realex


/**
 * Returns the One True Instance of Realex
 *
 * @since 1.6.0
 * @return WC_Realex
 */
function wc_realex() {
	return WC_Realex::instance();
}


// fire it up!
wc_realex();


} // init_woocommerce_gateway_realex()
