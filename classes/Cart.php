<?php

namespace modules\checkout\classes;

use ErrorException;
use core\classes\Config;
use core\classes\Database;
use core\classes\Request;
use core\classes\URL;
use core\classes\Model;
use core\classes\models\Customer;

class Cart {
	protected $database;
	protected $url;
	protected $request;
	protected $config;

	protected $cart_contents = [];
	protected $cart_notes = '';
	protected $customer = NULL;

	public function __construct(Config $config, Database $database, Request $request) {
		$this->config = $config;
		$this->database = $database;
		$this->request = $request;
		$this->url = new URL($config);

		$customer_id = $request->getAuthentication()->getCustomerID();
		if ($customer_id) {
			$model = new Model($config, $database);
			$this->customer = $model->getModel('\core\classes\models\Customer')->get(['id' => $customer_id]);
		}

		if (!is_null($request->session->get('cart'))) {
			$this->cart_contents = $request->session->get(['cart', 'contents']);
			$this->cart_notes    = $request->session->get(['cart', 'notes']);
		}
	}

	public function getContentsString($language) {
		return $language->get('cart_contents_string', [
			count($this->getItemCount()),
			money_format('%n', $this->getGrandTotal()),
		]);
	}

	public function getCustomer() {
		return $this->customer;
	}

	public function setCustomer(Customer $customer) {
		$this->customer = $customer;
	}

	public function getContents() {
		$contents = [];
		$model = new Model($this->config, $this->database);
		foreach ($this->cart_contents as $type => $items) {
			$object = $model->getModel($this->typeToItem($type));
			foreach ($items as $id => $quantity) {
				$cart_item = $object->get(['id' => $id, 'active' => TRUE]);
				if ($cart_item) {
					$cart_item->setQuantity($quantity);
					$contents[] = $cart_item;
				}
			}
		}
		return $contents;
	}

	public function getItemCount() {
		$count = 0;
		$contents = $this->getContents();
		foreach ($contents as $item) {
			$count = $item->getQuantity();
		}
		return $count;
	}

	public function getCartTotal() {
		$total = 0;
		$contents = $this->getContents();
		foreach ($contents as $item) {
			$sub_total = $item->getQuantity() * $item->getPrice();
			$item->setTotal($sub_total);
			$total += money_format('%^!n', $sub_total);
		}
		return $total;
	}

	public function getGrandTotal() {
		$total = $this->getCartTotal();

		// Add shipping

		// Discounts

		return $total;
	}

	public function getCartCostPrice() {
		$total = 0;
		$contents = $this->getContents();
		foreach ($contents as $item) {
			$sub_total = $item->getQuantity() * $item->getCostPrice();
			$item->setTotal($sub_total);
			$total += money_format('%^!n', $sub_total);
		}
		return $total;
	}

	public function getTotals($language) {
		$totals = [];

		// TODO Add other totals here

		$totals[$language->get('total')] = $this->getCartTotal();

		return $totals;
	}

	public function getCartTax() {
		return 0;
	}

	public function getShippingCost() {
		return 0;
	}

	public function getShippingSell() {
		return 0;
	}

	public function getSpecialOfferAmount() {
		return 0;
	}

	public function isShippable() {
		$shippable = FALSE;
		$contents = $this->getContents();
		foreach ($contents as $item) {
			if ($item->isShippable()) {
				$shippable = TRUE;
			}
		}
		return $shippable;
	}

	public function getNotes() {
		return $this->cart_notes;
	}

	public function save() {
		$cart = ['contents' => $this->cart_contents, 'notes' => $this->cart_notes];
		$this->request->session->set('cart', $cart);
	}

	public function clear() {
		$this->cart_contents = [];
		$this->cart_notes = '';
		$this->save();
	}

	public function add($type, $id, $quantity) {
		$item = $this->getItem($type, $id, $quantity);

		if (isset($this->cart_contents[$type][$id])) {
			$quantity += $this->cart_contents[$type][$id];
		}

		if (!$item->allowMultiple()) {
			$this->cart_contents[$type] = [];
		}

		$this->cart_contents[$type][$id] = (int)$quantity;

		if (!is_null($item->getMaxQuantity()) && $this->cart_contents[$type][$id] > $item->getMaxQuantity()) {
			$this->cart_contents[$type][$id] = $item->getMaxQuantity();
		}

		$this->save();
	}

	public function update($type, $id, $quantity) {
		if ((int)$quantity == 0) {
			return $this->remove($type, $id);
		}

		$item = $this->getItem($type, $id);

		if (!$item->allowMultiple()) {
			$this->cart_contents[$type] = [];
		}

		$this->cart_contents[$type][$id] = (int)$quantity;

		if (!is_null($item->getMaxQuantity()) && $this->cart_contents[$type][$id] > $item->getMaxQuantity()) {
			$this->cart_contents[$type][$id] = $item->getMaxQuantity();
		}

		$this->save();
	}

	public function remove($type, $id) {
		$item = $this->getItem($type, $id);
		unset($this->cart_contents[$type][$id]);
		$this->save();
	}

	public function getItem($type, $id) {
		$id = (int)$id;
		$class = $this->typeToItem($type);
		$object = new $class($this->config, $this->database);
		$item = $object->get(['id' => $id]);
		if (!$item) {
			throw new ErrorException("Invalid checkout item: $type / $id");
		}
		return $item;
	}

	public function typeToItem($type) {
		return $this->config->siteConfig()->checkout->item_types->$type->item;
	}

	public function ItemToType($class) {
		foreach ($this->config->siteConfig()->checkout->item_types as $type => $classes) {
			if ($class == $classes->item) {
				return $type;
			}
		}

		throw new ErrorException('Invalid checkout class: '.$class);
	}
}

?>