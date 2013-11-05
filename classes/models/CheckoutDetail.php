<?php

namespace modules\checkout\classes\models;

use core\classes\Model;

class CheckoutDetail extends Model {

	protected $table       = 'checkout_detail';
	protected $primary_key = 'checkout_detail_id';
	protected $columns     = [
		'checkout_detail_id' => [
			'data_type'      => 'bigint',
			'auto_increment' => TRUE,
			'null_allowed'   => FALSE,
		],
		'checkout_id' => [
			'data_type'      => 'bigint',
			'null_allowed'   => FALSE,
		],
		'checkout_detail_type' => [
			'data_type'      => 'text',
			'data_length'    => 32,
			'null_allowed'   => TRUE,
		],
		'checkout_detail_type_code' => [
			'data_type'      => 'text',
			'data_length'    => 32,
			'null_allowed'   => FALSE,
		],
		'checkout_detail_amount' => [
			'data_type'      => 'numeric',
			'data_length'    => [6, 4],
			'null_allowed'   => FALSE,
		],
	];

	protected $indexes = [
		'checkout_id',
		'checkout_detail_type',
		'checkout_detail_type_code',
	];

	protected $foreign_keys = [
		'checkout_id' => ['checkout', 'checkout_id'],
	];
}
