<?php

namespace modules\checkout\controllers\administrator;

use core\classes\renderable\Controller;
use core\classes\Model;
use core\classes\Pagination;
use core\classes\FormValidator;

class Checkout extends Controller {

	protected $show_admin_layout = TRUE;

	protected $permissions = [
		'config' => ['administrator'],
	];

	public function config() {
		$this->language->loadLanguageFile('administrator/skeleton.php', 'core'.DS.'modules'.DS.'skeleton');
		$template = $this->getTemplate('pages/administrator/skeleton.php', [], 'core'.DS.'modules'.DS.'skeleton');
		$this->response->setContent($template->render());
	}

}