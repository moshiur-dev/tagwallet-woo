<?php

use WPAppsDev\WCTWPG\WPAppsDevLogger;

/**
 * The Tag Wallet payment gateway class.
 */
class WC_Gateway_Payinvite extends WC_Payment_Gateway {
	/**
	 * Constructor for the gateway.
	 *
	 * @return void
	 */
	public function __construct() {
		// Init basic settings
		$this->init();

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Init Hooks
		$this->init_hooks();
	}

	/**
	 * Init basic settings.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init() {
		$this->id                 = 'wpadtwpg_payinvite';
		//$this->icon               = WPADTWPG_ASSETS . 'img/razer-logo.png';
		$this->has_fields         = false;
		$this->method_title       = __( 'Payinvite', 'wpappsdev-tagwallet-gateway' );
		$this->method_description = __( 'Invite someone to pay for these.', 'wpappsdev-tagwallet-gateway' );

		// gateways can support subscriptions, refunds, saved payment methods,
		$this->supports = [
			'products',
		];

		// Define user setting variables.
		$title             = $this->get_option( 'title' );
		$this->title       = empty( $title ) ? __( 'Payinvite', 'wpappsdev-tagwallet-gateway' ) : $title;
		$this->testmode    = $this->get_option( 'testmode' );
		$this->description = $this->get_option( 'description' );
		$this->enabled     = $this->get_option( 'enabled' );

		if ( 'yes' == $this->testmode ) {
			$this->host_url    = $this->get_option( 'test_host_url' );
			$this->merchant_id = $this->get_option( 'test_merchant_id' );
			$this->api_key     = $this->get_option( 'test_api_key' );
			$this->secret_key  = $this->get_option( 'test_secret_key' );
		} else {
			$this->host_url    = $this->get_option( 'host_url' );
			$this->merchant_id = $this->get_option( 'merchant_id' );
			$this->api_key     = $this->get_option( 'api_key' );
			$this->secret_key  = $this->get_option( 'secret_key' );
		}

		$this->generate_payment = "{$this->host_url}api/payments/generate";
		$this->search_payment   = "{$this->host_url}api/payments/stream/";
	}

	/**
	 * Gateway settings form fields.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = [
			'enabled' => [
				'title'   => __( 'Enable/Disable', 'wpappsdev-tagwallet-gateway' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Payinvite', 'wpappsdev-tagwallet-gateway' ),
				'default' => 'no',
			],
			'title' => [
				'title'       => __( 'Title', 'wpappsdev-tagwallet-gateway' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'wpappsdev-tagwallet-gateway' ),
				'default'     => __( 'Payinvite', 'wpappsdev-tagwallet-gateway' ),
				'desc_tip'    => true,
			],
			'description' => [
				'title'       => __( 'Description', 'wpappsdev-tagwallet-gateway' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'wpappsdev-tagwallet-gateway' ),
				'default'     => __( 'Pay with Payinvite', 'wpappsdev-tagwallet-gateway' ),
				'desc_tip'    => true,
			],
			'testmode' => [
				'title'       => __( 'Payinvite sandbox', 'wpappsdev-tagwallet-gateway' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable Payinvite sandbox', 'wpappsdev-tagwallet-gateway' ),
				'default'     => 'no',
				'description' => sprintf( __( 'Payinvite sandbox can be used to test payments. Sign up for a developer account <a href="%s">here</a>.', 'wpappsdev-tagwallet-gateway' ), '#' ),
			],
			'host_url' => [
				'title'       => __( 'Host url', 'wpappsdev-tagwallet-gateway' ),
				'type'        => 'text',
				'description' => __( 'Please enter host url.', 'wpappsdev-tagwallet-gateway' ),
				'default'     => '',
			],
			'merchant_id' => [
				'title'       => __( 'Vendor ID', 'wpappsdev-tagwallet-gateway' ),
				'type'        => 'text',
				'description' => __( 'Please enter Vendor ID.', 'wpappsdev-tagwallet-gateway' ),
				'default'     => '',
			],
			'api_key' => [
				'title'       => __( 'API key', 'wpappsdev-tagwallet-gateway' ),
				'type'        => 'text',
				'description' => __( 'Please enter API key.', 'wpappsdev-tagwallet-gateway' ),
				'default'     => '',
			],
			'test_host_url' => [
				'title'       => __( 'Sandbox Host url', 'wpappsdev-tagwallet-gateway' ),
				'type'        => 'text',
				'description' => __( 'Please enter host url.', 'wpappsdev-tagwallet-gateway' ),
				'default'     => '',
			],
			'test_merchant_id' => [
				'title'       => __( 'Sandbox Vendor ID', 'wpappsdev-tagwallet-gateway' ),
				'type'        => 'text',
				'description' => __( 'Please enter sandbox Vendor ID.', 'wpappsdev-tagwallet-gateway' ),
				'default'     => '',
			],
			'test_api_key' => [
				'title'       => __( 'Sandbox API key', 'wpappsdev-tagwallet-gateway' ),
				'type'        => 'text',
				'description' => __( 'Please enter sandbox API key.', 'wpappsdev-tagwallet-gateway' ),
				'default'     => '',
			],
		];
	}

	/**
	 * Init hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init_hooks() {
		// Save gateway settings.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );

		// Process web hook response.
		add_action( 'woocommerce_api_wc_gateway_payinvite', [ $this, 'process_gateway_webhook_response' ] );

		// Handle payment notification response.
		add_action( 'wpadtwpg_gateway_webhook_process', [ $this, 'handle_callback_response' ] );

		// Checking if merchant_id is not empty.
		//add_action( 'admin_notices', array( $this, 'merchant_info_missing_notices' ) );
	}

	/**
	 * Output the admin options table.
	 */
	public function admin_options() {
		echo '<h3>' . __( 'Payinvite', 'wpappsdev-tagwallet-gateway' ) . '</h3>';
		echo '<p>' . __( 'Payinvite works by sending the user to Payinvite to enter their payment information.', 'wpappsdev-tagwallet-gateway' ) . '</p>';
		echo sprintf( __( 'You must add the following webhook endpoint <strong style="background-color:#ddd;">&nbsp;%s&nbsp;</strong> to your <a href="#" target="_blank">Tag Wallet account settings</a> (if there isn\'t one already enabled). This will enable you to receive notifications on the payment statuses.', 'wpappsdev-tagwallet-gateway' ), add_query_arg( 'wc-api', 'WC_Gateway_Payinvite', home_url( '/' ) ) );
		echo '<table class="form-table">' . $this->generate_settings_html( $this->get_form_fields(), false ) . '</table>';
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id
	 *
	 * @return array
	 */
	public function process_payment($order_id) {
		$order        = wc_get_order( $order_id );

		if ( ! is_a( $order, 'WC_Order') ) {
			return;
		}

		try {
			$payment_args = $this->generate_payment_args( $order );

			// Create payment source.
			$response = $this->generate_payment( $payment_args );
			//WPAppsDevLogger::log( 'PaymentSources: ' . print_r( $response, true ) );

			if ( is_wp_error( $response ) ) {
				WPAppsDevLogger::log( 'generate_payment: ' . print_r( $response, true ) );

				return [
					'result'   => 'fail',
					'redirect' => '',
				];
			}

			$payment_id = $response['payment_id'];
			$status     = $response['status'];

			$order->update_meta_data( 'wpadtwpg_payment_id', $payment_id );
			$order->save();
			WC()->cart->empty_cart();

			$this->update_order_status( $order, $status, $payment_id );

			return [
				'result'   => 'success',
				'redirect' => esc_url_raw( $order->get_checkout_order_received_url( true ) ),
			];
		} catch ( \Exception $e ) {
			wc_add_notice( $e->getMessage(), 'error' );
			$order->add_order_note( $e->getMessage() );

			return [
				'result'   => 'fail',
				'redirect' => '',
			];
		}
	}

	public function generate_payment_args($order) {
		$items        = [];
		$total        = $order->get_total();
		$order_number = $order->get_order_number();

		if ( sizeof( $order->get_items() ) > 0 ) {
			foreach ( $order->get_items() as $item_id => $item ) {
				$items[] = [
					'name'     => $item->get_name(),
					'qty'      => $item->get_quantity(),
					'subtotal' => $item->get_subtotal(),
					'tax'      => $item->get_subtotal_tax(),
					'total'    => $item->get_total(),
				];
			}
		}

		$desc = sprintf( __( 'Order #%s - %s', 'wpappsdev-tagwallet-gateway' ), $order_number, esc_attr( get_bloginfo( 'name' ) ) );

		$customer_info = [
			'bill_name'   => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
			'bill_mobile' => $order->get_billing_phone(),
			'bill_email'  => $order->get_billing_email(),
			'country'     => $order->get_billing_country(),
			'currency'    => get_woocommerce_currency(),
		];

		$cart = [
			'order_id'    => $order_number,
			'description' => $desc,
			'store_name'  => esc_attr( get_bloginfo( 'name' ) ),
			'subtotal'    => $order->get_subtotal(),
			'discount'    => $order->get_total_discount(),
			'total'       => $order->get_total(),
			'items'       => $items,
			'customer'    => $customer_info,
		];

		$payinvite_args = [
			'vendor_id' => $this->merchant_id,
			'api_key'   => $this->api_key,
			'order_id'  => $order_number,
			'cart'      => $cart,
		];

		return apply_filters( 'wpadtwpg_tagwallet_payment_args', $payinvite_args );
	}

	/**
	 * Handle Payinvite Responses.
	 *
	 * @return void
	 */
	public function process_gateway_webhook_response() {
		do_action( 'wpadtwpg_gateway_webhook_process' );
	}

	/**
	 * Handle Payment Gateway Responses.
	 *
	 * @return void
	 */
	public function handle_callback_response() {
		$payload = wp_unslash( $_REQUEST );
		WPAppsDevLogger::log( 'TagWallet Event: ' . print_r( $payload, true ) );

		if ( is_array( $payload ) ) {
			$event = $payload;

			if ( isset( $event['status'] ) && isset( $event['order_id'] ) ) {
				$order = wc_get_order( $event['order_id'] );

				if ( is_a( $order, 'WC_Order') ) {
					$this->update_order_status( $order, $event['status'], $event['payment_id'] );
				}
			}
		}
		http_response_code(200);
		exit();
	}

	/**
	 * Generate payment in Tag Wallet.
	 *
	 * @param float  $amount                 Amount.
	 * @param string $invoice_id             Invoice ID.
	 * @param bool   $calculate_final_amount Final amount calculation.
	 *
	 * @return mixed|string
	 */
	public function generate_payment($payment_data) {
		try {
			$headers = [
				'Content-Type' => 'application/json',
			];

			$response = $this->make_request( $this->generate_payment, $payment_data, $headers );

			if ( isset( $response['detail'] ) && count( $response['detail'] ) > 0 ) {
				return new \WP_Error( 'wpadtwpg_create_payment_error', $response );
			}
			WPAppsDevLogger::log( 'make_request: ' . print_r( $response, true ) );

			return $response;
		} catch ( \Exception $e ) {
			return new \WP_Error( 'wpadtwpg_create_payment_error', $e );
		}
	}

	/**
	 * Get payment from Tag Wallet.
	 *
	 * @param string $payment_id
	 *
	 * @return mixed|\WP_Error
	 */
	public function search_payment($payment_id) {
		$headers = [
			//'Content-Type' => 'application/json',
		];

		$url      = esc_url_raw( $this->search_payment . $payment_id );
		$response = wp_remote_get( $url, $headers );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$result = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $result['error_code'] ) && isset( $result['error_message'] ) ) {
			return new \WP_Error( 'wpadtwpg_search_payment_error', $result );
		}

		return $result;
	}

	/**
	 * Sending remote post request.
	 *
	 * @param string $url
	 * @param array  $data
	 * @param array  $headers
	 *
	 * @return mixed|string
	 */
	public function make_request($url, $data, $headers = []) {
		$args = [
			'body'        => wp_json_encode( $data ),
			'timeout'     => '10',
			'redirection' => '10',
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => $headers,
			'cookies'     => [],
		];

		$response = wp_remote_post( esc_url_raw( $url ), $args );

		if ( is_wp_error( $response ) ) {
			return new \WP_Error( 'wpadtwpg_remote_post_error', $response );
		}

		$body = wp_remote_retrieve_body( $response );

		return json_decode( $body, true );
	}

	/**
	 * Update order status based on TagWallet payment status.
	 *
	 * @param int $order_id
	 * @param int $payment_status
	 * @param int $tran_id
	 *
	 * @return void
	 */
	public function update_order_status($order, $payment_status, $payment_id) {
		$order_status = $order->get_status();

		if ( in_array( $order_status, [ 'processing', 'completed' ] ) ) {
			return;
		}

		switch ( $payment_status ) {
			case 'payment.success':
				$order->payment_complete();
				do_action( 'wpadtwpg_payment_completed', $order, $charge );
				break;
			case 'payment.pending':
				$order->update_status( 'on-hold', sprintf( __( 'Watting for payment confirmation.<br>Payment ID: %s', 'wpappsdev-tagwallet-gateway' ), $payment_id ) );
				break;
			case 'payment.failed':
				$order->update_status( 'failed', sprintf( __( 'PayInvite Payment processing failed. Please retry.', 'wpappsdev-tagwallet-gateway' ) ) );
				break;
			default:
				$_status      = 'Invalid Transaction';
				$woo_status   = 'on-hold';
				break;
		}
	}
}
