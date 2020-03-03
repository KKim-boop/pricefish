<?php
/**
 * WooCommerce Realex Payment Gateway
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Realex Payment Gateway to newer
 * versions in the future. If you wish to customize WooCommerce Realex Payment Gateway for your
 * needs please refer to http://docs.woocommerce.com/document/realex-payment-gateway/ for more information.
 *
 * @package     WC-Gateway-Realex/Classes
 * @author      SkyVerge
 * @copyright   Copyright (c) 2012-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * The Realex Payment Gateway class.  This class handles
 * all interaction with the Realex API and gateway.
 */
class WC_Gateway_Realex extends WC_Payment_Gateway {

	private $endpoint_url           = "https://epage.payandshop.com/epage-remote.cgi";
	private $realvault_endpoint_url = "https://epage.payandshop.com/epage-remote-plugins.cgi";

	private $testmode;
	private $debug;
	private $settlement;
	private $vault;
	private $vaulttext;
	private $managecards;
	private $cvv;
	private $avs;
	private $cardtypes;
	private $merchantid;
	private $sharedsecret;
	private $account_test;
	private $account_live;
	private $amex_account_test;
	private $amex_account_live;

	/**
	 * Associative array of realex card types to card name
	 * @var array
	 */
	private $card_type_options;


	/**
	 * Initialize the gateway
	 *
	 * @see WC_Payment_Gateway::__construct()
	 */
	public function __construct() {

		$this->id                 = 'realex';
		$this->method_title       = __( 'Realex', 'woocommerce-gateway-realex' );
		$this->method_description = __( 'Realex Remote Gateway provides a seamless and secure checkout process for your customers', 'woocommerce-gateway-realex' );

		// to set up the images icon for your shop, use the included images/cards.png
		//  for the card images you accept, and hook into this filter with a return
		//  value like: plugins_url( '/images/cards.png', __FILE__ );
		$this->icon               = apply_filters( 'woocommerce_realex_icon', '' );

		// define the default card type options, and allow plugins to add in additional ones.
		//  Additional display names can be associated with a single card type by using the
		//  following convention: VISA: Visa, VISA-1: Visa Debit, etc
		$default_card_type_options = array(
			'VISA'   => 'Visa',
			'MC'     => 'MasterCard',
			'AMEX'   => 'American Express',
			'LASER'  => 'Laser',
			'SWITCH' => 'Switch',
			'DINERS' => 'Diners'
		);
		$this->card_type_options = apply_filters( 'woocommerce_realex_card_types', $default_card_type_options );

		// pay page fallback
		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );

		// load the 3DSecure class
		require_once( wc_realex()->get_plugin_path() . '/classes/class-wc-realex-3dsecure.php' );
		$this->threedsecure = new Realex_3DSecure( $this );

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Define user set variables
		$this->enabled      = $this->settings['enabled'];
		$this->title        = $this->settings['title'];
		$this->description  = $this->settings['description'];
		$this->testmode     = $this->settings['testmode'];
		$this->debug        = $this->settings['debug'];
		$this->settlement   = $this->settings['settlement'];
		$this->vault        = $this->settings['vault'];
		$this->vaulttext    = $this->settings['vaulttext'];
		$this->managecards  = $this->settings['managecards'];
		$this->cvv          = $this->settings['cvv'];
		$this->avs          = $this->settings['avs'];
		$this->cardtypes    = (array) $this->settings['cardtypes'];
		$this->merchantid   = $this->settings['merchantid'];
		$this->sharedsecret = $this->settings['sharedsecret'];
		$this->account_test = $this->settings['accounttest'];
		$this->account_live = $this->settings['accountlive'];
		$this->amex_account_test = $this->settings['amexaccounttest'];
		$this->amex_account_live = $this->settings['amexaccountlive'];

		// ensure the authorize callback is only run once
		remove_all_filters( 'wc_gateway_realex_process_payment', 10 );

		// standard authorization action
		add_filter( 'wc_gateway_realex_process_payment', array( $this, 'process_payment_authorize' ), 10, 2 );

		if ( is_admin() ) {
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		// add gateway.js checkout javascript
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// notify others that we're initialized
		do_action( 'wc_gateway_realex_init' );

		if ( $this->threedsecure->is_3dsecure_available() ) {
			$this->order_button_text = __( 'Continue', 'woocommerce-gateway-realex' );
		}
	}


	/**
	 * Initialise Settings Form Fields
	 *
	 * Add an array of fields to be displayed
	 * on the gateway's settings screen.
	 *
	 * @see WC_Settings_API::init_form_fields()
	 */
	public function init_form_fields() {

		$this->form_fields = apply_filters( 'wc_gateway_realex_form_fields', array(

			'enabled' => array(
				'title'       => __( 'Enable', 'woocommerce-gateway-realex' ),
				'label'       => __( 'Enable Realex', 'woocommerce-gateway-realex' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no'
			),
			'title' => array(
				'title'       => __( 'Title', 'woocommerce-gateway-realex' ),
				'type'        => 'text',
				'description' => __( 'Payment method title that the customer will see on your website.', 'woocommerce-gateway-realex' ),
				'default'     => __( 'Credit Card', 'woocommerce-gateway-realex' )
			),
			'description' => array(
				'title'       => __( 'Description', 'woocommerce-gateway-realex' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description that the customer will see on your website.', 'woocommerce-gateway-realex' ),
				'default'     => __( 'Pay securely using your credit card.', 'woocommerce-gateway-realex' )
			),
			'testmode' => array(
				'title'       => __( 'Test Mode', 'woocommerce-gateway-realex' ),
				'label'       => __( 'Enable Test Mode', 'woocommerce-gateway-realex' ),
				'type'        => 'checkbox',
				'description' => __( 'Place the payment gateway in test mode to work with your test account.', 'woocommerce-gateway-realex' ),
				'default'     => 'yes'
			),
			'debug' => array(
				'title'       => __( 'Debug Mode', 'woocommerce-gateway-realex' ),
				'label'       => __( 'Enable Debug Mode', 'woocommerce-gateway-realex' ),
				'type'        => 'checkbox',
				'description' => __( 'Output the response from Realex on the payment page for debugging purposes.', 'woocommerce-gateway-realex' ),
				'default'     => 'no'
			),
			'settlement' => array(
				'title'       => __( 'Submit for Settlement', 'woocommerce-gateway-realex' ),
				'label'       => __( 'Submit all transactions for settlement immediately.', 'woocommerce-gateway-realex' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'yes'
			),
			'vault' => array(
				'title'       => __( 'RealVault', 'woocommerce-gateway-realex' ),
				'label'       => __( 'Allow customers to save their credit card data to your vault.', 'woocommerce-gateway-realex' ).' <strong><a href="http://www.realexpayments.co.uk/product-corporate?area=recurring-payments" target="_blank">' . __( 'Requires Realex RealVault', 'woocommerce-gateway-realex' ) . '</a></strong>',
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no'
			),
			'vaulttext'       => array(
				'title'       => __( 'Checkout Save Card Text', 'woocommerce-gateway-realex' ),
				'type'        => 'textarea',
				'description' => __( 'Text buyers see allowing them to save their credit card to your vault.', 'woocommerce-gateway-realex' ),
				'default'     => 'Securely Save Card to Account'
			),
			'managecards'     => array(
				'title'       => __( 'Manage My Cards Text', 'woocommerce-gateway-realex' ),
				'type'        => 'textarea',
				'description' => __( 'Text for manage credit cards button during checkout.', 'woocommerce-gateway-realex' ),
				'default'     => 'Manage My Credit Cards'
			),
			'cvv' => array(
				'title'       => __( 'Card Verification', 'woocommerce-gateway-realex' ),
				'label'       => __( 'Require customer to enter credit card verification code', 'woocommerce-gateway-realex' ),
				'type'        => 'checkbox',
				'default'     => 'no'
			),
			'avs' => array(
				'title'       => __( 'Address Verification Service (AVS)', 'woocommerce-gateway-realex' ),
				'label'       => __( 'Perform an AVS check on customers billing addresses', 'woocommerce-gateway-realex' ),
				'type'        => 'checkbox',
				'default'     => 'no'
			),
			'cardtypes' => array(
				'title'       => __( 'Accepted Cards', 'woocommerce-gateway-realex' ),
				'type'        => 'multiselect',
				'class'       => 'wc-enhanced-select',
				'css'         => 'width: 350px;',
				'description' => __( 'Select which card types to accept.', 'woocommerce-gateway-realex' ),
				'default'     => '',  // no defaults for this as it depends on the merchant account
				'options'     => $this->card_type_options,
			),
			'merchantid' => array(
				'title'       => __( 'Merchant ID', 'woocommerce-gateway-realex' ),
				'type'        => 'text',
				'description' => __( 'Your Realex merchant id.', 'woocommerce-gateway-realex' ),
				'default'     => ''
			),
			'sharedsecret' => array(
				'title'       => __( 'Shared Secret', 'woocommerce-gateway-realex' ),
				'type'        => 'password',
				'description' => __( 'The shared secret for your account, provided by Realex.', 'woocommerce-gateway-realex' ),
				'default'     => ''
			),
			'accounttest' => array(
				'title'       => __( 'Test Account', 'woocommerce-gateway-realex' ),
				'type'        => 'text',
				'description' => __( 'Optional test account (if not supplied the default account will be used)', 'woocommerce-gateway-realex' ),
				'default'     => ''
			),
			'accountlive' => array(
				'title'       => __( 'Live Account', 'woocommerce-gateway-realex' ),
				'type'        => 'text',
				'description' => __( 'Optional live account (if not supplied the default account will be used)', 'woocommerce-gateway-realex' ),
				'default'     => ''
			),
			'amexaccounttest' => array(
				'title'       => __( 'Amex Test Account', 'woocommerce-gateway-realex' ),
				'type'        => 'text',
				'description' => __( 'Optional test account for American Express transactions (if not supplied the main test account will be used)', 'woocommerce-gateway-realex' ),
				'default'     => ''
			),
			'amexaccountlive' => array(
				'title'       => __( 'Amex Live Account', 'woocommerce-gateway-realex' ),
				'type'        => 'text',
				'description' => __( 'Optional live account for American Express transactions (if not supplied the main live account will be used)', 'woocommerce-gateway-realex' ),
				'default'     => ''
			)
		) );
	}


	/**
	 * Override the admin options method to add a little javascript to control
	 * how the gateway settings behave
	 *
	 * @see WC_Settings_API::admin_options()
	 */
	public function admin_options() {

		// allow parent to do its thing
		parent::admin_options();

		ob_start();
		?>
		$('#woocommerce_realex_vault').change(
			function() {
				var saveCardTextRow = $(this).closest('tr').next();
				var manageMyCardsTextRow = saveCardTextRow.next();

				if ($(this).is(':checked')) {
					saveCardTextRow.show();
					manageMyCardsTextRow.show();
				} else {
					manageMyCardsTextRow.hide();
					saveCardTextRow.hide();
				}
			}).change();
		<?php
		$javascript = ob_get_clean();
		wc_enqueue_js( apply_filters( 'wc_gateway_realex_admin_options_js', $javascript ) );
	}


	/**
	 * Enqueues the gateway javascript
	 *
	 * @since 1.7.1
	 * @return boolean true if the scripts were enqueued, false otherwise
	 */
	public function enqueue_scripts() {

		// only load javascript once, if the gateway is available
		if ( ! $this->is_available() || wp_script_is( 'wc-realex', 'enqueued' ) || ! is_checkout() ) {
			return false;
		}

		wp_enqueue_script( 'wc-realex', wc_realex()->get_plugin_url() . '/assets/js/frontend/wc-realex.min.js', array(), wc_realex()->get_version(), true );

		$params = array();

		$params['ajax_loader_url'] = wc_realex()->get_framework_assets_url() . '/images/ajax-loader.gif';
		$params['checkout_url']    = add_query_arg( 'action', 'wc-realex-checkout', WC()->ajax_url() );
		$params['card_types']      = $this->get_real_cardtypes();
		$params['vault_available'] = $this->vault_available();

		// get the current order if applicable
		$order_id = isset( $GLOBALS['wp']->query_vars['order-pay'] ) ? absint( $GLOBALS['wp']->query_vars['order-pay'] ) : 0;

		if ( $order_id ) {
			$order = wc_get_order( $order_id );
			$params['order_id'] = SV_WC_Order_Compatibility::get_prop( $order, 'id' );
		}

		wp_localize_script( 'wc-realex', 'realex_params', $params );

		return true;
	}



	/**
	 * get_icon function.
	 *
	 * @see WC_Payment_Gateway::get_icon()
	 * @return string accepted payment icons
	 */
	public function get_icon() {

		$icon = '';
		if ( $this->icon ) {
			// default behavior
			$icon = '<img src="' . esc_url( WC_HTTPS::force_https_url( $this->icon ) ) . '" alt="' . esc_attr( $this->title ) . '" />';
		} elseif ( $this->cardtypes ) {
			// display icons for the selected card types
			$icon = '';
			foreach ( $this->cardtypes as $cardtype ) {
				if ( file_exists( wc_realex()->get_plugin_path() . '/assets/images/card-' . strtolower( $cardtype ) . '.png' ) ) {
					$icon .= '<img src="' . esc_url( WC_HTTPS::force_https_url( wc_realex()->get_plugin_url() . '/assets/images/card-' . strtolower( $cardtype ) . '.png' ) ) . '" alt="' . esc_attr( strtolower( $cardtype ) ) . '" />';
				}
			}
		}

		return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
	}


	/**
	 * Payment fields for Realex.
	 *
	 * @see WC_Payment_Gateway::payment_fields()
	 */
	public function payment_fields() {
		global $wp;

		if ( $this->threedsecure->is_3dsecure_available() && ( ! is_checkout_pay_page() || isset( $_GET['pay_for_order'] ) ) ) {

			parent::payment_fields(); ?>
			<style type="text/css">#payment ul.payment_methods li label[for='payment_method_realex'] img:nth-child(n+2) { margin-left:1px; }</style>
			<?php

			return;
		}

		// default to new card
		$card_ref = 'new';

		if ( $this->vault_available() ) {

			// get the credit card tokens for the user
			$current_user = wp_get_current_user();
			$credit_cards = array();
			if ( $current_user->ID ) $credit_cards = get_user_meta( $current_user->ID, 'woocommerce_realex_cc', true );

			// if there are saved cards, and one hasn't been selected, default to the first
			if ( $credit_cards ) {
				$card_ref = (object) current( $credit_cards );
				$card_ref = $card_ref->ref;
			}
		}

		?>
		<style type="text/css">#payment ul.payment_methods li label[for='payment_method_realex'] img:nth-child(n+2) { margin-left:1px; }</style>
		<fieldset>
			<?php if ( $this->description ) : ?><p><?php echo esc_html( $this->description ); ?> <?php if ( $this->is_test_mode() ) : ?><?php esc_html_e( 'TEST MODE ENABLED', 'woocommerce-gateway-realex' ); ?><?php endif; ?></p><?php endif; ?>

			<?php
			if ( $this->vault_available() && $credit_cards ) : ?>
				<div>
					<p class="form-row form-row-first" style="width:65%;">
						<?php foreach ( $credit_cards as $credit_card ) : $credit_card = (object) $credit_card; ?>
							<input type="radio" id="<?php echo esc_attr( $credit_card->ref ); ?>" name="realex_card_ref" style="width:auto;" value="<?php echo esc_attr( $credit_card->ref ); ?>" <?php checked( $credit_card->ref, $card_ref ) ?> />
							<label style="display:inline;" for="<?php echo esc_attr( $credit_card->ref ); ?>">
								<?php /* translators: Placeholders: %1$s - credit card type, %2$s - credit card last 4, %3$s - credit card expiration MM/YY */
								printf( esc_html__( '%1$s ending in %2$s (%3$s)', 'woocommerce-gateway-realex' ),
									$this->card_type_options[ $credit_card->type ],
									$credit_card->last4,
									$credit_card->expiration_month . '/' . $credit_card->expiration_year
								); ?>
							</label><br />
						<?php endforeach; ?>
						<input type="radio" id="realex_new" name="realex_card_ref" style="width:auto;" <?php checked( 'new', $card_ref ) ?> value="0" /> <label style="display:inline;" for="realex_new"><?php esc_html_e( 'Use Another Credit Card', 'woocommerce-gateway-realex' ); ?></label>
					</p>
					<p class="form-row form-row-last" style="width:30%;"><a class="button" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>#saved-cards"><?php echo esc_html( $this->managecards ); ?></a></p>
					<div style="clear:both;"></div>
				</div>
				<div class="clear"></div>

			<?php endif; ?>

			<div class="realex_vault_new" style="<?php echo $card_ref == 'new' ? '' : 'display:none;' ?>">
				<p class="form-row form-row-first">
					<label for="realex_accountNumber"><?php echo __( "Credit Card number", 'woocommerce-gateway-realex' ) ?> <span class="required">*</span></label>
					<input type="text" class="input-text" id="realex_accountNumber" name="realex_accountNumber" maxlength="19" autocomplete="off" />
				</p>
				<p class="form-row form-row-last">
					<label for="realex_cardType"><?php _e( 'Card Type', 'woocommerce-gateway-realex' ); ?> <span class="required">*</span></label>
					<select name="realex_cardType" id="realex_cardType" style="width:auto;"><br />
						<option value="">
						<?php
							foreach ( $this->cardtypes as $type ) :
								if ( isset( $this->card_type_options[ $type ] ) ) :
									?>
									<option value="<?php echo esc_attr( preg_replace( '/-.*$/', '', $type ) ); ?>" rel="<?php echo esc_attr( $type ); ?>"><?php esc_html_e( $this->card_type_options[ $type ], 'woocommerce-gateway-realex' ); ?></option>
									<?php
								endif;
							endforeach;
						?>
					</select>
				</p>
				<div class="clear"></div>

				<p class="form-row form-row-first">
					<label for="realex_expirationMonth"><?php esc_html_e( 'Expiration date', 'woocommerce-gateway-realex' ) ?> <span class="required">*</span></label>
					<select name="realex_expirationMonth" id="realex_expirationMonth" class="woocommerce-select woocommerce-cc-month" style="width:auto;">
						<option value=""><?php esc_attr_e( 'Month', 'woocommerce-gateway-realex' ) ?></option>
						<?php foreach ( range( 1, 12 ) as $month ) : ?>
							<option value="<?php echo sprintf( '%02d', $month ) ?>"><?php echo sprintf( '%02d', $month ) ?></option>
						<?php endforeach; ?>
					</select>
					<select name="realex_expirationYear" id="realex_expirationYear" class="woocommerce-select woocommerce-cc-year" style="width:auto;">
						<option value=""><?php esc_attr_e( 'Year', 'woocommerce-gateway-realex' ) ?></option>
						<?php foreach ( range( date( 'Y' ), date( 'Y' ) + 20 ) as $year ) : ?>
							<option value="<?php echo $year ?>"><?php echo $year ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<?php if ( $this->cvv == 'yes' ) : ?>

				<p class="form-row form-row-last">
					<label for="realex_cvNumber"><?php esc_html_e( 'Card security code', 'woocommerce-gateway-realex' ) ?> <span class="required">*</span></label>
					<input type="text" class="input-text" id="realex_cvNumber" name="realex_cvNumber" maxlength="4" style="width:60px" autocomplete="off" />
				</p>
				<?php endif ?>

				<?php if ( in_array( 'SWITCH', $this->get_real_cardtypes() ) ) : ?>
					<?php if ( $this->cvv == 'no' ) : ?>
						<p class="form-row form-row-last" style="display:none;">
					<?php else: ?>
						<div class="clear"></div>
						<p class="form-row form-row-first" style="display:none;">
					<?php endif; ?>
					<label for="realex_issueNumber"><?php esc_html_e( 'Issue Number', 'woocommerce-gateway-realex' ) ?></label>
					<input type="text" class="input-text" id="realex_issueNumber" name="realex_issueNumber" maxlength="3" style="width:60px" autocomplete="off" />
					</p>
				<?php endif; ?>
				<div class="clear"></div>

				<?php if ( $this->vault_available() ) : ?>

					<?php $tokenization_forced = false;

					// begin checking for subscriptions
					if ( wc_realex()->is_subscriptions_active() && in_array( 'subscriptions', $this->supports ) ) :

						if ( is_checkout_pay_page() ) :

							$order_id = isset( $wp->query_vars['order-pay'] ) ? absint( $wp->query_vars['order-pay'] ) : 0;

							if ( SV_WC_Plugin_Compatibility::is_wc_subscriptions_version_gte_2_0() ) :
								$tokenization_forced = $order_id && ( wcs_order_contains_subscription( $order_id ) || wcs_order_contains_renewal( $order_id ) );
							else :
								$tokenization_forced = $order_id && WC_Subscriptions_Order::order_contains_subscription( $order_id );
							endif;

						else :
							$tokenization_forced = WC_Subscriptions_Cart::cart_contains_subscription() || wcs_cart_contains_renewal();
						endif;

					endif; ?>

					<?php if ( $tokenization_forced ) : ?>
						<input name="realex_vault_new" type="hidden" value="1" />
					<?php else: /* Normal behavior */ ?>
						<div class="realex_create-account">
							<p class="form-row">
								<input id="realex_vault_new" name="realex_vault_new" type="checkbox" value="1" style="width:auto;" />
								<label for="realex_vault_new" style="display:inline;"><?php echo esc_html( $this->vaulttext ); ?></label>
							</p>
						</div>
						<div class="clear"></div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</fieldset>
		<?php
	}


	/**
	 * Process the payment and return the result
	 *
	 * @see WC_Payment_Gateway::process_payment()
	 * @param int $order_id order identifier
	 */
	public function process_payment( $order_id ) {
		return apply_filters( 'wc_gateway_realex_process_payment', false, $order_id );
	}


	/**
	 * Perform a standard payment authorization
	 *
	 * @param boolean $return
	 * @param int $order_id the order identifier
	 */
	public function process_payment_authorize( $return, $order_id ) {

		require_once( 'class-wc-realex-api.php' );

		$order = wc_get_order( $order_id );

		// set payment total here so it can be modified for subscriptions if needed
		$order->payment_total = number_format( $order->get_total(), 2, '.', '' );

		// create the realex api client
		$realex_client = new Realex_API( $this->get_endpoint_url(), $this->get_realvault_endpoint_url(), $this->get_shared_secret() );

		// regular, unsupported card type, or vault transaction
		return $this->authorize( $realex_client, $order );
	}


	/**
	 * Process an standard auth/vault transaction
	 *
	 * @param Realex_API $realex_client realex api client
	 * @param WC_Order $order the order
	 * @param array $data optional post data to use in place of $_POST
	 * @param boolean $increment_retry_count optional whether to increment the retry
	 *        count to avoid order number clashes, defaults to true.  It's important
	 *        that this be true for, and only for, the first request of any transaction
	 */
	public function authorize( $realex_client, $order, $data = null, $increment_retry_count = true ) {

		// paying with a tokenized cc?
		$vault_card_ref = $this->get_post( 'realex_card_ref', $data );

		$order_id = SV_WC_Order_Compatibility::get_prop( $order, 'id' );

		// subscription support, this is a scheduled subscription payment, and not a live payment
		// TODO: this is terrible, refactor all this when possible
		$subscription = false;
		if ( get_post_meta( $order_id, '_realex_cardref', true ) ) {
			$vault_card_ref = get_post_meta( $order_id, '_realex_cardref', true );
			$subscription = true;
		}

		// user in the vault?
		$vault_payer_ref = null;
		if ( $order->get_user_id() ) {
			$vault_payer_ref = get_user_meta( $order->get_user_id(), 'woocommerce_realex_payerref', true );
		}

		// create/update the customer in the vault as needed
		if ( $this->vault_available() && ! $subscription ) {

			if ( $vault_payer_ref ) {
				// update the vault payer info
				$this->update_vault_payer( $realex_client, $vault_payer_ref, $order );
			}

			// new vault payer (non-guest checkout)
			if ( ! $vault_payer_ref && $this->get_post( 'realex_vault_new', $data ) && $order->get_user_id() ) {
				$vault_payer_ref = $this->get_new_payer_ref( $order->get_user_id() );
				$this->create_new_vault_payer( $realex_client, $vault_payer_ref, $order );
			}

			// add a new card?
			if ( $vault_payer_ref && $this->get_post( 'realex_vault_new', $data ) && ! $vault_card_ref ) {
				$card_type = $this->get_post( 'realex_cardType', $data );

				// generate a unique vault card ref and add the card
				$vault_card_ref = $this->get_new_card_ref( $order->get_user_id(), $card_type );
				$vault_new_card_response = $this->create_new_vault_card( $realex_client, $vault_payer_ref, $vault_card_ref, $order, $data );

				if( $vault_new_card_response && $vault_new_card_response->result == '501' && stripos( $vault_new_card_response->message, 'There is no such payer ref' ) !== false ) {

					// the payerref we have on file is invalid: delete it and attempt to create a new one and add this card to it
					$this->delete_payer_ref( $order->get_user_id() );
					$vault_payer_ref = $this->get_new_payer_ref( $order->get_user_id() );
					$this->create_new_vault_payer( $realex_client, $vault_payer_ref, $order );

					if ( $vault_payer_ref ) {  // if vault payer was added try one last time to add the card
						$vault_card_ref = $this->get_new_card_ref( $order->get_user_id(), $card_type );
						$this->create_new_vault_card( $realex_client, $vault_payer_ref, $vault_card_ref, $order, $data );
					}
				}
			}
		}

		// with subscriptions, the payment total can be zero for the initial setup
		if ( $order->payment_total == 0 ) {

			if ( $vault_card_ref ) {
				// record the card token used for this order
				update_post_meta( $order_id, '_realex_cardref', (string) $vault_card_ref );
			}

			return;
		}

		$cards = null;
		if ( $vault_card_ref ) {
			// paying using an existing token
			$cards = get_user_meta( $order->get_user_id(), 'woocommerce_realex_cc', true );

			// validate the input
			if ( ! isset( $cards[ $vault_card_ref ] ) ) {
				SV_WC_Helper::wc_add_notice( __( "An error occurred, try an alternate form of payment", 'woocommerce-gateway-realex' ), 'error' );
				return;
			}

			// perform the auth transaction using the supplied token (never 3DSecure)
			$response = $this->vault_auth_request( $realex_client, $vault_payer_ref, $cards[ $vault_card_ref ]['type'], $vault_card_ref, $order );

		} else {
			// perform the regular auth transaction
			$response = $this->auth_request( $realex_client, $order, $data, $increment_retry_count );
		}

		if ( ! $response ) {
			SV_WC_Helper::wc_add_notice( __( 'Connection error', 'woocommerce-gateway-realex' ), 'error' );
			return;
		}

		if ( '00' == $response->result ) {
			// Successful payment

			if ( ! $realex_client->verify_transaction_signature( $response ) ) {
				// response was not properly signed by realex
				SV_WC_Helper::wc_add_notice( __( 'Error - invalid transaction signature, check your Realex settings.', 'woocommerce-gateway-realex' ), 'error' );

				// if debug mode load the response into the messages object
				if ( $this->is_debug_mode() ) {
					$this->response_debug_message( $response, $vault_card_ref ? "Response: receipt-in" : "Response: auth", 'error' );
				}
				return;
			}

			// if debug mode load the response into the messages object
			if ( $this->is_debug_mode() ) {
				$this->response_debug_message( $response, $vault_card_ref ? "Response: receipt-in" : "Response: auth", 'message', true );
			}

			// collect the credit card information used for this transaction, whether it was a vaulted transaction or a regular
			if ( $vault_card_ref ) {
				$card = (object) $cards[ $vault_card_ref ];
				$card_type        = $card->type;
				$last4            = $card->last4;
				$expiration_month = $card->expiration_month;
				$expiration_year  = $card->expiration_year;
			} else {
				$card_type        = $this->get_post( 'realex_cardType', $data );
				$last4            = substr( $this->get_post( 'realex_accountNumber', $data ), -4 );
				$expiration_month = $this->get_post( 'realex_expirationMonth', $data );
				$expiration_year  = $this->get_post( 'realex_expirationYear', $data );
			}

			// update the order record with success
			/* translators: Placeholders: %1$s - credit card type, %2$s - credit card last 4, %3$s - credit card expiration MM/YY */
			$order_note = sprintf( __( 'Credit Card Transaction Approved: %1$s ending in %2$s (%3$s).', 'woocommerce-gateway-realex' ),
				$this->card_type_options[ $card_type ],
				$last4,
				$expiration_month . '/' . $expiration_year
			);

			$order_note = apply_filters( 'wc_gateway_realex_order_note', $order_note, $order, $data, $card_type, $cards );

			// avs response, if check was performed.
			if ( $this->avs_check() ) {
				// There's also 'M' for Matched, and 'X' if AVS check was not performed
				$avs_response_codes = array( 'N' => 'Not matched', 'I' => 'Problem with check', 'U' => 'Unable to check', 'P' => 'Partial match' );

				$code = (string) $response->avspostcoderesponse;
				if ( isset( $avs_response_codes[ $code ] ) )
					$order_note .= "\n" . sprintf( __( 'AVS post code response: %s', 'woocommerce-gateway-realex' ), $avs_response_codes[ $code ] );
				$code = (string) $response->avsaddressresponse;
				if ( isset( $avs_response_codes[ $code ] ) )
					$order_note .= "\n" . sprintf( __( 'AVS address response: %s', 'woocommerce-gateway-realex' ), $avs_response_codes[ $code ] );
			}

			if ( ! $subscription ) {
				// normal execution
				$order->add_order_note( $order_note );

				$order->payment_complete();

				// store the payment reference in the order (add_post_meta not update_post_meta)
				add_post_meta( $order_id, '_realex_payment_reference', (string) $response->pasref );

				if ( $vault_card_ref ) {
					// record the card token used for this order (add_post_meta not update_post_meta)
					add_post_meta( $order_id, '_realex_cardref', (string) $vault_card_ref );
				}

				WC()->cart->empty_cart();

				// Return thank you redirect
				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order ),
				);
			} else {
				// special subscription handling
				return $response;
			}

		} else {
			// Failure: it's important that the order status be set to 'failed' so that a new order number can be generated,
			// because Realex does not allow the same order number to be used once a payment attempt has failed
			/* translators: Placeholders: %1$s - response result, %2$s - failure message */
			$error_note = sprintf( __( 'Realex Credit Card payment failed (Result: %1$s - "%2$s").', 'woocommerce-gateway-realex' ), $response->result, $response->message );

			if ( ! $subscription ) $this->order_failed( $order, $error_note );

			$message = __( 'The transaction has been declined by your bank, please contact your bank for more details, or try an alternative payment method.  Your order has been recorded, please contact us if you wish to provide payment over the phone.', 'woocommerce-gateway-realex' );

			if ( $vault_card_ref && $response->result == 520 ) {

				if ( stripos( $response->message, 'There is no such Payer' ) !== false ) {
					// payerref does not exist
					$this->delete_payer_ref( $order->get_user_id() );
					$message = __( "Internal error, please use an alternate form of payment", 'woocommerce-gateway-realex' );
				} elseif ( stripos( $response->message, 'There is no such Payment Method' ) !== false ) {
					// card ref does not exist
					$this->delete_card_ref( $vault_card_ref );
					$message = __( "Unknown card, please use an alternate form of payment", 'woocommerce-gateway-realex' );
				}
			}

			// provide some default error message
			if ( SV_WC_Helper::wc_notice_count( 'error' ) == 0 ) {
				if ( $message ) SV_WC_Helper::wc_add_notice( $message, 'error' );
				else SV_WC_Helper::wc_add_notice( __( "An error occurred, please try again or try an alternate form of payment", 'woocommerce-gateway-realex' ), 'error' );
			}

			// if debug mode load the response into the messages object
			if ( $this->is_debug_mode() ) {
				$this->response_debug_message( $response, $vault_card_ref ? "Response: receipt-in" : "Response: auth", 'error' );
			}

			// special subscription handling
			if ( $subscription ) return $response;
		}

	}


	/**
	 * Validate payment form fields
	 */
	public function validate_fields() {

		if ( $this->threedsecure->is_3dsecure_available() && ! SV_WC_Helper::get_post( 'woocommerce_pay_page' ) ) {
			return;
		}

		$vault_card_ref   = $this->get_post( 'realex_card_ref' );
		$card_type        = $this->get_post( 'realex_cardType' );
		$account_number   = $this->get_post( 'realex_accountNumber' );
		$cv_number        = $this->get_post( 'realex_cvNumber' );
		$expiration_month = $this->get_post( 'realex_expirationMonth' );
		$expiration_year  = $this->get_post( 'realex_expirationYear' );
		$issue_number     = $this->get_post( 'realex_issueNumber' );  // switch only

		// if we're using an existing vaulted credit card then there's nothing to validate
		if ( $vault_card_ref ) return true;

		if ( empty( $card_type ) ) {
			SV_WC_Helper::wc_add_notice( __( 'Please select a card type', 'woocommerce-gateway-realex' ), 'error' );
			return false;
		}

		if ( $this->cvv == 'yes' ) {
			// check security code
			if ( empty( $cv_number ) ) {
				SV_WC_Helper::wc_add_notice( __( 'Card security code is missing', 'woocommerce-gateway-realex' ), 'error' );
				return false;
			}

			if ( ! ctype_digit( $cv_number ) ) {
				SV_WC_Helper::wc_add_notice( __( 'Card security code is invalid (only digits are allowed)', 'woocommerce-gateway-realex' ), 'error' );
				return false;
			}

			if ( ( strlen( $cv_number ) != 3 && in_array( $card_type, array( 'VISA', 'MC' ) ) ) || ( strlen( $cv_number ) != 4 && $card_type == 'AMEX' ) ) {
				SV_WC_Helper::wc_add_notice( __( 'Card security code is invalid (wrong length)', 'woocommerce-gateway-realex' ), 'error' );
				return false;
			}
		}

		// validate optional Switch issue number
		if ( $card_type == 'SWITCH' && $issue_number ) {

			if ( ! ctype_digit( $issue_number ) ) {
				SV_WC_Helper::wc_add_notice( __( 'Switch issue number is invalid (only digits are allowed)', 'woocommerce-gateway-realex' ), 'error' );
				return false;
			}

			if ( strlen( $issue_number ) > 3 ) {
				SV_WC_Helper::wc_add_notice( __( 'Switch issue number is invalid (wrong length)', 'woocommerce-gateway-realex' ), 'error' );
				return false;
			}
		}

		// check expiration data
		$current_year = date( 'Y' );
		$current_month = date( 'n' );

		if ( ! ctype_digit( $expiration_month ) || ! ctype_digit( $expiration_year ) ||
			 $expiration_month > 12 ||
			 $expiration_month < 1 ||
			 $expiration_year < $current_year ||
			 ( $expiration_year == $current_year && $expiration_month < $current_month ) ||
			 $expiration_year > $current_year + 20
		) {
			SV_WC_Helper::wc_add_notice( __( 'Card expiration date is invalid', 'woocommerce-gateway-realex' ), 'error' );
			return false;
		}

		// check card number
		$account_number = str_replace( array( ' ', '-' ), '', $account_number );

		if ( empty( $account_number ) || ! ctype_digit( $account_number ) ||
		     strlen( $account_number ) < 12 || strlen( $account_number ) > 19 ||
		     ! $this->luhn_check( $account_number ) ) {
			SV_WC_Helper::wc_add_notice( __( 'Card number is invalid', 'woocommerce-gateway-realex' ), 'error' );
			return false;
		}

		return true;
	}


	/**
	 * receipt_page
	 */
	public function receipt_page( $order ) {
		echo '<p>' . __( 'Thank you for your order.', 'woocommerce-gateway-realex' ) . '</p>';
	}


	/**
	 * Check if this gateway is enabled and configured.
	 *
	 * @see WC_Payment_Gateway::is_available()
	 */
	public function is_available() {

		// proper configuration
		if ( ! $this->get_merchant_id() || ! $this->get_shared_secret() ) return false;

		// all dependencies met
		if ( count( wc_realex()->get_missing_dependencies() ) > 0 ) return false;

		return parent::is_available();
	}


	/** Frontend ******************************************************/


	/**
	 * List saved credit cards on user account page
	 */
	public function account_cc() {

		// bail if the vault is not available
		if ( ! $this->vault_available() ) return;

		// retrieve the customer's current credit cards
		$current_user = wp_get_current_user();
		$credit_cards = get_user_meta( $current_user->ID, 'woocommerce_realex_cc', true );

		?>
		<h2 style="margin-top:40px;"><?php _e( 'Saved Credit Cards', 'woocommerce-gateway-realex' ); ?></h2><?php

		if ( isset( $_POST['realex-delete-card'] ) && isset( $credit_cards[ $_POST['realex-delete-card'] ] ) ) {

			require_once( wc_realex()->get_plugin_path() . '/classes/class-wc-realex-api.php' );

			// create the realex api client
			$realex_client = new Realex_API( $this->get_endpoint_url(), $this->get_realvault_endpoint_url(), $this->get_shared_secret() );

			$response = $this->card_cancel_card_request( $realex_client, get_user_meta( $current_user->ID, 'woocommerce_realex_payerref', true ), $_POST['realex-delete-card'] );

			if ( $response && ( $response->result == '00' && $realex_client->verify_transaction_signature( $response ) ) || ( $response->result == '501' ) ) {
				// card successfully removed from the vault, or unknown card/payer combination.  Either way, remove the local token
				$this->delete_card_ref( $_POST['realex-delete-card'] );
				unset( $credit_cards[ $_POST['realex-delete-card'] ] );
			}
		}
		if ( empty( $credit_cards ) ) echo '<p>' . __( 'You do not have any saved credit cards.', 'woocommerce-gateway-realex' ) . '</p>';
		else { ?>
			<a name="saved-cards"></a>
			<table>
				<tr>
					<th><?php esc_html_e( 'Card Number', 'woocommerce-gateway-realex' ); ?></th>
					<th><?php esc_html_e( 'Card Type',   'woocommerce-gateway-realex' ); ?></th>
					<th><?php esc_html_e( 'Expires',     'woocommerce-gateway-realex' ); ?></th>
					<th></th>
				</tr>
				<?php foreach ( $credit_cards as $credit_card ) : $credit_card = (object) $credit_card; ?>
				<tr>
					<td><?php echo $credit_card->last4; ?></td>
					<td><?php echo $this->card_type_options[ $credit_card->type ]; ?></td>
					<td><?php echo $credit_card->expiration_month . '/' . $credit_card->expiration_year; ?></td>
					<td>
						<form method="post">
							<input type="hidden" name="realex-delete-card" value="<?php echo $credit_card->ref; ?>" />
							<input type="submit" class="button" value="<?php _e( 'Remove', 'woocommerce-gateway-realex' ); ?>" />
						</form>
					</td>
				</tr>
				<?php endforeach; ?>
			</table><?php
		}
	}


	/** Realex Communication Methods **********************************/


	/**
	 * Communicate with Realex to set up a new or edit an existing secure vault payer
	 *
	 * @param Realex_API $realex_client realex api client
	 * @param boolean $new whether this is a request to set up a new payer
	 * @param string $payer_ref unique realex vault customer reference token
	 * @param WC_Order $order the order
	 *
	 * @return SimpleXMLElement response, or false on error
	 */
	private function update_vault_payer_request( $realex_client, $new, $payer_ref, $order ) {

		$request = new stdClass();

		$request->merchantid = $this->get_merchant_id();
		$request->orderid    = $this->get_order_number( $order );

		$request->payer = new stdClass();
		$request->payerRef = $payer_ref;
		$request->payer->firstname = SV_WC_Order_Compatibility::get_prop( $order, 'billing_first_name' );
		$request->payer->surname   = SV_WC_Order_Compatibility::get_prop( $order, 'billing_last_name' );
		$request->payer->company   = SV_WC_Order_Compatibility::get_prop( $order, 'billing_company' );

		$request->payer->address = new stdClass();
		$request->payer->address->line1       = SV_WC_Order_Compatibility::get_prop( $order, 'billing_address_1' );
		$request->payer->address->line2       = SV_WC_Order_Compatibility::get_prop( $order, 'billing_address_2' );
		$request->payer->address->city        = SV_WC_Order_Compatibility::get_prop( $order, 'billing_city' );
		$request->payer->address->county      = SV_WC_Order_Compatibility::get_prop( $order, 'billing_state' );
		// for some reason the create/edit user postcode allows only alphas and numerics, unlike the auth method
		$request->payer->address->postcode    = preg_replace( '/[^0-9a-z]/i', '', SV_WC_Order_Compatibility::get_prop( $order, 'billing_postcode' ) );
		// country name. for some stupid reason in WC 2.1 "United States" became "United States (US)" and "United Kingdom" became "United Kingdom (UK)"
		$request->payer->address->country     = preg_replace( '/\s\(\w+\)/', '', WC()->countries->countries[ SV_WC_Order_Compatibility::get_prop( $order, 'billing_country' ) ] );
		$request->payer->address->countryCode = SV_WC_Order_Compatibility::get_prop( $order, 'billing_country' );  // country code

		$request->payer->phonenumbers = new stdClass();
		$request->payer->phonenumbers->home = preg_replace( '/[^0-9+]/', '', SV_WC_Order_Compatibility::get_prop( $order, 'billing_phone' ) );

		$request->payer->email = SV_WC_Order_Compatibility::get_prop( $order, 'billing_email' );

		if ( $new )
			return $realex_client->payer_new_request( $request );
		else
			return $realex_client->payer_edit_request( $request );
	}


	/**
	 * Add up a new secure vault payment card
	 *
	 * @param Realex_API $realex_client realex api client
	 * @param string $payer_ref unique payer reference token
	 * @param string $card_ref unique card reference token
	 * @param WC_Order $order the order
	 * @param array $data optional data array to use in place of the $_POST
	 *        superglobal, if not null
	 *
	 * @return SimpleXMLElement response, or false on error
	 */
	private function new_card_request( $realex_client, $payer_ref, $card_ref, $order, $data = null ) {

		$request = new stdClass();

		$request->merchantid = $this->get_merchant_id();
		$request->orderid    = $this->get_order_number( $order );

		$request->card = new stdClass();
		$request->card->ref      = $card_ref;
		$request->card->payerref = $payer_ref;
		$request->card->number   = str_replace( array( ' ', '-' ), '', $this->get_post( 'realex_accountNumber', $data ) );
		$request->card->expdate  = $this->get_post( 'realex_expirationMonth', $data ) . substr( $this->get_post( 'realex_expirationYear', $data ), -2 );
		$request->card->chname   = $order->get_formatted_billing_full_name();  // TODO: hmmm, should I be collecting this from the checkout form?
		$request->card->type     = $this->get_post( 'realex_cardType', $data );

		if ( $this->get_post( 'realex_cardType', $data ) == 'SWITCH' ) {
			$request->card->issueno = $this->get_post( 'realex_issueNumber', $data );
		}

		return $realex_client->card_new_request( $request );
	}


	/**
	 * Remove a vault payment card
	 *
	 * @param Realex_API $realex_client realex api client
	 * @param string $payer_ref unique payer reference token
	 * @param string $card_ref unique card reference token
	 *
	 * @return SimpleXMLElement response, or false on error
	 */
	private function card_cancel_card_request( $realex_client, $payer_ref, $card_ref ) {

		$request = new stdClass();

		$request->merchantid = $this->get_merchant_id();

		$request->card = new stdClass();
		$request->card->ref      = $card_ref;
		$request->card->payerref = $payer_ref;

		return $realex_client->card_cancel_card_request( $request );
	}


	/**
	 * Perform an authorization request using a stored vault token
	 *
	 * @param Realex_API $realex_client realex api client
	 * @param string $payer_ref unique payer reference token
	 * @param string $card_type card type, one of $this->card_type_options keys
	 * @param string $payment_method unique card reference token
	 * @param WC_Order $order the order
	 *
	 * @return SimpleXMLElement response, or false on error
	 */
	private function vault_auth_request( $realex_client, $payer_ref, $card_type, $payment_method, $order ) {

		$request = new stdClass();

		$request->merchantid = $this->get_merchant_id();
		$request->account    = $this->get_account( $card_type, $order );  // blank account defaults to 'internet'
		$request->orderid    = $this->get_order_number( $order, $order->needs_payment() );

		$request->autosettleFlag = $this->settlement == "yes" ? 1 : 0;

		$request->amount = $order->payment_total * 100;  // in pennies
		$request->amountCurrency = SV_WC_Order_Compatibility::get_prop( $order, 'currency', 'view' );

		$request->payerref      = $payer_ref;
		$request->paymentmethod = $payment_method;

		return $realex_client->receipt_in_request( $request );
	}


	/**
	 * Perform an authorization request
	 * TODO: perhaps rather than passing in a parameter for the increment retry count, we could keep a state object for that
	 *
	 * @param Realex_API $realex_client realex api client
	 * @param WC_Order $order the order
	 * @param array $data optional post data to use in place of $_POST
	 * @param boolean $increment_retry_count optional whether to increment the retry
	 *        count to avoid order number clashes, defaults to true.  It's important
	 *        that this be true for, and only for, the first request of any transaction
	 *
	 * @return SimpleXMLElement response, or false on error
	 */
	private function auth_request( $realex_client, $order, $data = null, $increment_retry_count = true ) {

		$request = new stdClass();

		$request->merchantid = $this->get_merchant_id();
		$request->account    = $this->get_account( $this->get_post( 'realex_cardType', $data ), $order );  // blank account defaults to 'internet'

		// it's important that the order suffix for failed orders be generated *only* for non-3dsecure
		//  transactions here, because the 3ds-verifyenrolled will have already taken care of this for us
		$request->orderid = $this->get_order_number( $order, $increment_retry_count );

		$request->amount = $order->payment_total * 100;  // in pennies
		$request->amountCurrency = SV_WC_Order_Compatibility::get_prop( $order, 'currency', 'view' );

		$request->card = new stdClass();
		$request->card->number   = str_replace( array( ' ', '-' ), '', $this->get_post( 'realex_accountNumber', $data ) );
		$request->card->expdate  = $this->get_post( 'realex_expirationMonth', $data ) . substr( $this->get_post( 'realex_expirationYear', $data ), -2 );
		$request->card->type     = $this->get_post( 'realex_cardType', $data );
		$request->card->chname   = $order->get_formatted_billing_full_name();  // TODO: hmmm, should I be collecting this from the checkout form?

		if ( $this->cvv == "yes" ) {
			$request->card->cvn = new stdClass();
			$request->card->cvn->number  = $this->get_post( 'realex_cvNumber', $data );
			$request->card->cvn->presind = 1;
		}

		if ( $this->get_post( 'realex_cardType', $data ) == 'SWITCH' ) {
			$request->card->issueno = $this->get_post( 'realex_issueNumber', $data );
		}

		$request->autosettleFlag = $this->settlement == "yes" ? 1 : 0;

		$request->tssinfo = new stdClass();
		if ( $order->get_user_id() ) $request->tssinfo->custnum = $order->get_user_id();

		// Realex accepts only the postcode/country for auth addresses
		$request->tssinfo->addressBilling = new stdClass();
		if ( $this->avs_check() ) {
			$request->tssinfo->addressBilling->code = preg_replace( '/\D/', '', SV_WC_Order_Compatibility::get_prop( $order, 'billing_postcode' ) ) . '|' . preg_replace( '/\D/', '', SV_WC_Order_Compatibility::get_prop( $order, 'billing_address_1' ) );
		} else {
			$request->tssinfo->addressBilling->code = SV_WC_Order_Compatibility::get_prop( $order, 'billing_postcode' );
		}
		$request->tssinfo->addressBilling->country = SV_WC_Order_Compatibility::get_prop( $order, 'billing_country' );

		if ( SV_WC_Order_Compatibility::get_prop( $order, 'shipping_postcode' ) ) {
			$request->tssinfo->addressShipping = new stdClass();
			$request->tssinfo->addressShipping->code    = SV_WC_Order_Compatibility::get_prop( $order, 'shipping_postcode' );
			$request->tssinfo->addressShipping->country = SV_WC_Order_Compatibility::get_prop( $order, 'shipping_country' );
		}

		// allow additional request information to be added
		$request = apply_filters( 'wc_gateway_realex_auth_request', $request );

		// display the request object, if debug mode
		if ( $this->is_debug_mode() ) {
			$this->response_debug_message( $request, "Request: auth" );
		}

		return $realex_client->auth_request( $request );
	}


	/** Helper methods ******************************************************/


	/**
	 * Gets the array of real card types available for checkout.
	 * This will be an array of string values like the following:
	 * 'VISA', 'MC', 'AMEX', etc, these are the card type values that
	 * Realex accepts
	 *
	 * @return array of realex card types, ie 'VISA', 'MC', 'AMEX', etc
	 */
	private function get_real_cardtypes() {
		$real_cardtypes = array();
		foreach ( $this->cardtypes as $type ) {
			$real_type = preg_replace( '/-.*$/', '', $type );
			if ( ! in_array( $real_type, $real_cardtypes ) ) {
				$real_cardtypes[] = $real_type;
			}
		}
		return $real_cardtypes;
	}


	/**
	 * Mark the given order as failed, and set the order note
	 *
	 * @param WC_Order $order the order
	 * @param string $order_note the order note to set
	 */
	public function order_failed( $order, $order_note ) {
		if ( ! $order->has_status( 'failed' ) ) {
			$order->update_status( 'failed', $order_note );
		} else {
			// otherwise, make sure we add the order note so we can detect when someone fails to check out multiple times
			$order->add_order_note( $order_note );
		}
	}


	/**
	 * Gets the order number for the given order, including the order number
	 * retry suffix to avoid 501 Transaction Already Processed errors, and
	 * incrementing the retry count when $increment_retry_count is tru
	 *
	 * @param WC_Order $order the order object
	 * @param boolean $increment_retry_count optional whether to increment the retry
	 *        count to avoid order number clashes, defaults to false.  It's important
	 *        that this be true for, and only for, the first request of any transaction
	 */
	public function get_order_number( $order, $increment_retry_count = false ) {

		// Realex will not allow the reuse of order numbers, even for failed
		//  transactions.  As failed orders are not recreated from the Pay
		//  page (as they are in the regular checkout page), they are not
		//  assigned a new order number automatically, so we must modify the
		//  order number for failed orders to allow them to be processed
		$realex_order_number_suffix = '';
		$realex_retry_count         = SV_WC_Order_Compatibility::get_meta( $order, '_realex_retry_count' );

		if ( ! is_numeric( $realex_retry_count ) ) {
			$realex_retry_count = '';
		}

		// existing failed order, increment the retry count so we don't get order number clashes
		if ( $increment_retry_count ) {

			if ( is_numeric( $realex_retry_count ) ) {
				$realex_retry_count++;
			} else {
				$realex_retry_count = 0;
			}

			update_post_meta( SV_WC_Order_Compatibility::get_prop( $order, 'id' ), '_realex_retry_count', $realex_retry_count );
		}

		$realex_subscription_count = SV_WC_Order_Compatibility::get_meta( $order, '_realex_subscription_count' );

		if ( ! is_numeric( $realex_subscription_count ) || ! $realex_subscription_count ) {
			$realex_subscription_count = SV_WC_Order_Compatibility::get_meta( $order, '_subscription_count' );
		}

		if ( ! $realex_subscription_count ) {
			$realex_subscription_count = 0;
		}

		$realex_order_number_suffix = '';
		if ( $realex_retry_count )        $realex_order_number_suffix .= '-' . $realex_retry_count;
		if ( $realex_subscription_count ) $realex_order_number_suffix .= '-' . $realex_subscription_count;

		$realex_order_number_suffix = apply_filters( 'wc_realex_order_number_suffix', $realex_order_number_suffix, SV_WC_Order_Compatibility::get_prop( $order, 'id' ) );

		return preg_replace( '/[^\w\d_\-]/', '', $order->get_order_number() ) . $realex_order_number_suffix;
	}


	/**
	 * Attempt to add a new card to the vault payer identified by $vault_payer_ref.
	 * On failure the passed-in $card_ref is set to null.
	 *
	 * @param Realex_API $realex_client realex api client
	 * @param string $payer_ref vault customer reference token
	 * @param string $card_ref vault card reference token passed by reference
	 * @param array $data optional data array to use in place of the $_POST
	 *        superglobal, if not null
	 * @param WC_Order $order the order
	 *
	 * @return SimpleXMLElement or false on communication failure
	 */
	private function create_new_vault_card( $realex_client, $payer_ref, &$card_ref, $order, $data = null ) {

		$response = $this->new_card_request( $realex_client, $payer_ref, $card_ref, $order, $data );

		if ( $response && $response->result == '00' && $realex_client->verify_transaction_signature( $response ) ) {

			// Store the vault credit card ref
			$cards = get_user_meta( $order->get_user_id(), 'woocommerce_realex_cc', true );

			if ( empty( $cards ) ) {
				$cards = array();
			}

			$cards[ $card_ref ] = array( 'ref'              => $card_ref,
			                             'type'             => $this->get_post( 'realex_cardType', $data ),
			                             'last4'            => substr( $this->get_post( 'realex_accountNumber', $data ), -4 ),
			                             'expiration_month' => $this->get_post( 'realex_expirationMonth', $data ),
			                             'expiration_year'  => $this->get_post( 'realex_expirationYear', $data ) );
			update_user_meta( $order->get_user_id(), 'woocommerce_realex_cc', $cards ); // Update WP user meta with our tokens
			$message = 'message';

		} else {
			// clear out the card_ref (by reference)
			$card_ref = null;
			$message = 'error';
		}

		if( $this->is_debug_mode() ) {
			// if in debug mode, display the error message
			$this->response_debug_message( $response, "Response: card-new", $message, true );
		}

		return $response;
	}


	/**
	 * Attempt to create a new secure vault payer.  On failure the passed-in
	 * $payer_ref is set to null
	 *
	 * @param Realex_API $realex_client realex api client
	 * @param string $payer_ref unique realex vault customer reference token
	 * @param WC_Order $order the order
	 *
	 * @return SimpleXMLElement or false on communication failure
	 */
	private function create_new_vault_payer( $realex_client, &$payer_ref, $order ) {

		// add the payer to the vault
		$response = $this->update_vault_payer_request( $realex_client, true, $payer_ref, $order );

		if ( $response && $response->result == '00' && $realex_client->verify_transaction_signature( $response ) ) {

			// Success: Store the vault user ID
			update_user_meta( $order->get_user_id(), 'woocommerce_realex_payerref', $payer_ref );
			$message = 'message';

		} else {
			// clear out the payer_ref (by reference)
			$payer_ref = null;
			$message = 'error';
		}

		if( $this->is_debug_mode() ) {
			// if in debug mode, display the error message
			$this->response_debug_message( $response, "Response: payer-new", $message, true );
		}

		return $response;
	}


	/**
	 * Attempt to update an existing secure vault payer: name, address, etc.  If the payer is
	 * not found in the Vault, the passed-in $payer_ref is set to null.
	 *
	 * @param Realex_API $realex_client realex api client
	 * @param string $payer_ref unique realex vault customer reference token
	 * @param WC_Order $order the order
	 *
	 * @return SimpleXMLElement or false on communication failure
	 */
	private function update_vault_payer( $realex_client, &$payer_ref, $order ) {

		$response = $this->update_vault_payer_request( $realex_client, false, $payer_ref, $order );

		if ( $response && $response->result == '00' && $realex_client->verify_transaction_signature( $response ) ) {
			$message = 'message';
		} else {
			$message = 'error';
		}

		if( $response && $response->result == '501' && stripos( $response->message, 'There is no such payer ref' ) !== false ) {

			// the payerref we have on file is invalid: delete it and allow following code to attempt to recreate it
			$this->delete_payer_ref( $order->get_user_id() );
			$payer_ref = null;
		}

		if( $this->is_debug_mode() ) {
			// if in debug mode, display the error message
			$this->response_debug_message( $response, "Response: payer-edit", $message, true );
		}

		return $response;
	}


	/**
	 * Returns a new unique realex payer ref in the form wc_<em>time</em>_<em>user_id</em>
	 *
	 * @param int $user_id wordpress user identifier
	 *
	 * @return string new unique realex customer reference token
	 */
	private function get_new_payer_ref( $user_id ) {
		return 'wc_' . time() . '_' . $user_id;
	}


	/**
	 * Returns a new unique realex card ref in the form
	 * <em>user_id</em>_<em>card_type</em>_<em>uniqid</em>
	 *
	 * @param int $user_id wordpress user identifier
	 * @param string $card_type card type
	 *
	 * @return string new unique realex card reference token
	 */
	private function get_new_card_ref( $user_id, $card_type ) {
		return uniqid( $user_id . '_' . strtolower( $card_type ) . '_' );
	}


	/**
	 * Delete the locally stored realex user ref associated with the given
	 * $user_id, along with any associated credit card tokens
	 *
	 * @param int $user_id wordpress user id
	 */
	private function delete_payer_ref( $user_id ) {

		delete_user_meta( $user_id, 'woocommerce_realex_payerref' );
		delete_user_meta( $user_id, 'woocommerce_realex_cc' );
	}


	/**
	 * Remove the realex card reference token $card_ref from the local database
	 *
	 * @param string $card_ref realex card reference token
	 */
	private function delete_card_ref( $card_ref ) {
		// retrieve the customer's current credit cards
		$current_user = wp_get_current_user();
		$credit_cards = get_user_meta( $current_user->ID, 'woocommerce_realex_cc', true );
		unset( $credit_cards[ $card_ref ] );
		update_user_meta( $current_user->ID, 'woocommerce_realex_cc', $credit_cards );
	}


	/**
	 * Perform standard luhn check.  Algorithm:
	 *
	 * 1. Double the value of every second digit beginning with the second-last right-hand digit.
	 * 2. Add the individual digits comprising the products obtained in step 1 to each of the other digits in the original number.
	 * 3. Subtract the total obtained in step 2 from the next higher number ending in 0.
	 * 4. This number should be the same as the last digit (the check digit). If the total obtained in step 2 is a number ending in zero (30, 40 etc.), the check digit is 0.
	 *
	 * @param string $account_number the credit card number to check
	 *
	 * @return boolean true if $account_number passes the check, false otherwise
	 */
	private function luhn_check( $account_number ) {
		$sum = 0;
		for ( $i = 0, $ix = strlen( $account_number ); $i < $ix - 1; $i++) {
			$weight = substr( $account_number, $ix - ( $i + 2 ), 1 ) * ( 2 - ( $i % 2 ) );
			$sum += $weight < 10 ? $weight : $weight - 9;
		}

		return substr( $account_number, $ix - 1 ) == ( ( 10 - $sum % 10 ) % 10 );
	}


	/**
	 * Add the XML response to the woocommerce message object
	 *
	 * @param SimpleXMLElement $response response from Realex server
	 * @param string $title debug message title to display
	 * @param string $type optional message type, one of 'message' or 'error', defaults to 'message'
	 * @param boolean $set_message optional whether to set the supplied
	 *        message so that it appears on the next page load (ie, a
	 *        message you want displayed on the 'thank you' page
	 *
	 * @return void
	 */
	public function response_debug_message( $response, $title, $type = 'message', $set_message = false ) {

		if ( get_class( $response ) == 'SimpleXMLElement' ) {
			$dom = dom_import_simplexml( $response )->ownerDocument;
			$dom->formatOutput = true;
			$raw_message = $dom->saveXML();
			$message = htmlspecialchars( preg_replace( "/\n+/", "\n", $raw_message ) );
		} elseif ( is_object( $response ) ) {
			$raw_message = $message = print_r( $response, true );
		}

		$debug_message = "<pre><strong>" . $title . "</strong>\n" . $message . "</pre>";

		if ( $type == 'message' ) {
			SV_WC_Helper::wc_add_notice( $debug_message );
		} else {
			SV_WC_Helper::wc_add_notice( $debug_message, 'error' );
		}
	}


	/**
	 * Safely get post data (or passed $data) if set
	 *
	 * @param string $name name of post argument to get
	 * @param array $data optional data array to use in place of the $_POST
	 *        superglobal, if not null
	 * @return mixed post data, or null
	 */
	public function get_post( $name, $data = null ) {

		if ( $data !== null ) {
			if ( isset( $data[ $name ] ) ) {
				return wc_clean( $data[ $name ] );
			}
		} else {
			if ( isset( $_POST[ $name ] ) ) {
				return wc_clean( $_POST[ $name ] );
			}
		}

		return null;
	}


	/** Getter methods ******************************************************/


	/**
	 * Returns true if the gateway is enabled.  This has nothing to do with
	 * whether the gateway is properly configured or functional.
	 *
	 * @return boolean true if the gateway is enabled
	 */
	public function is_enabled() {
		return $this->enabled;
	}


	/**
	 * Returns true if an AVS check should be performed for customer's billing
	 * address.  This applies only to UK customers.
	 *
	 * @return boolean true if an AVS check should be performed
	 */
	private function avs_check() {
		return $this->avs == 'yes';
	}


	/**
	 * Returns true if the Vault is enabled and available
	 */
	public function vault_available() {
		return apply_filters( 'wc_gateway_realex_vault_available', $this->vault == 'yes' );
	}


	/**
	 * Return the merchant id
	 *
	 * @return string merchant id
	 */
	public function get_merchant_id() {
		return $this->merchantid;
	}


	/**
	 * Returns the endpoint url
	 *
	 * @return string endpoint URL
	 */
	public function get_endpoint_url() {
		return $this->endpoint_url;
	}


	/**
	 * Returns the shared secret
	 *
	 * @return string shared secret
	 */
	public function get_shared_secret() {
		return $this->sharedsecret;
	}


	/**
	 * Returns the vault endpoint url
	 *
	 * @return string endpoint URL
	 */
	public function get_realvault_endpoint_url() {
		return $this->realvault_endpoint_url;
	}


	/**
	 * Returns the account for the current mode (test/live)
	 *
	 * @param string $card_type card type, one of $this->card_type_options keys
	 * @param WC_Order $order the order
	 *
	 * @return string account
	 */
	public function get_account( $card_type, $order ) {

		if ( $this->is_test_mode() ) {
			// test mode
			if ( $card_type == 'AMEX' && $this->amex_account_test ) {
				$account = $this->amex_account_test;
			} else {
				$account = $this->account_test;
			}
		} else {
			// live mode
			if ( $card_type == 'AMEX' && $this->amex_account_live ) {
				$account = $this->amex_account_live;
			} else {
				$account = $this->account_live;
			}
		}

		return apply_filters( 'woocommerce_realex_account', $account, $card_type, $order, $this );
	}


	/**
	 * Is test mode enabled?
	 *
	 * @return boolean true if test mode is enabled
	 */
	public function is_test_mode() {
		return $this->testmode == "yes";
	}


	/**
	 * Is debug mode enabled?
	 *
	 * @return boolean true if debug mode is enabled
	 */
	public function is_debug_mode() {
		return $this->debug == "yes";
	}


	/**
	 * Returns the Realex 3DSecure object
	 *
	 * @return Realex_3DSecure 3DSecure object
	 */
	public function get_threedsecure() {
		return $this->threedsecure;
	}

}
