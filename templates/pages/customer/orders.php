<div class="<?php echo $page_div_class; ?>">
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
								<th class="hidden-xs" nowrap="nowrap"><?php echo $text_created; ?> <?php echo $pagination->getSortUrls('created'); ?></th>
								<th class="hidden-xs" nowrap="nowrap"><?php echo $text_special_offers; ?> <?php echo $pagination->getSortUrls('special_offers'); ?></th>
								<th class="hidden-sm hidden-xs" nowrap="nowrap"><?php echo $text_tax; ?> <?php echo $pagination->getSortUrls('tax'); ?></th>
								<th nowrap="nowrap"><?php echo $text_total; ?> <?php echo $pagination->getSortUrls('total'); ?></th>
								<th></th>
							</tr>
							<?php foreach ($orders as $order) { ?>
							<tr>
								<td nowrap="nowrap"><?php echo $order->getReferenceNumber(); ?></td>
								<td class="hidden-xs"><?php echo $order->created; ?></td>
								<td class="hidden-xs"><?php echo money_format('%n', $order->special_offers); ?></td>
								<td class="hidden-sm hidden-xs"><?php echo money_format('%n', $order->tax); ?></td>
								<td><?php echo money_format('%n', $order->getTotal()); ?></td>
								<td>
									<a href="<?php echo $this->url->getUrl('customer/Orders', 'view', [$order->getReferenceNumber()]); ?>" class="btn btn-primary" title="<?php echo $text_view; ?>"><i class="fa fa-edit"></i></a>
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
	<?php echo $message_js; ?>
</script>
