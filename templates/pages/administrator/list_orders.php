<div class="container">
	<div class="row">
		<div class="col-md-12">
			<form class="admin-search-form" method="get" id="form-orders-search">
				<div class="widget">
					<div class="widget-header">
						<h3><?php echo $text_search; ?></h3>
					</div>
					<div class="widget-content">
						<div class="row">
							<div class="col-md-6">
								<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_reference; ?></div>
								<div class="col-md-9 col-sm-9 ">
									<input type="text" class="form-control" name="search_reference" value="<?php echo htmlspecialchars($form->getValue('search_reference')); ?>" />
									<?php echo $form->getHtmlErrorDiv('search_reference'); ?>
								</div>
							</div>
						</div>
						<hr class="separator-2column" />
						<div class="row">
							<div class="col-md-6">
								<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_first_name; ?></div>
								<div class="col-md-9 col-sm-9 ">
									<input type="text" class="form-control" name="search_first_name" value="<?php echo htmlspecialchars($form->getValue('search_first_name')); ?>" />
									<?php echo $form->getHtmlErrorDiv('search_first_name'); ?>
								</div>
							</div>
							<div class="col-md-6 visible-xs">
								<hr class="separator-2column" />
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_last_name; ?></div>
									<div class="col-md-9 col-sm-9 ">
										<input type="text" class="form-control" name="search_last_name" value="<?php echo htmlspecialchars($form->getValue('search_last_name')); ?>" />
										<?php echo $form->getHtmlErrorDiv('search_last_name'); ?>
									</div>
								</div>
							</div>
						</div>
						<hr class="separator-2column" />
						<div class="row">
							<div class="col-md-6">
								<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_login; ?></div>
								<div class="col-md-9 col-sm-9 ">
									<input type="text" class="form-control" name="search_login" value="<?php echo htmlspecialchars($form->getValue('search_login')); ?>" />
									<?php echo $form->getHtmlErrorDiv('search_login'); ?>
								</div>
							</div>
							<div class="col-md-6 visible-xs">
								<hr class="separator-2column" />
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_email; ?></div>
									<div class="col-md-9 col-sm-9 ">
										<input type="text" class="form-control" name="search_email" value="<?php echo htmlspecialchars($form->getValue('search_email')); ?>" />
										<?php echo $form->getHtmlErrorDiv('search_email'); ?>
									</div>
								</div>
							</div>
						</div>
						<hr class="separator-2column" />
						<div class="align-right">
							<button type="submit" class="btn btn-primary" name="form-orders-search-submit"><?php echo $text_search; ?></button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
			<div class="widget">
				<div class="widget-header">
					<h3><?php echo $text_search_results; ?></h3>
				</div>
				<div class="widget-content">
					<div class="pagination">
						<?php echo $pagination->getPageLinks(); ?>
					</div>
					<form action="<?php echo $this->url->getUrl('administrator/Subscriptions', 'deleteSubscription'); ?>" method="post">
						<table class="table">
							<tr>
								<th nowrap="nowrap"><?php echo $text_reference; ?></th>
								<th class="hidden-xxs" nowrap="nowrap"><?php echo $text_login; ?> <?php echo $pagination->getSortUrls('login'); ?></th>
								<th class="hidden-xs" nowrap="nowrap"><?php echo $text_created; ?> <?php echo $pagination->getSortUrls('created'); ?></th>
								<th class="hidden-sm hidden-xs" nowrap="nowrap"><?php echo $text_special_offers; ?> <?php echo $pagination->getSortUrls('special_offers'); ?></th>
								<th class="hidden-sm hidden-xs" nowrap="nowrap"><?php echo $text_tax; ?> <?php echo $pagination->getSortUrls('tax'); ?></th>
								<th class="hidden-sm hidden-xs" nowrap="nowrap"><?php echo $text_cost_price; ?> <?php echo $pagination->getSortUrls('cost_price'); ?></th>
								<th nowrap="nowrap"><?php echo $text_sell_price; ?> <?php echo $pagination->getSortUrls('sell_price'); ?></th>
								<th class="hidden-xs" nowrap="nowrap"><?php echo $text_profit; ?> <?php echo $pagination->getSortUrls('profit'); ?></th>
								<th></th>
							</tr>
							<?php foreach ($orders as $order) { ?>
								<?php $this->config->setLocale($order->locale); ?>
								<tr>
									<td nowrap="nowrap"><?php echo $order->getReferenceNumber(); ?></td>
									<td class="hidden-xxs"><?php echo $order->getCustomer() ? $order->getCustomer()->login : ''; ?></td>
									<td class="hidden-xs"><?php echo $order->created; ?></td>
									<td class="hidden-sm hidden-xs"><?php echo money_format('%n', $order->special_offers); ?></td>
									<td class="hidden-sm hidden-xs"><?php echo money_format('%n', $order->tax); ?></td>
									<td class="hidden-sm hidden-xs"><?php echo money_format('%n', $order->getCostPrice()); ?></td>
									<td><?php echo money_format('%n', $order->getSellPrice()); ?></td>
									<td class="hidden-xs"><?php echo money_format('%n', $order->getProfit()); ?></td>
									<td>
										<a href="<?php echo $this->url->getUrl('administrator/Orders', 'editOrder', [$order->id]); ?>" class="btn btn-primary"><i class="fa fa-edit" title="<?php echo $text_edit; ?>"></i></a>
									</td>
								</tr>
							<?php } ?>
						</table>
					</form>
					<div class="pagination">
						<?php echo $pagination->getPageLinks(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	<?php echo $form->getJavascriptValidation(); ?>
	<?php echo $message_js; ?>
</script>
