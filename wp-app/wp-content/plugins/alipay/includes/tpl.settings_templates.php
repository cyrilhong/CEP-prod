<?php
/*
 additem of settings page
*/

/*
'tplid',模版ID-INT AUTO INCREAMENT
'tplname',模版名称-VARCHAR(20)
'tpldescription',模版描述-VARCHAR(255)
'tpljs',javascript-TEXT
'tplcss',CSS-TEXT
'tplhtml'HTML-TEXT
*/
require_once('cfg.config.php');


?>

<!--#########################################################################-->

<script type="text/javascript">
    //THE SECURITY CODE
    var $ws_alipay_security_code = '<?php echo ws_alipay_security_code();?>';


    var $this = {
        prefix: 'ws_alipay_table_',
        css: {nav: 'ws_alipay_table_nav'},
        id: {
            hotkey: 'ws_alipay_main_wrap',
            mainWrap: 'ws_alipay_child_wrap',
            formMore: 'ws_alipay_table_more_form',
            table: 'ws_table_id',
            tableParent: 'ws_alipay_child_wrap'
        },
        class: {table: 'widefat ws_alipay_table'},
        arr: {
            head: ['模版编号', '模版名称', '模版描述'],
            colsWidth: ['5%', '10%', '25%', '40%', '5%', '5%', '5%']
        },
        num: {cols: 7},
        pos: {primaryKey: 1, rowIndex: 0},
        bln: {showMore: 0, showEdit: 1, showDelete: 1, showCopy: 1},
        currentKey: 0,
        fields: 'tplid,tplname,tpldescription',	//this should be changed
        primaryKey: 'tplid',
        tabname: 'templates',//this should be changed AND MUST BE THE SAME AS THE DB.LOADER.PHP'S TABLE
        showFields: {
            tplid: '',
            tplname: '',
            tpldescription: ''
        },
        showLength: {
            tplid: 10,
            tplname: 10,
            tpldescription: 25
        },
        getClass: function(afix) {
            return '.' + this.prefix + afix;
        },
        getClassNoDot: function(afix) {
            return  this.prefix + afix;
        },
        getId: function(afix) {
            return '#' + this.prefix + afix;
        },
        getIdNoDot: function(afix) {
            return  this.prefix + afix;
        },
        getPriKey: function(a) {
            return a;
        }

    }


    var $ = jQuery.noConflict();
    jQuery(function() {//THE BEGINNING OF JQ

        ws_alipay_page_init();

    });//END OF JQ


</script>

<input type="button" value="添加模版" id="ws_alipay_table_add"/>
<a href="http://www.waisir.com/alipay-fast-demo" target="_blank"><input type="button" value="模版下载"
                                                                        id="ws_alipay_table_tpl-download"/></a>

<div id="ws_alipay_item_more" style="display:none">
    <div class="ws_alipay_item_more_main_wrap">
        <div class="ws_alipay_loading_more">
            <div class="ws_alipay_loading_bar"></div>
        </div>
        <div class="ws_alipay_item_more_wrap">

            <form action="" method="post" id="ws_alipay_table_more_form" class="ws_alipay_table_form_templates">
                <?php


                $htmls = array(
                    array('tplname', '模版名称', 'attrs' => array('style' => 'width:30%;margin:auto auto 10px 20px')),
                    array(
                        'tpldescription',
                        '模版描述',
                        'attrs' => array('style' => 'width:70%;margin:auto auto 10px 20px')
                    ),
                    array(
                        'tplcss',
                        '模版CSS代码:(请自行在代码中添加&lt;style&gt;标签,可以使用链接关系)',
                        'type'  => 'textarea',
                        'attrs' => array('class' => 'ws_alipay_tpl_css')
                    ),
                    array(
                        'tplhtml',
                        '模版HTML代码:(请直接在&lt;div&gt;标签下写代码)',
                        'type'  => 'textarea',
                        'attrs' => array('class' => 'ws_alipay_tpl_html')
                    ),
                    array(
                        'tpljs',
                        '模版javascript代码:(请自行在代码中添加&lt;script&gt;标签,可以使用脚本路径)',
                        'type'  => 'textarea',
                        'attrs' => array('class' => 'ws_alipay_tpl_js')
                    ),
                );
                $htmls = apply_filters('ws_alipay_templates_htmls', $htmls);

                echo ws_alipay_label_input_html($htmls, 'ws_alipay_templates_');

                ?>

                <input type="submit" class="button-primary" value="更新"/>

                <div class="clear"></div>
            </form>


        </div>
    </div>
</div>





