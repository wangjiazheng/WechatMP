<?php

require dirname(__FILE__)."/WeChatMP.php";

$obj = new WeChatMP( WeChatMP::WEIXIN_SHARE_JS );
$obj = $obj->get();

// 获取微信分享数据
$url = "https://xiao.wifi.com/data/page?id=100039896&ideaId=2149";
var_dump( $obj->getWxShareData( $url ) );