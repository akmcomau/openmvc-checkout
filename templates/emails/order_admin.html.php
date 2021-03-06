<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
</head>
<body>
<div style="background-color: #EEE; border: 1px solid #CCC;">
<h2><?php echo $text_order_from; ?> <?php echo $this->config->siteConfig()->name; ?></h2>
<h3 style="text-align: center;">
<?php echo $text_receipt_number; ?>:
<?php echo $checkout->getReferenceNumber(); ?>
</h3>
<?php if ($checkout->receipt_note) echo '<h1>'.nl2br(${'text_'.$checkout->receipt_note}).'</h1>'; ?>
<br />
<div>
<table style="width: 100%;"><tr><td>
<table style="background-color: #FFF;width: 100%;padding: 20px;">
<thead>
<tr>
<th colspan="4"><?php echo $text_order_details; ?></th>
</tr>
</thead>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_status; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo $checkout->getStatus()->name; ?></td>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_payment_type; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo $checkout->payment_code; ?></td>
</tr>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_item_cost; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo money_format('%n', $checkout->checkout_items_cost); ?></td>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_items_sell; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo money_format('%n', $checkout->checkout_amount); ?></td>
</tr>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_shipping_cost; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo money_format('%n', $checkout->checkout_shipping_cost); ?></td>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_shipping_sell; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo money_format('%n', $checkout->checkout_shipping); ?></td>
</tr>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_tax; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo money_format('%n', $checkout->checkout_tax); ?></td>
<th style="text-align: right;border-top: 1px solid #ddd;">&nbsp;</th>
<td style="border-top: 1px solid #ddd;">&nbsp;</td>
</tr>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_fees; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo money_format('%n', $checkout->checkout_fees); ?></td>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_profit; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo money_format('%n', $checkout->getProfit()); ?></td>
</tr>
</table>
</td><td>
<table style="background-color: #FFF;width: 100%;padding: 20px;">
<thead>
<tr>
<th colspan="2" style="text-align: left;"><?php echo $text_customer_details; ?></th>
</tr>
</thead>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_login; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo $customer ? $customer->login : 'N/A'; ?></td>
</tr>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_first_name; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo $customer ? $customer->first_name : 'N/A'; ?></td>
</tr>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_last_name; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo $customer ? $customer->last_name : 'N/A'; ?></td>
</tr>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_email; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo $customer ? $customer->email : 'N/A'; ?></td>
</tr>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_phone; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo $customer ? $customer->phone : 'N/A'; ?>&nbsp;</td>
</tr>
</table>
</td></tr></table>
</div>
<div>
<div>
<table style="background-color: #FFF;width: 100%;padding: 20px;">
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
<td style="border-top: 1px solid #ddd;"><?php echo $item->getSKU(); ?></td>
<td style="border-top: 1px solid #ddd;"><?php echo $item->getName(); ?></td>
<td style="border-top: 1px solid #ddd;"><?php echo money_format('%n', $item->getSellPrice()); ?></td>
<td style="border-top: 1px solid #ddd;"><?php echo $item->getQuantity(); ?></td>
<td style="border-top: 1px solid #ddd;"><?php echo money_format('%n', $item->getTotal()); ?></td>
</tr>
<?php } ?>
<?php foreach ($checkout->getTotals($this->language) as $name => $value) { ?>
<tr>
<th colspan="3" style="border-top: 1px solid #ddd;"></th>
<th style="border-top: 1px solid #ddd;"><?php echo $name; ?></th>
<th style="border-top: 1px solid #ddd;"><?php echo money_format('%n', $value); ?></th>
</tr>
<?php } ?>
</table>
</div>
</div>
<div>
<table style="width: 100%;"><tr><td>
<?php if ($billing) { ?>
<table style="background-color: #FFF;width: 100%;padding: 20px; width: 50%;">
<thead>
<tr>
<th colspan="2" style="text-align: left;"><?php echo $text_billing_address; ?></th>
</tr>
</thead>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_first_name; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo $billing->first_name; ?></td>
</tr>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_last_name; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo $billing->last_name; ?></td>
</tr>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_address1; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo $billing->address_line1; ?></td>
</tr>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_address2; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo $billing->address_line2; ?>&nbsp;</td>
</tr>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_postcode; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo $billing->address_postcode; ?></td>
</tr>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_city; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo $billing->getCity()->name; ?></td>
</tr>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_state; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo $billing->getState() ? $billing->getState()->name : ''; ?></td>
</tr>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_country; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo $billing->getCountry()->name; ?></td>
</tr>
</table>
<?php } ?>
</td><td>
<?php if ($shipping) { ?>
<table style="background-color: #FFF;width: 100%;padding: 20px; width: 50%;">
<thead>
<tr>
<th colspan="2" style="text-align: left;"><?php echo $text_shipping_address; ?></th>
</tr>
</thead>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_first_name; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo $shipping->first_name; ?></td>
</tr>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_last_name; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo $shipping->last_name; ?></td>
</tr>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_address1; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo $shipping->address_line1; ?></td>
</tr>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_address2; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo $shipping->address_line2; ?>&nbsp;</td>
</tr>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_postcode; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo $shipping->address_postcode; ?></td>
</tr>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_city; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo $shipping->getCity()->name; ?></td>
</tr>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_state; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo $shipping->getState() ? $shipping->getState()->name : ''; ?></td>
</tr>
<tr>
<th style="text-align: right;border-top: 1px solid #ddd;"><?php echo $text_country; ?>: </th>
<td style="border-top: 1px solid #ddd;"><?php echo $shipping->getCountry()->name; ?></td>
</tr>
</table>
<?php } ?>
</td></tr></table>
</div>
</div>
</body>
</html>
