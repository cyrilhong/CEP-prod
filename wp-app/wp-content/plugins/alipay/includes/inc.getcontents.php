<?php
die('OUT OF SERVICE');
$url = dirname($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
$ret = file_get_contents('http://' . $url . '/api_alipay/inc.alipay_gateway.php');
echo htmlspecialchars($ret);



