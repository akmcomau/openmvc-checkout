<?php echo $text_order_from; ?> <?php echo $this->config->siteConfig()->name; ?>

<?php echo $text_receipt_number; ?>: <?php echo $checkout->getReferenceNumber(); ?>


<?php if ($checkout->receipt_note) echo $checkout->receipt_note."\n\n\n"; ?>
<?php echo $text_order_details; ?>


<?php echo $text_status; ?>: <?php echo $checkout->getStatus()->name; ?>

<?php echo $text_payment_type; ?>: <?php echo $checkout->payment_code; ?>

<?php echo $text_item_cost; ?>: <?php echo money_format('%n', $checkout->checkout_items_cost); ?>

<?php echo $text_items_sell; ?>: <?php echo money_format('%n', $checkout->checkout_amount); ?>

<?php echo $text_shipping_cost; ?>: <?php echo money_format('%n', $checkout->checkout_shipping_cost); ?>

<?php echo $text_shipping_sell; ?>: <?php echo money_format('%n', $checkout->checkout_shipping); ?>

<?php echo $text_tax; ?>: <?php echo money_format('%n', $checkout->checkout_tax); ?>

<?php echo $text_fees; ?>: <?php echo money_format('%n', $checkout->checkout_fees); ?>

<?php echo $text_profit; ?>: <?php echo money_format('%n', $checkout->getProfit()); ?>



<?php echo $text_customer_details; ?>


<?php echo $text_login; ?>: <?php echo $customer ? $customer->login : 'N/A'; ?>

<?php echo $text_first_name; ?>: <?php echo $customer ? $customer->first_name : 'N/A'; ?>

<?php echo $text_last_name; ?>: <?php echo $customer ? $customer->last_name : 'N/A'; ?>

<?php echo $text_email; ?>: <?php echo $customer ? $customer->email : 'N/A'; ?>

<?php echo $text_phone; ?>: <?php echo $customer ? $customer->phone : 'N/A'; ?>



<?php echo $text_items; ?>


<?php foreach ($checkout->getItems() as $item) { ?>
<?php echo $text_sku; ?>: <?php echo $item->getSKU(); ?>

<?php echo $text_name; ?>: <?php echo $item->getName(); ?>

<?php echo $text_price; ?>: <?php echo money_format('%n', $item->getSellPrice()); ?>

<?php echo $text_quantity; ?>: <?php echo $item->getQuantity(); ?>

<?php echo $text_subtotal; ?>: <?php echo money_format('%n', $item->getTotal()); ?>


<?php } ?>
<?php foreach ($checkout->getTotals($this->language) as $name => $value) { ?>
<?php echo $name; ?>: <?php echo money_format('%n', $value); ?>

<?php } ?>
<?php if ($shipping) { ?>


<?php echo $text_shipping_address; ?>


<?php echo $text_first_name; ?>: <?php echo $shipping->first_name; ?>

<?php echo $text_last_name; ?>: <?php echo $shipping->last_name; ?>

<?php echo $text_address1; ?>: <?php echo $shipping->address_line1; ?>

<?php echo $text_address2; ?>: <?php echo $shipping->address_line2; ?>

<?php echo $text_postcode; ?>: <?php echo $shipping->address_postcode; ?>

<?php echo $text_city; ?>: <?php echo $shipping->getCity()->name; ?>

<?php echo $text_state; ?>: <?php echo $shipping->getState() ? $shipping->getState()->name : ''; ?>

<?php echo $text_country; ?>: <?php echo $shipping->getCountry()->name; ?>

<?php } ?>
<?php if ($billing) { ?>


<?php echo $text_billing_address; ?>


<?php echo $text_first_name; ?>: <?php echo $billing->first_name; ?>

<?php echo $text_last_name; ?>: <?php echo $billing->last_name; ?>

<?php echo $text_address1; ?>: <?php echo $billing->address_line1; ?>

<?php echo $text_address2; ?>: <?php echo $billing->address_line2; ?>

<?php echo $text_postcode; ?>: <?php echo $billing->address_postcode; ?>

<?php echo $text_city; ?>: <?php echo $billing->getCity()->name; ?>

<?php echo $text_state; ?>: <?php echo $billing->getState() ? $billing->getState()->name : ''; ?>

<?php echo $text_country; ?>: <?php echo $billing->getCountry()->name; ?>

<?php } ?>
