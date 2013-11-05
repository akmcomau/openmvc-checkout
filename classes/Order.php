<?php

namespace modules\checkout\classes;

use ErrorException;
use core\classes\Config;
use core\classes\Database;
use core\classes\URL;
use core\classes\Model;
use modules\checkout\classes\models\Checkout;
use modules\checkout\classes\models\CheckoutItem;

class Order {
	protected $database;
	protected $url;
	protected $cart;
	protected $config;

	protected $fees = 0;
	protected $tracking_number = '';

	public function __construct(Config $config, Database $database, Cart $cart) {
		$this->config = $config;
		$this->database = $database;
		$this->cart = $cart;
		$this->url = new URL($config);
	}

	public function setFees($fees) {
		$this->fees = $fees;
	}

	public function setTrackingNumber($tracking_number) {
		$this->tracking_number = $tracking_number;
	}

	public function purchase() {
		$model = new Model($this->config, $this->database);
		$status = $model->getModel('\modules\checkout\classes\models\CheckoutStatus');

		// create the checkout record
		$checkout = $model->getModel('\modules\checkout\classes\models\Checkout');
		$checkout->customer_id              = $this->cart->getCustomer()->id;
		$checkout->status_id                = $status->getStatusId('Processing');
		$checkout->payment_code             = 'test';
		$checkout->checkout_items_cost      = $this->cart->getCartCostPrice();
		$checkout->checkout_shipping_cost   = $this->cart->getShippingCost();
		$checkout->checkout_amount          = $this->cart->getCartTotal();
		$checkout->checkout_tax             = $this->cart->getCartTax();
		$checkout->checkout_shipping        = $this->cart->getShippingSell();
		$checkout->checkout_special_offers  = $this->cart->getSpecialOfferAmount();
		$checkout->checkout_fees            = $this->fees;
		$checkout->checkout_tracking_number = $this->tracking_number;

		// Put an address on the order
		$address = $model->getModel('\core\classes\models\Address')->get(['customer_id' => $this->cart->getCustomer()->id]);
		if (!$address) {

			// Fake an address
			$australia = $model->getModel('\core\classes\models\Country')->get(['code' => 'AU']);
			$qld = $model->getModel('\core\classes\models\State')->get([
				'country_id' => $australia->id,
				'name' => 'Queensland',
			]);
			$brisbane = $model->getModel('\core\classes\models\City')->get([
				'country_id' => $australia->id,
				'state_id' => $qld->id,
				'name' => 'Brisbane',
			]);
			$address = $model->getModel('\core\classes\models\Address');
			$address->customer_id        = $this->cart->getCustomer()->id;
			$address->address_first_name = 'Joe';
			$address->address_last_name  = 'Bloggs';
			$address->address_line1      = '50 Edward St';
			$address->address_line2      = '';
			$address->address_postcode   = '4000';
			$address->city_id            = $brisbane->id;
			$address->state_id           = $qld->id;
			$address->country_id         = $australia->id;
			$address->insert();
		}
		$checkout->delivery_address_id = $address->id;
		$checkout->billing_address_id = $address->id;

		// create the checkout record
		$checkout->insert();

		// create the cart item records
		foreach ($this->cart->getContents() as $item) {
			$checkout_item = $model->getModel('\modules\checkout\classes\models\CheckoutItem');
			$checkout_item->checkout_id = $checkout->id;
			$checkout_item->checkout_item_type = $item->getType();
			$checkout_item->checkout_item_type_id = $item->id;
			$checkout_item->cost_price = $item->getCostPrice();
			$checkout_item->sell_price = $item->getPrice();
			$checkout_item->quantity = $item->getQuantity();
			$checkout_item->insert();
			$item->purchase($checkout, $checkout_item, $item);
		}

		return $checkout;
	}
}