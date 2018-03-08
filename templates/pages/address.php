<div class="<?php echo $page_class; ?>">
	<div class="float-right">
		<strong><?php echo $this->language->get('curreny', $this->config->siteConfig()->currency); ?></strong>
	</div>
	<h1><?php echo $text_checkout; ?></h1>

	<div class="row">
		<form id="form-checkout-address" action="<?php echo $this->url->getUrl('Checkout', 'address'); ?>" method="post">
			<div class="col-md-6" style="padding-left: 30px; padding-right: 30px;">
				<h2><?php echo $text_billing_address; ?></h2>
				<hr class="separator-2column" />
				<div class="row">
					<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_first_name; ?></div>
					<div class="col-md-9 col-sm-9 ">
						<input type="text" name="billing_first_name" class="form-control" value="<?php echo htmlspecialchars($form->getValue('billing_first_name')); ?>" />
						<?php echo $form->getHtmlErrorDiv('billing_first_name'); ?>
					</div>
				</div>
				<hr class="separator-2column" />
				<div class="row">
					<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_last_name; ?></div>
					<div class="col-md-9 col-sm-9 ">
						<input type="text" name="billing_last_name" class="form-control" value="<?php echo htmlspecialchars($form->getValue('billing_last_name')); ?>" />
						<?php echo $form->getHtmlErrorDiv('billing_last_name'); ?>
					</div>
				</div>
				<hr class="separator-2column" />
				<div class="row">
					<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_phone; ?></div>
					<div class="col-md-9 col-sm-9 ">
						<input type="text" name="billing_phone" class="form-control" value="<?php echo htmlspecialchars($form->getValue('billing_phone')); ?>" />
						<?php echo $form->getHtmlErrorDiv('billing_phone'); ?>
					</div>
				</div>
				<hr class="separator-2column" />
				<div class="row">
					<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_email; ?></div>
					<div class="col-md-9 col-sm-9 ">
						<input type="text" name="billing_email" class="form-control" value="<?php echo htmlspecialchars($form->getValue('billing_email')); ?>" />
						<?php echo $form->getHtmlErrorDiv('billing_email'); ?>
					</div>
				</div>
				<hr class="separator-2column" />
				<div class="row">
					<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_address_line1; ?></div>
					<div class="col-md-9 col-sm-9 ">
						<input type="text" name="billing_address_line1" class="form-control" value="<?php echo htmlspecialchars($form->getValue('billing_address_line1')); ?>" />
						<?php echo $form->getHtmlErrorDiv('billing_address_line1'); ?>
					</div>
				</div>
				<hr class="separator-2column" />
				<div class="row">
					<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_address_line2; ?></div>
					<div class="col-md-9 col-sm-9 ">
						<input type="text" name="billing_address_line2" class="form-control" value="<?php echo htmlspecialchars($form->getValue('billing_address_line2')); ?>" />
						<?php echo $form->getHtmlErrorDiv('billing_address_line2'); ?>
					</div>
				</div>
				<hr class="separator-2column" />
				<div class="row">
					<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_postcode; ?></div>
					<div class="col-md-9 col-sm-9 ">
						<input type="text" name="billing_postcode" class="form-control" value="<?php echo htmlspecialchars($form->getValue('billing_postcode')); ?>" />
						<?php echo $form->getHtmlErrorDiv('billing_postcode'); ?>
					</div>
				</div>
				<hr class="separator-2column" />
				<div class="row">
					<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_city; ?></div>
					<div class="col-md-9 col-sm-9 ">
						<input type="text" name="billing_city" class="form-control" value="<?php echo htmlspecialchars($form->getValue('billing_city')); ?>" />
						<?php echo $form->getHtmlErrorDiv('billing_city'); ?>
					</div>
				</div>
				<hr class="separator-2column" />
				<div class="row">
					<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_state; ?></div>
					<div class="col-md-9 col-sm-9 ">
						<input type="text" name="billing_state" class="form-control" value="<?php echo htmlspecialchars($form->getValue('billing_state')); ?>" />
						<?php echo $form->getHtmlErrorDiv('billing_state'); ?>
					</div>
				</div>
				<hr class="separator-2column" />
				<div class="row">
					<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_country; ?></div>
					<div class="col-md-9 col-sm-9 ">
						<select name="billing_country" class="form-control">
							<?php foreach ($countries as $country) { ?>
								<option value="<?php echo $country->id; ?>" <?php if ($form->getValue('billing_country') == $country->id) echo 'selected="selected"'; ?>><?php echo htmlspecialchars($country->name); ?></option>
							<?php } ?>
						</select>
						<?php echo $form->getHtmlErrorDiv('billing_country'); ?>
					</div>
				</div>
			</div>

			<div class="col-md-6" style="padding-left: 30px; padding-right: 30px;">
				<h2><?php echo $text_shipping_address; ?></h2>
				<hr class="separator-2column" />
				<div class="row">
					<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_same_as_billing; ?></div>
					<div class="col-md-9 col-sm-9 ">
						<input type="checkbox" name="same_as_billing" value="1" <?php if ($form->getValue('same_as_billing')) echo 'checked="checked"'; ?> />
						<?php echo $form->getHtmlErrorDiv('same_as_billing'); ?>
					</div>
				</div>
				<hr class="separator-2column" />
				<div class="row">
					<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_first_name; ?></div>
					<div class="col-md-9 col-sm-9 ">
						<input type="text" name="shipping_first_name" class="form-control" value="<?php echo htmlspecialchars($form->getValue('shipping_first_name')); ?>" />
						<?php echo $form->getHtmlErrorDiv('shipping_first_name'); ?>
					</div>
				</div>
				<hr class="separator-2column" />
				<div class="row">
					<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_last_name; ?></div>
					<div class="col-md-9 col-sm-9 ">
						<input type="text" name="shipping_last_name" class="form-control" value="<?php echo htmlspecialchars($form->getValue('shipping_last_name')); ?>" />
						<?php echo $form->getHtmlErrorDiv('shipping_last_name'); ?>
					</div>
				</div>
				<hr class="separator-2column" />
				<div class="row">
					<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_phone; ?></div>
					<div class="col-md-9 col-sm-9 ">
						<input type="text" name="shipping_phone" class="form-control" value="<?php echo htmlspecialchars($form->getValue('shipping_phone')); ?>" />
						<?php echo $form->getHtmlErrorDiv('shipping_phone'); ?>
					</div>
				</div>
				<hr class="separator-2column" />
				<div class="row">
					<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_email; ?></div>
					<div class="col-md-9 col-sm-9 ">
						<input type="text" name="shipping_email" class="form-control" value="<?php echo htmlspecialchars($form->getValue('shipping_email')); ?>" />
						<?php echo $form->getHtmlErrorDiv('shipping_email'); ?>
					</div>
				</div>
				<hr class="separator-2column" />
				<div class="row">
					<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_address_line1; ?></div>
					<div class="col-md-9 col-sm-9 ">
						<input type="text" name="shipping_address_line1" class="form-control" value="<?php echo htmlspecialchars($form->getValue('shipping_address_line1')); ?>" />
						<?php echo $form->getHtmlErrorDiv('shipping_address_line1'); ?>
					</div>
				</div>
				<hr class="separator-2column" />
				<div class="row">
					<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_address_line2; ?></div>
					<div class="col-md-9 col-sm-9 ">
						<input type="text" name="shipping_address_line2" class="form-control" value="<?php echo htmlspecialchars($form->getValue('shipping_address_line2')); ?>" />
						<?php echo $form->getHtmlErrorDiv('shipping_address_line2'); ?>
					</div>
				</div>
				<hr class="separator-2column" />
				<div class="row">
					<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_postcode; ?></div>
					<div class="col-md-9 col-sm-9 ">
						<input type="text" name="shipping_postcode" class="form-control" value="<?php echo htmlspecialchars($form->getValue('shipping_postcode')); ?>" />
						<?php echo $form->getHtmlErrorDiv('shipping_postcode'); ?>
					</div>
				</div>
				<hr class="separator-2column" />
				<div class="row">
					<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_city; ?></div>
					<div class="col-md-9 col-sm-9 ">
						<input type="text" name="shipping_city" class="form-control" value="<?php echo htmlspecialchars($form->getValue('shipping_city')); ?>" />
						<?php echo $form->getHtmlErrorDiv('shipping_city'); ?>
					</div>
				</div>
				<hr class="separator-2column" />
				<div class="row">
					<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_state; ?></div>
					<div class="col-md-9 col-sm-9 ">
						<input type="text" name="shipping_state" class="form-control" value="<?php echo htmlspecialchars($form->getValue('shipping_state')); ?>" />
						<?php echo $form->getHtmlErrorDiv('shipping_state'); ?>
					</div>
				</div>
				<hr class="separator-2column" />
				<div class="row">
					<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_country; ?></div>
					<div class="col-md-9 col-sm-9 ">
						<select id="shipping_country_select" name="shipping_country" class="form-control">
							<?php foreach ($countries as $country) { ?>
								<option value="<?php echo $country->id; ?>" <?php if ($form->getValue('shipping_country') == $country->id) echo 'selected="selected"'; ?>><?php echo htmlspecialchars($country->name); ?></option>
							<?php } ?>
						</select>
						<input type="hidden" id="shipping_country_hidden" name="" value="" />
						<?php echo $form->getHtmlErrorDiv('shipping_country'); ?>
					</div>
				</div>
			</div>
			<div class="col-md-12 align-center">
				<br /><br />
				<button type="submit" name="form-checkout-address-submit" class="btn btn-lg btn-primary"><?php echo $text_continue; ?></button>
			</div>
		</form>
	</div>
</div>
<script type="text/javascript">
	<?php echo $form->getJavascriptValidation(); ?>
	<?php /* echo $message_js; */ ?>

	var same_as_billing = false;
	$('input[name="same_as_billing"]').change(function () {
		if ($(this).is(':checked')) {
			$('input[name="shipping_first_name"]').attr('readonly', 'readonly');
			$('input[name="shipping_last_name"]').attr('readonly', 'readonly');
			$('input[name="shipping_phone"]').attr('readonly', 'readonly');
			$('input[name="shipping_email"]').attr('readonly', 'readonly');
			$('input[name="shipping_address_line1"]').attr('readonly', 'readonly');
			$('input[name="shipping_address_line2"]').attr('readonly', 'readonly');
			$('input[name="shipping_postcode"]').attr('readonly', 'readonly');
			$('input[name="shipping_city"]').attr('readonly', 'readonly');
			$('input[name="shipping_state"]').attr('readonly', 'readonly');

			$('#shipping_country_select').attr('disabled', 'disabled');
			$('#shipping_country_select').attr('name', '');
			$('#shipping_country_hidden').attr('name', 'shipping_country');
			$('#shipping_country_hidden').val($('#shipping_country_select').val());

			same_as_billing = true;

			$('input[name="billing_first_name"]').change();
			$('input[name="billing_last_name"]').change();
			$('input[name="billing_phone"]').change();
			$('input[name="billing_email"]').change();
			$('input[name="billing_address_line1"]').change();
			$('input[name="billing_address_line2"]').change();
			$('input[name="billing_postcode"]').change();
			$('input[name="billing_city"]').change();
			$('input[name="billing_state"]').change();
			$('select[name="billing_country"]').change();
		}
		else {
			$('input[name="shipping_first_name"]').removeAttr('readonly');
			$('input[name="shipping_last_name"]').removeAttr('readonly');
			$('input[name="shipping_phone"]').removeAttr('readonly');
			$('input[name="shipping_email"]').removeAttr('readonly');
			$('input[name="shipping_address_line1"]').removeAttr('readonly');
			$('input[name="shipping_address_line2"]').removeAttr('readonly');
			$('input[name="shipping_postcode"]').removeAttr('readonly');
			$('input[name="shipping_city"]').removeAttr('readonly');
			$('input[name="shipping_state"]').removeAttr('readonly');

			$('#shipping_country_select').removeAttr('disabled');
			$('#shipping_country_select').attr('name', 'shipping_country');
			$('#shipping_country_hidden').attr('name', '');

			same_as_billing = false;
		}
	});
	$('input[name="same_as_billing"]').change();

	function setInputVal(name) {
		if (same_as_billing) {
			$('input[name="shipping_'+name+'"]').attr('value', $('input[name="billing_'+name+'"]').val());
		}
	}

	$('input[name="billing_first_name"]').change(function() {setInputVal('first_name');});
	$('input[name="billing_last_name"]').change(function() {setInputVal('last_name');});
	$('input[name="billing_phone"]').change(function() {setInputVal('phone');});
	$('input[name="billing_email"]').change(function() {setInputVal('email');});
	$('input[name="billing_address_line1"]').change(function() {setInputVal('address_line1');});
	$('input[name="billing_address_line2"]').change(function() {setInputVal('address_line2');});
	$('input[name="billing_postcode"]').change(function() {setInputVal('postcode');});
	$('input[name="billing_city"]').change(function() {setInputVal('city');});
	$('input[name="billing_state"]').change(function() {setInputVal('state');});

	$('select[name="billing_country"]').change(function() {
		if (same_as_billing) {
			$('#shipping_country_hidden').attr('value', $(this).val());
			$('#shipping_country_select').find('option[value="'+$(this).val()+'"]').attr('selected', 'selected');
		}
	});
</script>
