<?php

/** Integration with WooCommerce Bookings */

class WC_GFPA_Integrations_Bookings {
	private static WC_GFPA_Integrations_Bookings $instance;

	public static function register() {
		if ( empty( self::$instance ) ) {
			self::$instance = new WC_GFPA_Integrations_Bookings();
		}
	}

	public function __construct() {
		add_filter( 'booking_form_params', array( $this, 'filter_booking_form_params' ) );

		add_filter(
			'woocommerce_bookings_calculated_booking_cost_success_output',
			array(
				$this,
				'filter_output_cost',
			),
			9,
			3
		);
	}

	/**
	 * Filter the booking form params to add the pao_active flag.
	 *
	 * @param array $params The booking form params.
	 *
	 * @return array The filtered booking form params.
	 * @since 1.11.4
	 */
	public function filter_booking_form_params( array $params ): array {
		$params['pao_active'] = true;
		$params['pao_pre_30'] = false;
		return $params;
	}

	/**
	 * Filter the cost display of bookings after booking selection.
	 * This only filters on success.
	 *
	 * @since 1.11.4
	 */
	public function filter_output_cost( $output, $display_price, $product ) {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- $posted requires sanitization.
		parse_str( wp_unslash( $_POST['form'] ), $posted );

		if ( ! is_a( $product, 'WC_Product' ) ) {
			$product = wc_get_product( $product );
		}

		$the_product_id = $product->get_id();
		if ( $product->is_type( 'variation' ) ) {
			$the_product_id = $product->get_parent_id();
		}

		if ( ! wc_gfpa()->has_gravity_form( $the_product_id ) ) {
			return $output;
		}

		$booking_data = wc_bookings_get_posted_data( $posted, $product );
		$cost         = WC_Bookings_Cost_Calculation::calculate_booking_cost( $booking_data, $product );

		wp_send_json(
			array(
				'result'    => 'SUCCESS',
				'html'      => $output,
				'raw_price' => (float) wc_get_price_to_display( $product, array( 'price' => $cost ) ),
			)
		);
	}
}
