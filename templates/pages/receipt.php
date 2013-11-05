<div class="container">
	<h1><?php echo $text_receipt; ?></h1>
	<h3 class="align-center">
		<?php echo $text_receipt_number; ?>:
		<?php echo $receipt_number; ?>
	</h3>
	<br />
	<form action="" method="post">
		<div class="row public-form">
			<table class="table">
				<tr>
					<th class="hidden-xs"><?php echo $text_sku; ?></th>
					<th><?php echo $text_name; ?></th>
					<th class="hidden-xs"><?php echo $text_price; ?></th>
					<th><?php echo $text_quantity; ?></th>
					<th><?php echo $text_total; ?></th>
				</tr>
				<?php foreach ($contents as $item) { ?>
					<tr>
						<td class="hidden-xs"><?php echo $item->getSKU(); ?></td>
						<td><?php echo $item->getName(); ?></td>
						<td class="hidden-xs"><?php echo money_format('%n', $item->getPrice()); ?></td>
						<td><?php echo $item->getQuantity(); ?></td>
						<td><?php echo money_format('%n', $item->getTotal()); ?></td>
					</tr>
				<?php } ?>
				<?php foreach ($totals as $name => $value) { ?>
					<tr>
						<th class="visible-xs"></th>
						<th class="hidden-xs" colspan="3"></th>
						<th><?php echo $name; ?></th>
						<th><?php echo money_format('%n', $value); ?></th>
					</tr>
				<?php } ?>
			</table>
		</div>
	</form>
</div>