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
 * WooCommerce Realex Gateway Subscriptions class
 *
 * Extends the base Realex gateway to include support for WC Subscriptions
 *
 * @since 1.4
 */
class WC_Gateway_Realex_Subscriptions extends WC_Gateway_Realex {

	/**
	 * Load parent gateway and subscription-specific hooks
	 *
	 * @since  1.4
	 * @return WC_Gateway_Realex_Subscriptions
	 */
	public function __construct() {

		// load parent gateway
		parent::__construct();

		// subscriptions require the vault
		if ( ! $this->vault_available() ) return;

		// add subscription support
		$this->supports = array_merge( $this->supports,
			array(
				'subscriptions',
				'subscription_suspension',
				'subscription_cancellation',
				'subscription_reactivation',
				'subscription_amount_changes',
				'subscription_date_changes',
				// 2.0.x
				'multiple_subscriptions',
			)
		);

		if ( SV_WC_Plugin_Compatibility::is_wc_subscriptions_version_gte_2_0() ) {

			// 2.0.x

			// process renewal payments
			add_action( 'woocommerce_scheduled_subscription_payment_' . $this->id, array( $this, 'process_renewal_payment' ), 10, 2 );

			// don't copy over order-specific meta to the new WC_Subscription object during upgrade to 2.0.x
			add_filter( 'wcs_upgrade_subscription_meta_to_copy', array( $this, 'do_not_copy_order_meta_during_subscriptions_upgrade' ) );

		} else {

			// 1.5.x

			// process scheduled subscription payments
			add_action( 'scheduled_subscription_payment_' . $this->id, array( $this, 'process_renewal_payment' ), 10, 3 );

			// prevent unnecessary order meta from polluting parent renewal orders
			add_filter( 'woocommerce_subscriptions_renewal_order_meta_query', array( $this, 'remove_renewal_order_meta' ), 10, 4 );
		}
	}


	/**
	 * Process an initial subscription payment if the order contains a
	 * subscription, otherwise use the parent::process_payment() method
	 *
	 * @since  1.4
	 * @param int $order_id the order identifier
	 * @return array
	 */
	public function process_payment( $order_id ) {

		require_once( 'class-wc-realex-api.php' );

		// processing subscription (which means we are ineligible for 3DSecure for now)
		if ( SV_WC_Plugin_Compatibility::is_wc_subscriptions_version_gte_2_0() ? wcs_order_contains_subscription( $order_id ) : WC_Subscriptions_Order::order_contains_subscription( $order_id ) ) {

			$order = wc_get_order( $order_id );

			// redirect to payment page for payment if 3D secure is enabled
			if ( $this->get_threedsecure()->is_3dsecure_available() && ! SV_WC_Helper::get_post( 'woocommerce_pay_page' ) ) {

				// empty cart before redirecting from Checkout page
				WC()->cart->empty_cart();

				// redirect to payment page to continue payment
				return array(
					'result'   => 'success',
					'redirect' => $order->get_checkout_payment_url( true ),
				);
			}

			$order->payment_total = SV_WC_Helper::number_format( ( SV_WC_Plugin_Compatibility::is_wc_subscriptions_version_gte_2_0() ? $order->get_total() : WC_Subscriptions_Order::get_total_initial_payment( $order ) ) );

			// create the realex api client
			$realex_client = new Realex_API( $this->get_endpoint_url(), $this->get_realvault_endpoint_url(), $this->get_shared_secret() );

			// create the customer/cc tokens, and authorize the initial payment amount, if any
			$result = $this->authorize( $realex_client, $order );

			// subscription with initial payment, everything is now taken care of
			if ( is_array( $result ) ) {

				// for Subscriptions 2.0.x, save payment token to subscription object
				if ( SV_WC_Plugin_Compatibility::is_wc_subscriptions_version_gte_2_0() ) {

					// a single order can contain multiple subscriptions
					foreach ( wcs_get_subscriptions_for_order( SV_WC_Order_Compatibility::get_prop( $order, 'id' ) ) as $subscription ) {

						// payment token
						update_post_meta( SV_WC_Order_Compatibility::get_prop( $subscription, 'id' ), '_realex_cardref', get_post_meta( SV_WC_Order_Compatibility::get_prop( $order, 'id' ), '_realex_cardref', true ) );
					}
				}

				return $result;
			}

			// otherwise there was no initial payment, so we mark the order as complete, etc
			if ( $order->payment_total == 0 ) {

				// mark order as having received payment
				$order->payment_complete();

				WC()->cart->empty_cart();

				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order ),
				);
			}

		} else {

			// processing regular product
			return parent::process_payment( $order_id );

		}
	}


	/**
	 * Process subscription renewal
	 *
	 * @since  1.4
	 * @param float $amount_to_charge subscription amount to charge, could include
	 *              multiple renewals if they've previously failed and the admin
	 *              has enabled it
	 * @param WC_Order $order original order containing the subscription
	 * @param int $product_id the ID of the subscription product
	 */
	public function process_renewal_payment( $amount_to_charge, $order, $product_id = null ) {

		require_once( 'class-wc-realex-api.php' );

		$realex_subscription_count = SV_WC_Order_Compatibility::get_meta( $order, '_realex_subscription_count' );

		if ( ! is_numeric( $realex_subscription_count ) || ! $realex_subscription_count ) {
			$realex_subscription_count = 0;
		}

		// increment the subscription count so we don't get order number clashes
		$realex_subscription_count++;

		update_post_meta( SV_WC_Order_Compatibility::get_prop( $order, 'id' ), '_realex_subscription_count', $realex_subscription_count );

		// set custom class member used by the realex gateway
		$order->payment_total = SV_WC_Helper::number_format( $amount_to_charge );

		// zero-dollar subscription renewal.  weird, but apparently it happens -- only applicable to Subs 1.5.x
		if ( ! SV_WC_Plugin_Compatibility::is_wc_subscriptions_version_gte_2_0() ) {
			if ( 0 == $order->payment_total ) {

				// add order note
				$order->add_order_note( sprintf( __( '%s0 Subscription Renewal Approved', 'woocommerce-gateway-realex' ), get_woocommerce_currency_symbol() ) );

				// update subscription
				WC_Subscriptions_Manager::process_subscription_payments_on_order( $order, $product_id );

				return;
			}
		}

		// This order is missing a tokenized card, lets see whether there's one available for the customer
		if ( ! get_post_meta( SV_WC_Order_Compatibility::get_prop( $order, 'id' ), '_realex_cardref', true ) ) {
			$credit_cards = get_user_meta( $order->get_user_id(), 'woocommerce_realex_cc', true );
			if ( is_array( $credit_cards ) ) {
				$card_ref = (object) current( $credit_cards );
				$card_ref = $card_ref->ref;
				update_post_meta( SV_WC_Order_Compatibility::get_prop( $order, 'id' ), '_realex_cardref', $card_ref );
				if ( SV_WC_Plugin_Compatibility::is_wc_subscriptions_version_gte_2_0() ) {

					foreach ( wcs_get_subscriptions_for_renewal_order( $order ) as $subscription ) {
						update_post_meta( SV_WC_Order_Compatibility::get_prop( $subscription, 'id' ), '_realex_cardref', $card_ref );
					}
				}
			}
		}

		// create the realex api client
		$realex_client = new Realex_API( $this->get_endpoint_url(), $this->get_realvault_endpoint_url(), $this->get_shared_secret() );

		// create the customer/cc tokens, and authorize the initial payment amount, if any
		$response = $this->authorize( $realex_client, $order );

		if ( $response && '00' == $response->result ) {

			// add order note
			$order->add_order_note( sprintf( __( 'Credit Card Subscription Renewal Payment Approved (Payment Reference: %s) ', 'woocommerce-gateway-realex' ), $response->pasref ) );

			// update subscription
			if ( SV_WC_Plugin_Compatibility::is_wc_subscriptions_version_gte_2_0() ) {

				$order->payment_complete( (string) $response->pasref );

			} else {
				WC_Subscriptions_Manager::process_subscription_payments_on_order( $order, $product_id );
			}

		} else {

			// generate the result message
			$message = __( 'Credit Card Subscription Renewal Payment Failed', 'woocommerce-gateway-realex' );

			/* translators: Placeholders: %1$s - result, %2$s - result message */
			if ( $response ) $message .= sprintf( __( ' (Result: %1$s - "%2$s").', 'woocommerce-gateway-realex' ), $response->result, $response->message );

			$order->add_order_note( $message );

			// update subscription
			if ( ! SV_WC_Plugin_Compatibility::is_wc_subscriptions_version_gte_2_0() ) {
				WC_Subscriptions_Manager::process_subscription_payment_failure_on_order( $order, $product_id );
			}
		}
	}


	/**
	 * Don't copy over profile/payment meta when creating a parent renewal order
	 *
	 * @access public
	 * @param array $order_meta_query MySQL query for pulling the metadata
	 * @param int $original_order_id Post ID of the order being used to purchased the subscription being renewed
	 * @param int $renewal_order_id Post ID of the order created for renewing the subscription
	 * @param string $new_order_role The role the renewal order is taking, one of 'parent' or 'child'
	 * @return string
	 */
	public function remove_renewal_order_meta( $order_meta_query, $original_order_id, $renewal_order_id, $new_order_role ) {

		if ( 'parent' == $new_order_role )
			$order_meta_query .= " AND `meta_key` NOT IN ( '_realex_payment_reference', '_realex_cardref' )";

		return $order_meta_query;
	}


	/**
	 * Don't copy order-specific meta to the new WC_Subscription object during
	 * upgrade to 2.0.x. This only allows the `_wc_realex_cardref`
	 * meta to be copied.
	 *
	 * @since 1.7.2
	 * @param array $order_meta order meta to copy
	 * @return array
	 */
	public function do_not_copy_order_meta_during_subscriptions_upgrade( $order_meta ) {

		foreach ( array( '_realex_subscription_count', '_realex_payment_reference', '_realex_retry_count' ) as $meta_key ) {

			if ( isset( $order_meta[ $meta_key ] ) ) {
				unset( $order_meta[ $meta_key ] );
			}
		}

		return $order_meta;
	}
}
