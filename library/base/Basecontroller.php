<?php
/**
 * @author Lucus
 * @2017年8月3日
 */

namespace library\base;

use GOD\CommonServer;
use GOD\Log;

use library\define\ErrorDefine;
use library\util\Utils;
use services\AuthorizeService;
use services\UserService;
use third\Arr;

class Basecontroller extends CommonServer {
    protected $request;
    protected $authToken = '';
    protected $uuid = '';
    protected static $post = [];

    public function init() {
        $this->request = $this->getRequest();
        $this->authToken = $this->header('AUTHORIZATION');
        $this->uuid = $this->header('UUID');
    }

    /**
     * 获取request请求参数
     */
    public function getParam($name, $tag = false) {
        if ($this->getRequest()->isPost()) {
            $val = $this->getRequest()->getPost($name);
        } else if (!in_array(str_replace('Action',
            '',
            $this->getRequest()->getActionName()),
            static::$post)) {
            $val = $this->getRequest()->getQuery($name);
        } else {
            $val = '';
        }

        if (!empty($val)) {
            if (is_array($val)) {
                foreach ($val as $key => $param) {
                    $val[$key] = $this->_filterParam($param, $tag);
                }
            } else {
                $val = $this->_filterParam($val, $tag);
            }
        }
        Log::addNotice('param.' . $name, isset($val) ? $val : '');
        return $val;
    }

    /**
     * 过滤request数据 (可根据需求扩展过滤规则)
     */
    private function _filterParam($value, $tag = false) {
        if ($tag) {
            $value = trim($value);
        } else {
            $value = trim(strip_tags($value));
        }
        return $value;
    }

    protected function logParams($params) {
        foreach ($params as $key => $value) {
            Log::addNotice('param.' . $key, $value);
        }
    }

    // 输出 json 结果
    public function outputJson($arrRet, $status = 200) {
        header('Content-Type:application/json;charset=utf-8', true, $status);
        echo json_encode($arrRet, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param int $errno
     * @param string $errmsg
     * @param array $data
     * @param bool $showJson
     * @return array|void
     */
    protected function returnResult($errno = 0, $errmsg = '', $data = '', $showJson = true) {
        header('Content-Type:application/json');
        $errno = intval($errno);
        $errmsg = $errmsg ? $errmsg : ErrorDefine::getMsg($errno);
        $data   = $data ? $data : null;

        $result = [
            'errno' => $errno,
            'errmsg' => $errmsg,
            'data' => $data,
        ];

        if (!$showJson) {
            return $result;
        }
        Log::setErrno($errno);
        return $this->output($data, $errno, $errmsg);
    }

    protected function filterIntArr(array $arr) {
        return array_filter(array_unique(array_map('intval', $arr)));
    }

    protected function getIntSetParam($name) {
        $vals = $this->get($name);
        if (empty($vals)) {
            return [];
        }
        $vals = explode(',', $vals);
        return array_filter(array_unique(array_map('intval', $vals)));
    }

    protected function postIntSetParam($name) {
        $vals = $this->post($name);
        if (empty($vals)) {
            return [];
        }
        $vals = explode(',', $vals);
        return array_filter(array_unique(array_map('intval', $vals)));
    }

    protected function get($name, $default = '') {
        $val = $this->getRequest()->getQuery($name, $default);
        Log::addNotice('param.' . $name, str_replace(array("\r\n", "\n", "\r"), "", isset($val) ? $val : ''));
        return $val;
    }

    protected function post($name, $default = '') {
        $val = $this->getRequest()->getPost($name, $default);
        Log::addNotice('param.' . $name, str_replace(array("\r\n", "\n", "\r"), "", isset($val) ? $val : ''));
        return $val;
    }

    protected function param($name, $default = '') {
        $val = $this->getRequest()->getParam($name, $default);
        Log::addNotice('param.' . $name, str_replace(array("\r\n", "\n", "\r"), "", isset($val) ? $val: ''));
        return $val;
    }

    protected function header($name, $default = '') {
        $name = str_replace('-', '_', strtoupper($name));
        $val = Arr::get($_SERVER, 'HTTP_' . $name, $default);
        return $val;
    }

    protected function gets() {
        return $_GET;
    }

    protected function posts() {
        return $_POST;
    }

    protected function cookie($name, $default = '') {
        return $this->getRequest()->getCookie($name, $default);
    }


    protected function getIntParam($key, $default = 0) {
        return (int)$this->get($key, $default);
    }

    protected function postIntParam($key, $default = 0) {
        return (int)$this->post($key, $default);
    }

    protected function getTimestampParam($key) {
        $date_time = $this->get($key);
        if (empty($date_time)) {
            return 0;
        }
        return strtotime($date_time);
    }

    protected function getDatetimeParam($key, $default = '') {
        $date_time = $this->get($key, $default);
        if (empty($date_time)) {
            return '';
        }
        return date('Y-m-d H:i:s', strtotime($date_time));
    }

    protected function setAuthCookie($uid) {
        $host = $_SERVER['HTTP_HOST'];
        $pos = strpos($host, ':');
        if ($pos !== false) {
            $host = substr($host, 0, $pos);
        }
        $auth = openssl_encrypt("$uid:" . time(), 'aes-256-cbc', 'Shui@Bit&*', 0, 'aZdy0');
        $check = hash_hmac('sha256', $auth . 'shuibit.com', 'cookieAuth');
        if (strstr($host, 'shuibit.com')) {
            $host = 'shuibit.com';
        }
        setcookie('_sbit', "$auth:$check", time() + 86400 * 30, '/', $host);
    }

    protected function getUidFromCookie() {
        if (!isset($_COOKIE['_sbit']) || empty($_COOKIE['_sbit'])) {
            return false;
        }
        $arrTmp = explode(':', $_COOKIE['_sbit']);
        if (count($arrTmp) < 2) {
            return false;
        }
        $auth = $arrTmp[0];
        $check = $arrTmp[1];
        $genCheck = hash_hmac('sha256', $auth . 'shuibit.com', 'cookieAuth');
        if ($check != $genCheck) {
            Log::addNotice('cookie.check', 'failed');
            return false;
        }
        $str = openssl_decrypt($auth, 'aes-256-cbc', 'Shui@Bit&*', 0, 'aZdy0');
        Log::addNotice('cookie.auth', $str);
        $arrAuth = explode(':', $str);
        return $arrAuth[0];
    }

    /**
     * 登陆判断
     * @return bool
     */
    protected function isLogin() {
        if (!$this->authToken) {
            return false;
        }
        $service = new AuthorizeService();
        $login = $service->isLogin($this->authToken);
        return $login;
    }

    /**
     * 获取用户的信息
     * @return bool
     */
    protected function getUserInfo($raw = false) {
        if (!$this->authToken) {
            return false;
        }
        $service = new AuthorizeService();
        $authorize = $service->isLogin($this->authToken);
        if (!$authorize) {
           return false;
        }
        $userId = $authorize['user_id'];
        $uservice = new UserService();
        $user   = $uservice->getUserInfoById($userId, $raw);
        return $user;
    }

    protected function currentBaseUrl() {
        $schema = $_SERVER['REQUEST_SCHEME'];
        $host = $_SERVER['HTTP_HOST'];
        $uri = parse_url('http://example.com' . $_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return "$schema://$host";
    }
}
