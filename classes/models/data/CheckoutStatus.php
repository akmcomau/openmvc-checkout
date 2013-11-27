<?php

namespace modules\checkout\classes\models\data;

use modules\checkout\classes\models as models;

class CheckoutStatus extends models\CheckoutStatus {

	public function getRecords() {
		return [
			[
				'name'        => 'Pending',
				'description' => 'Pending payment from customer',
				'pending'     => TRUE,
				'processing'  => FALSE,
				'successful'  => FALSE,
				'failed'      => FALSE,
			],
			[
				'name'        => 'Processing',
				'description' => 'Order placed by customer',
				'pending'     => FALSE,
				'processing'  => TRUE,
				'successful'  => FALSE,
				'failed'      => FALSE,
			],
			[
				'name'        => 'Shipped',
				'description' => 'Order has been shipped',
				'pending'     => FALSE,
				'processing'  => FALSE,
				'successful'  => TRUE,
				'failed'      => FALSE,
			],
			[
				'name'        => 'Complete',
				'description' => 'Order is processed and complete',
				'pending'     => FALSE,
				'processing'  => FALSE,
				'successful'  => TRUE,
				'failed'      => FALSE,
			],
			[
				'name'        => 'Cancelled',
				'pending'     => FALSE,
				'processing'  => FALSE,
				'successful'  => FALSE,
				'failed'      => TRUE,
				'description' => 'Order has been cancelled',
			],
			[
				'name'        => 'Failed',
				'description' => 'Order has been cancelled',
				'pending'     => FALSE,
				'processing'  => FALSE,
				'successful'  => FALSE,
				'failed'      => TRUE,
			],
		];
	}
}
