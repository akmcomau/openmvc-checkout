<?php

namespace modules\checkout\classes;

use ErrorException;
use core\classes\Config;
use core\classes\Database;
use core\classes\Request;
use core\classes\URL;
use core\classes\Model;
use core\classes\Logger;
use core\classes\models\Customer;

class Cart {
	protected $database;
	protected $url;
	protected $request;
	protected $config;
	protected $logger;

	protected $cart_contents = [];
	protected $cart_notes = '';
	protected $cart_shipping = NULL;
	protected $customer = NULL;

	public function __construct(Config $config, Database $database, Request $request) {
		$this->config = $config;
		$this->database = $database;
		$this->request = $request;
		$this->url = new URL($config);
		$this->logger = Logger::getLogger(get_class($this));

		$customer_id = $request->getAuthentication()->getCustomerID();
		if ($customer_id) {
			$model = new Model($config, $database);
			$this->customer = $model->getModel('\core\classes\models\Customer')->get(['id' => $customer_id]);
		}

		if (!is_null($request->session->get('cart'))) {
			if (!is_null($request->session->get(['cart', 'contents']))) {
				$this->cart_contents = $request->session->get(['cart', 'contents']);
				$this->cart_notes    = $request->session->get(['cart', 'notes']);
				$this->cart_shipping = $request->session->get(['cart', 'shipping']);
			}
		}
	}

	public function getContentsString($language) {
		return $language->get('cart_contents_string', [
			$this->getItemCount(),
			money_format('%n', $this->getCartTotal()),
		]);
	}

	public function getRawContents() {
		return [
			'contents' => $this->cart_contents,
			'notes' => $this->cart_notes,
			'shipping' => $this->cart_shipping,
		];
	}

	public function getShipping() {
		return $this->cart_shipping;
	}

	public function setShipping(array $shipping) {
		$this->cart_shipping = $shipping;
		$this->save();
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
			$count += $item->getQuantity();
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
		$total  = 0;
		$totals = $this->getTotals();
		foreach ($totals as $name => $value) {
			$total += $value;
		}
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

	public function getTotals($language = NULL) {
		$totals = [];

		if ($language) {
			$totals[$language->get('total')] = $this->getCartTotal();
		}
		else {
			$totals['Total'] = $this->getCartTotal();
		}

		// TODO Add other totals here
		if ($this->cart_shipping) {
			foreach ($this->cart_shipping as $name => $data) {
				$method = $this->config->siteConfig()->checkout->shipping_methods->$name;
				$totals[$method->name] = $data['sell'];
			}
		}

		return $totals;
	}

	public function getSubTotalsDetail() {
		$sub_totals = [];

		if ($this->cart_shipping) {
			foreach ($this->cart_shipping as $name => $data) {
				$method = $this->config->siteConfig()->checkout->shipping_methods->$name;
				$sub_totals[$method->name] = [
					'type' => 'shipping',
					'code' => $name,
					'sell' => $data['sell'],
					'cost' => $data['cost'],
				];
			}
		}

		return $sub_totals;
	}

	public function getCartTax() {
		// TODO: return tax
		return 0;
	}

	public function getShippingCost() {
		$total = 0;
		if ($this->cart_shipping) {
			foreach ($this->cart_shipping as $name => $data) {
				$method = $this->config->siteConfig()->checkout->shipping_methods->$name;
				$total += $data['cost'];
			}
		}
		return $total;
	}

	public function getShippingSell() {
		$total = 0;
		if ($this->cart_shipping) {
			foreach ($this->cart_shipping as $name => $data) {
				$method = $this->config->siteConfig()->checkout->shipping_methods->$name;
				$total += $data['sell'];
			}
		}
		return $total;
	}

	public function getSpecialOfferAmount() {
		// TODO: special offers amount
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
		$this->request->session->set('cart', $this->getRawContents());
	}

	public function clear() {
		$this->logger->info("Clear Cart");
		$this->cart_shipping = NULL;
		$this->cart_contents = [];
		$this->cart_notes = '';
		$this->save();
	}

	public function add($type, $id, $quantity) {
		$this->logger->info("Add To Cart: $quantity x $type => $id");
		$item = $this->getItem($type, $id, $quantity);
		if (!$item) {
			return;
		}

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

		$this->cart_shipping = NULL;
		$this->save();
	}

	public function update($type, $id, $quantity) {
		$this->logger->info("Update Cart: $quantity x $type => $id");
		if ((int)$quantity == 0) {
			return $this->remove($type, $id);
		}

		$item = $this->getItem($type, $id);
		if (!$item) {
			return;
		}

		if (!$item->allowMultiple()) {
			$this->cart_contents[$type] = [];
		}

		$this->cart_contents[$type][$id] = (int)$quantity;

		if (!is_null($item->getMaxQuantity()) && $this->cart_contents[$type][$id] > $item->getMaxQuantity()) {
			$this->cart_contents[$type][$id] = $item->getMaxQuantity();
		}

		$this->cart_shipping = NULL;
		$this->save();
	}

	public function remove($type, $id) {
		$this->logger->info("Remove from Cart: $type => $id");
		$item = $this->getItem($type, $id);
		if (!$item) {
			return;
		}
		unset($this->cart_contents[$type][$id]);
		$this->cart_shipping = NULL;
		$this->save();
	}

	public function getItem($type, $id) {
		$id = (int)$id;
		$class = $this->typeToItem($type);
		$object = new $class($this->config, $this->database);
		$item = $object->get(['id' => $id]);
		if (!$item) {
			$this->logger->info("Invalid checkout item: $type / $id");
			return NULL;
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

	public function hasShippingMethod() {
		// if the cart is not shippable, return true
		if (!$this->isShippable()) {
			return TRUE;
		}

		if ($this->cart_shipping) {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
}

?>
