<?php

namespace modules\checkout\classes;

use ErrorException;
use core\classes\Config;
use core\classes\Database;
use core\classes\URL;
use core\classes\Language;
use core\classes\Model;
use core\classes\Module;
use core\classes\Logger;
use core\classes\Template;
use core\classes\Email;
use core\classes\models\Address;
use core\classes\models\Customer;
use modules\checkout\classes\models\Checkout;
use modules\checkout\classes\models\CheckoutItem;

class Order {
	protected $database;
	protected $url;
	protected $cart;
	protected $config;
	protected $logger;

	protected $fees = 0;
	protected $tracking_number = '';

	public function __construct(Config $config, Database $database, Cart $cart) {
		$this->config = $config;
		$this->database = $database;
		$this->cart = $cart;
		$this->url = new URL($config);
		$this->logger = Logger::getLogger(get_class($this));
	}

	public function setFees($fees) {
		$this->fees = $fees;
	}

	public function setTrackingNumber($tracking_number) {
		$this->tracking_number = $tracking_number;
	}

	public function purchase($payment_code, Customer $customer = NULL, Address $billing = NULL, Address $shipping = NULL) {
		$module_config = $this->config->moduleConfig('\modules\checkout');
		$model = new Model($this->config, $this->database);
		$status = $model->getModel('\modules\checkout\classes\models\CheckoutStatus');

		// check if the customer is logged in
		if ($this->cart->getCustomer()) {
			$customer = $this->cart->getCustomer();
			$was_anonymous = FALSE;
		}
		// else check if the email already exists, attach it to that account
		else {
			if (!$customer || !$customer->id) {
				$exists = $model->getModel('\core\classes\models\Customer')->get([
					'email' => $billing->email
				]);
				if ($exists) {
					$customer = $exists;
				}
				else {
					$customer = $model->getModel('\core\classes\models\Customer');
					$customer->site_id = $this->config->siteConfig()->site_id;
					$customer->first_name = $billing->first_name;
					$customer->last_name = $billing->last_name;
					$customer->login = $billing->email.'-'.time();
					$customer->password = '';
					$customer->email = $billing->email;
					$customer->phone = $billing->phone;
					$customer->insert();
				}
			}
			$was_anonymous = TRUE;
		}

		// check the billing address
		if ($billing) {
			$params = $billing->getRecord();
			$params['customer_id'] = $customer->id;
			$exists = $model->getModel('\core\classes\models\Address')->get($params);
			if ($exists) {
				$billing = $exists;
			}
			else {
				$billing->customer_id = $customer->id;
				$billing->insert();
			}
		}

		// check the shipping address
		if ($shipping) {
			$params = $shipping->getRecord();
			$params['customer_id'] = $customer->id;
			$exists = $model->getModel('\core\classes\models\Address')->get($params);
			if ($exists) {
				$shipping = $exists;
			}
			else {
				$shipping->customer_id = $customer->id;
				$shipping->insert();
			}
		}

		// create the checkout record
		$checkout = $model->getModel('\modules\checkout\classes\models\Checkout');
		$checkout->customer_id              = $customer->id;
		$checkout->status_id                = $status->getStatusId('Pending');
		$checkout->payment_code             = $payment_code;
		$checkout->checkout_items_cost      = $this->cart->getCartCostPrice();
		$checkout->checkout_amount          = $this->cart->getCartTotal();
		$checkout->checkout_tax             = $this->cart->getCartTax();
		$checkout->checkout_shipping        = $this->cart->getShippingSell();
		$checkout->checkout_shipping_cost   = $this->cart->getShippingCost();
		$checkout->checkout_special_offers  = $this->cart->getSpecialOfferAmount();
		$checkout->checkout_fees            = $this->fees;
		$checkout->checkout_tracking_number = $this->tracking_number;
		$checkout->billing_address_id       = $billing ? $billing->id : NULL;
		$checkout->locale                   = $this->config->siteConfig()->site_locale;
		$checkout->currency                 = $this->config->siteConfig()->currency;
		$checkout->exchange_rate            = $this->getExchangeRate($this->config, $this->database);

		$checkout->anonymous = $was_anonymous;

		if ($shipping && $this->cart->isShippable()) {
			$checkout->shipping_address_id = $shipping->id;
		}

		// create the checkout record
		$checkout->insert();

		// create the checkout detail records
		$sub_totals = $this->cart->getSubTotalsDetail();
		foreach ($sub_totals as $detail) {
			$checkout_detail = $model->getModel('\modules\checkout\classes\models\CheckoutDetail');
			$checkout_detail->checkout_id = $checkout->id;
			$checkout_detail->type        = $detail['type'];
			$checkout_detail->type_code   = $detail['code'];
			$checkout_detail->amount      = $this->applyExchangeRate($this->config, $this->database, $detail['sell'] ? $detail['sell'] : 0);
			$checkout_detail->cost        = $this->applyExchangeRate($this->config, $this->database, $detail['cost'] ? $detail['cost'] : 0);
			$checkout_detail->insert();
		}

		// create the cart item records
		foreach ($this->cart->getContents() as $item) {
			$checkout_item = $model->getModel('\modules\checkout\classes\models\CheckoutItem');
			$checkout_item->checkout_id = $checkout->id;
			$checkout_item->checkout_item_type = $item->getType();
			$checkout_item->checkout_item_type_id = $item->id;
			$checkout_item->checkout_item_name = $item->getName();
			$checkout_item->checkout_item_sku = $item->getSKU();
			$checkout_item->checkout_item_model = $item->getModelNo();
			$checkout_item->cost_price = $item->getCostPrice();
			$checkout_item->sell_price = $item->getSellPrice();
			$checkout_item->tax = 0; // TODO FIXME
			$checkout_item->quantity = $item->getQuantity();
			$checkout_item->insert();
			$item->purchase($checkout, $checkout_item, $item);
		}

		return $checkout;
	}

	public static function getExchangeRate($config, $database) {
		$rate = 1;
		$modules = (new Module($config))->getEnabledModules();
		foreach ($modules as $module) {
			if (isset($module['hooks']['checkout']['getExchangeRate'])) {
				$class = $module['namespace'].'\\'.$module['hooks']['checkout']['getExchangeRate'];
				$logger = Logger::getLogger(__CLASS__);
				$logger->debug("Calling Hook: $class::getExchangeRate");
				$class = new $class($config, $database, NULL);
				$rate = call_user_func_array(array($class, 'getExchangeRate'), []);
			}
		}
		return $rate;
	}

	public static function applyExchangeRate($config, $database, $price) {
		return $price * self::getExchangeRate($config, $database);
	}

	public function sendOrderEmails(Checkout $checkout, Language $language, $checkout_note_html = NULL, $checkout_note_txt = NULL) {
		$customer = $checkout->getCustomer();
		$data = [
			'checkout' => $checkout,
			'customer' => $customer,
			'shipping' => $checkout->getShippingAddress(),
			'billing'  => $checkout->getBillingAddress(),
			'checkout_note_html' => $checkout_note_html,
			'checkout_note_txt' => $checkout_note_txt,
		];

		// customer
		if ($customer) {
			$body = $this->getTemplate($language, 'emails/order_customer.txt.php', $data, 'modules'.DS.'checkout');
			$html = $this->getTemplate($language, 'emails/order_customer.html.php', $data, 'modules'.DS.'checkout');
			$email = new Email($this->config);
			$email->setToEmail($customer->email);
			$email->setSubject($language->get('customer_order_subject'));
			$email->setBodyTemplate($body);
			$email->setHtmlTemplate($html);
			$email->send();
		}

		// admin
		$body = $this->getTemplate($language, 'emails/order_admin.txt.php', $data, 'modules'.DS.'checkout');
		$html = $this->getTemplate($language, 'emails/order_admin.html.php', $data, 'modules'.DS.'checkout');
		$email = new Email($this->config);
		$email->setToEmail($this->config->siteConfig()->email_addresses->orders);
		$email->setSubject($language->get('admin_order_subject'));
		$email->setBodyTemplate($body);
		$email->setHtmlTemplate($html);
		$email->send();
	}

	protected function getTemplate(Language $language, $filename, array $data = NULL, $path = NULL) {
		return new Template($this->config, $language, $filename, $data, $path);
	}
}
