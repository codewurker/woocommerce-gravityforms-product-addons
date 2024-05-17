<?php

class WC_GFPA_Merge_Tags {

	private static WC_GFPA_Merge_Tags $_instance;

	public static function register(): WC_GFPA_Merge_Tags {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new WC_GFPA_Merge_Tags();
		}

		return self::$_instance;
	}

	public static function instance(): WC_GFPA_Merge_Tags {
		return self::register();
	}

	public function __construct() {
		// Merge tags are registered in the /assets/js/gravityforms-product-addons-admin.js file.

		// Process the merge tags.
		add_filter( 'gform_replace_merge_tags', array( $this, 'replace_woocommerce_merge_tags' ), 10, 7 );
	}

	public function get_merge_tags() {

		$notification_general_fields = array(
			'fromName',
			'to',
			'subject',
			'message',
			'_gform_setting_message',
		);

		$notification_email_fields = array(
			'from',
			'replyTo',
			'bcc',
		);

		$notification_fields = array_merge( $notification_general_fields, $notification_email_fields );


		$groups = array(
			array(
				'key'   => 'wcgfpa_wc_product',
				'label' => __( 'WooCommerce Product', 'wc_gf_addons' ),
				'desc'  => __( 'These merge tags are available when the form is associated with a WooCommerce product.  They provide information about the WooCommerce product that is associated with the entry.', 'wc_gf_addons' ),
				'tags'  => array(
					array(
						'tag'        => '{wc_product:name}',
						'label'      => __( 'Name', 'wc_gf_addons' ),
						'desc'       => __( 'The name of the product.', 'wc_gf_addons' ),
						'allowed_on' => array_merge( $notification_general_fields, array( 'field_default_value_*' )),
					),
					array(
						'tag'        => '{wc_product:sku}',
						'label'      => __( 'SKU', 'wc_gf_addons' ),
						'desc'       => __( 'The SKU of the product.', 'wc_gf_addons' ),
						'allowed_on' => array_merge( $notification_general_fields, array( 'field_default_value_*' )),
					),
					array(
						'tag'        => '{wc_product:price}',
						'label'      => __( 'Price', 'wc_gf_addons' ),
						'desc'       => __( 'The price of the product.', 'wc_gf_addons' ),
						'allowed_on' => array_merge( $notification_general_fields, array( 'field_default_value_*' )),
					),
					array(
						'tag'        => '{wc_product:regular_price}',
						'label'      => __( 'Regular Price', 'wc_gf_addons' ),
						'desc'       => __( 'The regular price of the product.', 'wc_gf_addons' ),
						'allowed_on' => array_merge( $notification_general_fields, array( 'field_default_value_*' )),
					),
					array(
						'tag'        => '{wc_product:sale_price}',
						'label'      => __( 'Sale Price', 'wc_gf_addons' ),
						'desc'       => __( 'The sale price of the product.', 'wc_gf_addons' ),
						'allowed_on' => array_merge( $notification_general_fields, array( 'field_default_value_*' )),
					),
					array(
						'tag'        => '{wc_product:stock_quantity}',
						'label'      => __( 'Stock Quantity', 'wc_gf_addons' ),
						'desc'       => __( 'The stock quantity of the product.', 'wc_gf_addons' ),
						'allowed_on' => array_merge( $notification_general_fields, array( 'field_default_value_*' )),
					),
					array(
						'tag'        => '{wc_product:stock_status}',
						'label'      => __( 'Stock Status', 'wc_gf_addons' ),
						'desc'       => __( 'The stock status of the product.', 'wc_gf_addons' ),
						'allowed_on' => array_merge( $notification_general_fields, array( 'field_default_value_*' )),
					),
					array(
						'tag'        => '{wc_product:product_url}',
						'label'      => __( 'Product URL', 'wc_gf_addons' ),
						'desc'       => __( 'The URL to view the product on the website.', 'wc_gf_addons' ),
						'allowed_on' => array_merge( $notification_general_fields, array( 'field_default_value_*' )),
					),
				),
			),
			array(
				'key'   => 'wcgfpa_wc_order',
				'label' => __( 'WooCommerce Order', 'wc_gf_addons' ),
				'desc'  => __( 'These merge tags are available when the form is associated with a WooCommerce product.  They provide information about the WooCommerce order that is associated with the entry.', 'wc_gf_addons' ),
				'tags'  =>
					array(
						array(
							'tag'        => '{wc_order:billing_address}',
							'label'      => __( 'Billing Address - Formatted', 'wc_gf_addons' ),
							'desc'       => __( 'The billing address of the order. If the order has a user, the shipping address will be used. Otherwise, the billing address will be used.', 'wc_gf_addons' ),
							'allowed_on' => $notification_general_fields,
						),
						array(
							'tag'        => '{wc_order:billing_email}',
							'label'      => __( 'Billing Email', 'wc_gf_addons' ),
							'desc'       => __( 'The billing email of the order.', 'wc_gf_addons' ),
							'allowed_on' => $notification_fields,
						),
						array(
							'tag'        => '{wc_order:customer_id}',
							'label'      => __( 'Customer ID', 'wc_gf_addons' ),
							'desc'       => __( 'The ID of the customer who placed the order. If the order has a user, the user ID will be used. Otherwise, the billing email will be used.', 'wc_gf_addons' ),
							'allowed_on' => $notification_general_fields,
						),
						array(
							'tag'        => '{wc_order:customer_email}',
							'label'      => __( 'Customer Email', 'wc_gf_addons' ),
							'desc'       => __( 'The email address of the customer who placed the order. If the order has a user, the user email will be used. Otherwise, the billing email will be used.', 'wc_gf_addons' ),
							'allowed_on' => $notification_fields,
						),
						array(
							'tag'        => '{wc_order:customer_display_name}',
							'label'      => __( 'Customer Display Name', 'wc_gf_addons' ),
							'desc'       => __( 'The display name of the customer who placed the order. If the order has a user, the user display name will be used. Otherwise, the billing email will be used.', 'wc_gf_addons' ),
							'allowed_on' => $notification_general_fields,
						),
						array(
							'tag'        => '{wc_order:order_url}',
							'label'      => __( 'Order URL', 'wc_gf_addons' ),
							'desc'       => __( 'The URL to view the order on the website.', 'wc_gf_addons' ),
							'allowed_on' => $notification_general_fields,
						),
						array(
							'tag'        => '{wc_order:order_id}',
							'label'      => __( 'Order ID', 'wc_gf_addons' ),
							'desc'       => __( 'The ID of the order.', 'wc_gf_addons' ),
							'allowed_on' => $notification_general_fields,
						),
						array(
							'tag'        => '{wc_order:order_number}',
							'label'      => __( 'Order Number', 'wc_gf_addons' ),
							'desc'       => __( 'The order number of the order.', 'wc_gf_addons' ),
							'allowed_on' => $notification_general_fields,
						),
						array(
							'tag'        => '{wc_order:shipping_address}',
							'label'      => __( 'Shipping Address - Formatted', 'wc_gf_addons' ),
							'desc'       => __( 'The shipping address of the order. If the order has a user, the shipping address will be used. Otherwise, the billing address will be used.', 'wc_gf_addons' ),
							'allowed_on' => $notification_general_fields,
						),
					),
			),
		);

		return apply_filters('wc_gf_addons_merge_tags', $groups);
	}

	public function replace_woocommerce_merge_tags( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {
		preg_match_all( '/{(\w+):(\w+)}/', $text, $matches, PREG_SET_ORDER );
		if ( ! empty( $matches ) ) {
			foreach ( $matches as $match ) {
				$full_tag = $match[0];
				$prefix   = $match[1];
				$tag      = $match[2];
				$result   = false;
				switch ( $prefix ) {
					case 'wc_order':
						$result = $this->process_merge_tag_wc_order( $tag, $entry, $form, $url_encode, $esc_html, $nl2br, $format );
						break;
					case 'wc_product':
						$result = $this->process_merge_tag_wc_product( $tag, $entry, $form, $url_encode, $esc_html, $nl2br, $format );
						break;
					default: // Do nothing.
						break;
				}

				if ( $result !== false ) {
					$text = str_replace( $full_tag, $result, $text );
				}
			}
		}
		return $text;
	}

	private function process_merge_tag_wc_order( $tag, $entry, $form, $url_encode, $esc_html, $nl2br, $format ) {

		$order_id  = gform_get_meta( $entry['id'], 'woocommerce_order_number' );
		$the_order = wc_get_order( $order_id );

		if ( empty( $the_order ) ) {
			return false;
		}

		$the_order_user = $the_order->get_user();

		$result = false;
		if ( $tag === 'id' || $tag === 'order_id' ) {
			$result = $the_order->get_id();
		}

		if ( $tag === 'order_number' ) {
			$result = $the_order->get_order_number();
		}

		if ( $tag === 'billing_address' || $tag === 'shipping_address' ) {
			if ( $the_order_user ) {
				$result = $the_order->get_formatted_shipping_address();
			} else {
				$result = $the_order->get_formatted_billing_address();
			}
		}

		if ( $tag === 'customer_email' ) {
			// If the order has a user, use the user email, otherwise use the billing email.
			if ( $the_order_user ) {
				$result = $the_order_user->user_email;
			} else {
				$result = $the_order->get_billing_email();
			}
		}

		if ( $tag === 'customer_display_name' ) {
			// If the order has a user, use the user display name, otherwise use the billing email.
			if ( $the_order_user ) {
				$result = $the_order_user->display_name;
			} else {
				$result = $the_order->get_formatted_billing_full_name();
			}
		}

		if ( $tag === 'order_url' ) {
			$result = $the_order->get_view_order_url();
		}

		// See if there is a method to handle the tag.
		if ( $result === false && method_exists( $the_order, "get_{$tag}" ) ) {
			$result = $the_order->{"get_{$tag}"}();
			// If the result is not a simple string, see if it is stringable.
			if ( ! is_string( $result ) && method_exists( $result, '__toString' ) ) {
				$result = $result->__toString();
			}
		}

		return $result;
	}

	private function process_merge_tag_wc_product( $tag, $entry, $form, $url_encode, $esc_html, $nl2br, $format ) {
		// If the entry is null, let's see if we are on the product page.
		if ( empty( $entry ) ) {
			$product = wc_get_product();
			if ( empty( $product ) ) {
				return false;
			}

			$form_id = wc_gfpa()->get_gravity_form_id( $product->get_id() );
			if ( empty( $form_id ) ) {
				return false;
			}

			// If we have the form, let's do extra checks to make sure this is the form attached to the product.
			if ($form && $form_id !== $form['id'] ) {
				return false;
			}
		} else {
			$order_id = gform_get_meta( $entry['id'], 'woocommerce_order_number' );
			$order_item_id = gform_get_meta( $entry['id'], 'woocommerce_order_item_number' );

			if ( ! empty( $order_id ) && ! empty( $order_item_id ) ) {
				$order = wc_get_order( $order_id );
				$order_item = $order->get_item( $order_item_id );
				$product = $order_item->get_product();
			} else {
				$product_id = gform_get_meta( $entry['id'], 'woocommerce_product_id' );
				if ( empty( $product_id ) ) {
					return false;
				}
				$product = wc_get_product( $product_id );
			}
		}

		if ( empty( $product ) ) {
			return false;
		}

		$result = false;
		if ( $tag === 'name' ) {
			$result = $product->get_name();
		}

		if ( $tag === 'sku' ) {
			$result = $product->get_sku();
		}

		if ( $tag === 'price' ) {
			$result = $product->get_price();
		}

		if ( $tag === 'regular_price' ) {
			$result = $product->get_regular_price();
		}

		if ( $tag === 'sale_price' ) {
			$result = $product->get_sale_price();
		}

		if ( $tag === 'stock_quantity' ) {
			$result = $product->get_stock_quantity();
		}

		if ( $tag === 'stock_status' ) {
			$result = $product->get_stock_status();
		}

		if ( $tag === 'product_url' ) {
			$result = $product->get_permalink();
		}

		// See if there is a method to handle the tag.
		if ( $result === false && method_exists( $product, "get_{$tag}" ) ) {
			$result = $product->{"get_{$tag}"}();
			// If the result is not a simple string, see if it is stringable.
			if ( ! is_string( $result ) && method_exists( $result, '__toString' ) ) {
				$result = $result->__toString();
			}
		}

		return $result;
	}
}
