// JavaScript Document
jQuery(function($) {
	$('#ws_alipay_add_product').bind('click', function() {
		location.href = $(this).attr('action-href');
	});


	$('.ws_alipay_prolink').bind('click', function() {
		//location.href = $(this).val();
		window.open($(this).val(), '_blank')
	});


});