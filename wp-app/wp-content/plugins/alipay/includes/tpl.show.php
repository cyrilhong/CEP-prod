<?php
/**
 *parse the $attr
 *
 *
 *
 *
 *
 */
//############################################################################
require_once('cfg.config.php');
require_once('cls.dbparser.php');
//global $wpdb;
//global $WS_ALIPAY_DB_PREFIX;
//global $ws_alipay_tpl_show_g78009;
global $ws_alipay_in_class_proid;
//############################################################################
if (isset($atts)) {
    extract(shortcode_atts(array('id' => ''), $atts));
    if (!isset($id) || $id == '') {
        $ws_alipay_show_return = "该商品不存在";
        return;
    }
    $ws_alipay_in_class_proid = $id;
} else {
    $id = $ws_alipay_in_class_proid;
}
$id = (isset($id) && $id !== '') ? $id : 0;
//CALL THE CLASS TO PARSE THE $ID(PROID)
$output                = new ws_alipay_db_parser($id);
$ws_alipay_show_return = $output->ret;



