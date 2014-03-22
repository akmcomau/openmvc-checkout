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

			$data = [
				'login' => $form_login,
				'register' => $form_register,
				'controller' => 'Checkout',
				'method' => 'index',
				'params' => NULL,
				'anonymous_checkout_enabled' => $module_config->anonymous_checkout,
				'anonymous_checkout' => $anonymous_checkout,
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
			$code   = $this->request->requestParam('payment_method');
			$method = $this->config->siteConfig()->checkout->payment_methods->$code;
			throw new SoftRedirectException($method->public, 'payment');
		}

		$this->language->loadLanguageFile('checkout.php', 'modules'.DS.'checkout');

		$data = [
			'contents' => $cart->getContents(),
			'totals' => $cart->getTotals($this->language),
			'grand_total' => $cart->getGrandTotal(),
			'payment_types' => $this->config->siteConfig()->checkout->payment_methods,
		];
		$template = $this->getTemplate('pages/checkout.php', $data, 'modules'.DS.'checkout');
		$this->response->setContent($template->render());
	}

	public function receipt($reference, $status = NULL) {
		$bcrypt_cost = $this->config->siteConfig()->bcrypt_cost;
		$this->language->loadLanguageFile('customer.php');
		$this->language->loadLanguageFile('checkout.php', 'modules'.DS.'checkout');
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
					'price'    => $item->getPrice(),
					'quantity' => $item->getQuantity(),
				];
			}

			$this->layout->setTemplateData(['ecommerce' => $ecommerce]);
		}

		$data = [
			'contents' => $checkout->getItems(),
			'receipt_number' => $transaction_ref,
			'totals' => $checkout->getTotals($this->language),
			'grand_total' => $checkout->getGrandTotal(),
			'created_customer' => ($this->request->session->get('anonymous_checkout_purchase') && $customer->password == ''),
			'form' => $form,
		];
		$template = $this->getTemplate('pages/receipt.php', $data, 'modules'.DS.'checkout');
		$this->response->setContent($template->render());
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
