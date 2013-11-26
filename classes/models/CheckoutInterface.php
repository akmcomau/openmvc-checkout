<?php

namespace modules\checkout\classes\models;

interface CheckoutInterface {
	public function getCheckoutItem();
	public function getSellPrice();
	public function getCostPrice();
}