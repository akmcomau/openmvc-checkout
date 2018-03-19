<?php

namespace modules\checkout\controllers;

use core\classes\exceptions\RedirectException;
use core\classes\exceptions\SoftRedirectException;
use core\classes\renderable\Controller;
use core\classes\Model;
use core\classes\Encryption;
use core\classes\Pagination;
use core\classes\FormValidator;
use modules\checkout\classes\Cart as CartContents;
use core\controllers\Customer as CustomerController;

class Checkout extends Controller {

	protected $permissions = [
	];

	public function getAllUrls($include_filter = NULL, $exclude_filter = NULL) {
		return [];
	}

	public function index($type = NULL) {
		$cart = new CartContents($this->config, $this->database, $this->request);
		$module_config = $this->config->moduleConfig('\modules\checkout');
		$this->language->loadLanguageFile('checkout.php', 'modules'.DS.'checkout');

		// if the cart is empty go to the cart page
		if ($cart->getItemCount() == 0) {
			$this->logger->info('Cart is empty');
			throw new RedirectException('Cart');
		}

		// FIXME: Currently support for only one type of shipping method
		if (count($this->config->siteConfig()->checkout->shipping_methods) && !$cart->hasShippingMethod()) {
			foreach ($this->config->siteConfig()->checkout->shipping_methods as $name => $shipping_config) {
				// break now and $shipping methood has the first shipping method
				break;
			}
			throw new SoftRedirectException($shipping_config->public, 'shipping');
		}

		// Check for an anonymous checkout or show login register page
		$anonymous_checkout = FALSE;
		if ($module_config->anonymous_checkout && !$this->authentication->customerLoggedIn() && $type == 'anonymous') {
			$anonymous_checkout = TRUE;
		}
		elseif (!$this->authentication->customerLoggedIn()) {
			$this->language->loadLanguageFile('customer.php');
			$controller = new CustomerController($this->config, $this->database, $this->request, $this->response);
			$controller->setLanguage($this->language);
			$form_login    = $controller->getLoginForm();
			$form_register = $controller->getRegisterForm();

			// get the remember me token
			$remember_me = NULL;
			if (isset($this->request->cookies['rememberme'])) {
				$remember_me = json_decode($this->request->cookies['rememberme']);
			}

			$data = [
				'login' => $form_login,
				'register' => $form_register,
				'controller' => 'Checkout',
				'method' => 'index',
				'params' => NULL,
				'anonymous_checkout_enabled' => $module_config->anonymous_checkout,
				'anonymous_checkout' => $anonymous_checkout,
				'remember_me' => $remember_me,
			];

			$login_template = $this->getTemplate('pages/customer/login_form.php', $data);
			$register_template = $this->getTemplate('pages/customer/register_form.php', $data);

			$data['login_form'] = $login_template->render();
			$data['register_form'] = $register_template->render();

			$template = $this->getTemplate('pages/login_register.php', $data, 'modules'.DS.'checkout');
			$this->response->setContent($template->render());
			return;
		}

		if ($this->request->requestParam('payment') && $this->request->requestParam('payment_method')) {
			// Get the selected payment type
			$code   = $this->request->requestParam('payment_method');
			$method = $this->config->siteConfig()->checkout->payment_methods->$code;

			$cart->setPaymentMethod($code);

			// if the shipping/billing addresses need to be obtained
			if ($module_config->shipping_address || $module_config->billing_address) {
				throw new RedirectException($this->url->getURl('Checkout', 'address'));
			}

			throw new SoftRedirectException($method->public, 'payment');
		}

		$data = [
			'contents' => $cart->getContents(),
			'totals' => $cart->getTotals($this->language),
			'grand_total' => $cart->getGrandTotal(),
			'payment_types' => $this->config->siteConfig()->checkout->payment_methods,
		];

		// is also purchased enabled
		if ($module_config->enable_upsell) {
			$checkout = $this->model->getModel('\modules\checkout\classes\models\Checkout');
			$data['also_purchased'] = $checkout->alsoPurchased($cart->getContents());
		}

		$template = $this->getTemplate('pages/checkout.php', $data, 'modules'.DS.'checkout');
		$this->response->setContent($template->render());
	}

	public function address() {
		$cart = new CartContents($this->config, $this->database, $this->request);
		$module_config = $this->config->moduleConfig('\modules\checkout');
		$this->language->loadLanguageFile('checkout.php', 'modules'.DS.'checkout');

		// if the cart is empty go to the cart page
		if ($cart->getItemCount() == 0) {
			$this->logger->info('Cart is empty');
			throw new RedirectException('Cart');
		}

		$country = $this->model->getModel('\core\classes\models\Country');
		$form = $this->getAddressForm();

		if ($form->validate()) {
			$billing_address = $this->createAddress($form, 'billing');
			$shipping_address = $this->createAddress($form, 'shipping');

			$cart->setShippingAddress($shipping_address->getRecord());
			$cart->setBillingAddress($billing_address->getRecord());

			$method = $this->config->siteConfig()->checkout->payment_methods->{$cart->getPaymentMethod()};

			throw new SoftRedirectException($method->public, 'payment');
		}

		$data = [
			'form' => $form,
			'countries' => $country->getMulti(NULL, ['name' => 'asc']),
			'contents' => $cart->getContents(),
			'totals' => $cart->getTotals($this->language),
			'grand_total' => $cart->getGrandTotal(),
			'payment_types' => $this->config->siteConfig()->checkout->payment_methods,
		];

		$template = $this->getTemplate('pages/address.php', $data, 'modules'.DS.'checkout');
		$this->response->setContent($template->render());
	}

	protected function createAddress($form, $type) {
		$model = new Model($this->config, $this->database);

		// The country must exist
		$country = $model->getModel('\core\classes\models\Country')->get([
			'id' => $form->getValue($type.'_country')
		]);
		if (!$country) {
			throw new \ErrorException("Invalid country selected in checkout address");
		}

		// get the state
		$state = NULL;
		if ($form->getValue($type.'_state')) {
			$state = $model->getModel('\core\classes\models\State')->get([
				'country_id' => $country->id,
				'name' => $form->getValue($type.'_state'),
			]);
			if (!$state) {
				$state = $model->getModel('\core\classes\models\State')->get([
					'country_id' => $country->id,
					'abbrev' => $form->getValue($type.'_state'),
				]);
				if (!$state) {
					try {
						$state = $model->getModel('\core\classes\models\State');
						$state->country_id = $country->id;
						$state->abbrev     = $form->getValue($type.'_state');
						$state->name       = $form->getValue($type.'_state');
						$state->insert();
					}
					catch (\Exception $ex) {
						$state = $model->getModel('\core\classes\models\State')->get([
							'country_id' => $country->id,
							'name' => $form->getValue($type.'_state'),
						]);
						if (!$state) {
							throw new \ErrorException("Error creating state record: ".$ex);
						}
					}
				}
			}
		}

		// get the city
		$city_name = $form->getValue($type.'_city');
		$city = $model->getModel('\core\classes\models\City')->get([
			'country_id' => $country->id,
			'state_id' => $state ? $state->id : NULL,
			'name' => $city_name,
		]);
		if (!$city) {
			try {
				$city = $model->getModel('\core\classes\models\City');
				$city->country_id = $country->id;
				$city->state_id   = $state ? $state->id : NULL;
				$city->name       = $city_name;
				$city->insert();
			}
			catch (\Exception $ex) {
				$city = $model->getModel('\core\classes\models\City')->get([
					'country_id' => $country->id,
					'state_id' => $state ? $state->id : NULL,
					'name' => $city_name,
				]);
				if (!$city) {
					throw new \ErrorException("Error creating city record: ".$ex);
				}
			}
		}

		// create the address
		$address = $model->getModel('\core\classes\models\Address');
		$address->first_name  = $form->getValue($type.'_first_name');
		$address->last_name   = $form->getValue($type.'_last_name');
		$address->email       = $form->getValue($type.'_email');
		$address->phone       = $form->getValue($type.'_phone');
		$address->line1       = $form->getValue($type.'_address_line1');
		$address->line2       = $form->getValue($type.'_address_line2');
		$address->postcode    = $form->getValue($type.'_postcode');
		$address->city_id     = $city->id;
		$address->state_id    = $state ? $state->id : NULL;
		$address->country_id  = $country->id;

		return $address;
	}

	public function receipt($reference, $status = NULL) {
		$bcrypt_cost = $this->config->siteConfig()->bcrypt_cost;
		$this->language->loadLanguageFile('customer.php');
		$this->language->loadLanguageFile('checkout.php', 'modules'.DS.'checkout');
		$this->language->loadLanguageFile('administrator/orders.php', 'modules'.DS.'checkout');
		$model = new Model($this->config, $this->database);
		$checkout = $model->getModel('\modules\checkout\classes\models\Checkout')->getByReference($reference);
		$customer = $checkout->getCustomer();

		$form = $this->getPaswordForm($checkout);
		if ($form->validate()) {
			$customer->login = $form->getValue('username');
			$customer->password = Encryption::bcrypt($form->getValue('password1'), $bcrypt_cost);
			$customer->update();
			throw new RedirectException($this->url->getUrl('Checkout', 'receipt', [$checkout->getReferenceNumber(), 'update-success']));
		}
		elseif ($form->isSubmitted()) {
			$form->setNotification('error', $this->language->get('notification_password_error'));
		}

		$transaction_ref = $checkout->getReferenceNumber();
		if ($this->config->siteConfig()->enable_analytics && $this->config->siteConfig()->enable_analytics_ecommerce) {
			$ecommerce = (object)[
				'id'          => $transaction_ref,
				'affiliation' => $this->config->siteConfig()->name,
				'revenue'     => $checkout->getGrandTotal(),
				'shipping'    => $checkout->shipping,
				'tax'         => 0,
				'currency'    => 'AUD',
				'items'       => [],
			];

			foreach ($checkout->getItems() as $item) {
				$ecommerce->items[] = (object)[
					'id'       => $transaction_ref,
					'name'     => $item->getName(),
					'sku'      => $item->getSKU(),
					'category' => $item->getItemType()->getCategoryName(),
					'price'    => $item->getSellPrice(),
					'quantity' => $item->getQuantity(),
				];
			}

			$this->layout->setTemplateData(['ecommerce' => $ecommerce]);
		}

		$data = [
			'contents' => $checkout->getItems(),
			'checkout' => $checkout,
			'customer' => $checkout->getCustomer(),
			'shipping' => $checkout->getShippingAddress(),
			'billing'  => $checkout->getBillingAddress(),
			'receipt_number' => $transaction_ref,
			'totals' => $checkout->getTotals($this->language),
			'grand_total' => $checkout->getGrandTotal(),
			'receipt_note' => $checkout->receipt_note ? 'text_'.$checkout->receipt_note : NULL,
			'created_customer' => ($this->request->session->get('anonymous_checkout_purchase') && $customer && $customer->password == ''),
			'checkout_note' => $this->request->session->get('checkout_note'),
			'form' => $form,
		];
		$template = $this->getTemplate('pages/receipt.php', $data, 'modules'.DS.'checkout');
		$this->response->setContent($template->render());
	}

	protected function getAddressForm() {
		$inputs = [
			'billing_first_name' => [
				'type' => 'string',
				'min_length' => 2,
				'max_length' => 128,
				'required' => TRUE,
				'message' => $this->language->get('error_first_name')
			],
			'billing_last_name' => [
				'type' => 'string',
				'min_length' => 2,
				'max_length' => 128,
				'required' => TRUE,
				'message' => $this->language->get('error_last_name')
			],
			'billing_phone' => [
				'type' => 'string',
				'min_length' => 2,
				'max_length' => 128,
				'required' => TRUE,
				'message' => $this->language->get('error_phone')
			],
			'billing_email' => [
				'type' => 'email',
				'required' => TRUE,
				'message' => $this->language->get('error_email')
			],
			'billing_address_line1' => [
				'type' => 'string',
				'min_length' => 2,
				'max_length' => 128,
				'required' => TRUE,
				'message' => $this->language->get('error_address_line1')
			],
			'billing_address_line2' => [
				'type' => 'string',
				'min_length' => 2,
				'max_length' => 128,
				'required' => FALSE,
				'message' => $this->language->get('error_address_line2')
			],
			'billing_postcode' => [
				'type' => 'string',
				'min_length' => 2,
				'max_length' => 10,
				'required' => TRUE,
				'message' => $this->language->get('error_postcode')
			],
			'billing_city' => [
				'type' => 'string',
				'min_length' => 1,
				'max_length' => 128,
				'required' => TRUE,
				'message' => $this->language->get('error_city')
			],
			'billing_state' => [
				'type' => 'string',
				'min_length' => 1,
				'max_length' => 128,
				'required' => FALSE,
				'message' => $this->language->get('error_state')
			],
			'billing_country' => [
				'type' => 'integer',
				'required' => TRUE,
				'message' => $this->language->get('error_country')
			],
			'same_as_billing' => [
				'type' => 'integer',
				'required' => FALSE,
				'message' => $this->language->get('error_same_as_billing')
			],
			'shipping_first_name' => [
				'type' => 'string',
				'min_length' => 2,
				'max_length' => 128,
				'required' => TRUE,
				'message' => $this->language->get('error_first_name')
			],
			'shipping_last_name' => [
				'type' => 'string',
				'min_length' => 2,
				'max_length' => 128,
				'required' => TRUE,
				'message' => $this->language->get('error_last_name')
			],
			'shipping_phone' => [
				'type' => 'string',
				'min_length' => 2,
				'max_length' => 128,
				'required' => TRUE,
				'message' => $this->language->get('error_phone')
			],
			'shipping_email' => [
				'type' => 'email',
				'required' => TRUE,
				'message' => $this->language->get('error_email')
			],
			'shipping_address_line1' => [
				'type' => 'string',
				'min_length' => 2,
				'max_length' => 128,
				'required' => TRUE,
				'message' => $this->language->get('error_address_line1')
			],
			'shipping_address_line2' => [
				'type' => 'string',
				'min_length' => 2,
				'max_length' => 128,
				'required' => FALSE,
				'message' => $this->language->get('error_address_line2')
			],
			'shipping_postcode' => [
				'type' => 'string',
				'min_length' => 2,
				'max_length' => 10,
				'required' => TRUE,
				'message' => $this->language->get('error_postcode')
			],
			'shipping_city' => [
				'type' => 'string',
				'min_length' => 1,
				'max_length' => 128,
				'required' => TRUE,
				'message' => $this->language->get('error_city')
			],
			'shipping_state' => [
				'type' => 'string',
				'min_length' => 1,
				'max_length' => 128,
				'required' => FALSE,
				'message' => $this->language->get('error_state')
			],
			'shipping_country' => [
				'type' => 'integer',
				'required' => TRUE,
				'message' => $this->language->get('error_country')
			],
		];

		return new FormValidator($this->request, 'form-checkout-address', $inputs);
	}

	protected function getPaswordForm($checkout) {
		$model = new Model($this->config, $this->database);

		$inputs = [
			'username' => [
				'type' => 'string',
				'min_length' => 6,
				'max_length' => 32,
				'message' => $this->language->get('error_username')
			],
			'password1' => [
				'type' => 'string',
				'min_length' => 6,
				'max_length' => 32,
				'message' => $this->language->get('error_password')
			],
			'password2' => [
				'type' => 'string',
				'min_length' => 6,
				'max_length' => 32,
				'message' => $this->language->get('error_password')
			]
		];

		$validators = [
			'username' => [
				[
					'type'     => 'function',
					'message'  => $this->language->get('error_username_taken'),
					'function' => function($value) use ($model, $checkout) {
						$customer = $model->getModel('core\classes\models\Customer');
						$customer = $customer->get(['login' => $value]);
						if ($customer && $customer->email != $checkout->getCustomer()->email) {
							return FALSE;
						}
						else {
							return TRUE;
						}
					}
				],
			],
			'password1' => [
				[
					'type'    => 'params-equal',
					'param'   => 'password2',
					'message' => $this->language->get('error_password_mismatch'),
				],
				[
					'type'      => 'regex',
					'regex'     => '\d',
					'modifiers' => '',
					'message'   => $this->language->get('error_password_number'),
				],
			],
		];

		return new FormValidator($this->request, 'form-password', $inputs, $validators);
	}

}
