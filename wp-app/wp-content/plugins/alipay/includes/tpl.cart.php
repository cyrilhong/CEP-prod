<?php
session_start();
session_destroy();

require_once 'cfg.config.php';
require_once 'cls.info.php';


/////是否要求登录?

if (ws_alipay_get_setting('user_must_login') && !is_user_logged_in()) {
    if (is_ssl()) {
        $proto = 'https://';
    } else {
        $proto = 'http://';
    }
    $login_url = site_url(
        'wp-login.php?redirect_to=' . urlencode($proto . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'])
    );
    wp_redirect($login_url);
    exit;
}


isset($_REQUEST['proid']) || die(ws_alipay_show_tip('SIGN_INVALID'));

$pro = new  ws_alipay_product();

$proInfo = $pro->get_info($_REQUEST['proid']);
//print_r($proInfo);	
//array('name'=>'','label'=>'', 'validate'=>'', 'priority'=>10, 'tip'=>'');
/*
VALIDATE:
DEFAULT: BOOLEAN TRUE or 'TRUE' - NOT EMPTY IS OK
CASE: EMPTY or 'FALSE' - ANYTHING IS OK
CASE: NUM - LEAST
CASE: ARRAY(LEAST,MOST) - LEAST LETTERS, MOST LETTERS
CASE: STRING - VALIDATE FUNCTION ( RETURN TRUE FOR OK, FALSE FOR BAD )
*/

$arr_buyerInfo['CUSTOM'] = array(
    array('name' => 'ordname', 'label' => '收货人姓名', 'validate' => array(2, 20)),
    array('name' => 'addr', 'label' => '详细地址', 'validate' => array(12, 40)),
    array('name' => 'postcode', 'label' => '邮政编码', 'validate' => 'ws_alipay_validateFormat', 'type' => 'POSTCODE'),
    array('name' => 'tel', 'label' => '联系手机', 'validate' => 'ws_alipay_validateFormat', 'type' => 'TEL'),
    array('name' => 'email', 'label' => '电子邮箱', 'validate' => 'ws_alipay_validateFormat', 'type' => 'EMAIL'),
    array('name' => 'msg', 'label' => '给卖家留言', 'validate' => array(-1, 300)),
);

$arr_buyerInfo['CUSTOM'] = apply_filters('ws_alipay_carBbuyerInfo_CUSTOM', $arr_buyerInfo['CUSTOM']);

$arr_buyerInfo['VIRTUAL'] = array(
    array('name' => 'email', 'label' => '电子邮箱', 'validate' => 'ws_alipay_validateFormat', 'type' => 'EMAIL'),
    array('name' => 'msg', 'label' => '给卖家留言', 'validate' => array(-1, 300)),
);


//-----------------------------------------------------------------------
//Widget ad
//-----------------------------------------------------------------------
/////////////////////////////////////////////////////////////////////////////////////
$arr_buyerInfo['ADP'] = array(
    array('name' => 'imgSrc', 'label' => '图片地址', 'validate' => 'ws_alipay_validateFormat', 'type' => 'URL'),
    array('name' => 'imgLink', 'label' => '跳转地址', 'validate' => 'ws_alipay_validateFormat', 'type' => 'URL'),
    array('name' => 'email', 'label' => '电子邮箱', 'validate' => 'ws_alipay_validateFormat', 'type' => 'EMAIL'),
    array('name' => 'msg', 'label' => '给卖家留言', 'validate' => array(-1, 300)),
);

$arr_buyerInfo['LINK'] = array(
    array('name' => 'linkName', 'label' => '链接名称', 'validate' => array(1, 7)),
    array('name' => 'linkUrl', 'label' => '链接地址', 'validate' => 'ws_alipay_validateFormat', 'type' => 'URL'),
    array('name' => 'linkDesc', 'label' => '链接描述', 'validate' => array(-1, 30)),
    array('name' => 'email', 'label' => '电子邮箱', 'validate' => 'ws_alipay_validateFormat', 'type' => 'EMAIL'),
    array('name' => 'msg', 'label' => '给卖家留言', 'validate' => array(-1, 300)),
);


/////////////////////////////////////////////////////////////////////////////////////


foreach ($arr_buyerInfo as $k => $v) {
    $arr_buyerInfo[$k] = apply_filters("ws_alipay_carBbuyerInfo_$k", $arr_buyerInfo[$k]);
}

$arr_buyerInfo = apply_filters('ws_alipay_cartBbuyerInfo', $arr_buyerInfo);


add_filter('ws_alipay_cartProType', 'ws_alipay_cartProType_fn', 999, 2);

function ws_alipay_cartProType_fn($info, $type)
{
    if (!empty($type) && isset($info[$type])) {
        return $info[$type];
    } else {
        return 'CUSTOM';
    }
}


if (empty($proInfo['protype'])) {
    $type = 'CUSTOM';
} else {
    $type = $proInfo['protype'];
}


$type = apply_filters('ws_alipay_cartProTypeName', $type, $_REQUEST);

//$type = 'VIRTUAL';


$arr_buyerInfo = apply_filters('ws_alipay_cartProType', $arr_buyerInfo, $type);


if (in_array($type, array('ADP', 'LINK'))) {
    $showMultiPrice = true;
} else {
    $showMultiPrice = false;
}

if ($showMultiPrice):

    $unitHtml = '<select name="unit" class="unitsl" id="cartPriceUnit">';
    if (!empty($proInfo['pricePerDay']) && $proInfo['pricePerDay'] > 0) {
        $unitHtml .= '<option value="pricePerDay-' . $proInfo['pricePerDay'] . '">' . __('Day', 'wsali') . '</option>';
    }
    if (!empty($proInfo['pricePerWeek']) && $proInfo['pricePerWeek'] > 0) {
        $unitHtml .= '<option value="pricePerWeek-' . $proInfo['pricePerWeek'] . '">' . __(
              'Week',
              'wsali'
          ) . '</option>';
    }
    if (!empty($proInfo['pricePerMonth']) && $proInfo['pricePerMonth'] > 0) {
        $unitHtml .= '<option value="pricePerMonth-' . $proInfo['pricePerMonth'] . '">' . __(
              'Month',
              'wsali'
          ) . '</option>';
    }
    if (!empty($proInfo['pricePerQuarter']) && $proInfo['pricePerQuarter'] > 0) {
        $unitHtml .= '<option value="pricePerQuarter-' . $proInfo['pricePerQuarter'] . '">' . __(
              'Quarter',
              'wsali'
          ) . '</option>';
    }
    if (!empty($proInfo['pricePerYear']) && $proInfo['pricePerYear'] > 0) {
        $unitHtml .= '<option value="pricePerYear-' . $proInfo['pricePerYear'] . '">' . __(
              'Year',
              'wsali'
          ) . '</option>';
    }
    $unitHtml .= '</select>';

endif;


//验证字段的合法性
if (isset($_REQUEST['cartSubmit'])) {
    foreach ($arr_buyerInfo as $arr) { //T for error
        if (!isset($_REQUEST[$arr['name']]) || !isset($arr['validate'])) {
            continue;
        }
        $src = $_REQUEST[$arr['name']];
        $v   = $arr['validate'];
        if ($v === true || strtoupper((string)$v) == 'TRUE') {
            if (empty($src)) {
                $validerr[$arr['name']] = true;
            } else {
                $validerr[$arr['name']] = false;
            }
        } elseif (empty($v)) {
            $validerr[$arr['name']] = false;
        } elseif (is_numeric($v)) {
            preg_match_all("@.@us", $src, $match);
            $len = count($match[0]);
            if ($len !== $v) {
                $validerr[$arr['name']] = true;
            } else {
                $validerr[$arr['name']] = false;
            }
        } elseif (is_array($v) && count($v) == 2) { //长度限制
            preg_match_all("@.@us", $src, $match);
            $len = count($match[0]);
            if ($v[0] !== -1 && $v[1] !== -1 && ($len < $v[0] || $len > $v[1])) {
                $validerr[$arr['name']] = true;
            } elseif ($v[1] == -1 && $len < $v[0]) {
                $validerr[$arr['name']] = true;
            } elseif ($v[0] == -1 && $len > $v[1]) {
                $validerr[$arr['name']] = true;
                echo $arr['name'];
            } else {
                $validerr[$arr['name']] = false;
            }
        } elseif (is_string($v)) {
            $vlitype = (isset($arr['type'])) ? $arr['type'] : null;
            if (is_callable($v)) {
                if (call_user_func($v, $src, $vlitype)) {
                    $validerr[$arr['name']] = false;
                } else {
                    $validerr[$arr['name']] = true;
                }
            }
        }
        //END OF IF
    }
    //END OF FOR EACH
    $errExist = false;
    foreach ($validerr as $v) {
        if ($v) {
            $errExist = true;
            break;
        }
    }
    if (!$errExist) {
        $nonce = wp_create_nonce('fromcart');
        header("Location:inc.payto.php?{$_SERVER['QUERY_STRING']}&nonce=$nonce");
    }
}
//END OF IF


/////////////////////////////////////////////////////////////////////////////////////


if (isset($validerr)) {
    $err = $validerr;
}

//print_r($err);
//生成FOOTER
$siteName = get_option('blogname');
$siteUrl = get_option('siteurl');
$year = date('Y', time());
$year2 = $year + 1;
$footer_Copyright = "
Copyright &copy; {$year}-{$year2} <a href=$siteUrl>$siteName</a> All Rights Reserved
";


//获取请求参数
$arr_fields = array(
    'proid',
    'email',
    'num',
    'msg',
    'extra',
    'addr',
    'tel',
    'ordname',
    'postcode',
    'nonce',
    'paygate',
    'bankType',
    'unit',
    'imgSrc',
    'imgLink',
    'referer'
);

$arr_fields = apply_filters('ws_alipay_cart_queryVars', $arr_fields);

//把未声明的预使用变量定义为''
$ord = ws_alipay_noEmpty($arr_fields, $_REQUEST);
//先获取编码,如果不存在则默认为WP设置的编码
isset($_REQUEST['charset']) || $_REQUEST['charset'] = WS_ALIPAY_CHARSET;
//对URL中的字符解码并转码, 转成此WP的编码, 后续的数据库写入就不在考虑编码的问题了
//不管外界是以何种编码传入,转码后从此页面提交给服务器的编码肯定是和服务器一致了
$ord = ws_alipay_urlDecodeDeep($ord, $_REQUEST['charset'], WS_ALIPAY_CHARSET);
//对发送给用户的浏览器的该网页头
header('Content-Type:text/html; charset=' . $_REQUEST['charset']);


$proid = intval($_REQUEST['proid']);

//生成NONCE
$nonce = wp_create_nonce('ws_alipay_front_nonce_action', 'ws_alipay_front_nonce_name');


//对ord数组进行重写.
!empty($ord['num']) || $ord['num'] = 1;


$ord['proFee'] = $proInfo['price'] * $ord['num'];
if ($proInfo['spfre']) {
    $ord['logFee'] = $proInfo['freight'];
} else {
    $ord['logFee'] = '0.00';
}
$ord['totFee'] = $ord['proFee'] + $ord['logFee'];


//对商品图片重写
!empty($proInfo['images']) || $proInfo['images'] = WS_ALIPAY_IMG_URL . '/cart_small.jpg';

//银行参数
$arr_banks = array(
    '1001' => array('招商银行', '58'),
    '1002' => array('中国建设银行', '26'),
    '1004' => array('上海浦东发展银行', '147'),
    '1005' => array('中国农业银行', '-2'),
    '1006' => array('民生银行', '328'),
    '1008' => array('深圳发展银行', '178'),
    '1009' => array('兴业银行', '208'),
    '1010' => array('平安银行', '418'),
    '1020' => array('交通银行', '298'),
    '1021' => array('中信银行', '357'),
    '1022' => array('光大银行', '267'),
    '1024' => array('上海银行', '534'),
    '1026' => array('中国银行', '117'),
    '1027' => array('广东发展银行', '388'),
    '1030' => array('中国工商银行(企业)', '838'),
    '1032' => array('北京银行', '237'),
    '1042' => array('招商银行(企业)', '897'),
    '1040' => array('中国建设银行(企业)', '1047'),
    '1043' => array('中国农业银行(企业)', '1077'),
    '1054' => array('南京银行', '987'),
    '1056' => array('宁波银行', '927'),
    '1076' => array('BEA东亚银行', '597'),
    '1080' => array('浦发银行(企业)', '1167'),
    '1082' => array('上海农商银行', '507'),
    '2033' => array('中国邮政储蓄银行', '447'),
);

$arr_bankOrder = array(
    '1001',
    '1002',
    '1005',
    '1026',
    '1004',
    '1027',
    '1022',
    '1006',
    '1021',
    '1009',
    '1010',
    '1008',
    '1020',
    '1032',
    '1054',
    '1056',
    '1024',
    '1082',
    '1076',
    '2033',
    '1080',
    '1040',
    '1043',
    '1030',
    '1042',
);


$arr_buyerInfo = apply_filters('ws_alipay_buyerInfo', $arr_buyerInfo);

$arr_buyerInfo = ws_alipay_sortByOneKey($arr_buyerInfo, 'priority', 10);
function ws_alipay_buyerInfo_fn($info)
{
    //unset( $info['ordname']);
    //$info = array_merge($info,array('ordFOO'	 => array('收货人FOO','请填写收货人姓名')));
    return $info;
}


$arr_buyInfo = array(
    'ALIPAY' => array('支付宝', 1),
    'TENPAY' => array('财付通', 0),
    'PAYPAL' => array('PayPal', 0),
    'UNION'  => array('网银直联', 0),
);

//银行列表显示开关
if (isset($_REQUEST['paygate']) && strtoupper($_REQUEST['paygate']) == 'UNION') {
    $bankListStyle = 'display:block';
} else {
    $bankListStyle = 'display:none';
}



?>

<!DOCTYPE HTML>
<html>
<head>
    <title>商品购买详情 | 快捷支付通欢迎您</title>
    <link href="<?php echo WS_ALIPAY_URL; ?>/styles/cart.css" rel="stylesheet"/>
    <script src="http://lib.sinaapp.com/js/jquery/1.5.2/jquery.min.js"></script>
    <script src="../javascripts/cart.js"></script>
    <?php echo apply_filters('ws_alipay_cartHeader', null); ?>
</head>

<body>

<div id="header">
    <div id="headCenter">
        <div id="logo"><h1>快捷支付</h1></div>
        <div id="headInfo">快捷支付通欢迎您! | <a href="javascript:void(0);">我的交易</a> | <a href="javascript:void(0);">帮助中心</a> |
            <a href="javascript:void(0);">提点建议</a>
        </div>
    </div>
</div>
<div id="main">
    <div id="mainPatch">
        <form action="" method="get" id="frmOrdInfo">
            <h1>购买宝贝</h1>
            <hr/>
            <div id="cartInfo">
                <h2>第一步：确认宝贝信息</h2>
                <hr class="dot"/>
                <!--#########################################################################-->
                <table id="cartTable">
                    <thead>
                    <tr>

                        <th class="proName">宝贝名称</th>
                        <th class="proPrice">单价(元)</th>
                        <th class="option">X</th>
                        <th class="buyNum">数量</th>
                        <th class="equal">=</th>
                        <th class="proFee">小计(元)</th>
                    </tr>


                    </thead>

                    <tfoot>
                    <tr>
                        <td colspan="6" class="totalFee">应付总额 (含运费<em id="cartLogFee"><?php echo $ord['logFee']; ?></em>元)：
                            ￥<em id="cartTotFee"><?php echo $ord['totFee']; ?></em>元
                        </td>
                    </tr>
                    </tfoot>

                    <tbody>
                    <tr>
                        <td>
                            <img id="proImg" src="<?php echo $proInfo['images']; ?>"
                                 title="<?php echo '(ID:' . $proInfo['proid'] . ')&nbsp;'; ?>">

                            <div id="proName"><?php echo $proInfo['name']; ?></div>
                        </td>
                        <td id="cartProPrice"><?php echo $proInfo['price']; ?></td>
                        <td>X</td>
                        <td>
                            <span><input type="button" value="－" id="proNumDecre"/></span>
                            <span id="cartProNum"><?php echo $ord['num']; ?></span>
                            <span><input type="button" value="+" id="proNumIncre"/></span>

                            <?php
                            if ($showMultiPrice) {
                                echo '<p>' . $unitHtml . '</p>';
                            }
                            ?>

                        </td>
                        <input type="hidden" name="num" id="ordInfo_num" value="<?php echo $ord['num']; ?>"/>
                        <td>=</td>
                        <td id="cartProFee"><?php echo $ord['proFee']; ?></td>
                    </tr>
                    </tbody>

                </table>
                <!--#########################################################################-->
            </div>
            <div id="buyerInfo">
                <h2>第二步：填写收货信息</h2>

                <hr class="dot"/>


                <?php

                foreach ($arr_buyerInfo as $key => $item) {
                    if (isset($arr_buyerInfo['html'])) {
                        echo $arr_buyerInfo['html'];
                        break;
                    }
                    $error  = false;
                    $_class = '';
                    $k      = $item['name'];
                    if (isset($err[$k]) && $err[$k] == true) {
                        $error = true;
                    }
                    if ($error) {
                        $_class = 'class="error"';
                    }
                    if ($k !== 'msg') {
                        echo '<p>';
                    } else {
                        echo '<p class="wideText">';
                    }
                    echo '<label>' . $item['label'] . '</label>';
                    if ($k !== 'msg') {
                        isset($ord[$k]) || $ord[$k] = '';
                        echo '<input type="text" name="' . $k . '" ' . $_class . ' value="' . $ord[$k] . '"/>';
                        if (isset($item['validate']) && ($item['validate'] !== false && (isset($item['validate']['0']) && $item['validate']['0'] !== -1))) {
                            echo '<span>*</span>';
                        } else {
                            echo '<span>&nbsp;</span>';
                        }
                    } else {
                        echo '<textarea name="msg">' . $ord['msg'] . '</textarea>';
                    }
                    $spanStyle = ($error) ? 'block' : 'none';
                    echo '<span class="tip" style="display:' . $spanStyle . '">';
                    if (!empty($item['tip'])) {
                        $tip = $item['tip'];
                    } else {
                        $tip = '请填写正确的' . $item['label'];
                    }
                    echo $tip;
                    echo '</span></p><div class="clear"></div>';
                }
                ?>


            </div>
            <div class="clear"></div>

            <div id="payInfo">
                <h2>第三步：选择付款方式</h2>
                <hr class="dot"/>
                <ul>
                    <?php


                    foreach ($arr_buyInfo as $k => $item) {
                        $innerHtml = ws_alipay_wRCheck('paygate', $k, $ord['paygate'], $item[1]);
                        echo '<li>
			<input type="radio" ' . $innerHtml . '/>
			<img src="../styles/images/' . strtolower($k) . '_logo.gif" class="' . strtolower(
                              $k
                          ) . 'Logo" title="' . $item[0] . '"/>
          </li>';
                    }

                    ?>

                    <div class="clear"></div>

                </ul>
                <div class="clear"></div>
                <div id="bankList" style='<?php echo $bankListStyle ?>'>

                    <h2 class="bankHeader">
                        <img src="../styles/images/tenpay_logo.gif"
                             title="财付通"/>为您提供以下网上支付服务<span>（财付通是腾讯旗下第三方支付平台）</span></h2>

                    <div class="clear"></div>

                    <ul>
                        <?php

                        foreach ($arr_bankOrder as $i) {
                            $top       = -1 * $arr_banks[$i][1];
                            $innerHtml = ws_alipay_wRCheck('bankType', $i, $ord['bankType']);
                            echo '<li style="background-position:30px ' . $top . 'px" title="' . $arr_banks[$i][0] . '">
	<input type="radio" ' . $innerHtml . '/></li>';
                        }

                        ?>
                    </ul>
                    <div class="clear"></div>
                </div>
                <!--END OF BANKLIST-->

            </div>
            <!--END OF PAYINFO-->

            <div id="paySubmit">
                <h2>第四步：去收银台结账</h2>
                <hr class="dot"/>
                <input type="submit" value="确定订单并付款"/>
            </div>
            <input type="hidden" name="referer" value="<?php echo $ord['referer']; ?>"/>
            <input type="hidden" name="nonce" value="<?php echo $nonce; ?>"/>
            <input type="hidden" name="proid" value="<?php echo $proid; ?>"/>
            <input type="hidden" name="cartSubmit" value="1"/>
        </form>
    </div>
    <!--END OF MAIN PATCH-->
</div>
<!--END OF MAIN-->

<div id="footer">
    <?php echo apply_filters('ws_alipay_cartFooter', null); ?>
    <div id="copyright">
        <p>
            Powered by <a href="<?php echo $siteUrl; ?>"><?php echo $siteName; ?></a> . Provided by <a
              href="http://www.waisir.com">歪世界</a>
        </p>

        <p>
            <?php echo $footer_Copyright; ?>
        </p>
    </div>
    <p class="bankpreload"></p>
</div>
<!--EN OF FOOTER-->

</body>
</html>