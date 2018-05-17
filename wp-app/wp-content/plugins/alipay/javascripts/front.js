var $wsAliFrontArr = [];
$wsAliFrontArr['payto_path'] = '/wp-content/plugins/alipay/includes/tpl.cart.php?';
//$wsAliFrontArr['arr_query']  = ['proid','email','num','msg','extra','addr','tel','ordname','postcode','nonce'];
//$wsAliFrontArr['arr_query'] = '';
$wsAliFrontArr['prefix'] = '.ws_alipay_buy_';
//$wsAliFrontArr['p'] = '';

jQuery(function($) {

	$('.ws_alipay_buy_wrap .ws_alipay_buy_pay').click(function() {


		$wsAliFrontArr['p'] = $(this).parents('.ws_alipay_buy_wrap');

		$wsAliFrontArr['arr_query'] = $wsAliFrontArr['p'].find('.ws_alipay_buy_fields').val().split(',');

		$PARA = ws_alipay_http_build_query($wsAliFrontArr['arr_query']);

		var $PROTO = window.location.protocol + '//';
		var $HOST = window.location.host;
		var $PORT = window.location.port;
		var $PATH = $wsAliFrontArr['payto_path'];
		var $URI = $PROTO + $HOST + $PORT + $PATH + $PARA;

		$URI = encodeURI($URI);
		//window.location.href = $URI ;

		open($URI);
	});


});//EOJQ


function ws_alipay_http_build_query($query_fields) {
	var ret = '';
	var $p = $wsAliFrontArr['p'];
	var $prefix = $wsAliFrontArr['prefix'];

	for (var i in $query_fields) {
		fiels_val = $p.find($prefix + $query_fields[i]).val();
		fiels_val = ws_alipay__E28(fiels_val);
		ret += '&' + $query_fields[i] + '=' + fiels_val;
	}

	var referer = ws_alipay__E28(window.location.href);
	ret = 'referer=' + referer + ret;
	return ret;
}

function ws_alipay__E28(o) {
	if (typeof o == 'undefined') {
		o = ''
	}
	return  encodeURIComponent(o);
}
