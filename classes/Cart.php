<?php

namespace modules\checkout\classes;

use ErrorException;
use core\classes\Config;
use core\classes\Database;
use core\classes\Request;
use core\classes\URL;
use core\classes\Model;
use core\classes\Module;
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
	protected $shipping_address = NULL;
	protected $billing_address = NULL;
	protected $payment_method = NULL;
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
				$this->cart_contents    = $request->session->get(['cart', 'contents']);
				$this->cart_notes       = $request->session->get(['cart', 'notes']);
				$this->cart_shipping    = $request->session->get(['cart', 'shipping']);
				$this->shipping_address = $request->session->get(['cart', 'shipping_address']);
				$this->billing_address  = $request->session->get(['cart', 'billing_address']);
				$this->payment_method   = $request->session->get(['cart', 'payment_method']);
			}
		}
	}

	public function getContentsString($language) {
		return $language->get('cart_contents_string', [
			$this->getItemCount(),
			money_format('%n', $this->getCartSellTotal()),
		]);
	}

	public function getRawContents() {
		return [
			'contents' => $this->cart_contents,
			'notes' => $this->cart_notes,
			'shipping' => $this->cart_shipping,
			'shipping_address' => $this->shipping_address,
			'billing_address' => $this->billing_address,
			'payment_method' => $this->payment_method,
		];
	}

	public function getShipping() {
		return $this->cart_shipping;
	}

	public function setShipping(array $shipping) {
		$this->cart_shipping = $shipping;
		$this->save();
	}

	public function setBillingAddress(array $address) {
		$this->billing_address = $address;
		$this->save();
	}

	public function getBillingAddress() {
		return $this->billing_address;
	}

	public function setShippingAddress(array $address) {
		$this->shipping_address = $address;
		$this->save();
	}

	public function getShippingAddress() {
		return $this->shipping_address;
	}

	public function setPaymentMethod($method) {
		$this->payment_method = $method;
		$this->save();
	}

	public function getPaymentMethod() {
		return $this->payment_method;
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

	public function getCartSellTotal() {
		$total = $this->getCartTotal();

		$checkout_config = $this->config->moduleConfig('\modules\checkout');
		$tax_type = $checkout_config->tax_type;
		$tax_class = NULL;
		if ($tax_type) {
			$tax_class = $this->config->siteConfig()->checkout->tax_types->$tax_type->class;
			$tax_class = new $tax_class($this->config, $this->database);
			$total = $tax_class->calculateTax($total) + $total;
		}

		return $total;
	}

	public function getCartTotal() {
		$total = 0;
		$contents = $this->getContents();
		foreach ($contents as $item) {
			$sub_total = $item->getQuantity() * $item->getSellPrice();
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

	public function getTotals($language = NULL, $include_shipping = TRUE) {
		$checkout_config = $this->config->moduleConfig('\modules\checkout');
		$tax_type = $checkout_config->tax_type;

		$totals = [];

		if ($language) {
			if ($tax_type) {
				$totals[$language->get('subtotal')] = $this->getCartTotal(FALSE);
			}
			else {
				$totals[$language->get('total')] = $this->getCartTotal(FALSE);
			}
		}
		else {
			if ($tax_type) {
				$totals['Sub-Total'] = $this->getCartTotal(FALSE);
			}
			else {
				$totals['Total'] = $this->getCartTotal(FALSE);
			}
		}

		// Shipping total
		if ($include_shipping && $this->cart_shipping) {
			foreach ($this->cart_shipping as $name => $data) {
				$method = $this->config->siteConfig()->checkout->shipping_methods->$name;
				$totals[$method->name] = $this->callPriceHook($data['sell']);
			}
		}

		// add tax total
		$tax_class = NULL;

		if ($tax_type) {
			$tax_config = $this->config->siteConfig()->checkout->tax_types->$tax_type;
			$tax_class  = $tax_config->class;
			$tax_class  = new $tax_class($this->config, $this->database);

			$sub_total = 0;
			foreach ($totals as $value) {
				$sub_total += $value;
			}

			$totals[$tax_config->name] = $this->callPriceHook($tax_class->calculateTax($sub_total));
		}

		return $totals;
	}

	protected function callPriceHook($price) {
		$modules = (new Module($this->config))->getEnabledModules();
		foreach ($modules as $module) {
			if (isset($module['hooks']['checkout']['getSellPrice'])) {
				$class = $module['namespace'].'\\'.$module['hooks']['checkout']['getSellPrice'];
				$this->logger->debug("Calling Hook: $class::getSellPrice");
				$class = new $class($this->config, $this->database, NULL);
				$price = call_user_func_array(array($class, 'getSellPrice'), [$price]);
			}
		}
		return $price;
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
					'cost' => isset($data['cost']) ? $data['cost'] : 0,
				];
			}
		}

		// add tax total
		$checkout_config = $this->config->moduleConfig('\modules\checkout');
		$tax_type = $checkout_config->tax_type;
		$tax_class = NULL;
		if ($tax_type) {
			$totals     = $this->getTotals();
			$tax_config = $this->config->siteConfig()->checkout->tax_types->$tax_type;
			$tax_class  = $tax_config->class;
			$tax_class  = new $tax_class($this->config, $this->database);

			$tax = $totals[$tax_config->name];

			$sub_totals[$tax_config->name] = [
				'type' => 'tax',
				'code' => $tax_type,
				'sell' => $tax,
				'cost' => $tax,
			];
		}

		return $sub_totals;
	}

	public function getCartTax() {
		$checkout_config = $this->config->moduleConfig('\modules\checkout');
		$tax_type = $checkout_config->tax_type;
		if ($tax_type) {
			$tax_config = $this->config->siteConfig()->checkout->tax_types->$tax_type;
			$tax_class  = $tax_config->class;
			$tax_class  = new $tax_class($this->config, $this->database);

			return $this->callPriceHook($tax_class->calculateTax($this->getCartTotal(FALSE)));
		}

		return 0;
	}

	public function getShippingCost() {
		$total = 0;
		if ($this->cart_shipping) {
			foreach ($this->cart_shipping as $name => $data) {
				$method = $this->config->siteConfig()->checkout->shipping_methods->$name;
				if (isset($data['cost'])) {
					$total += $data['cost'];
				}
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
		return $this->callPriceHook($total);
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
		$item = $object->get(['id' => $id, 'active' => TRUE]);
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

	public function hasShippingAddress() {
		$module_config = $this->config->moduleConfig('\modules\checkout');

		// if the cart is not shippable, return true
		if (!$module_config->shipping_address) {
			return TRUE;
		}

		if ($this->shipping_address) {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}

	public function hasBillingAddress() {
		$module_config = $this->config->moduleConfig('\modules\checkout');

		// if the cart is not shippable, return true
		if (!$module_config->billing_address) {
			return TRUE;
		}

		if ($this->billing_address) {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
}

?>
