<?php
$common = dirname( __FILE__ ) . '/src';
require_once $common . '/Pngx/Autoloader.php';
$autoloader = Pngx__Autoloader::instance();
$autoloader->register_prefix( 'Pngx__', $common . '/Pngx' );
$autoloader->register_autoloader();