<?php

namespace modules\checkout\controllers\administrator;

use core\classes\renderable\Controller;
use core\classes\Model;
use core\classes\Pagination;
use core\classes\FormValidator;

class Orders extends Controller {

	protected $show_admin_layout = TRUE;

	protected $permissions = [
		'index' => ['administrator'],
		'editOrder' => ['administrator'],
	];

	public function index($message = NULL) {
		$this->language->loadLanguageFile('administrator/orders.php', 'modules'.DS.'checkout');
		$form_search = $this->getSearchForm();

		$model    = new Model($this->config, $this->database);
		$checkout = $model->getModel('\modules\checkout\classes\models\Checkout');
		$pagination = new Pagination($this->request, 'name');

		$params = ['site_id' => ['type'=>'in', 'value'=>$this->allowedSiteIDs()]];
		if ($form_search->validate()) {
			$values = $form_search->getSubmittedValues();
			foreach ($values as $name => $value) {
				if (preg_match('/^search_(first_name|last_name|email|login)$/', $name, $matches) && $value != '') {
					$value = strtolower($value);
					$params['customer_'.$matches[1]] = ['type'=>'like', 'value'=>'%'.$value.'%'];
				}
				elseif ($name == 'search_reference' && $value != '') {
					$params['id'] = $checkout->decodeReferenceNumber($value);
				}
			}
		}

		$orders   = $checkout->getMulti($params, $pagination->getOrdering(), $pagination->getLimitOffset());
		$pagination->setRecordCount($checkout->getCount($params));

		$message_js = NULL;
		switch($message) {
		}

		$data = [
			'form' => $form_search,
			'orders' => $orders,
			'pagination' => $pagination,
			'message_js' => $message_js,
		];

		$template = $this->getTemplate('pages/administrator/list_orders.php', $data, 'modules'.DS.'checkout');
		$this->response->setContent($template->render());
	}

	public function editOrder($order_id) {
		$this->language->loadLanguageFile('customer.php');
		$this->language->loadLanguageFile('checkout.php', 'modules'.DS.'checkout');
		$this->language->loadLanguageFile('administrator/orders.php', 'modules'.DS.'checkout');

		$model = new Model($this->config, $this->database);
		$checkout = $model->getModel('\modules\checkout\classes\models\Checkout')->get([
			'id' => (int)$order_id,
		]);

		$data = [
			'checkout' => $checkout,
			'customer' => $checkout->getCustomer(),
			'shipping' => $checkout->getShippingAddress(),
			'billing'  => $checkout->getBillingAddress(),
		];
		$template = $this->getTemplate('pages/administrator/edit_order.php', $data, 'modules'.DS.'checkout');
		$this->response->setContent($template->render());
	}

	protected function getSearchForm() {
		$model = new Model($this->config, $this->database);

		$inputs = [
			'search_reference' => [
				'type' => 'string',
				'required' => FALSE,
				'max_length' => 32,
				'message' => $this->language->get('error_search_reference'),
			],
			'search_first_name' => [
				'type' => 'string',
				'required' => FALSE,
				'max_length' => 256,
				'message' => $this->language->get('error_search_first_name'),
			],
			'search_last_name' => [
				'type' => 'string',
				'required' => FALSE,
				'max_length' => 256,
				'message' => $this->language->get('error_search_last_name'),
			],
			'search_login' => [
				'type' => 'string',
				'required' => FALSE,
				'max_length' => 32,
				'message' => $this->language->get('error_search_login'),
			],
			'search_email' => [
				'type' => 'string',
				'required' => FALSE,
				'max_length' => 256,
				'message' => $this->language->get('error_search_email'),
			],
		];

		$validators = [
			'search_reference' => [
				[
					'type'     => 'function',
					'message'  => $this->language->get('error_search_reference'),
					'function' => function($value) use ($model) {
						$checkout = $model->getModel('\modules\checkout\classes\models\Checkout');
						if ($checkout->decodeReferenceNumber($value)) {
							return TRUE;
						}
						else {
							return FALSE;
						}
					}
				],
			],
		];

		return new FormValidator($this->request, 'form-orders-search', $inputs, $validators);
	}

	public function report() {
		$this->language->loadLanguageFile('administrator/skeleton.php', 'core'.DS.'modules'.DS.'skeleton');
		$template = $this->getTemplate('pages/administrator/skeleton.php', [], 'core'.DS.'modules'.DS.'skeleton');
		$this->response->setContent($template->render());
	}

}