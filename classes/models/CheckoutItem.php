<?php

namespace modules\checkout\classes\models;

use core\classes\Model;

class CheckoutItem extends Model {

	protected $table       = 'checkout_item';
	protected $primary_key = 'checkout_item_id';
	protected $columns     = [
		'checkout_item_id' => [
			'data_type'      => 'bigint',
			'auto_increment' => TRUE,
			'null_allowed'   => FALSE,
		],
		'checkout_id' => [
			'data_type'      => 'bigint',
			'null_allowed'   => FALSE,
		],
		'checkout_item_type' => [
			'data_type'      => 'text',
			'data_length'    => 32,
			'null_allowed'   => FALSE,
		],
		'checkout_item_type_id' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
		'checkout_item_price' => [
			'data_type'      => 'numeric',
			'data_length'    => [6, 4],
			'null_allowed'   => FALSE,
		],
		'checkout_item_quantity' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
	];

	protected $indexes = [
		'checkout_id',
		'checkout_item_type',
		'checkout_item_type_id',
	];

	protected $foreign_keys = [
		'checkout_id' => ['checkout', 'checkout_id'],
	];
}
