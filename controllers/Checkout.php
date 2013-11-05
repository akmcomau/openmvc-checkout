<?php

namespace modules\checkout\controllers;

use core\classes\exceptions\SoftRedirectException;
use core\classes\renderable\Controller;
use core\classes\Model;
use core\classes\Encryption;
use core\classes\Pagination;
use core\classes\FormValidator;
use modules\checkout\classes\Cart as CartContents;

class Checkout extends Controller {

	protected $permissions = [
		'index' => ['customer'],
		'receipt' => ['customer'],
	];

	public function index() {
		if (!is_null($this->request->requestParam('payment')) && !is_null(!is_null($this->request->requestParam('payment_method')))) {
			$code   = $this->request->requestParam('payment_method');
			$method = $this->config->siteConfig()->checkout->payment_methods->$code;
			throw new SoftRedirectException($method->controller, 'payment');
		}

		$this->language->loadLanguageFile('checkout.php', 'modules'.DS.'checkout');
		$cart = new CartContents($this->config, $this->database, $this->request);

		$data = [
			'contents' => $cart->getContents(),
			'totals' => $cart->getTotals($this->language),
			'payment_types' => $this->config->siteConfig()->checkout->payment_methods,
		];
		$template = $this->getTemplate('pages/checkout.php', $data, 'modules'.DS.'checkout');
		$this->response->setContent($template->render());
	}

	public function receipt($checkout_id) {
		$this->language->loadLanguageFile('checkout.php', 'modules'.DS.'checkout');
		$model = new Model($this->config, $this->database);
		$checkout_id = Encryption::defuscate($checkout_id, $this->config->siteConfig()->secret);
		$checkout = $model->getModel('\modules\checkout\classes\models\Checkout')->get(['id' => $checkout_id]);

		$data = [
			'contents' => $checkout->getItems(),
			'receipt_number' => Encryption::obfuscate($checkout->id, $this->config->siteConfig()->secret),
			'totals' => $checkout->getTotals($this->language),
		];
		$template = $this->getTemplate('pages/receipt.php', $data, 'modules'.DS.'checkout');
		$this->response->setContent($template->render());
	}

}