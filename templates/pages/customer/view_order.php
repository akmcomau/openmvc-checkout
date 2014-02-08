<br />
<div class="<?php echo $page_div_class; ?> page-area">
	<h3 class="align-center">
		<?php echo $text_receipt_number; ?>:
		<?php echo $checkout->getReferenceNumber(); ?>
	</h3>
	<br />
	<form action="" method="post">
		<div class="row">
			<div class="col-md-6">
				<table class="table list">
					<thead>
						<tr>
							<th colspan="2"><?php echo $text_order_details; ?></th>
						</tr>
					</thead>
					<tr>
						<th class="align-right"><?php echo $text_status; ?></th>
						<td><?php echo $checkout->getStatus()->name; ?></td>
					</tr>
					<tr>
						<th class="align-right"><?php echo $text_payment_type; ?></th>
						<td><?php echo $checkout->payment_code; ?></td>
					</tr>
					<tr>
						<th class="align-right"><?php echo $text_items_amount; ?></th>
						<td><?php echo money_format('%n', $checkout->checkout_amount); ?></td>
					</tr>
					<tr>
						<th class="align-right"><?php echo $text_shipping_amount; ?></th>
						<td><?php echo money_format('%n', $checkout->checkout_shipping); ?></td>
					</tr>
					<tr>
						<th class="align-right"><?php echo $text_tax; ?></th>
						<td><?php echo money_format('%n', $checkout->checkout_tax); ?></td>
					</tr>
				</table>
			</div>
			<div class="col-md-6">
				<table class="table list">
					<thead>
						<tr>
							<th colspan="2"><?php echo $text_customer_details; ?></th>
						</tr>
					</thead>
					<tr>
						<th class="align-right"><?php echo $text_login; ?></th>
						<td><?php echo $customer->login; ?></td>
					</tr>
					<tr>
						<th class="align-right"><?php echo $text_first_name; ?></th>
						<td><?php echo $customer->first_name; ?></td>
					</tr>
					<tr>
						<th class="align-right"><?php echo $text_last_name; ?></th>
						<td><?php echo $customer->last_name; ?></td>
					</tr>
					<tr>
						<th class="align-right"><?php echo $text_email; ?></th>
						<td><?php echo $customer->email; ?></td>
					</tr>
					<tr>
						<th class="align-right"><?php echo $text_phone; ?></th>
						<td><?php echo $customer->phone; ?></td>
					</tr>
				</table>
			</div>
		</div>
		<div class="row public-form">
			<div class="col-md-12">
				<table class="table">
					<thead>
						<tr>
							<th class="hidden-xs"><?php echo $text_sku; ?></th>
							<th><?php echo $text_name; ?></th>
							<th class="hidden-xs"><?php echo $text_price; ?></th>
							<th><?php echo $text_quantity; ?></th>
							<th><?php echo $text_total; ?></th>
						</tr>
					</thead>
					<?php foreach ($checkout->getItems() as $item) { ?>
						<tr>
							<td class="hidden-xs"><?php echo $item->getSKU(); ?></td>
							<td><?php echo $item->getName(); ?></td>
							<td class="hidden-xs"><?php echo money_format('%n', $item->getPrice()); ?></td>
							<td><?php echo $item->getQuantity(); ?></td>
							<td><?php echo money_format('%n', $item->getTotal()); ?></td>
						</tr>
					<?php } ?>
					<?php foreach ($checkout->getTotals($this->language) as $name => $value) { ?>
						<tr>
							<th class="visible-xs"></th>
							<th class="hidden-xs" colspan="3"></th>
							<th><?php echo $name; ?></th>
							<th><?php echo money_format('%n', $value); ?></th>
						</tr>
					<?php } ?>
					<?php if (count($checkout->getTotals()) > 1) { ?>
						<tr>
							<th class="visible-xs"></th>
							<th class="hidden-xs" colspan="3"></th>
							<th><?php echo $text_grand_total; ?></th>
							<th><?php echo money_format('%n', $grand_total); ?></th>
						</tr>
					<?php } ?>
				</table>
			</div>
		</div>
		<div class="row">
			<?php if ($shipping) { ?>
				<div class="col-md-6">
					<table class="table list">
						<thead>
							<tr>
								<th colspan="2"><?php echo $text_shipping_address; ?></th>
							</tr>
						</thead>
						<tr>
							<th class="align-right"><?php echo $text_first_name; ?></th>
							<td><?php echo $shipping->first_name; ?></td>
						</tr>
						<tr>
							<th class="align-right"><?php echo $text_last_name; ?></th>
							<td><?php echo $shipping->last_name; ?></td>
						</tr>
						<tr>
							<th class="align-right"><?php echo $text_address1; ?></th>
							<td><?php echo $shipping->address_line1; ?></td>
						</tr>
						<tr>
							<th class="align-right"><?php echo $text_address2; ?></th>
							<td><?php echo $shipping->address_line2; ?></td>
						</tr>
						<tr>
							<th class="align-right"><?php echo $text_postcode; ?></th>
							<td><?php echo $shipping->address_postcode; ?></td>
						</tr>
						<tr>
							<th class="align-right"><?php echo $text_city; ?></th>
							<td><?php echo $shipping->getCity()->name; ?></td>
						</tr>
						<tr>
							<th class="align-right"><?php echo $text_state; ?></th>
							<td><?php echo $shipping->getState()->name; ?></td>
						</tr>
						<tr>
							<th class="align-right"><?php echo $text_country; ?></th>
							<td><?php echo $shipping->getCountry()->name; ?></td>
						</tr>
					</table>
				</div>
			<?php } ?>
			<?php if ($billing) { ?>
				<div class="col-md-6">
					<table class="table list">
						<thead>
							<tr>
								<th colspan="2"><?php echo $text_billing_address; ?></th>
							</tr>
						</thead>
						<tr>
							<th class="align-right"><?php echo $text_first_name; ?></th>
							<td><?php echo $billing->first_name; ?></td>
						</tr>
						<tr>
							<th class="align-right"><?php echo $text_last_name; ?></th>
							<td><?php echo $billing->last_name; ?></td>
						</tr>
						<tr>
							<th class="align-right"><?php echo $text_address1; ?></th>
							<td><?php echo $billing->address_line1; ?></td>
						</tr>
						<tr>
							<th class="align-right"><?php echo $text_address2; ?></th>
							<td><?php echo $billing->address_line2; ?></td>
						</tr>
						<tr>
							<th class="align-right"><?php echo $text_postcode; ?></th>
							<td><?php echo $billing->address_postcode; ?></td>
						</tr>
						<tr>
							<th class="align-right"><?php echo $text_city; ?></th>
							<td><?php echo $billing->getCity()->name; ?></td>
						</tr>
						<tr>
							<th class="align-right"><?php echo $text_state; ?></th>
							<td><?php echo $billing->getState()->name; ?></td>
						</tr>
						<tr>
							<th class="align-right"><?php echo $text_country; ?></th>
							<td><?php echo $billing->getCountry()->name; ?></td>
						</tr>
					</table>
				</div>
			<?php } ?>
		</div>
	</form>
</div>
