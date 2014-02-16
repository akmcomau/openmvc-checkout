<div class="<?php echo $page_div_class; ?>">
	<h1><?php echo $text_receipt; ?></h1>
	<h3 class="align-center">
		<?php echo $text_receipt_number; ?>:
		<?php echo $receipt_number; ?>
	</h3>
	<br />
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
	<?php if ($created_customer) { ?>
		<div class="row">
			<form id="form-password" class="form-login-register" action="<?php echo $this->url->getUrl('Checkout', 'receipt', [$receipt_number]); ?>" method="post">
				<div class="widget">
					<div class="widget-header">
						<h3><?php echo $text_account_created; ?></h3>
					</div>
					<div class="widget-content">
						<p><?php echo $text_account_created_msg; ?></p>
						<?php echo $form->getHtmlErrorDiv('login-failed', 'login-failed'); ?>
						<input type="text" name="username" class="form-control" placeholder="<?php echo $text_username; ?>" autofocus="autofocus" value="<?php echo $form->getEncodedValue('username'); ?>" />
						<?php echo $form->getHtmlErrorDiv('username'); ?>
						<hr />
						<input type="password" name="password1" class="form-control" placeholder="<?php echo $text_password1; ?>" />
						<?php echo $form->getHtmlErrorDiv('password1'); ?>
						<hr />
						<input type="password" name="password2" class="form-control" placeholder="<?php echo $text_password2; ?>" />
						<?php echo $form->getHtmlErrorDiv('password2'); ?>
						<button name="form-password-submit" class="btn btn-lg btn-primary btn-block" type="submit"><?php echo $text_save; ?></button>
					</div>
				</div>
			</form>
		</div>
	<?php } ?>
</div>
<script type="text/javascript">
	<?php echo $form->getJavascriptValidation(); ?>
</script>
