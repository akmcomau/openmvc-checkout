<?php

namespace modules\checkout\classes\models;

use modules\checkout\classes\models\ItemInterface;
use modules\checkout\classes\models\Checkout;
use modules\checkout\classes\models\CheckoutItem;

interface ItemInterface {
	public function getType();
	public function allowMultiple();
	public function getMaxQuantity();
	public function getName();
	public function getSKU();
	public function purchase(Checkout $checkout, CheckoutItem $item, ItemInterface $item);

	public function getPrice();
	public function getCostPrice();

	public function setQuantity($quantity);
	public function getQuantity();

	public function setTotal($total);
	public function getTotal();
}