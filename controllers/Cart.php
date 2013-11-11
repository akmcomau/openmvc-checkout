<?php

namespace modules\checkout\controllers;

use core\classes\exceptions\RedirectException;
use core\classes\renderable\Controller;
use core\classes\Model;
use core\classes\Pagination;
use core\classes\FormValidator;
use modules\checkout\classes\Cart as CartContents;

class Cart extends Controller {

	public function index() {
		$this->language->loadLanguageFile('checkout.php', 'modules'.DS.'checkout');
		$cart = new CartContents($this->config, $this->database, $this->request);

		if (isset($this->request->request_params['update-cart'])) {
			if (is_array($this->request->requestParam('quantity'))) {
				foreach ($this->request->requestParam('quantity') as $item => $quantity) {
					if (preg_match('/^(\w+):(\d+)$/', $item, $matches)) {
						$type = $matches[1];
						$id   = $matches[2];
						$cart->update($type, $id, $quantity);
					}
				}
			}
			if (is_array($this->request->requestParam('remove'))) {
				foreach ($this->request->requestParam('remove') as $item) {
					if (preg_match('/^(\w+):(\d+)$/', $item, $matches)) {
						$type = $matches[1];
						$id   = $matches[2];
						$cart->remove($type, $id);
					}
				}
			}
			throw new RedirectException($this->url->getUrl('Cart'));
		}
		elseif (isset($this->request->request_params['checkout'])) {
			throw new RedirectException($this->url->getUrl('Checkout'));
		}

		$data = [
			'contents' => $cart->getContents(),
			'total' => $cart->getCartTotal(),
		];
		$template = $this->getTemplate('pages/cart.php', $data, 'modules'.DS.'checkout');
		$this->response->setContent($template->render());
	}

	public function clear() {
		$cart = new CartContents($this->config, $this->database, $this->request);
		$cart->clear();
		throw new RedirectException($this->url->getUrl('Cart'));
	}

	public function add($type, $id, $quantity = 1) {
		$cart = new CartContents($this->config, $this->database, $this->request);
		$cart->add($type, $id, $quantity);
		throw new RedirectException($this->url->getUrl('Cart'));
	}

	public function update($type, $id, $quantity) {
		$cart = new CartContents($this->config, $this->database, $this->request);
		$cart->update($type, $id, $quantity);
		throw new RedirectException($this->url->getUrl('Cart'));
	}

	public function remove($type, $id) {
		$cart = new CartContents($this->config, $this->database, $this->request);
		$cart->remove($type, $id);
		throw new RedirectException($this->url->getUrl('Cart'));
	}

}