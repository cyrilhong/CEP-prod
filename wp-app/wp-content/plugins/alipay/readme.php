<?php


$c = array();
//$c[] = array('anchor'=>'','cat'=>1,'head'=>'','content'=>'');
$c[] = array(
    'anchor'  => 'before-use',
    'cat'     => 1,
    'head'    => '申请一个支付许可',
    'content' => '该插件根据支付宝,财付通,贝宝的支付接口集合而成,因此使用本插件之前你至少需要一个支付许可.关于如何申请,这里不详细说明,请到相应的官方网站查看帮助或者询问客服.谢谢(注意:您需要申请的是"即时到帐")'
);

$c[] = array(
    'anchor'  => 'broswers-suggest',
    'cat'     => 1,
    'head'    => '获得更好的插件体验',
    'content' => '请升级浏览器到最新版本,推荐您使用Firefox火狐浏览器!'
);
$c[] = array(
    'anchor'  => 'safty-caution',
    'cat'     => 1,
    'head'    => '插件安全使用警告',
    'content' => '为保证贵网站的交易的安全,请务必到插件官方网站下载插件,不使用第三方修改插件或者补丁,否则将可能给您带来损失!'
);
$c[] = array(
    'anchor'  => 'feedback',
    'cat'     => 1,
    'head'    => '欢迎您的反馈',
    'content' => '由于各种因素,插件可能会有各种不尽人意的地方,如果你愿意,欢迎您到我的博客进行反馈,帮助提升插件的体验!'
);

$c[] = array(
    'anchor'  => 'emial-support',
    'cat'     => 1,
    'head'    => '请启用邮件支持',
    'content' => '本插件在交易的同时很大程度上依赖于Email的交互,如果您是租用的的主机,那么您几乎不必担心邮件的问题,如果您是自己搭建的主机,请安装SMTP相关插件启用Email支持!'
);
$c[] = array(
    'anchor'  => 'which2giveup',
    'cat'     => 1,
    'head'    => '网站运行慢的站长请注意',
    'content' => '如果您租用的主机经常出现数据库无法链接,访问超时等不良访问情况, 我们不建议您立即使用该插件, 因为这可能造成订单丢失和反馈失败或者其他的情况以至于交易无法顺利完成.我们建议您选择口碑较好的知名主机商.'
);
$c[] = array(
    'anchor'  => 'protect-key',
    'cat'     => 1,
    'head'    => '保护您的接口密钥',
    'content' => '正如您所见,您可以直接在常规设置面板中设置您的接口密钥. 如果您觉得密钥被保存在数据库中不安全,你可以打开插件的文件进行密钥变量设置,具体如下:<br />支付宝密钥:需修改文件是:..includes/api_alipay/cls.alipay_submit.php和..includes/api_alipay/cls.alipay_notify.php,搜索private $key = \'\';(一般就在文件的前10行),将你的接口密钥填入其中即可<br />财付通密钥:修改文件cls.tenpay_return.php和cls.tenpay_submit.php,修改方法雷同<br />修改完后请在该插件的常规设置面板中将对应的密钥字段清空即可'
);
$c[] = array(
    'anchor'  => 'delete-caution',
    'cat'     => 1,
    'head'    => '请勿随意删除订单和商品',
    'content' => '如果您是自己在测试插件所产生的订单,删除订单尚无大碍,但是如果是客户提交的订单,建议您不要在短期内删除相关商品和订单,否则可能造成付款后无法自动发货.这个BUG将会在下一个版本中修复!'
);
$c[] = array(
    'anchor'  => 'create-template',
    'cat'     => 1,
    'head'    => '如何创建一个模版',
    'content' => '考虑到各网站的风格不尽相同,色调参差不齐,插件无法满足用户名的模版风格需求,由于HTML标记语言站长们基本都能懂,因此将模版的美化工作交给广大的插件使用者了.美化方法如下:<br />在插件的"模版管理"中,你可以看到有CSS,HTML,JS代码的输入框,你更多的需要关注HTML和CSS的编写,而JS可以提升用户的体验,但不是必须.如下范例<br /><textarea>' . hs(
          '<div><label>收件人姓名:</label><input type="text" class="[in_ordname]" value=""/></div>
          <div><label>收件人电话:</label><input type="text" class="[in_tel]" value=""/></div>
          <div><label>收件人邮编:</label><input type="text" class="[in_postcode]" value=""/></div>
          <div><label>收件人地址:</label><input type="text" class="[in_addr]" value=""/></div>
          <div><label>备注信息:</label></div>
          <div><textarea class="[in_extra]"></textarea></div>
          <div><label>买家留言:</label></div>
          <div><textarea class="[in_msg]"></textarea></div>
          <div><label>收货邮箱:</label></div>
          <div><input type="text" class="[in_email]" value="" /></div>
          <div><label>购买数量:</label></div>
          <div><select class="[in_num]">
          <option value="1">1件</option>
          <option value="2">2件</option>
          <option value="3">3件</option>
          </select></div>
          <div><input type="button" class="[in_pay]" value="点此购买"></div>'
      ) . '</textarea><br />您至少需要布置点击购买按钮,即:' . hs('<div><input type="button" class="[in_pay]" value="点此购买"></div>') . '<br />其他的,你还可以添加买家邮箱等信息,他们的布置都遵循一个规则,都使用表单的标签,且在其class属性中添加如[in_email]这样的短代码.<br />但是这个短代码也不是随意写的,首先需要用户输入的都用in_开头,需要显示给用户看的都用out_开头,目前支持的字段如下:<br />
<table border="1px" cellspacing="0">
<thead>
	<tr>
		<th>标签代码</th><th>代码含义</th>
	</tr>
</thead>
<tbody>
	
	<td>in_pay</td><td>点击购买(用于type="button")</td></tr>
	<tr><td>in_num</td><td>购买数量</td></tr>
	<tr><td>in_email</td><td>买家邮箱</td></tr>
	<tr><td>in_addr</td><td>收件人详细地址</td></tr>
	<tr><td>in_tel</td><td>收件人电话/手机</td></tr>
	<tr><td>in_ordname</td><td>收件人姓名</td></tr>
	<tr><td>in_postcode</td><td>收件人邮编</td></tr>
	<tr><td>in_imgSrc</td><td>图片地址</td></tr>
	<tr><td>in_imgLink</td><td>点击图片跳转地址</td></tr>
	<tr><td>in_linkName</td><td>链接名称</td></tr>
	<tr><td>in_linkUrl</td><td>链接地址</td></tr>
	<tr><td>in_linkDesc</td><td>链接描述</td></tr>
	
	<tr><td>■■■■■■■■■■</td><td>■■■■■■■■■■</td></tr>
	
	<tr><td>out_proid</td><td>商品ID</td></tr>
	<tr><td>out_name</td><td>商品名称</td></tr>
	<tr><td>out_price</td><td>商品价格</td></tr>
	<tr><td>out_oprice</td><td>商品原价</td></tr>
	<tr><td>out_cprice</td><td>商品当前价格</td></tr>
	<tr><td>out_sprice</td><td>当前购买所能节约的价格</td></tr>
	<tr><td>out_num</td><td>商品剩余数量</td></tr>
	<tr><td>out_snum</td><td>卖出数量</td></tr>
	<tr><td>out_weight</td><td>商品重量KG</td></tr>
	<tr><td>out_description</td><td>商品描述</td></tr>
	<tr><td>out_images</td><td>商品图片</td></tr>
	<tr><td>out_service</td><td>客服QQ/阿里旺旺</td></tr>
	<tr><td>out_download</td><td>下载链接</td></tr>
	<tr><td>out_zipcode</td><td>解压密码</td></tr>
	<tr><td>out_callback</td><td>返回地址</td></tr>
	<tr><td>out_categories</td><td>商品分类</td></tr>
	<tr><td>out_tags</td><td>商品标签</td></tr>
	<tr><td>out_spfre</td><td>买家承担运费b</td></tr>
	<tr><td>out_freight</td><td>运费</td></tr>
	<tr><td>out_location</td><td>所在地</td></tr>
	<tr><td>out_atime</td><td>商品添加时间戳</td></tr>
	<tr><td>out_btime</td><td>商品开始时间戳</td></tr>
	<tr><td>out_etime</td><td>商品结束时间戳</td></tr>
	<tr><td>out_probdate</td><td>促销开始日期</td></tr>
	<tr><td>out_proedate</td><td>促销结束日期</td></tr>
	<tr><td>out_probtime</td><td>促销开始时刻</td></tr>
	<tr><td>out_proetime</td><td>促销结束时刻</td></tr>
	<tr><td>out_discount</td><td>商品折扣</td></tr>

</tbody>
</table>'
);
$c[] = array(
    'anchor'  => 'how2usetpl',
    'cat'     => 1,
    'head'    => '如何使用已创建的模版',
    'content' => '每一个模版都对应一个唯一的ID,这个ID可以在模版管理中查看,将该ID填入商品管理中商品的"模版选择"字段中即可使用该模版'
);
$c[] = array(
    'anchor'  => 'how2useinpost',
    'cat'     => 1,
    'head'    => '如何在文章中调用商品',
    'content' => '在文章编辑器中使用短代码[zfb id=100]即可调用商品id为100的商品.以此类推'
);
$c[] = array(
    'anchor'  => 'mailfailed',
    'cat'     => 1,
    'head'    => '无法发送邮件',
    'content' => '请检查垃圾箱中是否有邮件.建议您使用SMTP验证身份的邮件进行发送'
);
$c[] = array('anchor' => 'refreshfailed', 'cat' => 1, 'head' => '后台数据更新滞后', 'content' => '出现这个情况很可能是您启用了数据库缓存等插件造成的!');
$c[] = array('anchor' => 'tplloadfail', 'cat' => 1, 'head' => '模版无法读取', 'content' => '这是老版本问题,请升级到最新版!');

$menu = '<ol>';
$content = '';

foreach ($c as $item) {
    !empty($item['anchor']) || $item['anchor'] = 'anchor' . rand(10000, 99999);
    $content .= '<a name="' . $item['anchor'] . '"></a>';
    $content .= '<h3>◆' . $item['head'] . '</h3>';
    $menu .= '<li><a href="#' . $item['anchor'] . '">' . $item['head'] . '</a></li>';
    $content .= '<p>' . $item['content'] . '</p>';
}
$menu .= '</ol>';

function hs($in)
{
    return htmlspecialchars($in);
}

?>
<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>集成支付宝WordPress插件帮助文档</title>
    <script src="http://lib.sinaapp.com/js/jquery/1.5.2/jquery.min.js"></script>

    <style type="text/css">
        html, body, *{
            margin:0;
            padding:0;
            font-family:微 软 雅 黑
        }
        #main_wrap{
            margin:20px
        }
        #main_wrap #menu ol{
            padding:20px;
            padding-left:25%;
            background:#CCC;
            border-radius:5px;
            margin-bottom:10px
        }
        #main_wrap #menu p{
            text-align:center;
            font-size:24px;
        }
        #main_wrap #content{
            background:#eee;
            padding:10px 20px;
            border-radius:5px
        }
        #main_wrap #content h3{
            color:#966;
            margin-bottom:5px
        }
        #main_wrap #content p{
            text-indent:2em;
            margin-bottom:20px
        }
        #main_wrap #content textarea{
            width:90%;
            min-height:100px
        }
        #main_wrap #content table th, #main_wrap #content table td{
            width:300px;
            padding-left:15px
        }
        #translate{
            float:right;
            height:80px;
            overflow:hidden
        }
        #topbar{
            width:50px;
            height:50px;
            background:#903;
            border-radius:10px;
            position:fixed;
            right:20px;
            bottom:20px;
            opacity:0.5;
            color:#000;
            text-align:center;
            line-height:50px
        }
        #topbar:hover{
            background:#900
        }
        h1{
            background:#BF0630;
            padding:5px 30px;
            float:left;
            text-shadow:#000 0px -1px;
            color:#CCC;
            margin-bottom:30px;
            border-radius:15px 0 15px 0
        }
        .clear{
            clear:both;
            height:0 !important;
            float:none
        }
    </style>

    <script type="text/javascript">
        $(function() {


        });//END OF JQUERY
    </script>

</head>

<body>
<div id="main_wrap">

    <div id="child_wrap">
        <a name="top"></a>

        <h1>集成支付宝WordPress插件帮助文档</h1>

        <div id="translate">
            <script
              src="http://gmodules.com/ig/ifr?url=http://www.google.com/ig/modules/translatemypage.xml&up_source_language=zh-CN&w=180&h=75&title=&border=http://&output=js"></script>
        </div>
        <div class="clear"></div>
        <div id="menu">
            <p>目录(MENU)</p>
            <?php echo $menu; ?>
        </div>
        <div id="content">
            <?php echo $content; ?>
        </div>
    </div>

</div>

<a href="#top">
    <div id="topbar">TOP</div>
</a>
</body>
</html>