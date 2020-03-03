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

/**
 * Realex Payment Gateway API Class
 *
 * The Realex Payment Gateway API class manages the communication between the
 * WooCommerce and Realex payment servers
 */
class Realex_API {

	/**
	 * Communication URL
	 */
	var $endpoint_url;
	/**
	 * Realvault Communication URL
	 */
	var $realvault_endpoint_url;
	/**
	 * RealMPI Communication URL
	 */
	var $realmpi_endpoint_url;
	/**
	 * Shared secret
	 */
	var $secret;


	/**
	 * @param string $endpoint_url Realex endpoint url
	 * @param string $realvault_endpoint_url RealVault endpoint url
	 * @param string $secret realex shared secret
	 */
	public function __construct( $endpoint_url, $realvault_endpoint_url, $secret ) {
		$this->endpoint_url = $endpoint_url;
		$this->realvault_endpoint_url = $realvault_endpoint_url;
		$this->secret = $secret;
	}


	/**
	 * Sets the RealMPI (3DSecure) endpoint URL
	 *
	 * @param string $realmpi_endpoint_url RealMPI endpoint url
	 */
	public function set_realmpi_endpoint_url( $realmpi_endpoint_url ) {
		$this->realmpi_endpoint_url = $realmpi_endpoint_url;
	}


	/**
	 * Verify the transaction response was properly signed by Realex with our
	 * shared secret key.
	 *
	 * @param SimpleXMLElement $response response object
	 *
	 * @return boolean true if the response is properly signed
	 */
	public function verify_transaction_signature( $response ) {

		// no transaction signature, so nothing to verify.  Just return true
		if ( ( ! isset( $response->md5hash  ) && ! $response->md5hash ) &&
		     ( ! isset( $response->sha1hash ) && ! $response->sha1hash ) ) return true;

		$attributes = $response->attributes();
		foreach ( $attributes as $key => $value ) {
			if ( $key == 'timestamp' ) {
				$timestamp = $value;
				break;
			}
		}

		// do the hashes match up?
		if ( isset( $response->md5hash ) && $response->md5hash ) {
			return $response->md5hash == $this->get_hash( $timestamp, $response->merchantid, $response->orderid, $response->result, $response->message, $response->pasref, $response->authcode );
		} else {
			return $response->sha1hash == $this->get_sha1_hash( $timestamp, $response->merchantid, $response->orderid, $response->result, $response->message, $response->pasref, $response->authcode );
		}
	}


	/**
	 * Remove an existing vault payer method
	 *
	 * @param object $request request object
	 *
	 * @return SimpleXMLElement response, or false on error
	 */
	public function card_cancel_card_request( $request ) {

		$timestamp = date( 'YmdHis' );

		$request->sha1hash = $this->sign_card_cancel_card_request( $request, $timestamp );

		// build the simplexml object
		$request_xml = simplexml_load_string( "<?xml version='1.0' encoding='utf-8'?><request />" );

		$request_xml->addAttribute( 'type', 'card-cancel-card' );
		$request_xml->addAttribute( 'timestamp', $timestamp );

		$request_xml->addChild( 'merchantid', $request->merchantid );

		$card_node = $request_xml->addChild( 'card' );
		$card_node->addChild( 'ref',      $request->card->ref );
		$card_node->addChild( 'payerref', $request->card->payerref );

		$request_xml->addChild( 'sha1hash', $request->sha1hash );

		return $this->perform_request( $this->realvault_endpoint_url, $request_xml->asXML() );
	}


	/**
	 * Add a new vault payer method
	 *
	 * @param object $request request object
	 *
	 * @return SimpleXMLElement response, or false on error
	 */
	public function card_new_request( $request ) {

		$timestamp = date( 'YmdHis' );

		$request->sha1hash = $this->sign_card_new_request( $request, $timestamp );


		// build the simplexml object
		$request_xml = simplexml_load_string( "<?xml version='1.0' encoding='utf-8'?><request />" );

		$request_xml->addAttribute( 'type', 'card-new' );
		$request_xml->addAttribute( 'timestamp', $timestamp );

		$request_xml->addChild( 'merchantid', $request->merchantid );
		$request_xml->addChild( 'orderid',    $request->orderid );

		$card_node = $request_xml->addChild( 'card' );
		$card_node->addChild( 'ref',      $request->card->ref );
		$card_node->addChild( 'payerref', $request->card->payerref );
		$card_node->addChild( 'number',   $request->card->number );
		$card_node->addChild( 'expdate',  $request->card->expdate );
		$card_node->addChild( 'chname',   $request->card->chname );
		$card_node->addChild( 'type',     $request->card->type );

		if ( isset( $request->card->issueno ) ) $card_node->addChild( 'issueno', $request->card->issueno );

		$request_xml->addChild( 'sha1hash', $request->sha1hash );

		return $this->perform_request( $this->realvault_endpoint_url, $request_xml->asXML() );
	}


	/**
	 * Perform a credit card payment using an existing vault token
	 *
	 * @param object $request request object
	 *
	 * @return SimpleXMLElement response, or false on error
	 */
	public function receipt_in_request( $request ) {

		// defaults and special fields
		if ( ! isset( $request->autosettleFlag ) ) $request->autosettleFlag = 0;

		$timestamp = date( 'YmdHis' );

		$request->sha1hash = $this->sign_receipt_in_request( $request, $timestamp );


		// build the simplexml object
		$request_xml = simplexml_load_string( "<?xml version='1.0' encoding='utf-8'?><request />" );

		$request_xml->addAttribute( 'type', 'receipt-in' );
		$request_xml->addAttribute( 'timestamp', $timestamp );

		$request_xml->addChild( 'merchantid', $request->merchantid );
		$request_xml->addChild( 'account',    $request->account );
		$request_xml->addChild( 'orderid',    $request->orderid );

		$autosettle_node = $request_xml->addChild( 'autosettle' );
		$autosettle_node->addAttribute( 'flag', $request->autosettleFlag );

		$amount_node = $request_xml->addChild( 'amount', $request->amount );
		$amount_node->addAttribute( 'currency', $request->amountCurrency );

		$request_xml->addChild( 'payerref', $request->payerref );            // user token
		$request_xml->addChild( 'paymentmethod', $request->paymentmethod );  // credit card token

		$request_xml->addChild( 'sha1hash', $request->sha1hash );

		return $this->perform_request( $this->realvault_endpoint_url, $request_xml->asXML() );
	}


	/**
	 * Edit an existing secure vault payer
	 *
	 * @param object $request request object
	 *
	 * @return SimpleXMLElement response, or false on error
	 */
	public function payer_edit_request( $request ) {
		return $this->payer_request( 'payer-edit', $request );
	}


	/**
	 * Set up a new secure vault payer
	 *
	 * @param object $request request object
	 *
	 * @return SimpleXMLElement response, or false on error
	 */
	public function payer_new_request( $request ) {
		return $this->payer_request( 'payer-new', $request );
	}


	/**
	 * Perform the 3ds-verifyenrolled request
	 *
	 * @param object $request request object
	 *
	 * @return SimpleXMLElement response, or false on error
	 */
	public function threeds_verifyenrolled_request( $request ) {

		$timestamp = date( 'YmdHis' );

		// same signature as the auth request
		$request->sha1hash = $this->sign_auth_request( $request, $timestamp );


		// build the simplexml object
		$request_xml = simplexml_load_string( "<?xml version='1.0' encoding='utf-8'?><request />" );

		$request_xml->addAttribute( 'type', '3ds-verifyenrolled' );
		$request_xml->addAttribute( 'timestamp', $timestamp );

		$request_xml->addChild( 'merchantid', $request->merchantid );
		$request_xml->addChild( 'account',    $request->account );
		$request_xml->addChild( 'orderid',    $request->orderid );

		$amount_node = $request_xml->addChild( 'amount', $request->amount );
		$amount_node->addAttribute( 'currency', $request->amountCurrency );

		$card_node = $request_xml->addChild( 'card' );
		$card_node->addChild( 'number',  $request->card->number );
		$card_node->addChild( 'expdate', $request->card->expdate );
		$card_node->addChild( 'type',    $request->card->type );
		$card_node->addChild( 'chname',  $request->card->chname );

		$request_xml->addChild( 'sha1hash', $request->sha1hash );

		return $this->perform_request( $this->realmpi_endpoint_url, $request_xml->asXML() );
	}


	/**
	 * Perform the 3ds-verifysig request
	 *
	 * @param object $request request object
	 *
	 * @return SimpleXMLElement response, or false on error
	 */
	public function threeds_verifysig_request( $request ) {

		$timestamp = date( 'YmdHis' );

		// same signature as the auth request
		$request->sha1hash = $this->sign_auth_request( $request, $timestamp );


		// build the simplexml object
		$request_xml = simplexml_load_string( "<?xml version='1.0' encoding='utf-8'?><request />" );

		$request_xml->addAttribute( 'type', '3ds-verifysig' );
		$request_xml->addAttribute( 'timestamp', $timestamp );

		$request_xml->addChild( 'merchantid', $request->merchantid );
		$request_xml->addChild( 'account',    $request->account );
		$request_xml->addChild( 'orderid',    $request->orderid );

		$amount_node = $request_xml->addChild( 'amount', $request->amount );
		$amount_node->addAttribute( 'currency', $request->amountCurrency );

		$card_node = $request_xml->addChild( 'card' );
		$card_node->addChild( 'number',  $request->card->number );
		$card_node->addChild( 'expdate', $request->card->expdate );
		$card_node->addChild( 'type',    $request->card->type );
		$card_node->addChild( 'chname',  $request->card->chname );

		$request_xml->addChild( 'pares', $request->pares );

		$request_xml->addChild( 'sha1hash', $request->sha1hash );

		return $this->perform_request( $this->realmpi_endpoint_url, $request_xml->asXML() );
	}


	/**
	 * Perform the authorization request
	 *
	 * @param object $request request object
	 *
	 * @return SimpleXMLElement response, or false on error
	 */
	public function auth_request( $request ) {

		// defaults and special fields
		if ( ! isset( $request->autosettleFlag ) ) $request->autosettleFlag = 0;

		$timestamp = date( 'YmdHis' );

		$request->sha1hash = $this->sign_auth_request( $request, $timestamp );


		// build the simplexml object
		$request_xml = simplexml_load_string( "<?xml version='1.0' encoding='utf-8'?><request />" );

		$request_xml->addAttribute( 'type', 'auth' );
		$request_xml->addAttribute( 'timestamp', $timestamp );

		$request_xml->addChild( 'merchantid', $request->merchantid );
		$request_xml->addChild( 'account',    $request->account );
		$request_xml->addChild( 'orderid',    $request->orderid );

		$amount_node = $request_xml->addChild( 'amount', $request->amount );
		$amount_node->addAttribute( 'currency', $request->amountCurrency );

		$card_node = $request_xml->addChild( 'card' );
		$card_node->addChild( 'number',  $request->card->number );
		$card_node->addChild( 'expdate', $request->card->expdate );
		$card_node->addChild( 'type',    $request->card->type );
		$card_node->addChild( 'chname',  $request->card->chname );

		// card security code is optional
		if ( isset( $request->card->cvn ) ) {
			$cvn_node = $card_node->addChild( 'cvn' );
			$cvn_node->addChild( 'number',  $request->card->cvn->number );
			$cvn_node->addChild( 'presind', $request->card->cvn->presind );
		}

		// issueno is used only for switch cards
		if ( isset( $request->card->issueno ) ) $card_node->addChild( 'issueno', $request->card->issueno );

		$autosettle_node = $request_xml->addChild( 'autosettle' );
		$autosettle_node->addAttribute( 'flag', $request->autosettleFlag );

		$tssinfo_node = $request_xml->addChild( 'tssinfo' );
		if ( isset( $request->tssinfo->custnum ) ) $tssinfo_node->addChild( 'custnum', $request->tssinfo->custnum );

		$address_node = $tssinfo_node->addChild( 'address' );
		$address_node->addAttribute( 'type', 'billing' );
		$address_node->addChild( 'code', $request->tssinfo->addressBilling->code );
		$address_node->addChild( 'country', $request->tssinfo->addressBilling->country );

		if ( isset( $request->tssinfo->addressShipping ) ) {
			$shipping_address_node = $tssinfo_node->addChild( 'address' );
			$shipping_address_node->addAttribute( 'type', 'shipping' );
			$shipping_address_node->addChild( 'code',    $request->tssinfo->addressShipping->code );
			$shipping_address_node->addChild( 'country', $request->tssinfo->addressShipping->country );
		}

		// optional RealMPI request
		if ( isset( $request->mpi ) ) {
			$mpi_node = $request_xml->addChild( 'mpi' );
			$mpi_node->addChild( 'eci', $request->mpi->eci );
			if ( isset( $request->mpi->cavv ) ) $mpi_node->addChild( 'cavv', $request->mpi->cavv );
			if ( isset( $request->mpi->xid  ) ) $mpi_node->addChild( 'xid',  $request->mpi->xid );
		}

		$request_xml->addChild( 'sha1hash', $request->sha1hash );

		return $this->perform_request( $this->endpoint_url, $request_xml->asXML() );
	}


	/** Helper methods ******************************************************/


	/**
	 * Helper method to perform a payer request of type $type
	 *
	 * @param string $request the type of request to perform; one of 'payer-edit'
	 *        or 'payer-new'
	 * @param object $request request object
	 *
	 * @return SimpleXMLElement response, or false on error
	 */
	private function payer_request( $type, $request ) {

		$timestamp = date( 'YmdHis' );

		$request->sha1hash = $this->sign_payer_new_request( $request, $timestamp );


		// build the simplexml object
		$request_xml = simplexml_load_string( "<?xml version='1.0' encoding='utf-8'?><request />" );

		$request_xml->addAttribute( 'type', $type );
		$request_xml->addAttribute( 'timestamp', $timestamp );

		$request_xml->addChild( 'merchantid', $request->merchantid );
		$request_xml->addChild( 'orderid',    $request->orderid );

		$payer_node = $request_xml->addChild( 'payer' );
		$payer_node->addAttribute( 'type', 'Business' );
		$payer_node->addAttribute( 'ref', $request->payerRef );
		$payer_node->addChild( 'firstname', $request->payer->firstname );
		$payer_node->addChild( 'surname',   $request->payer->surname );
		$payer_node->addChild( 'company',   $request->payer->company );

		$address_node = $payer_node->addChild( 'address' );
		$address_node->addChild( 'line1',    $request->payer->address->line1 );
		$address_node->addChild( 'line2',    $request->payer->address->line2 );
		$address_node->addChild( 'city',     $request->payer->address->city );
		$address_node->addChild( 'county',   $request->payer->address->county );
		$address_node->addChild( 'postcode', $request->payer->address->postcode );

		$country_node = $address_node->addChild( 'country', $request->payer->address->country );  // country name
		$country_node->addAttribute( 'code', $request->payer->address->countryCode );

		$phone_numbers_node = $payer_node->addChild( 'phonenumbers' );
		$phone_numbers_node->addChild( 'home', $request->payer->phonenumbers->home );  // 0-9 + - ""

		$payer_node->addChild( 'email', $request->payer->email );

		$request_xml->addChild( 'sha1hash', $request->sha1hash );

		return $this->perform_request( $this->realvault_endpoint_url, $request_xml->asXML() );
	}


	/**
	 * Get the digitial signature of the passed card cancel card request
	 *
	 * @param object $request request object
	 * @param int $timestamp the timestamp
	 *
	 * @return string digital signature of the request
	 */
	private function sign_card_cancel_card_request( $request, $timestamp ) {
		return $this->get_sha1_hash( $timestamp, $request->merchantid, $request->card->payerref, $request->card->ref );
	}

	/**
	 * Get the digitial signature of the passed new card request
	 *
	 * @param object $request request object
	 * @param int $timestamp the timestamp
	 *
	 * @return string digital signature of the request
	 */
	private function sign_card_new_request( $request, $timestamp ) {
		return $this->get_sha1_hash( $timestamp, $request->merchantid, $request->orderid, null, null, $request->card->payerref, $request->card->chname, $request->card->number );
	}

	/**
	 * Get the digitial signature of the passed new payer request
	 *
	 * @param object $request request object
	 * @param int $timestamp the timestamp
	 *
	 * @return string digital signature of the request
	 */
	private function sign_payer_new_request( $request, $timestamp ) {
		return $this->get_sha1_hash( $timestamp, $request->merchantid, $request->orderid, null, null, $request->payerRef );
	}


	/**
	 * Get the digitial signature of the passed "receipt in" request
	 *
	 * @param object $request request object
	 * @param int $timestamp the timestamp
	 *
	 * @return string digital signature of the request
	 */
	private function sign_receipt_in_request( $request, $timestamp ) {
		return $this->get_sha1_hash( $timestamp, $request->merchantid, $request->orderid, $request->amount, $request->amountCurrency, $request->payerref );
	}


	/**
	 * Get the digitial signature of the passed auth request
	 *
	 * @param object $request request object
	 * @param int $timestamp the timestamp
	 *
	 * @return string digital signature of the request
	 */
	private function sign_auth_request( $request, $timestamp ) {
		return $this->get_sha1_hash( $timestamp, $request->merchantid, $request->orderid, $request->amount, $request->amountCurrency, $request->card->number );
	}


	/**
	 * Returns the Realex md5 hash for the provided arguments.  This function takes
	 * a variable list of arguments and returns the hash of them
	 *
	 * @param mixed ...
	 *
	 * @return string realex md5 hash
	 */
	private function get_hash() {
		$args = func_get_args();  // assign func_get_args() to avoid a Fatal error in some instances
		return md5( md5( implode( '.', $args ) ) . '.' . $this->secret );
	}


	/**
	 * Returns the Realex sha2 hash for the provided arguments.  This function takes
	 * a variable list of arguments and returns the hash of them
	 *
	 * @param mixed ...
	 *
	 * @return string realex sha1 hash
	 */
	private function get_sha1_hash() {
		$args = func_get_args();  // assign func_get_args() to avoid a Fatal error in some instances
		return sha1( sha1( implode( '.', $args ) ) . '.' . $this->secret );
	}


	/**
	 * Perform the request
	 *
	 * @param string $url endpoint URL
	 * @param string $request XML request data
	 *
	 * @return string XML response
	 */
	private function perform_request( $url, $request ) {

		$response = wp_safe_remote_post( $url, array(
			'method'      => 'POST',
			'redirection' => 0,
			'body'        => $request,
			'timeout'     => 60,
			'sslverify'   => true,
			'user-agent'  => "PHP " . PHP_VERSION
		) );

		$body = wp_remote_retrieve_body( $response );

		return simplexml_load_string( $body );
	}
}
