<div class="<?php echo $page_div_class; ?>">
	<div class="row">
		<div class="col-md-6 col-sm-6">
			<?php echo $login_form; ?>
			<?php if ($anonymous_checkout_enabled) { ?>
				<div class="row align-center">
					<h3><?php echo $text_anonymous_checkout; ?></h3>
					<a class="btn btn-lg btn-primary" href="<?php echo $this->url->getUrl('Checkout', 'index', ['anonymous']); ?>"><?php echo $text_dont_login_register; ?></a>
				</div>
			<?php } ?>
		</div>
		<div class="col-md-6 col-sm-6">
			<?php echo $register_form; ?>
		</div>
	</div>
</div>
<script type="text/javascript">
	<?php echo $login->getJavascriptValidation(); ?>
	<?php echo $register->getJavascriptValidation(); ?>
</script>
