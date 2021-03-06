<?php

namespace modules\checkout\controllers\customer;

use core\classes\renderable\Controller;
use core\classes\Model;
use core\classes\Pagination;
use core\classes\FormValidator;

class Orders extends Controller {

	protected $permissions = [
		'index' => ['customer'],
		'view' => ['customer'],
	];

	public function getAllUrls($include_filter = NULL, $exclude_filter = NULL) {
		return [];
	}

	public function index() {
		$this->language->loadLanguageFile('administrator/orders.php', 'modules'.DS.'checkout');

		$model    = new Model($this->config, $this->database);
		$checkout = $model->getModel('\modules\checkout\classes\models\Checkout');
		$pagination = new Pagination($this->request, 'created', 'desc');

		$status = $this->model->getModel('\modules\checkout\classes\models\CheckoutStatus');

		$params = [
			'customer_id' => $this->authentication->getCustomerID(),
			'status_id' => ['type' => '!=', 'value' => $status->getStatusId('Pending')],
		];
		$orders = $checkout->getMulti($params, $pagination->getOrdering(), $pagination->getLimitOffset());
		$pagination->setRecordCount($checkout->getCount($params));

		$message_js = NULL;
		switch($message) {
		}

		$data = [
			'orders' => $orders,
			'pagination' => $pagination,
			'message_js' => $message_js,
		];

		$template = $this->getTemplate('pages/customer/orders.php', $data, 'modules'.DS.'checkout');
		$this->response->setContent($template->render());
	}

	public function view($reference) {
		$this->language->loadLanguageFile('customer.php');
		$this->language->loadLanguageFile('checkout.php', 'modules'.DS.'checkout');
		$this->language->loadLanguageFile('administrator/orders.php', 'modules'.DS.'checkout');

		$model = new Model($this->config, $this->database);
		$checkout = $model->getModel('\modules\checkout\classes\models\Checkout')->getByReference($reference);
		if (!$checkout || $checkout->customer_id != $this->authentication->getCustomerID()) {
			throw new RedirectException($this->getUrl('administrator/Error', 'error_404'));
		}

		$this->config->setLocale($checkout->locale);
		$data = [
			'checkout' => $checkout,
			'customer' => $checkout->getCustomer(),
			'shipping' => $checkout->getShippingAddress(),
			'billing'  => $checkout->getBillingAddress(),
			'grand_total' => $checkout->getGrandTotal(),
		];
		$template = $this->getTemplate('pages/customer/view_order.php', $data, 'modules'.DS.'checkout');
		$this->response->setContent($template->render());
	}
}
