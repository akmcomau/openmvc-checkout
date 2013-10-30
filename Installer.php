<?php

namespace modules\checkout;

use ErrorException;
use core\classes\Config;
use core\classes\Database;
use core\classes\Language;
use core\classes\Model;
use core\classes\Menu;

class Installer {
	protected $config;
	protected $database;

	public function __construct(Config $config, Database $database) {
		$this->config = $config;
		$this->database = $database;
	}

	public function install() {
		$model = new Model($this->config, $this->database);

		$table = $model->getModel('\\modules\\checkout\\classes\\models\\PaymentType');
		$table->createTable();
		$table->createIndexes();
		$table->createForeignKeys();

		$table = $model->getModel('\\modules\\checkout\\classes\\models\\CheckoutStatus');
		$table->createTable();
		$table->createIndexes();
		$table->createForeignKeys();
		$table->insertInitalData('\\modules\\checkout\\classes\\models\\data\\CheckoutStatus');

		$table = $model->getModel('\\modules\\checkout\\classes\\models\\Checkout');
		$table->createTable();
		$table->createIndexes();
		$table->createForeignKeys();

		$table = $model->getModel('\\modules\\checkout\\classes\\models\\CheckoutItem');
		$table->createTable();
		$table->createIndexes();
		$table->createForeignKeys();
	}

	public function uninstall() {
		$model = new Model($this->config, $this->database);

		$table = $model->getModel('\\modules\\checkout\\classes\\models\\CheckoutItem');
		$table->dropTable();
		$table = $model->getModel('\\modules\\checkout\\classes\\models\\Checkout');
		$table->dropTable();
		$table = $model->getModel('\\modules\\checkout\\classes\\models\\CheckoutStatus');
		$table->dropTable();
		$table = $model->getModel('\\modules\\checkout\\classes\\models\\PaymentType');
		$table->dropTable();
	}

	public function enable() {
		$language = new Language($this->config);
		$language->loadLanguageFile('administrator/checkout.php', DS.'modules'.DS.'checkout');

		$layout_strings = $language->getFile('administrator/layout.php');
		$layout_strings['checkout_module_orders'] = $language->get('orders');
		$language->updateFile('administrator/layout.php', $layout_strings);

		$main_menu = new Menu($this->config, $language);
		$main_menu->loadMenu('menu_admin_main.php');
		$main_menu->insert_menu(['content'], 'orders', [
			'controller' => 'administrator/Orders',
			'method' => 'index',
			'icon' => 'icon-suitcase',
			'text_tag' => 'checkout_module_orders',
			'children' => [
				'orders_list' => [
					'controller' => 'administrator/Orders',
					'method' => 'index',
				],
				'orders_report' => [
					'controller' => 'administrator/Orders',
					'method' => 'report',
				],
			],
		]);


		$main_menu->update();
	}

	public function disable() {
		$language = new Language($this->config);
		$language->loadLanguageFile('administrator/block_question.php', DS.'modules'.DS.'block_question');

		$layout_strings = $language->getFile('administrator/layout.php');
		unset($layout_strings['checkout_module_orders']);
		$language->updateFile('administrator/layout.php', $layout_strings);

		// Remove some menu items to the admin menu
		$main_menu = new Menu($this->config, $language);
		$main_menu->loadMenu('menu_admin_main.php');
		$menu = $main_menu->getMenuData();

		unset($menu['orders']);

		$main_menu->setMenuData($menu);
		$main_menu->update();
	}
}