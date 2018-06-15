<?php
namespace library\define;

class ErrorDefine {
    // 基础功能错误号
    const ERRNO_SUCCESS     = 0;
    const ERRNO_FAIL        = 1000;
    const ERRNO_PARAMETER   = 1001;

    // 登录相关
    const ERRNO_NO_LOGIN        = 20000;
    const ERRNO_NO_RIGHT        = 20001;
    const ERRNO_BAD_USER        = 20002;
    const ERRNO_WRONG_PASSWD    = 20003;

    const ERRNO_REDIS_CONNENT_ERROR = 2000;
    const ERRNO_MYSQL_CONNECT_ERROR = 2001;

    const ERRNO_NO_ARTICLE              = 30000;
    const ERRNO_HAS_LIKE                = 30001;
    const ERRNO_HAS_QUOTE               = 30002;
    const ERRNO_HAS_SHARE               = 30003;
    const ERRNO_HAS_COLLECT             = 30004;

    public static $messageMap = [
        self::ERRNO_SUCCESS => 'success',
        self::ERRNO_FAIL  => 'fail',
        self::ERRNO_PARAMETER => '参数错误',
        self::ERRNO_REDIS_CONNENT_ERROR => 'redis 连接失败',
        self::ERRNO_MYSQL_CONNECT_ERROR => '数据库连接异常',
        self::ERRNO_NO_LOGIN => '用户未登陆',
        self::ERRNO_NO_RIGHT => '没有权限',
        self::ERRNO_BAD_USER => '用户不存在',
        self::ERRNO_WRONG_PASSWD => '账户或密码错误',

        self::ERRNO_NO_ARTICLE      => '文章丢失',
        self::ERRNO_HAS_LIKE        => '已经点赞了',
        self::ERRNO_HAS_QUOTE       => '已经转发了',
        self::ERRNO_HAS_SHARE       => '已经分享了',
        self::ERRNO_HAS_COLLECT     => '已经收藏了',
    ];

    public static $defaultError = "error message not set!";

    /**
     * @param $code
     * @return mixed|string
     */
    public static function getMsg($code, $msg = '') {
        if ($msg) {
            return $msg;
        }

        if (isset(self::$messageMap[$code])) {
            return self::$messageMap[$code];
        }
        return self::$defaultError;
    }
}
