<?php

namespace modules\checkout\controllers;

use core\classes\exceptions\RedirectException;
use core\classes\renderable\Controller;
use core\classes\Model;
use core\classes\Pagination;
use core\classes\FormValidator;
use modules\checkout\classes\Cart as CartContents;

class Cart extends Controller {

	public function getAllUrls($include_filter = NULL, $exclude_filter = NULL) {
		return [];
	}

	public function index() {
		$module_config = $this->config->moduleConfig('\modules\checkout');
		$this->language->loadLanguageFile('checkout.php', 'modules'.DS.'checkout');
		$cart = new CartContents($this->config, $this->database, $this->request);

		if (isset($this->request->request_params['update-cart']) || isset($this->request->request_params['checkout'])) {
			if (is_array($this->request->requestParam('quantity'))) {
				foreach ($this->request->requestParam('quantity') as $item => $quantity) {
					if (preg_match('/^(\w+):(\d+)$/', $item, $matches)) {
						$type = $matches[1];
						$id   = $matches[2];
						$cart->update($type, $id, $quantity);
						$this->request->addEvent('Update Cart Item', $id, $quantity, $type);
					}
				}
			}
			if (is_array($this->request->requestParam('remove'))) {
				foreach ($this->request->requestParam('remove') as $item) {
					if (preg_match('/^(\w+):(\d+)$/', $item, $matches)) {
						$type = $matches[1];
						$id   = $matches[2];
						$cart->remove($type, $id);
						$this->request->addEvent('Remove from Cart', $id, 0, $type);
					}
				}
			}

			if (isset($this->request->request_params['checkout'])) {
				throw new RedirectException($this->url->getUrl('Checkout'));
			}
			else {
				throw new RedirectException($this->url->getUrl('Cart'));
			}
		}

		$data = [
			'contents' => $cart->getContents(),
			'total' => $cart->getCartSellTotal(),
			'totals' => $cart->getTotals($this->language, FALSE),
		];

		// is also purchased enabled
		if ($module_config->enable_upsell) {
			$checkout = $this->model->getModel('\modules\checkout\classes\models\Checkout');
			$data['also_purchased'] = $checkout->alsoPurchased($cart->getContents());
		}

		$template = $this->getTemplate('pages/cart.php', $data, 'modules'.DS.'checkout');
		$this->response->setContent($template->render());
	}

	public function cartHeader() {
		$this->language->loadLanguageFile('checkout.php', 'modules'.DS.'checkout');
		$cart = new CartContents($this->config, $this->database, $this->request);

		$data = [
			'contents' => $cart->getContents(),
			'total' => $cart->getCartSellTotal(),
		];
		$template = $this->getTemplate('pages/cart_header.php', $data, 'modules'.DS.'checkout');
		$this->response->setJsonContent($this, json_encode([
			'success' => TRUE,
			'content' => $template->render()
		]));
	}

	public function clear() {
		$cart = new CartContents($this->config, $this->database, $this->request);
		$cart->clear();
		throw new RedirectException($this->url->getUrl('Cart'));
	}

	public function add($type, $id, $quantity = 1) {
		$cart = new CartContents($this->config, $this->database, $this->request);
		$this->request->addEvent('Add to Cart', $id, $quantity, $type);
		$cart->add($type, $id, $quantity);
		throw new RedirectException($this->url->getUrl('Cart'));
	}

	public function update($type, $id, $quantity) {
		$cart = new CartContents($this->config, $this->database, $this->request);
		$cart->update($type, $id, $quantity);
		$this->request->addEvent('Update Cart Item', $id, $quantity, $type);
		throw new RedirectException($this->url->getUrl('Cart'));
	}

	public function remove($type, $id) {
		$cart = new CartContents($this->config, $this->database, $this->request);
		$cart->remove($type, $id);
		$this->request->addEvent('Remove from Cart', $id, 0, $type);
		throw new RedirectException($this->url->getUrl('Cart'));
	}

}
