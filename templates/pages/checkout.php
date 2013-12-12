<div class="container">
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
				<button type="submit" class="btn btn-primary" id="payment-button" name="payment" value="1"><?php echo $text_pay; ?></button>
				<img src="/core/themes/default/images/spinner.gif" width="0px" height="0px" />
			</div>
		</div>
	</form>
</div>
<script type="text/javascript">
	$('#payment-button').click(function() {
		$('<img src="/core/themes/default/images/spinner.gif" width="40px" height="40px" />').insertAfter($(this));
		$('<input type="hidden" name="payment" value="1" />').insertAfter($(this));
		$(this).remove();
		$('#form-checkout').submit();
	});
</script>
