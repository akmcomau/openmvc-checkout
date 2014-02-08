<?php
$_MODULE = [
	"name" => "Checkout",
	"description" => "Support for a checkout and shopping cart",
	"namespace" => "\\modules\\checkout",
	"config_controller" => "administrator\\Checkout",
	"controllers" => [
		"Cart",
		"Checkout",
		"customer\\Orders",
		"administrator\\Checkout",
		"administrator\\Orders"
	],
	"default_config" => [
		"anonymous_checkout" => true
	]
];