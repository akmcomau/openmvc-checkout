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
	];

	public function index($message = NULL) {
		$this->language->loadLanguageFile('administrator/orders.php', 'modules'.DS.'checkout');
		$form_search = $this->getSearchForm();

		$pagination = new Pagination($this->request, 'name');

		$params = ['site_id' => ['type'=>'in', 'value'=>$this->allowedSiteIDs()]];
		if ($form_search->validate()) {
			$values = $form_search->getSubmittedValues();
			foreach ($values as $name => $value) {
				if (preg_match('/^search_(first_name|last_name|email|login)$/', $name, $matches) && $value != '') {
					$value = strtolower($value);
					$params['customer_'.$matches[1]] = ['type'=>'like', 'value'=>'%'.$value.'%'];
				}
			}
		}

		$model    = new Model($this->config, $this->database);
		$checkout = $model->getModel('\modules\checkout\classes\models\Checkout');
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

	protected function getSearchForm() {
		$inputs = [
			'search_name' => [
				'type' => 'string',
				'required' => FALSE,
				'max_length' => 256,
				'message' => ''//$this->language->get('error_search_name'),
			],
		];

		return new FormValidator($this->request, 'form-subscription-type-search', $inputs);
	}

	public function report() {
		$this->language->loadLanguageFile('administrator/skeleton.php', 'core'.DS.'modules'.DS.'skeleton');
		$template = $this->getTemplate('pages/administrator/skeleton.php', [], 'core'.DS.'modules'.DS.'skeleton');
		$this->response->setContent($template->render());
	}

}