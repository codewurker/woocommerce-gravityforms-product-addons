<?php

class WC_GFPA_Cart_Item_Meta {

	/**
	 * @var mixed
	 */
	private $cart_item;

	/**
	 * @var array|mixed
	 */
	private $other_data;

	public function __construct( $cart_item, $other_data = array() ) {
		$this->cart_item = $cart_item;
		$this->other_data = $other_data;
	}

}
