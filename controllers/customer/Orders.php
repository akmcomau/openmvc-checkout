<?php

namespace modules\checkout\controllers\customer;

use core\classes\renderable\Controller;
use core\classes\Model;
use core\classes\Pagination;
use core\classes\FormValidator;

class Orders extends Controller {

	protected $permissions = [
		'index' => ['customer'],
	];

	public function index() {
		$this->language->loadLanguageFile('administrator/skeleton.php', 'core'.DS.'modules'.DS.'skeleton');
		$template = $this->getTemplate('pages/administrator/skeleton.php', [], 'core'.DS.'modules'.DS.'skeleton');
		$this->response->setContent($template->render());
	}

}