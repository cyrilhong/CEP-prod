var $wsAliWidgetArr = [];


jQuery(function($) {

	$('.ws_alipay_widget_wrap').mouseenter(function() {
		$(this).children('.ws_alipay_widget_form').css('display', 'block');
	});

	$('.ws_alipay_widget_wrap').mouseleave(function() {
		$(this).children('.ws_alipay_widget_form').css('display', 'none');
	});

	$('.ws_alipay_widget_try').click(function() {
		alert('YOU GOT IT');
	});

});//EOJQ