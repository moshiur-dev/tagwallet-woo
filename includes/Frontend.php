<?php

namespace WPAppsDev\WCTWPG;

/**
 * The frontend class.
 */
class Frontend {
	/**
	 * Initialize the class.
	 */
	public function __construct() {
		add_action( 'woocommerce_thankyou', [ $this, 'display_payment_info_qrcode' ], 20 );
		add_action( 'woocommerce_view_order', [ $this, 'display_payment_info_qrcode' ], 20 );
	}

	public function display_payment_info_qrcode($order_id) {
		$order      = wc_get_order( $order_id );
		$payment_id = $order->get_meta( 'wpadtwpg_payment_id' );

		// instantiate the barcode class
		$barcode = new \Com\Tecnick\Barcode\Barcode();

		if( $payment_id != '' ) {
			// generate a barcode
			$bobj = $barcode->getBarcodeObj(
				'QRCODE,H',
				$payment_id,
				-4,
				-4,
				'black',
				[-2, -2, -2, -2]
			)->setBackgroundColor('white');

			echo '<h2>' . esc_attr__( 'Payment QR Code', 'wpappsdev-tagwallet-gateway' ) . '</h2>';

			// output the barcode as HTML div (see other output formats in the documentation and examples)
			echo $bobj->getHtmlDiv();
		}
	}
}
