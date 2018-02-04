<div class="<?php echo $page_class; ?>">
	<div class="float-right">
		<strong><?php echo $this->language->get('curreny', $this->config->siteConfig()->currency); ?></strong>
	</div>
	<h1><?php echo $text_cart; ?></h1>
	<form action="<?php echo $this->url->getUrl('Cart'); ?>" method="post">
		<div class="row public-form"><div class="col-md-12">
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
						<td class="hidden-xs"><?php echo money_format('%n', $item->getSellPrice()); ?></td>
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
					<th class="visible-xs" colspan="2"></th>
					<th class="hidden-xs" colspan="4"></th>
					<th><?php echo $text_total; ?></th>
					<th><?php echo money_format('%n', $total); ?></th>
				</tr>
			</table>
		</div></div>
		<div class="row">
			<div class="col-md-6 col-sm-6 col-xs-6 align-left">
				<button type="submit" class="btn btn-primary" name="update-cart"><?php echo $text_update; ?></button>
			</div>
			<div class="col-md-6 col-sm-6 col-xs-6 align-right">
				<button type="submit" class="btn btn-primary" name="checkout"><?php echo $text_checkout; ?></button>
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
