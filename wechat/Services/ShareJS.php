<?php

class ShareJS {

    private $appid = null;
    private $secret = null;
    private $reids = null;

    public function __construct($appid, $secret, $reids)
    {
        if ($this->appid == null) {
            $this->appid = $appid;
        }
        if ($this->secret == null) {
            $this->secret = $secret;
        }
        if ($this->reids == null) {
            $this->reids = $reids;
        }
    }

    //  获取令牌。在服务器端完成，代码如下：
    function wx_get_token() {
        $key = "wx_mp_access_token";
        $token = $this->reids->get( $key );
        if ( !$token ){
            $res = file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->appid . '&secret=' . $this->secret);
            $res = json_decode($res, true);
            $token = $res['access_token'];
            // 注意：这里需要将获取到的token缓存起来（或写到数据库中）
            // 不能频繁的访问https://api.weixin.qq.com/cgi-bin/token，每日有次数限制
            // 通过此接口返回的token的有效期目前为2小时。令牌失效后，JS-SDK也就不能用了。
            // 因此，这里将token值缓存1小时，比2小时小。缓存失效后，再从接口获取新的token，这样就可以避免token失效。
            // S()是ThinkPhp的缓存函数，如果使用的是不ThinkPhp框架，可以使用你的缓存函数，或使用数据库来保存。
            $this->reids->set( $key, $token, 3600 ); // 7200 expire time
        }
        return $token;
    }
    //    注意：返回的access_token长度至少要留够512字节。接口返回值：
    //    {"access_token":"vdlThyTfyB0N5eMoi3n_aMFMKPuwkE0MgyGf_0h0fpzL8p_hsdUX8VGxz5oSXuq5dM69lxP9wBwN9Yzg-0kVHY33BykRC0YXZZZ-WdxEic4","expires_in":7200}


    //    获取jsapi的ticket。jsapi_ticket是公众号用于调用微信JS接口的临时票据。正常情况下，jsapi_ticket的有效期为7200秒，通过access_token来获取。
    function wx_get_jsapi_ticket(){
        $key = "wx_mp_ticket";
        $ticket = $this->reids->get($key);
        if ( !empty($ticket)) {
            return $ticket;
        }
        $token = $this->reids->get("wx_mp_access_token");;
        if (empty($token)) {
            $token = $this->wx_get_token();
        }
        $url2 = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=" . $token . "&type=jsapi";
        $res = file_get_contents($url2);
        $res = json_decode($res, true);
        $ticket = $res['ticket'];
        // 注意：这里需要将获取到的ticket缓存起来（或写到数据库中）
        // ticket和token一样，不能频繁的访问接口来获取，在每次获取后，我们把它保存起来。
        $this->reids->set( $key, $ticket, 3600 ); // 7200 expire time
        return $ticket;
    }
    //    接口返回值：
    //    {"errcode":0,"errmsg":"ok","ticket":"sM4AOVdWfPE4DxkXGEs8VMKv7FMCPm-I98-klC6SO3Q3AwzxqljYWtzTCxIH9hDOXZCo9cgfHI6kwbe_YWtOQg","expires_in":7200}


    public function getWxShareData( $url )
    {
        $data['appId'] = $this->appid;
        $data['timestamp'] = time();
        $data['nonceStr'] = md5("weixinmp" . time());
        $wxticket = $this->wx_get_jsapi_ticket();
        // 签名，将jsapi_ticket、noncestr、timestamp、分享的url按字母顺序连接起来，进行sha1签名。
        $wxOri = sprintf("jsapi_ticket=%s&noncestr=%s&timestamp=%s&url=%s", $wxticket, $data['nonceStr'], $data['timestamp'], $url );
        $data['signature'] = sha1($wxOri);

        return $data;
    }


    /*
     *
    生成签名后，就可以使用js代码了。在你的html中，进行如下设置即可。

    <script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
    <script type="text/javascript">
    // 微信配置
    wx.config({
        debug: false,
        appId: "你的AppID",
        timestamp: '上一步生成的时间戳',
        nonceStr: '上一步中的字符串',
        signature: '上一步生成的签名',
        jsApiList: ['onMenuShareTimeline', 'onMenuShareAppMessage'] // 功能列表，我们要使用JS-SDK的什么功能
    });
    // config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在 页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready 函数中。
    wx.ready(function(){
        // 获取“分享到朋友圈”按钮点击状态及自定义分享内容接口
        wx.onMenuShareTimeline({
            title: '分享标题', // 分享标题
            link:"分享的url,以http或https开头",
            imgUrl: "分享图标的url,以http或https开头" // 分享图标
        });
        // 获取“分享给朋友”按钮点击状态及自定义分享内容接口
        wx.onMenuShareAppMessage({
            title: '分享标题', // 分享标题
            desc: "分享描述", // 分享描述
            link:"分享的url,以http或https开头",
            imgUrl: "分享图标的url,以http或https开头", // 分享图标
            type: 'link', // 分享类型,music、video或link，不填默认为link
        });
    });
    </script>

    */

}
