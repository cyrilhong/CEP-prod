<!DOCTYPE HTML>
<html>
<head>
    <title>页面跳转中...</title>
    <meta charset="utf-8"/>
    <style type="text/css">
        *{
            padding:0;
            margin:0;
        }
        html, body{
            height:100%;
            width:auto;
        }
        #outer_wrap{
            display:block;
            position:absolute;
            left:50%;
            top:40%;
            margin-left:-300px;
            margin-top:-1em;
            width:600px;
            height:2em;
            text-align:center;
            line-height:2em;
            font-size:20px;
            font-weight:bold;
            font-family:microsoft yahei, youyuan;
        }
        #inner_wrap{
            display:block;
            position:relative;
            background:#FFC;
            margin:150px auto;
            text-align:center
        }
        a.redirect{
            font-size:14px;
        }
        #loading{
            background:url(../styles/images/loading.gif) no-repeat 0 16px;
            padding:10px;
            background6:#ccc
        }
        .info{
            display:block;
            font-size:14px
        }
    </style>
</head>

<body>
<div id="outer_wrap">

    <span id="loading">&nbsp;&nbsp;&nbsp;</span>正在跳转中, 请稍候......&nbsp;&nbsp;&nbsp;
    <noscript><label class="info">【提示】您的浏览器未开启脚本支持,此操作无法继续进行,请启用javascript支持&nbsp;&nbsp;&nbsp;<a
              href="http://www.baidu.com/s?tn=monline_4_dg&rn=100&bs=%C6%F4%D3%C3javascript&f=8&rsv_bp=1&wd=%D4%F5%C3%B4%C6%F4%D3%C3javascript">如何启用?</a></label>
    </noscript>
</div>
