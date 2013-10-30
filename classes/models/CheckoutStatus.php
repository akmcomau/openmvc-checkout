<?php

namespace modules\checkout\classes\models;

use core\classes\Model;

class CheckoutStatus extends Model {

	protected $table       = 'checkout_status';
	protected $primary_key = 'checkout_status_id';
	protected $columns     = [
		'checkout_status_id' => [
			'data_type'      => 'int',
			'auto_increment' => TRUE,
			'null_allowed'   => FALSE,
		],
		'checkout_status_name' => [
			'data_type'      => 'text',
			'data_length'    => 32,
			'null_allowed'   => FALSE,
		],
		'checkout_status_description' => [
			'data_type'      => 'text',
			'data_length'    => 256,
			'null_allowed'   => FALSE,
		],
	];
}
