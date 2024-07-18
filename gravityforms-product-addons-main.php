<?php


if ( defined( 'DOING_AJAX' ) ) {
	include 'gravityforms-product-addons-ajax.php';
}

class WC_GFPA_Main {

	/**
	 * The main instance of this extension.
	 *
	 * @var WC_GFPA_Main
	 */
	private static $instance;

	public static function register() {
		if ( empty( self::$instance ) ) {
			self::$instance = new WC_GFPA_Main();
		}
	}

	/**
	 * Gets the single instance of the plugin.
	 *
	 * @return WC_GFPA_Main
	 */
	public static function instance(): WC_GFPA_Main {
		if ( empty( self::$instance ) ) {
			self::$instance = new WC_GFPA_Main();
		}

		return self::$instance;
	}

	public $assets_version = '3.3.1';

	public $gravity_products = array();

	public function __construct() {

		add_action( 'wp_head', array( $this, 'on_wp_head' ) );

		// Enqueue Gravity Forms Scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'woocommerce_gravityform_enqueue_scripts' ), 99 );
		add_action( 'wc_quick_view_enqueue_scripts', array( $this, 'wc_quick_view_enqueue_scripts' ), 99 );
		// Bind the form
		add_action(
			'woocommerce_before_add_to_cart_form',
			array(
				$this,
				'on_woocommerce_before_add_to_cart_form',
			)
		);

		add_action(
			'woocommerce_bv_before_add_to_cart_button',
			array(
				$this,
				'woocommerce_gravityform_bulk_variations',
			)
		);

		// Filters for price display
		add_filter( 'woocommerce_grouped_price_html', array( $this, 'get_price_html' ), 999, 2 );

		add_filter( 'woocommerce_variation_price_html', array( $this, 'get_price_html' ), 999, 2 );
		add_filter( 'woocommerce_variation_sale_price_html', array( $this, 'get_price_html' ), 999, 2 );

		add_filter( 'woocommerce_sale_price_html', array( $this, 'get_price_html' ), 999, 2 );
		add_filter( 'woocommerce_price_html', array( $this, 'get_price_html' ), 999, 2 );
		add_filter( 'woocommerce_get_price_html', array( $this, 'get_price_html' ), 999, 2 );
		add_filter( 'woocommerce_empty_price_html', array( $this, 'get_price_html' ), 999, 2 );

		add_filter( 'woocommerce_free_sale_price_html', array( $this, 'get_free_price_html' ), 999, 2 );
		add_filter( 'woocommerce_free_price_html', array( $this, 'get_free_price_html' ), 999, 2 );

		// Modify Add to Cart Buttons
		add_action( 'init', array( $this, 'get_gravity_products' ) );

		// Require Helper Classes / Functions
		require 'inc/gravityforms-product-addons-hook-manager.php';
		require 'inc/gravityforms-product-addons-helpers-entry.php';
		require 'inc/gravityforms-product-addons-submission-helpers.php';
		require 'inc/gravityforms-product-addons-field-helpers.php';

		// Custom merge tags
		require 'inc/gravityforms-product-addons-merge-tags.php';

		// Register the admin controller.
		require 'admin/gravityforms-product-addons-admin.php';
		WC_GFPA_Admin_Controller::register();

		require 'inc/gravityforms-product-addons-order.php';
		WC_GFPA_Order::register();

		require 'inc/gravityforms-product-addons-ajax.php';
		require 'inc/gravityforms-product-addons-bulk-variations.php';
		require 'inc/gravityforms-product-addons-cart-item.php';
		require 'inc/gravityforms-product-addons-cart.php';
		require 'inc/gravityforms-product-addons-cart-edit.php';
		require 'inc/gravityforms-product-addons-cart-validation.php';
		require 'inc/gravityforms-product-addons-reorder.php';
		require 'inc/gravityforms-product-addons-entry.php';
		require 'inc/gravityforms-product-addons-export.php';
		require 'inc/gravityforms-product-addons-stock.php';
		require 'inc/gravityforms-product-addons-display.php';
		require 'inc/gravityforms-product-addons-field-values.php';
		require 'inc/gravityforms-product-addons-structured-data.php';

		WC_GFPA_AJAX::register();
		WC_GFPA_Cart::register();
		WC_GFPA_Cart_Edit::register();
		WC_GFPA_Cart_Validation::register();
		WC_GFPA_Reorder::register();
		WC_GFPA_Display::register();
		WC_GFPA_FieldValues::register();
		WC_GFPA_Stock::register();
		WC_GFPA_Structured_Data::register();
		WC_GFPA_Export::register();
		WC_GFPA_Merge_Tags::register();
		$this->load_integrations();
		add_action( 'init', array( $this, 'on_init' ) );
	}

	public function load_integrations() {
		if ( class_exists( 'WC_Bookings' ) ) {
			require 'inc/integrations/bookings.php';
			WC_GFPA_Integrations_Bookings::register();
		}
	}

	public function on_init() {
		WC_GFPA_Entry::register();
	}

	public function on_woocommerce_before_add_to_cart_form() {
		$product = wc_get_product( get_the_ID() );

		if ( empty( $product ) ) {
			return;
		}

		if ( $product->is_type( 'variable' ) ) {
			// Addon display

			if ( apply_filters( 'woocommerce_gforms_use_template_back_compatibility', get_option( 'woocommerce_gforms_use_template_back_compatibility', false ) ) ) {
				add_action(
					'woocommerce_before_add_to_cart_button',
					array(
						$this,
						'woocommerce_gravityform',
					),
					10
				);
			} else {

				$hook = apply_filters( 'woocommerce_gforms_form_output_hook', 'woocommerce_single_variation', $product );

				// Use the new 2.4+ hook
				add_action( $hook, array( $this, 'woocommerce_gravityform' ), 11 );
				add_action( 'wc_cvo_after_single_variation', array( $this, 'woocommerce_gravityform' ), 9 );
			}
		} else {
			$hook = apply_filters( 'woocommerce_gforms_form_output_hook', 'woocommerce_before_add_to_cart_button', $product );
			add_action( $hook, array( $this, 'woocommerce_gravityform' ), 10 );
		}
	}

	public function on_wp_head() {
		echo '<style type="text/css">';
		echo 'dd ul.bulleted {  float:none;clear:both; }';
		echo '</style>';
	}

	public function get_gravity_products() {
		global $wpdb;
		$metakey                = '_gravity_form_data';
		$this->gravity_products = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key=%s", $metakey ) );
	}

	/*
	----------------------------------------------------------------------------------- */
	/*
	Product Form Functions */
	/* ----------------------------------------------------------------------------------- */

	public function woocommerce_gravityform() {
		global $post, $woocommerce;

		include_once 'gravityforms-product-addons-form.php';

		$gravity_form_data = $this->get_gravity_form_data( $post->ID );

		if ( is_array( $gravity_form_data ) && $gravity_form_data['id'] ) {
			$product = wc_get_product( $post->ID );

			$product_form = new woocommerce_gravityforms_product_form( $gravity_form_data['id'], $post->ID );
			$product_form->get_form( $gravity_form_data );

			echo '<input type="hidden" name="add-to-cart" value="' . esc_attr( $product->get_id() ) . '" />';

		}
		echo '<div class="clear"></div>';
	}

	public function woocommerce_gravityform_bulk_variations() {
		global $post, $woocommerce;

		include_once 'gravityforms-product-addons-form.php';

		$gravity_form_data = $this->get_gravity_form_data( $post->ID, 'bulk' );
		if ( is_array( $gravity_form_data ) && $gravity_form_data['id'] ) {
			$product = wc_get_product( $post->ID );

			$form_id                                     = isset( $gravity_form_data['bulk_id'] ) ? $gravity_form_data['bulk_id'] : $gravity_form_data['id'];
			$product_form                                = new woocommerce_gravityforms_product_form( $form_id, $post->ID );
			$gravity_form_data['disable_label_subtotal'] = 'yes';
			$gravity_form_data['disable_label_total']    = 'yes';

			$product_form->get_form( $gravity_form_data );
			echo '<div class="clear"></div>';
		}
	}


	public function wc_quick_view_enqueue_scripts() {
		global $wp_query, $post;

		$enqueue     = false;
		$prices      = array();
		$suffixes    = array();
		$use_ajax    = array();
		$use_anchors = array();

		$product_ids = array();

		if ( $post && preg_match_all( '/\[products +.*?((ids=.+?)|(name=.+?))\]/is', $post->post_content, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				// parsing shortcode attributes
				$attr       = shortcode_parse_atts( $match[1] );
				$product_id = isset( $attr['ids'] ) ? $attr['ids'] : false;
				if ( ! empty( $product_id ) ) {
					$product_ids = array_merge( $product_ids, array_map( 'trim', explode( ',', $product_id ) ) );
				}
			}
		} elseif ( $post && preg_match_all( '/\[woocommerce_one_page_checkout +.*?((ids=.+?)|(name=.+?))\]/is', $post->post_content, $matches, PREG_SET_ORDER ) ) {
			$ajax = false;
			foreach ( $matches as $match ) {
				// parsing shortcode attributes
				$attr       = shortcode_parse_atts( $match[1] );
				$product_id = isset( $attr['ids'] ) ? $attr['ids'] : false;
				if ( ! empty( $product_id ) ) {
					$product_ids = array_merge( $product_ids, array_map( 'trim', explode( ',', $product_id ) ) );
				}
			}
		} elseif ( $wp_query && ! empty( $wp_query->posts ) ) {
			$product_ids = wp_list_pluck( $wp_query->posts, 'ID' );
		}

		if ( ! empty( $product_ids ) ) {
			foreach ( $product_ids as $post_id ) {
				$_product = wc_get_product( $post_id );
				if ( $_product ) {
					$enqueue           = true;
					$gravity_form_data = $this->get_gravity_form_data( $post_id );
					if ( $gravity_form_data && is_array( $gravity_form_data ) ) {
						gravity_form_enqueue_scripts( $gravity_form_data['id'], $gravity_form_data['is_ajax'] ?? false );

						if ( ! empty( $gravity_form_data['bulk_id'] ) ) {
							gravity_form_enqueue_scripts( $gravity_form_data['bulk_id'], $gravity_form_data['is_ajax'] ?? false );
						}

						$prices[ $_product->get_id() ]      = wc_get_price_to_display( $_product );
						$suffixes[ $_product->get_id() ]    = $_product->get_price_suffix();
						$use_ajax[ $_product->get_id() ]    = $gravity_form_data['is_ajax'] ?? false;
						$use_anchors[ $_product->get_id() ] = $gravity_form_data['disable_anchor'] != 'yes';

						if ( $_product->has_child() ) {
							foreach ( $_product->get_children() as $variation_id ) {
								$variation               = wc_get_product( $variation_id );
								$prices[ $variation_id ] = wc_get_price_to_display( $variation );
							}
						}
					}
				}
			}
		}

		if ( $enqueue ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_register_script( 'accounting', WC()->plugin_url() . '/assets/js/accounting/accounting' . $suffix . '.js', array( 'jquery' ), '0.4.2' );

			wp_enqueue_script(
				'wc-gravityforms-product-addons',
				self::plugin_url() . '/assets/js/gravityforms-product-addons.js',
				array(
					'jquery',
					'accounting',
				),
				true
			);

			// Accounting
			wp_localize_script(
				'accounting',
				'accounting_params',
				array(
					'mon_decimal_point' => wc_get_price_decimal_separator(),
				)
			);

			$wc_gravityforms_params = array(
				'currency_format_num_decimals' => wc_get_price_decimals(),
				'currency_format_symbol'       => get_woocommerce_currency_symbol(),
				'currency_format_decimal_sep'  => esc_attr( wc_get_price_decimal_separator() ),
				'currency_format_thousand_sep' => esc_attr( wc_get_price_thousand_separator() ),
				'currency_format'              => esc_attr(
					str_replace(
						array( '%1$s', '%2$s' ),
						array(
							'%s',
							'%v',
						),
						get_woocommerce_price_format()
					)
				), // For accounting JS
				'prices'                       => $prices,
				'price_suffix'                 => $suffixes,
				'use_ajax'                     => $use_ajax,
				'use_anchors'                  => $use_anchors,
			);

			$wc_gravityforms_params = apply_filters( 'woocommerce_gforms_quickview_script_params', $wc_gravityforms_params, $product_ids );

			wp_localize_script( 'wc-gravityforms-product-addons', 'wc_gravityforms_params', $wc_gravityforms_params );
		}
	}

	public function woocommerce_gravityform_enqueue_scripts() {
		global $post;

		if ( is_product() ) {
			$product           = wc_get_product( get_the_ID() );
			$gravity_form_data = $this->get_gravity_form_data( $post->ID );
			if ( $gravity_form_data && is_array( $gravity_form_data ) ) {
				$this->__do_enqueue( $product, $gravity_form_data );
			}
		} else {
			$post_content = apply_filters( 'woocommerce_gforms_get_post_content', ( $post instanceof WP_Post ? $post->post_content : '' ), $post );
			if ( $post_content && preg_match_all( '/\[product_page[s]? +.*?((id=.+?)|(name=.+?))\]/is', $post_content, $matches, PREG_SET_ORDER ) ) {
				foreach ( $matches as $match ) {
					// parsing shortcode attributes
					$attr       = shortcode_parse_atts( $match[1] );
					$product_id = isset( $attr['id'] ) ? $attr['id'] : false;

					if ( ! empty( $product_id ) ) {
						$gravity_form_data = $this->get_gravity_form_data( $product_id );

						if ( $gravity_form_data && is_array( $gravity_form_data ) ) {
							$p = wc_get_product( $product_id );
							$this->__do_enqueue( $p, $gravity_form_data );
						}
					}
				}
			} elseif ( $post_content && preg_match_all( '/\[woocommerce_one_page_checkout +.*?((product_ids=.+?)|(name=.+?))\]/is', $post_content, $matches, PREG_SET_ORDER ) ) {
				foreach ( $matches as $match ) {
					// parsing shortcode attributes
					$attr        = shortcode_parse_atts( $match[1] );
					$product_id  = isset( $attr['product_ids'] ) ? $attr['product_ids'] : false;
					$product_ids = array();
					if ( ! empty( $product_id ) ) {
						$product_ids = array_merge( $product_ids, array_map( 'trim', explode( ',', $product_id ) ) );
					}

					if ( ! empty( $product_ids ) ) {
						foreach ( $product_ids as $product_id ) {
							$gravity_form_data = $this->get_gravity_form_data( $product_id );

							if ( $gravity_form_data && is_array( $gravity_form_data ) ) {
								$p = wc_get_product( $product_id );
								$this->__do_enqueue( $p, $gravity_form_data );
							}
						}
					}
				}
			} else {
				$product_ids = apply_filters( 'woocommerce_gforms_get_products_to_enqueue_for', array() );
				if ( ! empty( $product_ids ) ) {
					foreach ( $product_ids as $product_id ) {
						$gravity_form_data = $this->get_gravity_form_data( $product_id );

						if ( $gravity_form_data && is_array( $gravity_form_data ) ) {
							$p = wc_get_product( $product_id );
							$this->__do_enqueue( $p, $gravity_form_data );
						}
					}
				}
			}
		}
	}

	private function __do_enqueue( $product, $gravity_form_data ) {
		wp_enqueue_style( 'wc-gravityforms-product-addons', self::plugin_url() . '/assets/css/frontend.css', null );

		gravity_form_enqueue_scripts( $gravity_form_data['id'], false );
		if ( isset( $gravity_form_data['bulk_id'] ) && ! empty( $gravity_form_data['bulk_id'] ) ) {
			gravity_form_enqueue_scripts( $gravity_form_data['bulk_id'], false );
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'accounting', WC()->plugin_url() . '/assets/js/accounting/accounting' . $suffix . '.js', array( 'jquery' ), '0.4.2' );

		wp_enqueue_script(
			'wc-gravityforms-product-addons',
			self::plugin_url() . '/assets/js/gravityforms-product-addons.js',
			array(
				'jquery',
				'accounting',
			),
			'3.2.4',
			true
		);

		$prices = array(
			$product->get_id() => wc_get_price_to_display( $product ),
		);

		if ( $product->has_child() ) {
			foreach ( $product->get_children() as $variation_id ) {
				$variation = wc_get_product( $variation_id );
				if ( $variation ) {
					$prices[ $variation_id ] = wc_get_price_to_display( $variation );
				}
			}
		}

		// Accounting
		wp_localize_script(
			'accounting',
			'accounting_params',
			array(
				'mon_decimal_point' => wc_get_price_decimal_separator(),
			)
		);

		$wc_gravityforms_params = array(
			'currency_format_num_decimals' => wc_get_price_decimals(),
			'currency_format_symbol'       => get_woocommerce_currency_symbol(),
			'currency_format_decimal_sep'  => esc_attr( wc_get_price_decimal_separator() ),
			'currency_format_thousand_sep' => esc_attr( wc_get_price_thousand_separator() ),
			'currency_format'              => esc_attr(
				str_replace(
					array( '%1$s', '%2$s' ),
					array(
						'%s',
						'%v',
					),
					get_woocommerce_price_format()
				)
			), // For accounting JS
			'prices'                       => $prices,
			'price_suffix'                 => array( $product->get_id() => $product->get_price_suffix() ),
			'use_ajax'                     => array( $product->get_id() => apply_filters( 'woocommerce_gforms_use_ajax', isset( $gravity_form_data['use_ajax'] ) ? ( $gravity_form_data['use_ajax'] == 'yes' ) : false ) ),
			'use_anchors'                  => $gravity_form_data['disable_anchor'] != 'yes',
			'initialize_file_uploader'     => apply_filters( 'woocommerce_gforms_initialize_file_uploader', false ),
		);

		$wc_gravityforms_params = apply_filters( 'woocommerce_gforms_script_params', $wc_gravityforms_params, $product->get_id() );

		wp_localize_script( 'wc-gravityforms-product-addons', 'wc_gravityforms_params', $wc_gravityforms_params );
	}

	/**
	 * @param            $html
	 * @param WC_Product $_product
	 *
	 * @return string
	 */
	public function get_price_html( $html, $_product ) {
		$gravity_form_data = $this->get_gravity_form_data( $_product->get_id() );
		if ( $gravity_form_data && is_array( $gravity_form_data ) ) {

			if ( isset( $gravity_form_data['disable_woocommerce_price'] ) && $gravity_form_data['disable_woocommerce_price'] == 'yes' ) {
				$html = '';
			}

			if ( isset( $gravity_form_data['price_before'] ) && ! empty( $gravity_form_data['price_before'] ) ) {
				$html = '<span class="woocommerce-price-before">' . $gravity_form_data['price_before'] . ' </span>' . $html;
			}

			if ( isset( $gravity_form_data['price_after'] ) && ! empty( $gravity_form_data['price_after'] ) ) {
				$html .= '<span class="woocommerce-price-after"> ' . $gravity_form_data['price_after'] . '</span>';
			}
		}

		return $html;
	}

	/**
	 * @param            $html
	 * @param WC_Product $_product
	 *
	 * @return string
	 */
	public function get_free_price_html( $html, $_product ) {
		$gravity_form_data = $this->get_gravity_form_data( $_product->get_id() );
		if ( $gravity_form_data && is_array( $gravity_form_data ) ) {

			if ( isset( $gravity_form_data['disable_woocommerce_price'] ) && $gravity_form_data['disable_woocommerce_price'] == 'yes' ) {
				$html = '';
			}

			if ( isset( $gravity_form_data['price_before'] ) && ! empty( $gravity_form_data['price_before'] ) ) {
				$html = '<span class="woocommerce-price-before">' . $gravity_form_data['price_before'] . ' </span>' . $html;
			}

			if ( isset( $gravity_form_data['price_after'] ) && ! empty( $gravity_form_data['price_after'] ) ) {
				$html .= '<span class="woocommerce-price-after"> ' . $gravity_form_data['price_after'] . '</span>';
			}
		}

		return $html;
	}

	public function get_formatted_price( $price ) {
		return wc_price( $price );
	}



	/** Helper functions ***************************************************** */

	/**
	 * Get the plugin url.
	 *
	 * @access public
	 * @return string
	 */
	public static function plugin_url() {
		return plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) );
	}

	public function get_gravity_form_reorder_data( int $post_id, array $working_data = array() ) {
		$current_data = $this->get_gravity_form_data( $post_id );

		if ( ! is_array( $current_data ) ) {
			return false;
		}

		// Add in any new key / values from the current data.
		foreach ( $current_data as $key => $value ) {
			if ( ! isset( $working_data[ $key ] ) ) {
				$working_data[ $key ] = $value;
			}
		}

		$working_data['reorder_processing'] = $current_data['reorder_processing'] ?? 'revalidate';

		// Return the data, filtered.
		return apply_filters( 'woocommerce_gforms_get_product_reorder_form_data', $working_data, $current_data, $post_id );
	}

	public function has_gravity_form( int $product_id ): bool {
		$gravity_form_data = $this->get_gravity_form_data( $product_id );
		if ( $gravity_form_data && is_array( $gravity_form_data ) && isset( $gravity_form_data['id'] ) && intval( $gravity_form_data['id'] ) > 0 ) {
			return true;
		}

		return false;
	}

	public function get_gravity_form_id( $product_id ) {
		$gravity_form_data = $this->get_gravity_form_data( $product_id );
		if ( $gravity_form_data && is_array( $gravity_form_data ) && isset( $gravity_form_data['id'] ) && intval( $gravity_form_data['id'] ) > 0 ) {
			return $gravity_form_data['id'];
		}

		return false;
	}

	public function get_gravity_form_data( $post_id, $context = 'single' ) {
		$product = wc_get_product( $post_id );
		$data    = false;
		if ( $product ) {
			$data = $product->get_meta( '_gravity_form_data' );

		}

		// New defaults since 3.5.0
		$validation_message = apply_filters( 'woocommerce_gforms_validation_message', __( 'There was a problem with your submission. Please review the fields below.', 'woocommerce-gravityforms-product-addons' ), $post_id, $context );

		$data = apply_filters( 'woocommerce_gforms_get_product_form_data', $data, $post_id, $context );

		// Yes, set the defaults after we call the hook. This allows users to check if (empty($form_data)) from their hook.
		$data = wp_parse_args(
			$data,
			array(
				'reorder_processing'       => 'revalidate', // 'resubmit', 'revalidate', 'none'
				'reorder_hydrate_defaults' => 'yes',
				'use_ajax'                 => 'no',
				'is_ajax'                  => false,
				'show_wc_notices'          => 'yes',
				'validation_message'       => $validation_message,
			)
		);

		if ( isset( $data['id'] ) ) {
			if ( $context == 'bulk' ) {
				$data['id'] = isset( $data['bulk_id'] ) ? $data['bulk_id'] : $data['id'];
			}

			$structured_data_settings = array(
				'structured_data_override'      => isset( $data['structured_data_override'] ) ? $data['structured_data_override'] : 'no',
				'structured_data_low_price'     => isset( $data['structured_data_low_price'] ) ? $data['structured_data_low_price'] : '',
				'structured_data_high_price'    => isset( $data['structured_data_high_price'] ) ? $data['structured_data_high_price'] : '',
				'structured_data_override_type' => isset( $data['structured_data_override_type'] ) ? $data['structured_data_override_type'] : '',
			);

			$structured_data_settings = apply_filters( 'woocommerce_gforms_get_product_structured_data_settings', $structured_data_settings, $post_id, $data );
			$data                     = array_merge( $data, $structured_data_settings );
		}

		if ( isset( $data['id'] ) ) {
			return $data;
		} else {
			return false;
		}
	}

	public function get_form_field_hash( int $form_id ): string {
		global $wpdb;
		$hash = false;
		if ( class_exists( 'GFFormsModel' ) && class_exists( 'GFAPI' ) ) {
			$form = GFAPI::get_form( $form_id );
			// Get the gravity form meta db table.
			$table_name = GFFormsModel::get_meta_table_name();

			// Get the display_meta column from the gravity form meta db table.
			$form_row = $wpdb->get_row( $wpdb->prepare( "SELECT display_meta FROM {$table_name} WHERE form_id=%d", $form_id ), ARRAY_A );
			// Loading main form object (supports serialized strings as well as JSON strings)
			$form   = GFFormsModel::unserialize( rgar( $form_row, 'display_meta' ) );
			$fields = $form['fields'] = is_array( rgar( $form, 'fields' ) ) ? array_values( $form['fields'] ) : array();

			// Remove any properties from the fields that are not simple values.
			$fields = array_map(function($field) {
				return array_filter($field, function($value) {
					return !is_array($value) && !is_object($value);
				});
			}, $fields);

			// Remove the fields that are not visible.
			$fields = array_filter( $fields, function ( $field ) {
				return ! rgar( $field, 'adminOnly' );
			} );

			$hash   = md5( wp_json_encode( $fields ) );
		}

		return $hash;
	}


	public function log_debug( $message ) {
		if ( class_exists( 'GFLogging' ) ) {
			GFLogging::include_logger();
			GFLogging::log_message( 'woocommerce-gravityforms-product-addons', $message, KLogger::DEBUG );
		}
	}
}

/**
 * The instance of the plugin.
 *
 * @return WC_GFPA_Main
 */
function wc_gfpa() {
	return WC_GFPA_Main::instance();
}

WC_GFPA_Main::register();
