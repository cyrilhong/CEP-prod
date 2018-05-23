<?php
require_once('cfg.config.php');
header('Content-Type:text/html; charset=' . WS_ALIPAY_CHARSET);
//预处理未定义的索引
isset($_REQUEST['info']) || $_REQUEST['info'] = '';
isset($_REQUEST['trano']) || $_REQUEST['trano'] = '';
isset($_REQUEST['time']) || $_REQUEST['time'] = '';
isset($_REQUEST['sign']) || $_REQUEST['sign'] = '';
$_REQUEST['time'] !== '' || $_REQUEST['time'] = time();

$_REQUEST['info'] = wp_kses(
    $_REQUEST['info'],
    array('a' => array('href', 'target', 'title'), 'b' => array(), 'div' => array(), 'p' => array())
);
//使用密钥验证公钥的合法性
//$nonce = wp_create_nonce('ws_alipay_tip_sign');
$key = AUTH_KEY;
$sign = md5($_REQUEST['info'] . $_REQUEST['trano'] . $_REQUEST['time'] . $key);

if (!(isset($_REQUEST['pms']) && $_REQUEST['pms'] == 'sudo')) {
    if ($sign !== $_REQUEST['sign']) {
        $_REQUEST['info'] = 'SIGN_INVALID';
    }
}

$mainTitle = '支付提示:';
$datetime = date('Y-m-d H:i:s', $_REQUEST['time']);
$siteName = get_option('blogname');
$siteUrl = get_option('siteurl');
$year = date('Y', time());
$year2 = $year + 1;
$footer = "
$datetime<br />
Copyright &copy; {$year}-{$year2} <a href=$siteUrl>$siteName</a> All Rights Reserved
";


$success_img = 'http://www.iconpng.com/png/gnome-desktop/dialog-apply.png';
$attention_img = 'http://www.iconpng.com/png/sm-reflection-b/exclamation-diamond.png';

//PAY_SUCCESS:支付成功
//SIGN_INVALID:签名不合法
//PRO_EMPTY:商品余量不足
//NONCE_EMPTY:校验码为空
//NONCE_INVALID:校验码过期
//VERIFY_FAILED:权限验证失败
//TIMEOUT:连接超时
//UNSUPPORTED_GATE:不支持的支付方式
//其他:未知的结果

switch (strtoupper($_REQUEST['info'])) {
    case 'PAY_SUCCESS':
        $info = array(
            'h1'     => '支付成功',
            'img'    => $success_img,
            'msg'    => "
							感谢您的购买!<br />
							请记下您的订单号: {$_REQUEST['trano']}<br />
							您随时可以关闭此页面!<br />
							",
            'msg_en' => "
							Thanks for your payment! <br />
							Please take a note of the trade number which is {$_REQUEST['trano']} <br />
							Close the window anytime with pleasure!
							",
        );
        break;
    case 'SIGN_INVALID':
        $info = array(
            'h1'     => '签名不合法',
            'img'    => $attention_img,
            'msg'    => "
							签名不合法!<br />
							您可能来自未知渠道或者修改过该页的URL.如感到困惑,请联系管理员!<br />
							您随时可以关闭此页面!<br />
							",
            'msg_en' => "Sign invalid!<br />May you not goto this page from our site? <br />Contact with the administrator please!<br />Close the window anytime with pleasure! ",
        );
        break;
    case 'PRO_EMPTY':
        $info = array(
            'h1'     => '商品剩余数量不足',
            'img'    => $attention_img,
            'msg'    => "
							商品剩余数量不足!<br />
							交易无法继续进行!您的资金尚未扣除!请提醒管理员添加商品!<br />
							您随时可以关闭此页面!<br />
							",
            'msg_en' => "Goods' amount come to an end!<br />The process stopped for that! <br />Contact with the administrator please!<br />Close the window anytime with pleasure! ",
        );
        break;
    case 'NONCE_EMPTY':
        $info = array(
            'h1'     => '校验码为空',
            'img'    => $attention_img,
            'msg'    => "
							校验码为空! <br />
							交易无法继续进行! 您的资金尚未扣除!请联系管理员!<br />
							您随时可以关闭此页面!<br />
							",
            'msg_en' => "The NONCE code seems to be empty!<br />The process stopped for that! <br />Contact with the administrator please!<br />Close the window anytime with pleasure! ",
        );
        break;
    case 'NONCE_INVALID':
        $info = array(
            'h1'     => '校验码已过期',
            'img'    => $attention_img,
            'msg'    => "
							校验码已过期!<br />
							交易无法继续进行!您的资金尚未扣除!请提醒管理员更新缓存!<br />
							您随时可以关闭此页面!<br />
							",
            'msg_en' => "Expired NONCE code!<br />The process stopped for not refreshing cache! <br />Contact with the administrator please!<br />Close the window anytime with pleasure! ",
        );
        break;
    case 'VERIFY_FAILED':
        $info = array(
            'h1'     => '权限验证失败',
            'img'    => $attention_img,
            'msg'    => "
							权限验证失败!<br />
							您没有访问该页面的权限!<br />
							您随时可以关闭此页面!<br />
							",
            'msg_en' => "Authority Checked Failed!<br />There is not enough permission for you to process this page! <br />Close the window anytime with pleasure! ",
        );
        break;
    case 'TIMEOUT':
        $info = array(
            'h1'     => '连接服务器超时',
            'img'    => $attention_img,
            'msg'    => "
							连接服务器超时!<br />
							您暂时无法完成支付, 这可能是网站服务器的问题!请联系管理员<br />
							您随时可以关闭此页面!<br />
							",
            'msg_en' => "Connect to server timeout!<br />It seems to be something wrong with the server! <br />Contact with the administrator please!<br />Close the window anytime with pleasure! ",
        );
        break;
    case 'UNSUPPORTED_GATE':
        $info = array(
            'h1'     => '不支持该支付方式',
            'img'    => $attention_img,
            'msg'    => "
							不支持该支付方式!<br />
							暂时不支持此支付方式,请选择<a href='javascript:history.go(-1)'>其他支付方式</a>.谢谢!<br />
							您随时可以关闭此页面!<br />
							",
            'msg_en' => "Unsupported pay gate!<br />The gate you choose is not supported by our site! <br />Choose another please!<br />Close the window anytime with pleasure! ",
        );
        break;
    default:
        $info = array(
            'h1'     => '未知的结果',
            'img'    => $attention_img,
            'msg'    => "
							未知的结果:" . strtoupper($_REQUEST['info']) . "!<br />
							这可能是程序开发中产生的错误,请联系管理员!您的资金尚未扣除!<br />
							您随时可以关闭此页面!<br />
							",
            'msg_en' => "Unknown results:" . strtoupper(
                  $_REQUEST['info']
              ) . "!<br />It seems to be something wrong with our application code.<br />Contact with the administrator please!<br />Close the window anytime with pleasure! ",
        );
        break;
}

?>

<!DOCTYPE HTML>
<html>
<head>
    <link rel="shortcut icon" href="https://img.alipay.com/common/favicon/favicon.ico" type="image/x-icon"/>
    <title><?php echo $mainTitle . $info['h1']; ?></title>
    <style>
        html, body{
            width:100%;
            height:100%;
            margin:0;
            padding:0;
            background:#eef;
            font-family:微 软 雅 黑, Microsoft YaHei, simsun;
        }
        body *{
            margin:0;
            padding:0
        }
        #mainContariner{
            display:block;
            position:absolute;
            width:640px;
            height:320px;
            top:50%;
            left:50%;
            background:#fff;
            margin-left:-320px;
            margin-top:-180px;
            border:solid 1px #390; /*border-radius:5px*/;
            cursor:pointer
        }
        h1{
            width:100%;
            text-align:center;
            font-size:25px;
            padding-top:5px;
            padding-bottom:5px;
            background:url(../styles/images/h1_gray.png) repeat-x;
            color:#444
        }
        #content{
            margin:20px 80px 10px;
            font-size:14px;
            line-height:2.5em;
            font-family:Microsoft YaHei, simsun;
        }
        #content_en{
            margin:0px 80px 10px;
            font-size:12px;
            line-height:1.5em;
            font-family:Microsoft YaHei, simsun;
            color:#888
        }
        #footer{
            position:absolute;
            bottom:10px;
            right:20px;
            font-size:12px;
            line-height:1.5em;
            text-align:right;
            font-style:italic
        }
        .tip_img{
            position:absolute;
            left:10px;
            top:50px
        }

        .tl, .tr, .bl, .br{
            display:block;
            position:absolute;;
            width:5px;
            height:5px;
            background-repeat:no-repeat;
        }
        .tl{
            top:-1px;
            left:-1px;
            background-position:top left;
            background-image:url(../styles/images/cn_g_01.png)
        }
        .tr{
            top:-1px;
            right:-1px;
            _right:-2px;
            background-position:top right;
            background-image:url(../styles/images/cn_g_02.png)
        }
        .bl{
            bottom:-1px;
            _bottom:-2px;
            left:-1px;
            background-position:bottom left;
            background-image:url(../styles/images/cn_g_03.png)
        }
        .br{
            bottom:-1px;
            _bottom:-2px;
            right:-1px;
            _right:-2px;
            background-position:bottom right;
            background-image:url(../styles/images/cn_g_04.png)
        }
        a{
            text-decoration:none;
        }
    </style>


</head>
<body>
<div id="mainContariner">
    <div class="tl"></div>
    <div class="tr"></div>
    <div class="bl"></div>
    <div class="br"></div>
    <img class="tip_img" src="<?php echo $info['img']; ?>">

    <h1><?php echo $info['h1']; ?></h1>

    <div id="content">

        <?php echo $info['msg']; ?>
    </div>

    <div id="content_en">

        <?php echo $info['msg_en']; ?>
        <hr/>
    </div>

    <div id="footer">

        <?php echo $footer; ?>
    </div>

</div>

</body>
</html>