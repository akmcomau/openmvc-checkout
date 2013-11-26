<?php

namespace modules\checkout\classes\models;

use core\classes\Model;
use core\classes\Encryption;

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
		'shipping_address_id' => [
			'data_type'      => 'bigint',
			'null_allowed'   => TRUE,
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
		'shipping_address_id' => ['address', 'address_id'],
		'billing_address_id' => ['address', 'address_id'],
	];

	protected $relationships = [
		'customer' => [
			'where_fields'  => [
				'customer_first_name', 'customer_last_name',
				'customer_login', 'customer_email'
			],
			'join_clause'   => 'JOIN customer USING (customer_id)',
		],
	];

	public function getItems() {
		if (array_key_exists('checkout_items', $this->objects)) {
			return $this->objects['checkout_items'];
		}

		$checkout_item = $this->getModel('\modules\checkout\classes\models\CheckoutItem');
		$this->objects['checkout_items'] = $checkout_item->getMulti(['checkout_id' => $this->id]);
		return $this->objects['checkout_items'];
	}

	public function getTotal() {
		return $this->checkout_amount + $this->checkout_shipping - $this->special_offers;
	}

	public function getTotals($language) {
		$totals = [];

		// TODO Add other totals here

		$totals[$language->get('total')] = $this->checkout_amount;

		return $totals;
	}

	public function decodeReferenceNumber($reference) {
		return Encryption::defuscate($reference, $this->config->siteConfig()->secret);
	}

	public function getByReference($reference) {
		$checkout_id = $this->decodeReferenceNumber($reference);
		return $this->getModel(__CLASS__)->get(['id' => $checkout_id]);
	}

	public function getReferenceNumber() {
		return Encryption::obfuscate($this->id, $this->config->siteConfig()->secret);
	}

	public function getCostPrice() {
		return ($this->checkout_items_cost + $this->checkout_shipping_cost);
	}

	public function getSellPrice() {
		return ($this->checkout_amount + $this->checkout_shipping);
	}

	public function getProfit() {
		return ($this->amount + $this->shipping - $this->fees - $this->items_cost - $this->shipping_cost);
	}

	public function getCustomer() {
		if (isset($this->objects['customer'])) {
			return $this->objects['customer'];
		}

		$this->objects['customer'] = $this->getModel('\core\classes\models\Customer')->get([
			'id' => $this->customer_id,
		]);
		return $this->objects['customer'];
	}

	public function getStatus() {
		if (isset($this->objects['status'])) {
			return $this->objects['status'];
		}

		$this->objects['status'] = $this->getModel('\modules\checkout\classes\models\CheckoutStatus')->get([
			'id' => $this->status_id,
		]);
		return $this->objects['status'];
	}

	public function getShippingAddress() {
		if (is_null($this->shipping_address_id)) {
			return NULL;
		}

		if (isset($this->objects['shipping_address'])) {
			return $this->objects['shipping_address'];
		}

		$this->objects['shipping_address'] = $this->getModel('\core\classes\models\Address')->get([
			'id' => $this->shipping_address_id,
		]);
		return $this->objects['shipping_address'];
	}

	public function getBillingAddress() {
		if (is_null($this->billing_address_id)) {
			return NULL;
		}

		if (isset($this->objects['billing_address'])) {
			return $this->objects['billing_address'];
		}

		$this->objects['billing_address'] = $this->getModel('\core\classes\models\Address')->get([
			'id' => $this->billing_address_id,
		]);
		return $this->objects['billing_address'];
	}
}
