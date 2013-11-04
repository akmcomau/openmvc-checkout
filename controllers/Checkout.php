<?php

namespace modules\checkout\controllers;

use core\classes\renderable\Controller;
use core\classes\Model;
use core\classes\Pagination;
use core\classes\FormValidator;
use modules\checkout\classes\Cart as CartContents;

class Checkout extends Controller {

	protected $permissions = [
		'index' => ['customer'],
	];

	public function index() {
		$this->language->loadLanguageFile('checkout.php', 'modules'.DS.'checkout');
		$cart = new CartContents($this->config, $this->database, $this->request);

		$subtotal = 0;
		$contents = $cart->getContents();
		foreach ($contents as $item) {
			$sub_total = $item->getQuantity() * $item->getPrice();
			$item->setTotal($sub_total);
			$subtotal += money_format('%^!n', $sub_total);
		}

		$totals = [];
		/* TODO Add shipping and other totals here */

		if (count($totals) == 0) {
			$totals[$this->language->get('total')] = $subtotal;
		}
		else {
			$totals = array_merge($totals, [$this->language->get('total') => $subtotal]);
		}

		$data = [
			'contents' => $contents,
			'totals' => $totals,
		];
		$template = $this->getTemplate('pages/checkout.php', $data, 'modules'.DS.'checkout');
		$this->response->setContent($template->render());
	}

}