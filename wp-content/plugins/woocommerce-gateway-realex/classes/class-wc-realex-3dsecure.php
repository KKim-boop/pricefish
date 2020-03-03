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
 * Realex Payment Gateway 3DSecure Class
 *
 * This class adds 3DSecure processing capability to the Realex gateway
 */
class Realex_3DSecure {

	/**
	 * @var WC_Gateway_Realex Realex Gateway object
	 */
	private $wc_gateway_realex;

	/**
	 * @var string the realmpi endpoint URL
	 */
	private $realmpi_endpoint_url = "https://epage.payandshop.com/epage-3dsecure.cgi";

	/**
	 * @var boolean true if 3DSecure is enabled
	 */
	private $threeds;
	private $liability_shift;

	/**
	 * Memo 3DSecure response object containing eci, xid and cavv
	 * parameters used for performing a 3DSecure authorization request.
	 * Not *in love* with this solution, but it works for now
	 */
	private $threedsecure;

	/**
	 * @var string API URL for handling the ACS callback
	 */
	private $acs_term_url;

	/**
	 * @var string Encryption key
	 */
	private $encryption_key;


	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $wc_gateway_realex ) {

		$this->wc_gateway_realex = $wc_gateway_realex;

		// initialize the 3DSecure module after the Realex Gateway is initialized
		add_action( 'wc_gateway_realex_init', array( $this, 'init' ) );

		// add the 3DSecure configuration fields to the Realex Gateway
		add_filter( 'wc_gateway_realex_form_fields', array( $this, 'form_fields' ) );

		// add the 3DSecure configuration field javascript handling to the Realex Gateway
		add_filter( 'wc_gateway_realex_admin_options_js', array( $this, 'admin_options' ) );

		// pay page handling
		add_action( 'woocommerce_receipt_' . $this->wc_gateway_realex->id, array( $this, 'payment_page' ) );

		// remove the default pay page message
		remove_action( 'woocommerce_receipt_' . $this->wc_gateway_realex->id, array( $this->wc_gateway_realex, 'receipt_page' ) );
	}


	/**
	 * Initialize the 3DSecure class
	 */
	public function init() {

		$this->threeds         = $this->wc_gateway_realex->settings['threeds'];
		$this->liability_shift = $this->wc_gateway_realex->settings['liability_shift'];

		if ( $this->is_3dsecure_available() ) {

			// RealMPI 3DSecure ACS Term url
			$this->acs_term_url = add_query_arg( 'wc-api', get_class( $this->wc_gateway_realex ), home_url( '/' ) );

			if ( get_option( 'woocommerce_force_ssl_checkout' ) == 'yes' || is_ssl() ) {
				$this->acs_term_url = str_replace( 'http://', 'https://', $this->acs_term_url );
			}

			$wc_api_endpoint = strtolower( get_class( $this->wc_gateway_realex ) );

			// add listener for the ACS response
			add_action( 'woocommerce_api_' . $wc_api_endpoint, array( $this, 'handle_acs_response' ) );
			// ensure a listener exists for the general class name if Subscriptions is active
			add_action( 'woocommerce_api_' . str_replace( '_subscriptions', '', $wc_api_endpoint ), array( $this, 'handle_acs_response' ) );

			// ensure the authorize callback is only run once
			remove_all_filters( 'wc_gateway_realex_process_payment', 5 );

			// 3dsecure payment processing, hook into the process payment filter before parent
			add_filter( 'wc_gateway_realex_process_payment', array( $this, 'process_payment' ), 5, 2 );

			// add MPI fields to the auth request when needed
			add_filter( 'wc_gateway_realex_auth_request',    array( $this, 'auth_request' ) );

			// add liability shift note to the order
			add_filter( 'wc_gateway_realex_order_note',      array( $this, 'order_note' ), 10, 5 );

			// vault is not available if liability shift is required
			add_filter( 'wc_gateway_realex_vault_available', array( $this, 'vault_available' ) );
		}
	}


	/**
	 * Add the 3DSecure fields to the gateway options
	 */
	public function form_fields( $fields ) {
		$new_fields = array();

		foreach ( $fields as $name => $field ) {
			$new_fields[ $name ] = $field;

			if ( $name == 'avs' ) {

				$new_fields['threeds'] = array(
					'title'   => __( '3DSecure', 'woocommerce-gateway-realex' ),
					'label'   => __( 'Enable RealMPI Payer Authentication (3D Secure, SecureCode) for Visa, MasterCard and Switch cards only.  ', 'woocommerce-gateway-realex' ) . '<strong>' . __( 'Requires that you contact Realex support to enable on your account', 'woocommerce-gateway-realex' ) . '</strong>',
					'type'    => 'checkbox',
					'default' => 'no'
				);

				$new_fields['liability_shift'] = array(
					'title'       => __( 'Liability Shift', 'woocommerce-gateway-realex' ),
					'label'       => __( 'Require liability shift', 'woocommerce-gateway-realex' ),
					'type'        => 'checkbox',
					'description' => __( 'Only accept payments when liability shift has occurred (Requires 3DSecure to be enabled, and not compatible with RealVault or Subscriptions).', 'woocommerce-gateway-realex' ),
					'default'     => 'no'
				);
			}
		}

		return $new_fields;
	}


	/**
	 * Filter to add javascript code to handle the 3DSecure configuration
	 * fields on the Realex gateway settings page
	 *
	 * @param string $javascript the javascript to output
	 *
	 * @return string the javascript to output
	 */
	public function admin_options( $javascript ) {

		ob_start();
		?>
		$('#woocommerce_realex_threeds').change(
			function() {
				var liabilityShiftTextRow = $(this).closest('tr').next();

				if ($(this).is(':checked')) {
					liabilityShiftTextRow.show();
				} else {
					liabilityShiftTextRow.hide();
				}
			}).change();
		<?php
		$javascript .= ob_get_clean();

		return $javascript;
	}


	/**
	 * Filter to mark the Vault as not available if liability shift is enabled
	 */
	public function vault_available( $available ) {
		return $available && ! $this->liability_shift();
	}


	/**
	 * Perform a 3DSecure payment authorization
	 *
	 * @param boolean $return
	 * @param int $order_id the order identifier
	 */
	function process_payment( $return, $order_id ) {

		// redirect to payment page for payment if 3D secure is enabled
		if ( $this->is_3dsecure_available() && ! SV_WC_Helper::get_post( 'woocommerce_pay_page' ) ) {

			// unhook the standard payment processing filter
			remove_filter( 'wc_gateway_realex_process_payment', array( $this->wc_gateway_realex, 'process_payment_authorize' ), 10 );

			// redirect from Checkout page
			$order = wc_get_order( $order_id );

			WC()->cart->empty_cart();

			// redirect to payment page to continue payment
			return array(
				'result'   => 'success',
				'redirect' => $order->get_checkout_payment_url( true ),
			);
		}

		// process 3D secure payment from the payment page
		else {
			require_once( 'class-wc-realex-api.php' );

			$order = wc_get_order( $order_id );

			// set payment total here so it can be modified for subscriptions if needed
			$order->payment_total = number_format( $order->get_total(), 2, '.', '' );

			// create the realex api client
			$realex_client = new Realex_API( $this->wc_gateway_realex->get_endpoint_url(), $this->wc_gateway_realex->get_realvault_endpoint_url(), $this->wc_gateway_realex->get_shared_secret() );
			$realex_client->set_realmpi_endpoint_url( $this->get_realmpi_endpoint_url() );

			$vault_card_ref = $this->wc_gateway_realex->get_post( 'realex_card_ref' );

			$card_type = $this->wc_gateway_realex->get_post( 'realex_cardType' );

			// 3DSecure transaction with a supported card type?
			if ( $this->is_3dsecure_available() ) {
				if ( ! $vault_card_ref && in_array( $card_type, array( 'VISA', 'MC', 'SWITCH' ) ) ) {

					// unhook the standard payment processing filter
					remove_filter( 'wc_gateway_realex_process_payment', array( $this->wc_gateway_realex, 'process_payment_authorize' ), 10, 2 );

					return $this->authorize_3dsecure( $realex_client, $order );

				} elseif ( $this->liability_shift() && ! in_array( $card_type, apply_filters( 'wc_gateway_realex_liability_shift_excluded_card_types', array() ) ) ) {
					// liability shift required, order fail

					// unhook the standard payment processing action
					remove_filter( 'wc_gateway_realex_process_payment', array( $this->wc_gateway_realex, 'process_payment_authorize' ), 10, 2 );

					if ( $vault_card_ref ) $order_note = __( 'No Liability Shift: vaulted payments not compatible with 3D Secure.', 'woocommerce-gateway-realex' );
					else $order_note = __( 'No Liability Shift: Card Type not compatible with 3D Secure.', 'woocommerce-gateway-realex' );

					$this->wc_gateway_realex->order_failed( $order, $order_note );

					SV_WC_Helper::wc_add_notice( __( 'The transaction has been declined, please wait and try again or try an alternative payment method.  Your order has been recorded, please contact us if you wish to provide payment over the phone.', 'woocommerce-gateway-realex' ), 'error' );

					return;
				}
			}
		}
	}


	/**
	 * Handle the ACS response back from the 3DSecure authorization server.
	 * This POST will include the 'pares' field required to perform the
	 * 3ds-verifysig request and complete the authenticated checkout,
	 * along with the encrypted Merchant Data (MD) which contains the
	 * order identifier and credit card information.
	 */
	public function handle_acs_response() {

		require_once( 'class-wc-realex-api.php' );

		// decrypt the merchant data so we can complete the transaction
		$data = $this->wc_gateway_realex->get_post( 'MD' );

		// page requested with no data, nothing we can do but bail
		if ( ! $data ) return;

		$data = $this->decrypt_merchant_data( $data );

		$data['pares'] = $this->wc_gateway_realex->get_post( 'PaRes' );

		$order = wc_get_order( $data['order_id'] );

		if ( ! $order ) {

			SV_WC_Helper::wc_add_notice( __( 'Communication error', 'woocommerce-gateway-realex' ), 'error' );

			// redirect back to home page since we don't have valid order data
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}

		$order->payment_total = number_format( $order->get_total(), 2, '.', '' );

		// build the redirect url back to the payment page if needed for failure handling
		$payment_page = $order->get_checkout_payment_url();

		// create the realex api client
		$realex_client = new Realex_API( $this->wc_gateway_realex->get_endpoint_url(), $this->wc_gateway_realex->get_realvault_endpoint_url(), $this->wc_gateway_realex->get_shared_secret() );
		$realex_client->set_realmpi_endpoint_url( $this->get_realmpi_endpoint_url() );

		// perform the 3ds verify signature request
		$response = $this->threeds_verifysig_request( $realex_client, $order, $data );

		if ( ! $response ) {
			SV_WC_Helper::wc_add_notice( __( 'Communication error', 'woocommerce-gateway-realex' ), 'error' );

			// redirect back to payment page
			wp_redirect( $payment_page );
			exit;
		}

		if ( ! $realex_client->verify_transaction_signature( $response ) ) {
			// response was not properly signed by realex
			SV_WC_Helper::wc_add_notice( __( 'Error - invalid transaction signature, check your Realex settings.', 'woocommerce-gateway-realex' ), 'error' );

			// if debug mode load the response into the messages object
			if ( $this->wc_gateway_realex->is_debug_mode() ) {
				$this->wc_gateway_realex->response_debug_message( $response, "Response: 3ds-verifysig", 'error' );
			}

			wp_redirect( $payment_page );
			exit;
		}

		if ( $response->result == '00' ) {
			// Success
			if ( $this->wc_gateway_realex->is_debug_mode() ) $this->wc_gateway_realex->response_debug_message( $response, "Response: 3ds-verifysig" );

			$result = null;
			switch( $response->threedsecure->status ) {
				case 'Y':
					// the cardholder entered their passphrase correctly, this is a full 3DSecure transaction, send a normal Realex auth message (ECI field 5 or 2)
					$this->threedsecure = $response->threedsecure;

					$result = $this->wc_gateway_realex->authorize( $realex_client, $order, $data, false );
				break;

				case 'N':
					// the cardholder entered the wrong passphrase.  No shift in liability, do not proceed to authorization
					$error_note = __( '3DSecure authentication failure: incorrect passphrase', 'woocommerce-gateway-realex' );
					$this->wc_gateway_realex->order_failed( $order, $error_note );

					// customer error message
					$message = __( 'Order declined due to incorrect authentication passphrase.  Please try again with the correct authentication, or use a different card or payment method.', 'woocommerce-gateway-realex' );
					SV_WC_Helper::wc_add_notice( $message, 'error' );
				break;

				case 'U':
					// the issuer was having problems with their systems at the time and was unable to check the passphrase.  transaction may be completed, but no shift in liability will occur (ECI field 7 or 0)

					$order_note = __( '3DSecure enrollment status verification: issuer was having problems with their system, no shift in chargeback liability.', 'woocommerce-gateway-realex' );

					if ( $this->liability_shift() ) {
						// liability shift required, order fail
						$this->wc_gateway_realex->order_failed( $order, $order_note );

						SV_WC_Helper::wc_add_notice( __( 'The transaction has been declined, please wait and try again or try an alternative payment method.  Your order has been recorded, please contact us if you wish to provide payment over the phone.', 'woocommerce-gateway-realex' ), 'error' );
					} else {
						// charge without liability shift allowed
						$order->add_order_note( $order_note );
						$this->threedsecure = $this->get_eci_non_3dsecure( $data['realex_cardType'] );
						$result = $this->wc_gateway_realex->authorize( $realex_client, $order, $data, false );
					}
				break;

				case 'A':
					// bank acknowledges the attempt made by the merchant and accepts the liability shift.  send a normal auth message (ECI field 6 or 1)
					// TODO: no known test case
					$this->threedsecure = $response->threedsecure;
					$result = $this->wc_gateway_realex->authorize( $realex_client, $order, $data, false );
				break;
			}

			// if the authorization was successful, redirect to the thank-you page, otherwise redirect back to the payment page
			if ( isset( $result['result'] ) && $result['result'] == 'success' ) wp_redirect( $result['redirect'] );
			else wp_redirect( $payment_page );

		} elseif ( $response->result == '520' ) {

			// if debug mode load the response into the messages object
			if ( $this->wc_gateway_realex->is_debug_mode() ) {
				$this->wc_gateway_realex->response_debug_message( $response, "Response: 3ds-verifysig", 'error' );
			}

			// Invalid response from ACS, no liability shift.  send a normal auth message (ECI field 7 or 0) (note: this isn't described in the realmpi integration sequence of events, but appears in the test cards)
			$order_note = __( '3DSecure invalid response from ACS, no shift in chargeback liability.', 'woocommerce-gateway-realex' );

			if ( $this->liability_shift() ) {
				// liability shift required, order fail
				$this->wc_gateway_realex->order_failed( $order, $order_note );

				SV_WC_Helper::wc_add_notice( __( 'The transaction has been declined, please wait and try again or try an alternative payment method.  Your order has been recorded, please contact us if you wish to provide payment over the phone.', 'woocommerce-gateway-realex' ), 'error' );
			} else {
				// charge without liability shift allowed
				$order->add_order_note( $order_note );

				$this->threedsecure = $this->get_eci_non_3dsecure( $data['realex_cardType'] );
				$result = $this->wc_gateway_realex->authorize( $realex_client, $order, $data, false );
			}

			// if the authorization was successful, redirect to the thank-you page, otherwise redirect back to the payment page
			if ( isset( $result['result'] ) && $result['result'] == 'success' ) wp_redirect( $result['redirect'] );
			else wp_redirect( $payment_page );

		} else {

			// if debug mode load the response into the messages object
			if ( $this->wc_gateway_realex->is_debug_mode() ) {
				$this->wc_gateway_realex->response_debug_message( $response, "Response: 3ds-verifysig", 'error' );
			}

			if ( $response->result == '110' ) {
				// Failure: the Signature on the PaRes did not validate - treat this as a fraudulent authentication.
				$error_note = sprintf( __( '3DSecure Verify Signature mismatch, possible fradulent transaction (Result %s - "%s")', 'woocommerce-gateway-realex' ), $response->result, $response->message );
			} else {
				// the implementation guide doesn't indicate any other response errors, but lets be cautious
				$error_note = sprintf( __( 'Realex Verify Signature error (Result: %s - "%s").', 'woocommerce-gateway-realex' ), $response->result, $response->message );
			}

			// if this is a duplicate order error and the order is already paid, just redirect to the Thank You page
			// this accounts for situations where the customer refreshes/back-buttons during the 3DSecure processing
			if ( '501' === (string) $response->result && $order->is_paid() ) {

				$order->add_order_note( $error_note );

				wp_redirect( $this->wc_gateway_realex->get_return_url( $order ) );
				exit;
			}

			$this->wc_gateway_realex->order_failed( $order, $error_note );

			// customer error message
			$message = __( 'The transaction has been declined by your bank, please contact your bank for more details, or try an alternative payment method.  Your order has been recorded, please contact us if you wish to provide payment over the phone.', 'woocommerce-gateway-realex' );
			SV_WC_Helper::wc_add_notice( $message, 'error' );

			wp_redirect( $payment_page );
		}
		exit;
	}


	/**
	 * Process a RealMPI transaction
	 *
	 * @param Realex_API $realex_client realex api client
	 * @param WC_Order $order the order
	 */
	private function authorize_3dsecure( $realex_client, $order ) {

		$response = $this->threeds_verifyenrolled_request( $realex_client, $order );

		if ( ! $response ) {
			SV_WC_Helper::wc_add_notice( __( 'Connection error', 'woocommerce-gateway-realex' ), 'error' );
			return;
		}

		if ( ! $realex_client->verify_transaction_signature( $response ) ) {
			// response was not properly signed by realex
			SV_WC_Helper::wc_add_notice( __( 'Error - invalid transaction signature, check your Realex settings.', 'woocommerce-gateway-realex' ), 'error' );

			// if debug mode load the response into the messages object
			if ( $this->wc_gateway_realex->is_debug_mode() ) {
				$this->wc_gateway_realex->response_debug_message( $response, "Response: 3ds-verifyenrolled", 'error' );
			}
			return;
		}

		if ( $response->result == '00' || ( $response->result == '110' && $response->enrolled == 'N' && (string) $response->url ) ) {
			// Success: redirect cardholder to authentication URL
			//  or, card holder not enrolled but an attempt server is available.  There seems to be no test case
			//  for this case, but this check is based on my reading of the RealMPI Remote Developers Guide 7.8 "Response Codes for 3ds-verifyenrolled"
			if ( $this->wc_gateway_realex->is_debug_mode() ) {
				$this->wc_gateway_realex->response_debug_message( $response, "Response: 3ds-verifyenrolled", true );
			}

			$account_number   = $this->wc_gateway_realex->get_post( 'realex_accountNumber' );
			$card_type        = $this->wc_gateway_realex->get_post( 'realex_cardType' );
			$expiration_month = $this->wc_gateway_realex->get_post( 'realex_expirationMonth' );
			$expiration_year  = $this->wc_gateway_realex->get_post( 'realex_expirationYear' );
			$issueno          = $this->wc_gateway_realex->get_post( 'realex_issueNumber' );
			$cvnumber         = $this->wc_gateway_realex->get_post( 'realex_cvNumber' );
			$realex_vault_new = $this->wc_gateway_realex->get_post( 'realex_vault_new' );

			?>
			<html>
				<head>
					<title>3DSecure Payment Authorisation</title>
				</head>
				<body>
					<form name="frmLaunchACS" id="3ds_submit_form" method="POST" action="<?php echo $response->url; ?>">
						<input type="hidden" name="PaReq" value="<?php echo $response->pareq; ?>">
						<input type="hidden" name="TermUrl" value="<?php echo $this->acs_term_url; ?>">
						<input type="hidden" name="MD" value="<?php echo $this->encrypt_merchant_data( array(
							'order_id'               => SV_WC_Order_Compatibility::get_prop( $order, 'id' ),
							'realex_accountNumber'   => $account_number,
							'realex_cardType'        => $card_type,
							'realex_expirationMonth' => $expiration_month,
							'realex_expirationYear'  => $expiration_year,
							'realex_issueNumber'     => $issueno,
							'realex_cvNumber'        => $cvnumber,
							'realex_vault_new'       => $realex_vault_new
						) ); ?>">
						<noscript>
							<div class="woocommerce_message"><?php _e( 'Processing your Payer Authentication Transaction', 'woocommerce-gateway-realex' ); ?> - <?php _e( 'Please click Submit to continue the processing of your transaction.', 'woocommerce-gateway-realex' ); ?>  <input type="submit" class="button" id="3ds_submit" value="Submit" /></div>
						</noscript>
					</form>
					<script>
						document.frmLaunchACS.submit();
					</script>
				</body>
			</html>
			<?php
			exit;

			// If all goes well, we should receive an asynchronous request
			//  back from the authentication server, which will be handled
			//  by the handle_acs_response() method

		} elseif ( $response->result == '110' ) {
			// cardholder not enrolled

			if ( $this->wc_gateway_realex->is_debug_mode() ) $this->wc_gateway_realex->response_debug_message( $response, "Response: 3ds-verifyenrolled", true );

			if ( $response->enrolled == 'N' && ! (string) $response->url ) {
				// Not Enrolled: cardholder is not enrolled, liability shifted, send a normal auth message (ECI: Visa: 6, MC/SWITCH: 1)

				$this->threedsecure = $this->get_eci_merchant_3dsecure( $this->wc_gateway_realex->get_post( 'realex_cardType' ) );
				return $this->wc_gateway_realex->authorize( $realex_client, $order, null, false );

			} elseif ( $response->enrolled == 'U' ) {
				// enrolled status could not be verified, no liability shift, send a normal auth message (ECI: Visa: 7, MC/SWITCH: 0)
				$order_note = __( '3DSecure enrollment status could not be verified, no shift in chargeback liability.', 'woocommerce-gateway-realex' );

				if ( $this->liability_shift() ) {
					// liability shift required, order fail
					$this->wc_gateway_realex->order_failed( $order, $order_note );

					SV_WC_Helper::wc_add_notice( __( 'The transaction has been declined, please wait and try again or try an alternative payment method.  Your order has been recorded, please contact us if you wish to provide payment over the phone.', 'woocommerce-gateway-realex' ), 'error' );
				} else {
					// charge without liability shift allowed
					$order->add_order_note( $order_note );

					$this->threedsecure = $this->get_eci_non_3dsecure( $this->wc_gateway_realex->get_post( 'realex_cardType' ) );
					return $this->wc_gateway_realex->authorize( $realex_client, $order, null, false );
				}
			} else {
				// don't know whether this can happen because the implementation docs don't describe it, so we'll be conservative
				$order_note = sprintf( __( '3DSecure unknown enrollment status: (%d %s).', 'woocommerce-gateway-realex' ), $response->result, $response->enrolled );

				$this->wc_gateway_realex->order_failed( $order, $order_note );

				SV_WC_Helper::wc_add_notice( __( 'The transaction has been declined, please wait and try again or try an alternative payment method.  Your order has been recorded, please contact us if you wish to provide payment over the phone.', 'woocommerce-gateway-realex' ), 'error' );
			}

		} else {
			// 220: Message Timeout: card scheme directory server unavailable, no liability shift, send a normal auth message (ECI: Visa: 7, MC/SWITCH: 0) (no known test case)
			// 521: Card number is not a Switch Card.  3DSecure Transactions are not supported for Solo Cards.  no liability shift, send a normal auth message (ECI: Visa: 7, MC/SWITCH: 0) (no known test case)
			// 520: Invalid response from enrollment server, no liability shift. send a normal auth message (ECI: Visa: 7, MC/SWITCH: 0)
			// 503: No entry for Merchant ID

			if ( $this->wc_gateway_realex->is_debug_mode() ) $this->wc_gateway_realex->response_debug_message( $response, "Response: 3ds-verifyenrolled" );

			// create the appropriate order note
			if ( $response->result == '220' ) $order_note = __( '3DSecure card scheme directory server unavailable, no shift in chargeback liability (220).', 'woocommerce-gateway-realex' );
			elseif ( $response->result == '521' ) $order_note = __( '3DSecure are not supported for Solo Cards, no shift in chargeback liability (521).', 'woocommerce-gateway-realex' );
			elseif ( $response->result == '520' ) $order_note = __( 'Invalid response from 3DSecure Enrollment Server, no shift in chargeback liability (520).', 'woocommerce-gateway-realex' );
			elseif ( $response->result == '503' ) $order_note = sprintf( __( '3DSecure verify enrollment error, ensure RealMPI is enabled for your account/currency: (%s - %s).', 'woocommerce-gateway-realex' ), $response->result, $response->message );
			else $order_note = sprintf( __( 'Realex Verify Enrollment unknown error (Result: %s - "%s").', 'woocommerce-gateway-realex' ), $response->result, $response->message );

			if ( $this->liability_shift() ) {
				// liability shift required, order fail
				$this->wc_gateway_realex->order_failed( $order, $order_note );

				SV_WC_Helper::wc_add_notice( __( 'The transaction has been declined, please wait and try again or try an alternative payment method.  Your order has been recorded, please contact us if you wish to provide payment over the phone.', 'woocommerce-gateway-realex' ), 'error' );
			} else {
				// charge without liability shift allowed
				$order->add_order_note( $order_note );

				$this->threedsecure = $this->get_eci_non_3dsecure( $this->wc_gateway_realex->get_post( 'realex_cardType' ) );
				return $this->wc_gateway_realex->authorize( $realex_client, $order, null, false );
			}
		}
	}


	/**
	 * Displays the payment page if 3D secure is available and enabled
	 *
	 * @since 1.7.1
	 * @param int $order_id identifies the order
	 */
	public function payment_page( $order_id ) {

		if ( ! $this->is_3dsecure_available() ) {
			return;
		}

		$order = wc_get_order( $order_id );
		?>

		<form name="checkout" method="post" class="pay-page-checkout">

			<div id="payment">
				<ul class="payment_methods methods">
					<li class="payment_method_realex">
						<div class="payment_box payment_method_realex">
							<?php $this->wc_gateway_realex->payment_fields(); ?>
						</div>
					</li>
				</ul>

				<div class="form-row">
						<input type="submit" name="woocommerce_checkout_place_order" class="button alt" id="place_order" value="<?php _e( 'Pay for order', 'woocommerce-gateway-realex' ); ?>" />
						<input type="hidden" name="woocommerce_pay_page" value="1" />
						<input type="hidden" name="order_id"             value="<?php echo $order_id; ?>" />
				</div>

			</div>
		</form>

		<?php
	}


	/**
	 * Adds any 3DSecure data onto the auth request object
	 *
	 * @param object $request the request object
	 */
	public function auth_request( $request ) {

		// Handle RealMPI
		if ( $this->threedsecure ) {
			$request->mpi = new stdClass();

			$request->mpi->eci = (string) $this->threedsecure->eci;
			if ( isset( $this->threedsecure->cavv ) ) $request->mpi->cavv = (string) $this->threedsecure->cavv;
			if ( isset( $this->threedsecure->xid ) )  $request->mpi->xid  = (string) $this->threedsecure->xid;
		}

		return $request;
	}


	/**
	 * Updates the order note
	 * TODO: perhaps this could be cleaned up and not passed so much data
	 */
	public function order_note( $order_note, $order, $data, $card_type, $cards ) {

		$vault_card_ref = $this->wc_gateway_realex->get_post( 'realex_card_ref', $data );

		// card type not 3D Secure compatible
		if ( ! in_array( $card_type, array( 'VISA', 'MC', 'SWITCH' ) ) ) {
			$order_note .= __( '  No Liability Shift: Card Type not compatible with 3D Secure.', 'woocommerce-gateway-realex' );
		} elseif ( isset( $cards[ $vault_card_ref ] ) ) {
			// vaulted transaction, which means the customer never had a chance to authenticate
			$order_note .= __( '  No Liability Shift: vaulted payments not compatible with 3D Secure.', 'woocommerce-gateway-realex' );
		}

		if ( $this->threedsecure ) {
			switch( $this->threedsecure->eci ) {
				case 5:
				case 2:
					$order_note .= __( '  Liability Shift: Full 3D Secure.', 'woocommerce-gateway-realex' );
				break;
				case 6:
				case 1:
					$order_note .= __( '  Liability Shift: Merchant 3D Secure.', 'woocommerce-gateway-realex' );
				break;
				case 7:
				case 0:
					$order_note .= __( '  No Liability Shift: Non 3DSecure transaction.', 'woocommerce-gateway-realex' );
				break;
			}
		}

		return $order_note;
	}


	/** Realex Communication Methods **********************************/


	/**
	 * Perform a 3ds-verifyenrolled request
	 *
	 * @param Realex_API $realex_client realex api client
	 * @param WC_Order $order the order
	 *
	 * @return SimpleXMLElement response, or false on error
	 */
	private function threeds_verifyenrolled_request( $realex_client, $order ) {

		$request = new stdClass();

		$request->merchantid = $this->wc_gateway_realex->get_merchant_id();
		$request->account    = $this->wc_gateway_realex->get_account( $this->wc_gateway_realex->get_post( 'realex_cardType' ), $order );  // blank account defaults to 'internet'
		$request->orderid    = $this->wc_gateway_realex->get_order_number( $order, $order->has_status( 'failed' ) );

		$request->amount = $order->payment_total * 100;  // in pennies
		$request->amountCurrency = SV_WC_Order_Compatibility::get_prop( $order, 'currency', 'view' );

		$request->card = new stdClass();
		$request->card->number   = str_replace( array( ' ', '-' ), '', $this->wc_gateway_realex->get_post( 'realex_accountNumber' ) );
		$request->card->expdate  = $this->wc_gateway_realex->get_post( 'realex_expirationMonth' ) . substr( $this->wc_gateway_realex->get_post( 'realex_expirationYear' ), -2 );
		$request->card->type     = $this->wc_gateway_realex->get_post( 'realex_cardType' );
		$request->card->chname   = $order->get_formatted_billing_full_name();  // TODO: hmmm, should I be collecting this from the checkout form?

		// display the request object, if debug mode
		if ( $this->wc_gateway_realex->is_debug_mode() ) {
			$this->wc_gateway_realex->response_debug_message( $request, "Request: 3ds-verifyenrolled", true );
		}

		return $realex_client->threeds_verifyenrolled_request( $request );
	}


	/**
	 * Perform a 3ds-verifysig request
	 *
	 * @param Realex_API $realex_client realex api client
	 * @param WC_Order $order the order
	 * @param array $data the request data
	 *
	 * @return SimpleXMLElement response, or false on error
	 */
	private function threeds_verifysig_request( $realex_client, $order, $data ) {

		$request = new stdClass();

		$request->merchantid = $this->wc_gateway_realex->get_merchant_id();
		$request->account    = $this->wc_gateway_realex->get_account( $data['realex_cardType'], $order );  // blank account defaults to 'internet'
		$request->orderid    = $this->wc_gateway_realex->get_order_number( $order );

		$request->amount = $order->payment_total * 100;  // in pennies
		$request->amountCurrency = SV_WC_Order_Compatibility::get_prop( $order, 'currency', 'view' );

		$request->card = new stdClass();
		$request->card->number   = $data['realex_accountNumber'];
		$request->card->expdate  = $data['realex_expirationMonth'] . substr( $data['realex_expirationYear'], -2 );
		$request->card->type     = $data['realex_cardType'];
		$request->card->chname   = $order->get_formatted_billing_full_name();  // TODO: hmmm, should I be collecting this from the checkout form?

		$request->pares   = $data['pares'];

		// display the request object, if debug mode
		if ( $this->wc_gateway_realex->is_debug_mode() ) {
			$this->wc_gateway_realex->response_debug_message( $request, "Request: 3ds-verifysig" );
		}

		return $realex_client->threeds_verifysig_request( $request );
	}


	/** Getter methods ******************************************************/


	/**
	 * Encrypt the merchant data so it can be posted to the 3DSecure
	 * authorization server
	 *
	 * @param array $data the merchant data
	 * @return string encrypted $data
	 */
	private function encrypt_merchant_data( $data ) {

		$key = $this->get_encryption_key();

		// Blowfish/CBC uses an 8-byte Initialization Vector (IV)
		$iv = substr( md5( mt_rand(), true ), 0, 8 );

		// serialize the data structure into a string
		$data = maybe_serialize( $data );

		// encrypt using Blowfish/CBC
		$enc = mcrypt_encrypt( MCRYPT_BLOWFISH, $key, $data, MCRYPT_MODE_CBC, $iv );

		// include the IV, unencrypted, with the encrypted MD.  This is
		//  acceptable for secure encryption; although it would be ideal not
		//  to include the IV, we have no real convenient place to store it
		//  and the WP Transients are not to be trusted
		$enc = $iv . '.' . $enc;

		// per the RealMPI Remote Developers Guide, compress and base64 encode the encrypted MD
		$enc = base64_encode( gzcompress( $enc ) );

		return $enc;
	}


	/**
	 * Decrypt the merchant data returned from the 3DSecure authorization
	 * server response
	 *
	 * @param string $data encrypted merchant data
	 * @return array of decrypted merchant data
	 */
	private function decrypt_merchant_data( $data ) {

		$key = $this->get_encryption_key();

		// base64 decode and decompress the MD
		$data = gzuncompress( base64_decode( $data ) );

		// pull out the IV and encrypted portion of the MD
		$iv = substr( $data, 0, strpos( $data, '.' ) );
		$enc = substr( $data, strpos( $data, '.' ) + 1 );

		// decrypt (using same IV - a must for the CBC mode)
		$dec = mcrypt_decrypt( MCRYPT_BLOWFISH, $key, $enc, MCRYPT_MODE_CBC, $iv );

		return maybe_unserialize( $dec );
	}


	/**
	 * Returns true if 3DSecure is enabled
	 *
	 * @return boolean true if 3DSecure is enabled
	 */
	public function is_enabled() {
		return $this->threeds == 'yes';
	}


	/**
	 * Returns true if 3DSecure Payer Authenication should be performed prior
	 * to authorization, and the mcrypt module is available for encrypting
	 * the Merchant Data.
	 *
	 * @return boolean true if RealMPI should be used and mcrypt is available
	 */
	public function is_3dsecure_available() {
		return $this->is_enabled() && extension_loaded( 'mcrypt' );
	}


	/**
	 * Returns true if payments are only to be accepted when liability shift
	 * has occurred (requires 3DSecure)
	 *
	 * @return boolean true if payments are only to be accepted when
	 *         liability shift has occurred
	 */
	private function liability_shift() {
		return $this->liability_shift == 'yes';
	}


	/**
	 * Returns the real mpi endpoint url
	 *
	 * @return string endpoint URL
	 */
	private function get_realmpi_endpoint_url() {
		return $this->realmpi_endpoint_url;
	}


	/**
	 * Returns the key used to encrypt the 3DSecure Merchant Data (MD)
	 *
	 * @return string encryption key
	 */
	private function get_encryption_key() {

		// return the encryption key if we've already fetched it, fetch it if
		//  we've already created it, create it if it doesn't exist yet
		if ( ! $this->encryption_key ) {

			if ( ! ( $this->encryption_key = get_option( 'wc_realex_encryption_key' ) ) ) {
				$this->encryption_key = $this->rand_char( 56 );
				add_option( 'wc_realex_encryption_key', $this->encryption_key );
			}
		}

		return $this->encryption_key;
	}


	/**
	 * Returns a random character string of length $length
	 *
	 * @param int $length length of string to return
	 * @return string random character string
	 */
	private function rand_char( $length ) {
		$random = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$random .= chr( mt_rand( 33, 126 ) );
		}
		return $random;
	}


	/**
	 * Get the threeds object containing the "non 3D secure transaction"
	 * ECI code appropriate for the given $card_type
	 *
	 * @param string $card_type one of 'VISA', 'MC' or 'SWITCH'
	 *
	 * @return object containing an eci member
	 */
	private function get_eci_non_3dsecure( $card_type ) {
		$threeds = new stdClass();
		if ( $card_type == 'VISA' ) $threeds->eci = 7;
		elseif ( $card_type == 'MC' || $card_type == 'SWITCH' ) $threeds->eci = 0;

		return $threeds;
	}


	/**
	 * Get the threeds object containing the "Merchant 3D Secure"
	 * ECI code appropriate for the given $card_type
	 *
	 * @param string $card_type one of 'VISA', 'MC' or 'SWITCH'
	 *
	 * @return object containing an eci member
	 */
	private function get_eci_merchant_3dsecure( $card_type ) {
		$threeds = new stdClass();
		if ( $card_type == 'VISA' ) $threeds->eci = 6;
		elseif ( $card_type == 'MC' || $card_type == 'SWITCH' ) $threeds->eci = 1;

		return $threeds;
	}

}
