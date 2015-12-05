<?php

namespace modules\checkout\classes\models;

use core\classes\Model;
use core\classes\Module;
use core\classes\Encryption;

class Checkout extends Model {

	public $anonymous = FALSE;

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
			'null_allowed'   => TRUE,
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
			'null_allowed'   => TRUE,
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
		'checkout_receipt_note' => [
			'data_type'      => 'text',
			'null_allowed'   => TRUE,
		],
		'checkout_locale' => [
			'data_type'      => 'text',
			'max_length'     => 10,
			'null_allowed'   => FALSE,
		],
		'checkout_currency' => [
			'data_type'      => 'text',
			'max_length'     => 2,
			'null_allowed'   => FALSE,
		],
		'checkout_exchange_rate' => [
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

	public $reverse_exchange_rate = FALSE;

	public function getItems() {
		if (array_key_exists('checkout_items', $this->objects)) {
			return $this->objects['checkout_items'];
		}

		$checkout_item = $this->getModel('\modules\checkout\classes\models\CheckoutItem');
		$this->objects['checkout_items'] = $checkout_item->getMulti(['checkout_id' => $this->id]);

		foreach ($this->objects['checkout_items'] as $item) {
			$item->reverse_exchange_rate = $this->reverse_exchange_rate;
			$item->exchange_rate = $this->exchange_rate;
		}

		return $this->objects['checkout_items'];
	}

	public function getTotal() {
		return $this->reverseExchangeRate($this->checkout_amount + $this->checkout_shipping - $this->special_offers);
	}

	public function getTotals($language = NULL) {
		$totals = [];

		if ($language) {
			$totals[$language->get('total')] = $this->reverseExchangeRate($this->checkout_amount);
		}
		else {
			$totals['Total'] = $this->reverseExchangeRate($this->checkout_amount);
		}

		$details = $this->getModel('\modules\checkout\classes\models\CheckoutDetail')->getMulti([
			'checkout_id' => $this->id,
		]);
		foreach ($details as $detail) {
			switch ($detail->type) {
				case 'shipping':
					$code = $detail->type_code;
					$method = $this->config->siteConfig()->checkout->shipping_methods->$code;
					$totals[$method->name] = $this->reverseExchangeRate($detail->amount);
					break;

				case 'tax':
					$code = $detail->type_code;
					$tax_config = $this->config->siteConfig()->checkout->tax_types->$code;
					$totals[$tax_config->name] = $this->reverseExchangeRate($detail->amount);
					break;
			}
		}

		return $totals;
	}

	public function getGrandTotal() {
		$total  = 0;
		$totals = $this->getTotals();
		foreach ($totals as $name => $value) {
			$total += $value;
		}
		return $total;
	}

	protected function reverseExchangeRate($price) {
		if (!$this->reverse_exchange_rate) return $price;
		return $price / $this->exchange_rate;
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

	public function alsoPurchased($cart_contents, $limit = 3) {
		if (count($cart_contents) == 0) return [];

		$sql = "
			SELECT
				*
			FROM (
				SELECT
					checkout_item_type,
					checkout_item_type_id,
					COUNT(*) AS count
				FROM
					checkout_item
				WHERE
					checkout_id IN (
						SELECT
							checkout_id
						FROM
							checkout_item
						WHERE
		";

		$parts = [];
		foreach ($cart_contents as $item) {
			$parts[] = "
						(
							checkout_item_type = ".$this->database->quote($item->getType())."
							AND checkout_item_type_id = ".(int)$item->id."
						)
			";
		}
		$sql .= join("OR", $parts);

		$sql .= "
					)
		";

		// Don't want the same products
		$sql .= "AND NOT " . join("AND NOT ", $parts);

		$sql .= "
				GROUP BY
					checkout_item_type,
					checkout_item_type_id
			) AS items
			ORDER BY count DESC, RANDOM()
			LIMIT ".(int)$limit."
		";

		$purchased = [];
		$items = $this->database->queryMulti($sql);
		foreach ($items as $item) {
			$type = $item['checkout_item_type'];
			$item_type = $this->config->siteConfig()->checkout->item_types->$type;
			$class = $item_type->item;
			$object = (new $class($this->config, $this->database))->get(['id' => $item['checkout_item_type_id']]);
			$purchased[] = $object;
		}

		return $purchased;
	}
}
