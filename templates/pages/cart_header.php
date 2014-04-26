<div class="container">
	<form action="<?php echo $this->url->getUrl('Cart'); ?>" method="post">
		<div class="row public-form">
			<table class="table">
				<tr>
					<th><?php echo $text_name; ?></th>
					<th><?php echo $text_quantity; ?></th>
					<th><?php echo $text_total; ?></th>
				</tr>
				<?php foreach ($contents as $item) { ?>
					<tr>
						<td><?php echo $item->getName(); ?></td>
						<td>
							<?php if ($item->getMaxQuantity() == 1 && $item->getQuantity() == 1) { ?>
								<?php echo $item->getQuantity(); ?>
							<?php } else { ?>
								<input type="text" class="form-control small-text" name="quantity[<?php echo $item->getType().':'.$item->id; ?>]" value="<?php echo $item->getQuantity(); ?>" />
							<?php } ?>
						</td>
						<td><?php echo money_format('%n', $item->getSellTotal()); ?></td>
					</tr>
				<?php } ?>
				<tr>
					<th></th>
					<th><?php echo $text_total; ?></th>
					<th><?php echo money_format('%n', $total); ?></th>
				</tr>
			</table>
		</div>
		<div class="row">
			<div class="col-md-6 col-sm-6 col-xs-6 align-left">
				<button type="submit" class="btn btn-primary" name="update-cart"><?php echo $text_update; ?></button>
			</div>
			<div class="col-md-6 col-sm-6 col-xs-6 align-right">
				<button type="submit" class="btn btn-primary" name="checkout"><?php echo $text_checkout; ?></button>
			</div>
		</div>
	</form>
</div>
