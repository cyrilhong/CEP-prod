<?php




class WS_Alipay_AdWidget extends WP_Widget
{

    //构造函数
    function WS_Alipay_AdWidget()
    {
        parent::WP_Widget('ws_alipay', $name = '支付宝广告', array('description' => '使用该小工具可以添加一个自助广告位'));
    }

    function __construct()
    {
        $this->WS_Alipay_AdWidget();
    }

    //前台HTML
    function widget($args, $ins)
    {
        require_once(WS_ALIPAY_INC . 'cls.info.php');
        $ad  = new WS_Alipay_Ads($ins['proId']);
        $ads = $ad->get($ad->adfield);
        if (isset($_POST['ws_alipay_ad_preview'])) {
            $ins['imgSrc']  = $_POST['ws_alipay_ad_preview_src'];
            $ins['imgHref'] = $_POST['ws_alipay_ad_preview_url'];
            $formcls        = '';
            $formcls_s      = 'ws_alipay_widget_hide';
        } elseif ($ads[0]['endTime'] > time()) {
            $ins['imgSrc']  = $ads[0]['imgSrc'];
            $ins['imgHref'] = $ads[0]['imgLink'];
            $formcls        = '';
            $formcls_s      = 'ws_alipay_widget_hide';
        } else {
            $ins['imgSrc']  = $ins['imgSrc'];
            $ins['imgHref'] = 'javascript:void(0);';
            $formcls        = 'ws_alipay_widget_form';
            $formcls_s      = 'ws_alipay_widget_show';
            if ($ins['showChar'] == 'show') {
                $formcls_s = 'ws_alipay_widget_show';
            } else {
                $formcls_s = 'ws_alipay_widget_hide';
            }
        }
        extract($args);
        if (empty($ins['proId'])) {
            $tip = __('proid is not found!', 'wsali');
        } else {
            $tip = '';
        }
        $html = <<<HTML
$before_widget 
$before_title {$ins['title']} $after_title
<div class="ws_alipay_widget_wrap ws_alipay_buy_wrap"><!--1-->
<a href="{$ins['imgHref']}" target="_blank"><img src="{$ins['imgSrc']}" width="{$ins['imgWidth']}px" height="{$ins['imgHeight']}px" /></a>

<div class="$formcls_s" style="width:{$ins['imgWidth']}px;height:{$ins['imgHeight']}px;">
<p class="ws_alipay_widget_size">{$ins['imgWidth']}&nbsp;&nbsp;X&nbsp;&nbsp;{$ins['imgHeight']}</p>
$tip 
<p class="ws_alipay_widget_char">{&nbsp;火爆招商&nbsp;}</p>
<p class="ws_alipay_widget_char">{&nbsp;虚位以待&nbsp;}</p>
<p class="ws_alipay_widget_char">自助广告&nbsp;:&nbsp;{$this->choosePrice($ins)}</p>
<input type="button" value="试一试" name="ws_alipay_ad_preview" class="ws_alipay_widget_try"/>
</div>

<div class="$formcls" style="display:none;width:{$ins['imgWidth']}px;height:{$ins['imgHeight']}px;">
<!--2-->
<div class="ws_alipay_widget_form_wrap ws_alipay_buy_wrap"><!--3-->
<form method="POST" target="_blank">
<p>
<label>粘贴入您的广告图片地址</label>
<input type="text" name="ws_alipay_ad_preview_src" class="ws_alipay_buy_imgSrc"/>
</p>

<p>
<label>跳转地址</label>
<input type="text" name="ws_alipay_ad_preview_url" class="ws_alipay_buy_imgLink"/>
</p>

<p>

<input type="hidden" class="ws_alipay_buy_fields" value="imgSrc,imgLink,proid"/>
<input type="hidden" class="ws_alipay_buy_proid" value="{$ins['proId']}"/>
<input type="button" value="订购" name="ws_alipay_ad_order" class="ws_alipay_widget_btn ws_alipay_buy_pay"/>
<input type="submit" value="预览" name="ws_alipay_ad_preview" class="ws_alipay_widget_btn ws_alipay_widget_preview"/>
</p>

</form>
</div><!--3-->
</div><!--2-->
</div><!--1-->

$after_widget
HTML;
        echo $html;
    }

    //更新事件数据过滤器
    function update($new_instance, $old_instance)
    {
        $ins                    = $new_instance;
        $ins['title']           = strip_tags($ins['title']);
        $ins['imgWidth']        = (int)$ins['imgWidth'];
        $ins['imgHeight']       = (int)$ins['imgHeight'];
        $ins['pricePerDay']     = number_format(abs(floatval($ins['pricePerDay'])), 2, '.', '');
        $ins['pricePerWeek']    = number_format(abs(floatval($ins['pricePerWeek'])), 2, '.', '');
        $ins['pricePerMonth']   = number_format(abs(floatval($ins['pricePerMonth'])), 2, '.', '');
        $ins['pricePerQuarter'] = number_format(abs(floatval($ins['pricePerQuarter'])), 2, '.', '');
        $ins['pricePerYear']    = number_format(abs(floatval($ins['pricePerYear'])), 2, '.', '');
        $multiPrice             = array(
            'protype'         => 'ADP',
            'pricePerDay'     => $ins['pricePerDay'],
            'pricePerWeek'    => $ins['pricePerWeek'],
            'pricePerMonth'   => $ins['pricePerMonth'],
            'pricePerQuarter' => $ins['pricePerQuarter'],
            'pricePerYear'    => $ins['pricePerYear'],
        );
        include(WS_ALIPAY_INC . 'cls.info.php');
        $pro     = new ws_alipay_product($ins['proId']);
        $proInfo = $pro->set('', '', $multiPrice);
        return $ins;
    }

    //后台HTML
    function form($ins)
    {
        //echo $this->number;
        $arr_fields = array( /*'title',*/ /*'imgSrc',*/ /*'imgWidth','imgHeight',*/
            'proId',
            'showChar' /*'cyclePrice',*/ /*'cycleUnit'*/
        );
        $ins        = ws_alipay_no_empty($arr_fields, $ins);
        isset($ins['cycleUnit']) || $ins['cycleUnit'] = 'M';
        isset($ins['title']) || $ins['title'] = __('Auto Ads', 'wsali');
        isset($ins['imgSrc']) || $ins['imgSrc'] = WS_ALIPAY_IMG_URL . '/ad_01.gif';
        isset($ins['imgWidth']) || $ins['imgWidth'] = 250;
        isset($ins['imgHeight']) || $ins['imgHeight'] = 200;
        isset($ins['cyclePrice']) || $ins['cyclePrice'] = 300;
        isset($ins['pricePerDay']) || $ins['pricePerDay'] = 8;
        isset($ins['pricePerWeek']) || $ins['pricePerWeek'] = 48;
        isset($ins['pricePerMonth']) || $ins['pricePerMonth'] = 188;
        isset($ins['pricePerQuarter']) || $ins['pricePerQuarter'] = 548;
        isset($ins['pricePerYear']) || $ins['pricePerYear'] = 1888;
        $arr_input    = array(
            'title'           => array(__('Title', 'wsali')),
            'imgSrc'          => array(__('ImageSrc', 'wsali')),
            'imgWidth'        => array(__('Image', 'wsali') . __('Width', 'wsali') . '(px)'),
            'imgHeight'       => array(__('Image', 'wsali') . __('Height', 'wsali') . '(px)'),
            'proId'           => array(__('productid', 'wsali')),
            'pricePerDay'     => array(__('pricePerDay', 'wsali')),
            'pricePerWeek'    => array(__('pricePerWeek', 'wsali')),
            'pricePerMonth'   => array(__('pricePerMonth', 'wsali')),
            'pricePerQuarter' => array(__('pricePerQuarter', 'wsali')),
            'pricePerYear'    => array(__('pricePerYear', 'wsali')),
            //'cyclePrice' => array(__('cyclePrice','wsali')),
            //'cycleUnit'  => array(__('cycleUnit','wsali') , 'html'=>'
            //				<select style="display:block;width:100%" name="'.$this->get_field_name('cycleUnit').'" id="'.$this->get_field_id('cycleUnit').'">
            //			<option value="D"'.$this->sl($ins['cycleUnit'],'D').'>'.__('day','wsali').'</option>
            //			<option value="W"'.$this->sl($ins['cycleUnit'],'W').'>'.__('week','wsali').'</option>
            //			<option value="M"'.$this->sl($ins['cycleUnit'],'M').'>'.__('month','wsali').'</option>
            //			<option value="Q"'.$this->sl($ins['cycleUnit'],'Q').'>'.__('quarter','wsali').'</option>
            //			<option value="Y"'.$this->sl($ins['cycleUnit'],'Y').'>'.__('year','wsali').'</option>
            //				</select>
            //			'),
            'showChar'        => array(
                __('show the characters', 'wsali'),
                'html' => '
				<select style="display:block;width:100%" name="' . $this->get_field_name(
                      'showChar'
                  ) . '" id="' . $this->get_field_id('showChar') . '">
		<option value="show"' . $this->sl($ins['showChar'], 'show') . '>' . __('show', 'wsali') . '</option>
		<option value="hide"' . $this->sl($ins['showChar'], 'hide') . '>' . __('hide', 'wsali') . '</option>
				</select>
			'
            ),
        );
        $ins['title'] = esc_attr($ins['title']);
        echo '<p>';
        foreach ($arr_input as $k => $v) {
            $name = $this->get_field_name($k);
            $id   = $this->get_field_id($k);
            $val  = $ins[$k];
            echo '<label for="' . $name . '">' . _e($v[0], 'wsali') . ':</label>';
            if (isset($v['html'])) {
                echo $v['html'];
            } else {
                echo '
<input class="widefat" id="' . $id . '"  name="' . $name . '" type="text" value="' . $val . '" />';
            }
        }
        echo '</p>';
        ?>

    <?php
    }

    //-----------------------------------------------------------------------
    // TOOLS
    //-----------------------------------------------------------------------
    function sl($value, $default)
    {
        return ($value == $default) ? ' selected="selected" ' : '';
    }

    function e2c($e)
    {
        switch ($e) {
            case 'D':
                $c = __('Day', 'wsali');
                break;
            case 'W':
                $c = __('Week', 'wsali');
                break;
            case 'M':
                $c = __('Month', 'wsali');
                break;
            case 'Q':
                $c = __('Quarter', 'wsali');
                break;
            case 'Y':
                $c = __('Year', 'wsali');
                break;
            default:
                $c = $e;
        }
        return $c;
    }

    function choosePrice($ins)
    {
        $arr_fields = array('pricePerDay', 'pricePerWeek', 'pricePerMonth', 'pricePerQuarter', 'pricePerYear');
        $ins        = ws_alipay_no_empty($arr_fields, $ins);
        if (floatval($ins['pricePerDay']) > 0) {
            return "￥{$ins['pricePerDay']}/" . __('Day', 'wsali');
        } elseif (floatval($ins['pricePerWeek']) > 0) {
            return "￥{$ins['pricePerWeek']}/" . __('Week', 'wsali');
        } elseif (floatval($ins['pricePerMonth']) > 0) {
            return "￥{$ins['pricePerMonth']}/" . __('Month', 'wsali');
        } elseif (floatval($ins['pricePerQuarter']) > 0) {
            return "￥{$ins['pricePerQuarter']}/" . __('Quarte', 'wsali');
        } elseif (floatval($ins['pricePerYear']) > 0) {
            return "￥{$ins['pricePerYear']}/" . __('Year', 'wsali');
        }
    }
} // class FooWidget
// 注册 FooWidget 挂件
add_action('widgets_init', create_function('', 'return register_widget("ws_alipay_AdWidget");'));

