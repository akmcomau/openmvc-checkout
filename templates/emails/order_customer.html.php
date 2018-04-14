<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<style>
.full-width {
width: 100%;
margin-top: 20px;
}
.columns2 {
width: 50%;
float: left;
}
.block-center {
float: none !important;
margin-left: auto;
margin-right: auto;
}
table td, table th {
padding-top: 5px;
padding-bottom: 5px;
border-top: 1px solid #ddd;
}
table th {
vertical-align: top;
width: 125px;
text-align: right;
}

@media (max-width: 500px) {
.columns2 {
width: 100%;
float: none;
min-width: 500px;
margin-left: auto;
margin-right: auto;
margin-top: 20px;
}
}
</style>
</head>
<body>
<div style="background-color: #EEE; border: 1px solid #CCC; min-width: 600px;">
<h2><?php echo $text_receipt_from; ?> <?php echo $this->config->siteConfig()->name; ?></h2>
<h3 style="text-align: center;">
<?php echo $text_receipt_number; ?>:
<?php echo $checkout->getReferenceNumber(); ?>
</h3>
<br />

<?php if ($checkout_note_html) { ?>
<div class="full-width">
<div class="columns2 block-center">
<?php echo $checkout_note_html; ?>
<br />
</div>
</div>
<?php } ?>

<div class="full-width">
<div class="columns2">
<table style="background-color: #FFF; width: 100%;">
<thead>
<tr>
<th colspan="2" style="text-align: left;"><?php echo $text_order_details; ?></th>
</tr>
</thead>
<tr>
<th><?php echo $text_status; ?>: </th>
<td><?php echo $checkout->getStatus()->name; ?></td>
</tr>
<tr>
<th><?php echo $text_payment_type; ?>: </th>
<td><?php echo $checkout->payment_code; ?></td>
</tr>
<tr>
<th><?php echo $text_items_amount; ?>: </th>
<td><?php echo money_format('%n', $checkout->checkout_amount); ?></td>
</tr>
<tr>
<th><?php echo $text_shipping_amount; ?>: </th>
<td><?php echo money_format('%n', $checkout->checkout_shipping); ?></td>
</tr>
<?php if ($checkout->checkout_tax) { ?>
<tr>
<th><?php echo $text_tax; ?>: </th>
<td><?php echo money_format('%n', $checkout->checkout_tax); ?></td>
</tr>
<?php } else { ?>
<tr><td colspan="2">&nbsp;</td></tr>
<?php } ?>
</table>
</div>

<div class="columns2">
<table style="background-color: #FFF;width: 100%;">
<thead>
<tr>
<th colspan="2" style="text-align: left;"><?php echo $text_customer_details; ?></th>
</tr>
</thead>
<tr>
<th><?php echo $text_login; ?>: </th>
<td><?php echo $customer->login; ?></td>
</tr>
<tr>
<th><?php echo $text_first_name; ?>: </th>
<td><?php echo $customer->first_name; ?></td>
</tr>
<tr>
<th><?php echo $text_last_name; ?>: </th>
<td><?php echo $customer->last_name; ?></td>
</tr>
<tr>
<th><?php echo $text_email; ?>: </th>
<td><?php echo $customer->email; ?></td>
</tr>
<tr>
<th><?php echo $text_phone; ?>: </th>
<td><?php echo $customer->phone; ?>&nbsp;</td>
</tr>
</table>
</div>

<div style="clear: both;"></div>
</div>

<div class="full-width">
<table style="background-color: #FFF;width: 100%;">
<thead>
<tr>
<th style="text-align: left;"><?php echo $text_sku; ?></th>
<th style="text-align: left;"><?php echo $text_name; ?></th>
<th style="text-align: left;"><?php echo $text_price; ?></th>
<th style="text-align: left;"><?php echo $text_quantity; ?></th>
<th style="text-align: left;"><?php echo $text_total; ?></th>
</tr>
</thead>
<?php foreach ($checkout->getItems() as $item) { ?>
<tr>
<td><?php echo $item->getSKU(); ?></td>
<td><?php echo $item->getName(); ?></td>
<td><?php echo money_format('%n', $item->getSellPrice()); ?></td>
<td><?php echo $item->getQuantity(); ?></td>
<td><?php echo money_format('%n', $item->getTotal()); ?></td>
</tr>
<?php } ?>
<?php foreach ($checkout->getTotals($this->language) as $name => $value) { ?>
<tr>
<th colspan="3"></th>
<th style="text-align: left;"><?php echo $name; ?></th>
<th style="text-align: left;"><?php echo money_format('%n', $value); ?></th>
</tr>
<?php } ?>
</table>
</div>



<div class="full-width">
<div class="columns2">
<?php if ($billing) { ?>
<table style="background-color: #FFF;width: 100%;">
<thead>
<tr>
<th colspan="2" style="text-align: left;"><?php echo $text_billing_address; ?></th>
</tr>
</thead>
<tr>
<th><?php echo $text_first_name; ?>: </th>
<td><?php echo $billing->first_name; ?></td>
</tr>
<tr>
<th><?php echo $text_last_name; ?>: </th>
<td><?php echo $billing->last_name; ?></td>
</tr>
<tr>
<th><?php echo $text_address1; ?>: </th>
<td><?php echo $billing->address_line1; ?></td>
</tr>
<tr>
<th><?php echo $text_address2; ?>: </th>
<td><?php echo $billing->address_line2; ?>&nbsp;</td>
</tr>
<tr>
<th><?php echo $text_postcode; ?>: </th>
<td><?php echo $billing->address_postcode; ?></td>
</tr>
<tr>
<th><?php echo $text_city; ?>: </th>
<td><?php echo $billing->getCity()->name; ?></td>
</tr>
<tr>
<th><?php echo $text_state; ?>: </th>
<td><?php echo $billing->getState() ? $billing->getState()->name : ''; ?></td>
</tr>
<tr>
<th><?php echo $text_country; ?>: </th>
<td><?php echo $billing->getCountry()->name; ?></td>
</tr>
</table>
<?php } ?>
</div>

<div class="columns2">
<?php if ($shipping) { ?>
<table style="background-color: #FFF;width: 100%;">
<thead>
<tr>
<th colspan="2" style="text-align: left;"><?php echo $text_shipping_address; ?></th>
</tr>
</thead>
<tr>
<th><?php echo $text_first_name; ?>: </th>
<td><?php echo $shipping->first_name; ?></td>
</tr>
<tr>
<th><?php echo $text_last_name; ?>: </th>
<td><?php echo $shipping->last_name; ?></td>
</tr>
<tr>
<th><?php echo $text_address1; ?>: </th>
<td><?php echo $shipping->address_line1; ?></td>
</tr>
<tr>
<th><?php echo $text_address2; ?>: </th>
<td><?php echo $shipping->address_line2; ?>&nbsp;</td>
</tr>
<tr>
<th><?php echo $text_postcode; ?>: </th>
<td><?php echo $shipping->address_postcode; ?></td>
</tr>
<tr>
<th><?php echo $text_city; ?>: </th>
<td><?php echo $shipping->getCity()->name; ?></td>
</tr>
<tr>
<th><?php echo $text_state; ?>: </th>
<td><?php echo $shipping->getState() ? $shipping->getState()->name : ''; ?></td>
</tr>
<tr>
<th><?php echo $text_country; ?>: </th>
<td><?php echo $shipping->getCountry()->name; ?></td>
</tr>
</table>
<?php } ?>
</div>

</div>

</body>
</html>
