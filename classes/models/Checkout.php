<?php

namespace modules\checkout\classes\models;

use core\classes\Model;

class Checkout extends Model {

	protected $table       = 'checkout';
	protected $primary_key = 'checkout_id';
	protected $columns     = [
		'checkout_id' => [
			'data_type'      => 'bigint',
			'auto_increment' => TRUE,
			'null_allowed'   => FALSE,
		],
		'checkout_created' => [
			'data_type'      => 'datetime',
			'null_allowed'   => FALSE,
		],
		'customer_id' => [
			'data_type'      => 'bigint',
			'null_allowed'   => FALSE,
		],
		'checkout_status_id' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
		'checkout_tracking_number' => [
			'data_type'      => 'text',
			'data_length'    => 256,
			'null_allowed'   => FALSE,
		],
		'payment_code' => [
			'data_type'      => 'text',
			'data_length'    => 32,
			'null_allowed'   => TRUE,
		],
		'delivery_address_id' => [
			'data_type'      => 'bigint',
			'null_allowed'   => FALSE,
		],
		'billing_address_id' => [
			'data_type'      => 'bigint',
			'null_allowed'   => FALSE,
		],
		'checkout_items_cost' => [
			'data_type'      => 'numeric',
			'data_length'    => [6, 4],
			'null_allowed'   => FALSE,
		],
		'checkout_amount' => [
			'data_type'      => 'numeric',
			'data_length'    => [6, 4],
			'null_allowed'   => FALSE,
		],
		'checkout_shipping' => [
			'data_type'      => 'numeric',
			'data_length'    => [6, 4],
			'null_allowed'   => FALSE,
		],
		'checkout_shipping_cost' => [
			'data_type'      => 'numeric',
			'data_length'    => [6, 4],
			'null_allowed'   => FALSE,
		],
		'checkout_tax' => [
			'data_type'      => 'numeric',
			'data_length'    => [6, 4],
			'null_allowed'   => FALSE,
		],
		'checkout_special_offers' => [
			'data_type'      => 'numeric',
			'data_length'    => [6, 4],
			'null_allowed'   => FALSE,
		],
		'checkout_fees' => [
			'data_type'      => 'numeric',
			'data_length'    => [6, 4],
			'null_allowed'   => FALSE,
		],
	];

	protected $indexes = [
		'checkout_created',
		'customer_id',
		'payment_code',
		'checkout_status_id',
	];

	protected $foreign_keys = [
		'customer_id'     => ['customer',     'customer_id'],
		'checkout_status_id' => ['checkout_status', 'checkout_status_id'],
		'delivery_address_id' => ['address', 'address_id'],
		'billing_address_id' => ['address', 'address_id'],
	];

	public function getItems() {
		$checkout_item = $this->getModel('\modules\checkout\classes\models\CheckoutItem');
		$items = $checkout_item->getMulti(['checkout_id' => $this->id]);
		return $items;
	}

	public function getTotals($language) {
		$totals = [];

		// TODO Add other totals here

		$totals[$language->get('total')] = $this->checkout_amount;

		return $totals;
	}
}
