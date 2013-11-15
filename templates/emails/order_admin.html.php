<div class="container">
	<h2><?php echo $text_order_for; ?> <?php echo $this->config->siteConfig()->name; ?></h2>
	<h3>
		<?php echo $text_receipt_number; ?>:
		<a href="<?php echo $this->config->getSiteURL().$this->url->getUrl('administrator/Orders', 'editOrder', [$checkout_id]); ?>"><?php echo $receipt_number; ?></a>
	</h3>
	<br />
	<div>
		<table width="100%">
			<tr>
				<th style="text-align: left"><?php echo $text_sku; ?></th>
				<th style="text-align: left"><?php echo $text_name; ?></th>
				<th style="text-align: left"><?php echo $text_price; ?></th>
				<th style="text-align: left"><?php echo $text_quantity; ?></th>
				<th style="text-align: left"><?php echo $text_total; ?></th>
			</tr>
			<?php foreach ($contents as $item) { ?>
				<tr>
					<td><?php echo $item->getSKU(); ?></td>
					<td><?php echo $item->getName(); ?></td>
					<td><?php echo money_format('%n', $item->getPrice()); ?></td>
					<td><?php echo $item->getQuantity(); ?></td>
					<td><?php echo money_format('%n', $item->getTotal()); ?></td>
				</tr>
			<?php } ?>
			<?php foreach ($totals as $name => $value) { ?>
				<tr>
					<th style="text-align: left" colspan="3"></th>
					<th style="text-align: left"><?php echo $name; ?></th>
					<th style="text-align: left"><?php echo money_format('%n', $value); ?></th>
				</tr>
			<?php } ?>
		</table>
	</div>
</div>
