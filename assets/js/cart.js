function load_header_cart() {
	if (!$('#cart-dropdown').is(':visible')) {
		$('#cart-dropdown').html('<img src="/core/themes/default/images/spinner.gif" />');
		$.ajax({
			url: "/Cart/cartHeader",
			dataType: "json"
		}).done(function(data) {
			if (data.success) {
				$('#cart-dropdown').html(data.content);
			}
			else if (data.error) {
				alert(data.error);
				$('#cart-dropdown').hide();
			}
			else {
				alert('Sorry an error occured');
				$('#cart-dropdown').hide();
			}
		}).fail(function() {
			alert('Sorry an error occured');
			$('#cart-dropdown').hide();
		});
	}
}

$(document).ready(function () {
	$('#cart-dropdown-link').click(load_header_cart);
});
