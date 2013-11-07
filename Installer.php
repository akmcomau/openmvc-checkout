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

		$table = $model->getModel('\\modules\\checkout\\classes\\models\\CheckoutStatus');
		$table->createTable();
		$table->createIndexes();
		$table->createForeignKeys();
		$table->insertInitalData('\\modules\\checkout\\classes\\models\\data\\CheckoutStatus');

		$table = $model->getModel('\\modules\\checkout\\classes\\models\\Checkout');
		$table->createTable();
		$table->createIndexes();
		$table->createForeignKeys();

		$table = $model->getModel('\\modules\\checkout\\classes\\models\\CheckoutDetail');
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
		$table = $model->getModel('\\modules\\checkout\\classes\\models\\CheckoutDetail');
		$table->dropTable();
		$table = $model->getModel('\\modules\\checkout\\classes\\models\\Checkout');
		$table->dropTable();
		$table = $model->getModel('\\modules\\checkout\\classes\\models\\CheckoutStatus');
		$table->dropTable();
	}

	public function enable() {
		$language = new Language($this->config);
		$language->loadLanguageFile('administrator/checkout.php', DS.'modules'.DS.'checkout');

		$layout_strings = $language->getFile('administrator/layout.php');
		$layout_strings['checkout_module_checkout'] = $language->get('checkout');
		$language->updateFile('administrator/layout.php', $layout_strings);

		$main_menu = new Menu($this->config, $language);
		$main_menu->loadMenu('menu_admin_main.php');
		$main_menu->insert_menu(['content'], 'checkout', [
			'controller' => 'administrator/Orders',
			'method' => 'index',
			'icon' => 'icon-shopping-cart',
			'text_tag' => 'checkout_module_checkout',
			'children' => [
				'checkout_orders' => [
					'controller' => 'administrator/Orders',
					'method' => 'index',
					'children' => [
						'checkout_orders_list' => [
							'controller' => 'administrator/Orders',
							'method' => 'index',
						],
						'checkout_orders_report' => [
							'controller' => 'administrator/Orders',
							'method' => 'report',
						],
					],
				],
			],
		]);
		$main_menu->update();

		$user_menu = new Menu($this->config, $language);
		$user_menu->loadMenu('menu_public_user.php');
		$user_menu->insert_menu(['account', 'account_password'], 'account_orders', [
			'controller' => 'customer/Orders',
			'method' => 'index',
		]);
		$user_menu->update();

		// The checkout configuation, other modules can modify this config
		$config = $this->config->getSiteConfig();
		$config['sites'][$this->config->getSiteDomain()]['checkout'] = [
			'tax_types' => [],
			'shipping_methods' => [],
			'payment_methods' => [],
			'special_offers' => [],
			'item_types' => [],
		];
		$this->config->setSiteConfig($config);
	}

	public function disable() {
		$language = new Language($this->config);
		$language->loadLanguageFile('administrator/checkout.php', DS.'modules'.DS.'checkout');

		$layout_strings = $language->getFile('administrator/layout.php');
		unset($layout_strings['checkout_module_checkout']);
		$language->updateFile('administrator/layout.php', $layout_strings);

		// Remove some menu items to the admin menu
		$main_menu = new Menu($this->config, $language);
		$main_menu->loadMenu('menu_admin_main.php');
		$menu = $main_menu->getMenuData();
		unset($menu['checkout']);
		$main_menu->setMenuData($menu);
		$main_menu->update();

		$user_menu = new Menu($this->config, $language);
		$user_menu->loadMenu('menu_public_user.php');
		$menu = $user_menu->getMenuData();
		unset($menu['account']['children']['account_orders']);
		$user_menu->setMenuData($menu);
		$user_menu->update();

		$config = $this->config->getSiteConfig();
		unset($config['sites'][$this->config->getSiteDomain()]['checkout']);
		$this->config->setSiteConfig($config);
	}
}