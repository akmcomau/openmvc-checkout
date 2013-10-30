<?php

namespace modules\checkout\classes\models;

use core\classes\Model;

class PaymentType extends Model {

	protected $table       = 'payment_type';
	protected $primary_key = 'payment_type_id';
	protected $columns     = [
		'payment_type_id' => [
			'data_type'      => 'int',
			'auto_increment' => TRUE,
			'null_allowed'   => FALSE,
		],
		'payment_type_name' => [
			'data_type'      => 'text',
			'data_length'    => 32,
			'null_allowed'   => FALSE,
		],
		'payment_type_description' => [
			'data_type'      => 'text',
			'data_length'    => 256,
			'null_allowed'   => FALSE,
		],
	];
}
