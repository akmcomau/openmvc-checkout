<div class="container">
	<h1><?php echo $text_cart; ?></h1>
	<form action="" method="post">
		<div class="row public-form">
			<table class="table">
				<tr>
					<th><?php echo $text_remove; ?></th>
					<th class="hidden-xs"><?php echo $text_sku; ?></th>
					<th><?php echo $text_name; ?></th>
					<th class="hidden-xs"><?php echo $text_price; ?></th>
					<th><?php echo $text_quantity; ?></th>
					<th><?php echo $text_total; ?></th>
				</tr>
				<?php foreach ($contents as $item) { ?>
					<tr>
						<td><input type="checkbox" name="remove[]" value="<?php echo $item->getType().':'.$item->id; ?>" /></td>
						<td class="hidden-xs"><?php echo $item->getSKU(); ?></td>
						<td><?php echo $item->getName(); ?></td>
						<td class="hidden-xs"><?php echo money_format('%n', $item->getPrice()); ?></td>
						<td>
							<?php if ($item->getMaxQuantity() == 1 && $item->getQuantity() == 1) { ?>
								<?php echo $item->getQuantity(); ?>
							<?php } else { ?>
								<input type="text" class="form-control small-text" name="quantity[<?php echo $item->getType().':'.$item->id; ?>]" value="<?php echo $item->getQuantity(); ?>" />
							<?php } ?>
						</td>
						<td><?php echo money_format('%n', $item->getTotal()); ?></td>
					</tr>
				<?php } ?>
				<tr>
					<th class="visible-xs" colspan="2"></th>
					<th class="hidden-xs" colspan="4"></th>
					<th><?php echo $text_total; ?></th>
					<th><?php echo money_format('%n', $total); ?></th>
				</tr>
			</table>
			<div class="row">
				<div class="col-md-6 col-sm-6 col-xs-6 align-left">
					<button type="submit" class="btn btn-primary" name="update-cart"><?php echo $text_update; ?></button>
				</div>
				<div class="col-md-6 col-sm-6 col-xs-6 align-right">
					<button type="submit" class="btn btn-primary" name="checkout"><?php echo $text_checkout; ?></button>
				</div>
			</div>
		</div>
	</form>
</div>