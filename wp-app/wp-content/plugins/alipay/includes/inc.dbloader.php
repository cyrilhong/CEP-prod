<?php

############################################################################
define('SITE_ROOT', $_SERVER['DOCUMENT_ROOT']);
define('DR', DIRECTORY_SEPARATOR);
############################################################################
//require_once( SITE_ROOT.DR.'wp-load.php');
//./wp-content/plugins/alipay/includes/file.php
require_once('../../../../wp-load.php');
error_reporting(0);
require_once('cfg.config.php');
header("Content-Type: text/plain; charset=" . WS_ALIPAY_CHARSET);
############################################################################
//Die if load thfe page directly
if (empty($_REQUEST)) {
    die();
}
//Necessary security check
ws_alipay_security_check();
//stripslashe
$_REQUEST = stripslashes_deep($_REQUEST);
//FOR GET
//NEED: TABLE, FIELDS, ( PRIMARYKEY = INT )
$wsali_dbLoader = array();
//Command fields
$wsali_dbLoader['cmdfdsA'] = array(
    'action',
    'limit',
    'ws_security_check',
    'fields',
    'asc_fields',
    'table',
    'where',
    'single',
    'fields_refer'
);
$wsali_dbLoader['affdsA']  = array_diff_key($_REQUEST, array_flip($wsali_dbLoader['cmdfdsA']));
if (isset($_REQUEST['table'])) {
    $wsali_dbLoader['table'] = $_REQUEST['table'];
    $wsali_dbLoader['wptbl'] = $wpdb->wsaliprefix . $_REQUEST['table'];
    //Current table fields , an Array.
    $wsali_dbLoader['ctnfdsA']  = ${'ws_alipay_table_' . $_REQUEST['table']};
    $wsali_dbLoader['products'] = $ws_alipay_table_products;
    //$wsali_dbLoader['ords']		 = $ws_alipay_table_orders;
    //$wsali_dbLoader['tpls']		 = $ws_alipay_table_templates;
    $wsali_dbLoader['ctnfdsS'] = '';
    //To be affected db fields
    $wsali_dbLoader['afdbfdsA'] = array_intersect_key($_REQUEST, array_flip($wsali_dbLoader['ctnfdsA']));
    $wsali_dbLoader['metafdsA'] = array_diff_key(
        $_REQUEST,
        $wsali_dbLoader['afdbfdsA'],
        array_flip($wsali_dbLoader['cmdfdsA'])
    );
    //$ws_alipay_refer_field
    $wsali_dbLoader['referfd'] = '';
    //refer return array
    $wsali_dbLoader['referfdA'] = '';
    //array to merge temp
    $wsali_dbLoader['mergeA'] = '';
    //
}
if (isset($_REQUEST['where'])) {
    $wsali_where              = preg_split('/=/', $_REQUEST['where']);
    $wsali_dbLoader['wherek'] = $wsali_where[0];
    if (isset($wsali_where[1])) {
        $wsali_dbLoader['wherev'] = $wsali_where[1];
    }
}
//############################################################################
if (isset($_REQUEST['table']) && $_REQUEST['table'] !== '') {
    foreach ($wsali_dbLoader['ctnfdsA'] as $key => $value) {
        $wsali_dbLoader['ctnfdsS'] .= "$value,";
    }
    $wsali_dbLoader['ctnfdsS'] = substr($wsali_dbLoader['ctnfdsS'], 0, -1);
}
//allowed fileds first
if (isset($_REQUEST['fields']) && $_REQUEST['fields'] !== '') {
    if ($_REQUEST['fields'] !== '*') {
        $ws_alipay_asc_fields      = explode(',', $_REQUEST['fields']);
        $wsali_dbLoader['ctnfdsA'] = $ws_alipay_asc_fields;
    }
}
//then the disallowed
if (isset($_REQUEST['asc_fields']) && $_REQUEST['asc_fields'] !== '') {
    $ws_alipay_asc_fields      = explode(',', $_REQUEST['asc_fields']);
    $wsali_dbLoader['ctnfdsA'] = array_values(array_diff($wsali_dbLoader['ctnfdsA'], $ws_alipay_asc_fields));
}
//refer parse
if (isset($_REQUEST['fields_refer']) && $_REQUEST['fields_refer'] !== '') {
    $wsali_dbLoader['referfd'] = $_REQUEST['fields_refer'];
}
//var section
$arr_ret = array();
//global $wpdb;
$wpdb->query("SET time_zone = '" . ws_alipay_num2time(get_option('gmt_offset')) . "';");
############################################################################
//action list
if (isset($_REQUEST['action']) && $_REQUEST['action'] !== '') {
    switch ($_POST['action']) {
        case '78009':
            ws_alipay_get_data();
            break;
        case '78010':
            ws_alipay_add_data();
            break;
        case '78011':
            ws_alipay_add_data();
            break;
        case '78012':
            //ws_alipay_get_data_plus();
            //ws_alipay_get_refer_data();
            //ws_alipay_merge();
            break;
        case '78013':
            ws_alipay_api_update();
            break;
        case '78014':
            ws_alipay_get_data();
            break;
        case '78015':
            ws_alipay_update_data();
            break;
        case '78016':
            ws_alipay_insert_data();
            ws_alipay_get_data();
            break;
        case '78017':
            ws_alipay_delete_data();
            ws_alipay_get_data();
            break;
        case '78018':
            ws_alipay_copy_data();
            ws_alipay_get_data();
            break;
    }
}
//out put
$arr_ret = json_encode($arr_ret);
echo $arr_ret;
############################################################################
//functions section
function ws_alipay_merge()
{
    global $wsali_dbLoader;
    global $arr_ret;
    foreach ($arr_ret['data'] as $key => $value) {
        foreach ($wsali_dbLoader['mergeA'] as $key1 => $value1) {
            if ($value['proid'] == $value1->proid) {
                //ATTENTION:$value IS AN ARRAY,BUT $value1 IS AN OBJECT!!!
                //THAT'S B/C HERE SHOULD BE WRITTEN IN $value['proid'] == $value1->proid
                //OR WE CAN ADD A STATEMENT BEFORE THE IF STATEMENT LIKE $value1=(array)$value1
                $arr_ret['data'][$key] = array_merge($value, (array)$value1);
            }
        }
        if (!isset($arr_ret['data'][$key]['name'])) {
            $arr_ret['data'][$key]['name']  = '该商品已不存在';
            $arr_ret['data'][$key]['price'] = '未知';
        }
    }
}

function ws_alipay_get_refer_data($table = 1, $key = 1)
{
    global $wpdb, $wsali_dbLoader;
    $sql                      = "	SELECT name,proid,price
			FROM $wpdb->wsaliproducts
			WHERE proid
			IN (SELECT proid FROM $wpdb->wsaliorders)
			;";
    $wsali_dbLoader['mergeA'] = $wpdb->get_results($sql);
}

function ws_alipay_get_data()
{
    global $wpdb, $wsali_dbLoader;
    global $arr_ret;
    if (isset($_REQUEST['fields_refer']) && $_REQUEST['fields_refer'] !== '') {
        ws_alipay_get_data_plus();
        return;
    }
    if (isset($_REQUEST['single'])) { //只读1条记录,即查看详情或编辑
        //$id = preg_split( '@\=@', $_REQUEST['where'] );
        $sql = "
		SELECT {$wsali_dbLoader['ctnfdsS']} 
		FROM {$wsali_dbLoader['wptbl']} 
		WHERE $_REQUEST[where]
		;";
    } else { //读N条记录
        $sql = "
		SELECT " . $wsali_dbLoader['ctnfdsS'] . "
		FROM   " . $wsali_dbLoader['wptbl'] . " 
		LIMIT  " . $_REQUEST['limit'] . ";";
    }
    $rows_ret = $wpdb->get_results($sql);
    foreach ($rows_ret as $value) { //$key=0,1,2,3...
        $arr_temp = array();
        foreach ($wsali_dbLoader['ctnfdsA'] as $value1) { //$key1=0,1,2,3...
            $arr_temp[$value1] = $value->$value1;
        }
        $arr_ret['data'][] = $arr_temp;
    }
    if (isset($_REQUEST['single'])) { //只读1条记录,即查看详情或编
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //GET the add-on fields
        $metas = get_metadata(
            $wpdb->{'wsali' . $wsali_dbLoader['table'] . 'metatype'},
            $wsali_dbLoader['wherev'],
            '',
            true
        );
        //print_r($metas);
        //die();
        //Filter the JSON fields which is for ajax add-on
        if (!empty($metas)) {
            $metaCOMN = array();
            $metaJSON = array();
            foreach ($metas as $k => $v) {
                if (preg_match('@^\S+JSON$@', $k)) {
                    $tempA = json_decode($v[0], true);
                    if (!isset($tempA['transport']) || $tempA['transport'] == true) {
                        $metaJSON[] = $tempA;
                    }
                } else {
                    $metaCOMN[$k] = $v[0];
                }
            }
            $arr_ret['data'][0] = array_merge($arr_ret['data'][0], $metaCOMN);
            //uasort( $metaJSON, 'ws_alipay_metaJSON_sort', 'priority' );
            $metaJSON         = ws_alipay_sortByOneKey($metaJSON, 'priority', 10, true);
            $arr_ret['extra'] = $metaJSON;
        }
    }
    $row_count        = $wpdb->get_results("SELECT COUNT(*) FROM {$wsali_dbLoader['wptbl']};");
    $arr_ret['count'] = $row_count;
}

function ws_alipay_get_data_plus()
{
    global $wpdb, $wsali_dbLoader;
    global $arr_ret;
    $sql      = ws_alipay_get_refer_sql($wsali_dbLoader['referfd']);
    $rows_ret = $wpdb->get_results($sql);
    foreach ($rows_ret as $value) { //$key=0,1,2,3...
        $arr_temp = array();
        foreach ($wsali_dbLoader['ctnfdsA'] as $value1) { //$key1=0,1,2,3...
            $arr_temp[$value1] = stripslashes($value->$value1); //$arr_temp[price] = 3.00...
        }
        foreach ($wsali_dbLoader['referfdA'] as $value1) { //$key1=0,1,2,3...
            $arr_temp[$value1] = stripslashes($value->$value1); //$arr_temp[price] = 3.00...
        }
        $arr_ret['data'][] = $arr_temp;
    }
    $row_count        = $wpdb->get_results("SELECT COUNT(*) FROM {$wsali_dbLoader['wptbl']};");
    $arr_ret['count'] = $row_count;
}

function ws_alipay_add_data()
{
    global $wpdb, $wsali_dbLoader;
    $in = array();
    foreach ($wsali_dbLoader['afdbfdsA'] as $key => $value) {
        $in[$key] = $value;
    }
    $wpdb->insert($wsali_dbLoader['wptbl'], $in);
    die();
}

function ws_alipay_update_data()
{
    global $wpdb, $wsali_dbLoader;
    //HERE IS NECESSARY FOR THAT $wpdb->update WILL REGARG THE / AS THE ENTITIES
    //IF USE THE $ws_alipay_db_fields IN SQL DIRECTELY, WE'LL NOT STRIOSLASHES!!
    //$a = preg_split('/=/',$_REQUEST['where']);
    $wh = array($wsali_dbLoader['wherek'] => $wsali_dbLoader['wherev']);
    $wt = array('%d');
    $wpdb->update($wsali_dbLoader['wptbl'], $wsali_dbLoader['afdbfdsA'], $wh, null, $wt);
    //Update the metas
    foreach ($wsali_dbLoader['metafdsA'] as $k => $v) {
        update_metadata($wpdb->{'wsali' . $wsali_dbLoader['table'] . 'metatype'}, $wsali_dbLoader['wherev'], $k, $v);
    }
    die();
}

function ws_alipay_api_update()
{
    global $wsali_dbLoader;
    foreach ($wsali_dbLoader['affdsA'] as $key => $value) {
        //$ws_alipay_db_fields[$key] = esc_html($value);
    }
    $JSON = json_encode($wsali_dbLoader['affdsA']);
    update_option('ws_alipay_settings_api', $JSON);
}

//function ws_alipay_insert_data(){
//	global $wpdb, $wsali_dbLoader;
//	
//	$in = array( $_REQUEST['where']=>'' );
//
//	
//	$wt = array( '%d' );
//	
//	$wpdb->insert( $wsali_dbLoader['wptbl'], $in, $wt);
//}
function ws_alipay_insert_data()
{
    global $wpdb, $wsali_dbLoader;
    $in  = array($_REQUEST['where'] => '');
    $wt  = array('%d');
    $sql = "INSERT INTO {name} ({$_REQUEST['where']}) VALUES('aaa');";
    $wpdb->query($sql);
    //echo $wpdb->insert( $wsali_dbLoader['wptbl'], $in, $wt);
}

function ws_alipay_copy_data()
{
    global $wpdb, $wsali_dbLoader;
    $the_copy     = $wpdb->get_results("SELECT * FROM {$wsali_dbLoader['wptbl']} WHERE $_REQUEST[where]");
    $the_copy     = (array)$the_copy[0];
    $the_copy     = array_diff_key($the_copy, array('tplid' => '', 'proid' => '', 'ordid' => ''));
    $the_copy_key = array_keys($the_copy);
    foreach ($the_copy as &$value) {
        $value = "'" . addslashes($value) . "'";
    }
    $the_copy_key = ws_alipay_array_link($the_copy_key);
    $the_copy     = ws_alipay_array_link($the_copy);
    $wpdb->query("INSERT INTO {$wsali_dbLoader['wptbl']} ($the_copy_key) VALUES($the_copy)");
}

function ws_alipay_delete_data()
{
    global $wpdb, $wsali_dbLoader;
    $sql = "DELETE FROM {$wsali_dbLoader['wptbl']} WHERE $_REQUEST[where]";
    $wpdb->query($sql);
    //delete_metadata( $wpdb->{'wsali'.$wsali_dbLoader['table'].'metatype'}, $wsali_dbLoader['wherev']);
    $tbl_meta = $wpdb->{'wsali' . $wsali_dbLoader['table'] . 'meta'};
    $objk     = 'wsali' . $wsali_dbLoader['table'] . '_id';
    $sql      = "DELETE FROM $tbl_meta WHERE $objk = {$wsali_dbLoader['wherev']}";
    $wpdb->query($sql);
}

//############################################################################
function ws_alipay_array_link($arr)
{
    return substr(array_reduce($arr, 'ws_alipay_array_link_callback'), 1);
}

function ws_alipay_array_link_callback($v1, $v2)
{
    return $v1 . ',' . $v2;
}

function ws_alipay_get_refer_sql($arr_refer)
{
    return ws_alipay_sql_maker(ws_alipay_refer_parser($arr_refer));
}

function ws_alipay_refer_parser($arr_refer)
{
    foreach ($arr_refer as $key => $value) {
        $arr_temp;
        $val_temp            = preg_split('/\|/', $value);
        $arr_temp['table']   = $val_temp[0];
        $arr_temp['refer']   = $val_temp[1];
        $arr_temp['fields']  = preg_split('/,/', $val_temp[2]);
        $arr_refer_ret[$key] = $arr_temp;
    }
    return $arr_refer_ret;
}

function ws_alipay_sql_maker($arr_mix)
{
    global $wpdb, $wsali_dbLoader;
    $select = '';
    $from   = '';
    $join   = '';
    $on     = '';
    $prfix  = $wpdb->prefix . 'ws_alipay_';
    foreach ($arr_mix as $key => $items) {
        foreach ($items['fields'] as $field) {
            $select .= $prfix . $items['table'] . '.' . $field . ',';
        }
    }
    $select                     = substr($select, 0, -1);
    $from                       = $prfix . $arr_mix[0]['table'];
    $join                       = $prfix . $arr_mix[1]['table'];
    $on                         = $from . '.' . $arr_mix[0]['refer'] . '=' . $join . '.' . $arr_mix[1]['refer'];
    $wsali_dbLoader['referfdA'] = $arr_mix[1]['fields'];
    $ret                        = "
	SELECT $select
	FROM $from
	LEFT OUTER JOIN $join
	ON $on
	;";
    return $ret;
}

