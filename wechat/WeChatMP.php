<?php

class WeChatMP {

    const WEIXIN_SHARE_JS  = 1;
    const WEIXIN_OAUTH_FUN = 2;

    private $obj = null;

    public function __construct($kind)
    {
        $redis = new Redis();
        $config = new Config();
        $redis->connect($config::REDIS_HOST, $config::REDIS_PORT);
        $redis->select($config::REDIS_DB);
        switch ($kind) {
            case self::WEIXIN_SHARE_JS: {
                $ShareJS = new ShareJS($config::WEXIN_APPID, $config::WEXIN_SECRET, $redis);
                $this->obj = $ShareJS;
                break;
            }
            default: {
                return false;
            }
        }
    }

    public function get()
    {
        return $this->obj;
    }

}
