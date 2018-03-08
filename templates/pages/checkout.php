<div class="<?php echo $page_class; ?>">
	<div class="float-right">
		<strong><?php echo $this->language->get('curreny', $this->config->siteConfig()->currency); ?></strong>
	</div>
	<h1><?php echo $text_checkout; ?></h1>
	<form action="" method="post" id="form-checkout">
		<div class="row public-form">
			<div class="col-md-12">
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
							<td class="hidden-xs"><?php echo money_format('%n', $item->getSellPrice()); ?></td>
							<td><?php echo $item->getQuantity(); ?></td>
							<td><?php echo money_format('%n', $item->getSellTotal()); ?></td>
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
					<?php if (count($totals) > 1) { ?>
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
		<div class="row align-right">
			<div class="col-md-12">
				<?php if (count((array)$payment_types) == 1) { ?>
					<?php foreach ($payment_types as $code => $data) { ?>
						<input type="hidden" name="payment_method" value="<?php echo $code; ?>" />
					<?php } ?>
				<?php } else { ?>
					<?php foreach ($payment_types as $code => $data) { ?>
						<div>
							<label>
								<input type="radio" name="payment_method" value="<?php echo $code; ?>" />
								<?php echo $data->name; ?>
							</label>
						</div>
					<?php } ?>
					<br />
				<?php } ?>
				<button type="submit" class="btn btn-primary" id="payment-button" name="payment" value="1"><?php echo $text_continue; ?></button>
				<img src="/core/themes/default/images/spinner.gif" width="0px" height="0px" />
			</div>
		</div>
	</form>

	<?php if (isset($also_purchased) && count($also_purchased) > 0) { ?>
		<br /><br />
		<h3><?php echo $text_checkup_upsell_message; ?></h3>
		<div class="row">
			<?php foreach($also_purchased as $product) { ?>
				<div class="col-md-4 col-sm-6 product-cell">
					<a href="<?php echo $product->getUrl($this->url); ?>" class="product">
						<h4><?php echo htmlspecialchars($product->name); ?></h4>
						<?php
							$images = $product->getImages();
							$image  = NULL;
							if (count($images)) {
								$image = $images[0];
								?><img src="<?php echo $image->getThumbnailUrl(); ?>" /><?php
							}
						?>
					</a>
					<div class="add-to-cart">
						<div class="price"><?php echo  money_format('%n', $product->getSellPrice()); ?></div>
						<a href="<?php echo $this->url->getUrl('Cart', 'add', ['product', $product->id]); ?>" class="btn btn-primary"><?php echo $text_add_to_cart; ?></a>
					</div>
				</div>
			<?php } ?>
		</div>
	<?php } ?>
</div>
<script type="text/javascript">
	$('#payment-button').click(function() {
		$('<img src="/core/themes/default/images/spinner.gif" width="40px" height="40px" />').insertAfter($(this));
		$('<input type="hidden" name="payment" value="1" />').insertAfter($(this));
		$('#form-checkout').submit();
		$(this).hide();
	});
</script>
