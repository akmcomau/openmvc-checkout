<?php

namespace modules\checkout\classes\models\data;

use modules\checkout\classes\models as models;

class CheckoutStatus extends models\CheckoutStatus {

	public function getRecords() {
		return [
			[
				'name'        => 'Processing',
				'description' => 'Order placed by customer',
			],
			[
				'name'        => 'Shipped',
				'description' => 'Order has been shipped',
			],
			[
				'name'        => 'Complete',
				'description' => 'Order is processed and complete',
			],
			[
				'name'        => 'Cancelled',
				'description' => 'Order has been cancelled',
			],
		];
	}
}
