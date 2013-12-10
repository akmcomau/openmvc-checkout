<?php

namespace modules\checkout\classes\models;

use core\classes\exceptions\ModelException;
use core\classes\Model;

class CheckoutItem extends Model implements ItemInterface {

	protected $table       = 'checkout_item';
	protected $primary_key = 'checkout_item_id';
	protected $columns     = [
		'checkout_item_id' => [
			'data_type'      => 'bigint',
			'auto_increment' => TRUE,
			'null_allowed'   => FALSE,
		],
		'checkout_id' => [
			'data_type'      => 'bigint',
			'null_allowed'   => FALSE,
		],
		'checkout_item_type' => [
			'data_type'      => 'text',
			'data_length'    => 32,
			'null_allowed'   => FALSE,
		],
		'checkout_item_type_id' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
		'checkout_item_sku' => [
			'data_type'      => 'text',
			'data_length'    => 64,
			'null_allowed'   => FALSE,
		],
		'checkout_item_name' => [
			'data_type'      => 'text',
			'data_length'    => 128,
			'null_allowed'   => FALSE,
		],
		'checkout_item_cost_price' => [
			'data_type'      => 'numeric',
			'data_length'    => [6, 4],
			'null_allowed'   => TRUE,
		],
		'checkout_item_sell_price' => [
			'data_type'      => 'numeric',
			'data_length'    => [6, 4],
			'null_allowed'   => FALSE,
		],
		'checkout_item_tax' => [
			'data_type'      => 'numeric',
			'data_length'    => [6, 4],
			'null_allowed'   => FALSE,
		],
		'checkout_item_quantity' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
	];

	protected $indexes = [
		'checkout_id',
		'checkout_item_type',
		'checkout_item_type_id',
	];

	protected $foreign_keys = [
		'checkout_id' => ['checkout', 'checkout_id'],
	];

	public function getItemType() {
		if (isset($this->objects['item_type'])) {
			return $this->objects['item_type'];
		}

		$type = $this->getType();
		$item_type = $this->config->siteConfig()->checkout->item_types->$type;
		$class = $item_type->item;
		$object = (new $class($this->config, $this->database))->get(['id' => $this->checkout_item_type_id]);
		$this->objects['item_type'] = $object;
		return $object;
	}

	public function getType() {
		return $this->checkout_item_type;
	}

	public function allowMultiple() {
		$type = $this->getItemType();
		return $type->allowMultiple();
	}

	public function getMaxQuantity() {
		$type = $this->getItemType();
		return $type->getMaxQuantity();
	}

	public function getName() {
		return $this->name;
	}

	public function getSKU() {
		return $this->sku;
	}

	public function isShippable() {
		$type = $this->getItemType();
		return $type->isShippable();
	}

	public function purchase(Checkout $checkout, CheckoutItem $item, ItemInterface $item) {
		throw new ModelException(__METHOD__.' not allowed on CheckoutItem model');
	}

	public function getPrice() {
		return $this->checkout_item_sell_price;
	}

	public function getCostPrice() {
		return $this->checkout_item_cost_price;
	}

	public function setQuantity($quantity) {
		throw new ModelException(__METHOD__.' not allowed on CheckoutItem model');
	}

	public function getQuantity() {
		return $this->checkout_item_quantity;
	}

	public function setTotal($total) {
		throw new ModelException(__METHOD__.' not allowed on CheckoutItem model');
	}

	public function getTotal() {
		return $this->getQuantity() * $this->getPrice();
	}
}
