<?php
/*
 * @author Lucus
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
    //公共处理逻辑，如安全验证等
    protected $request;
    protected static $post = [];

    public function init() {
        $this->request = $this->getRequest();
    }

    /**
     * 获取request请求参数
     * @param  [type]  $name [description]
     * @param  boolean $tag  [description]
     * @return [type]        [description]
     */
    public function getParam($name, $tag = false) {
        if ($this->getRequest()->isPost()) {
            $val = $this->getRequest()->getPost($name);
        } else if (!in_array(str_replace('Action', '', $this->getRequest()->getActionName()),
            static::$post)) {
            $val = $this->getRequest()->getQuery($name);
        } else {
            $val = '';
        }

        if (!empty($val)) {
            if (is_array($val)) {
                foreach ($val as $key => $param) {
                    $val[$key] = $this->_filterParam($param);
                }
            } else {
                $val = $this->_filterParam($val);
            }
        }
        return $val;
    }

     /**
      * 过滤request数据 (可根据需求扩展过滤规则)
      * @param  string  $value [description]
      * @param  boolean $tag   [description]
      * @return string         [description]
      */
    private function _filterParam($value, $tag = false) {
        if ($tag) {
            $value = trim($value);
        } else {
            // 提出字符串种的html，xml和php的标签
            $value = trim(strip_tags($value));
        }
        return $value;
    }

    protected function logParams($params) {
        foreach ($params as $key => $value) {
            Log::addNotice('param.' . $key, $value);
        }
    }

    /**
     * 输出json
     * @param string $errmsg
     */
    public function outputJson($arrRet, $status = 200) {
        header('Content-Type:application/json;charset=utf-8', true, $status);
        echo json_encode($arrRet, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param int $errno
     * @param string $errmsg
     * @param string $data
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

    protected function header($name, $default = '') {
        $name = str_replace('-', '_', strtoupper($name));
        $val = Arr::get($_SERVER, 'HTTP_'.$name, $default);
        //        Log::addNotice($name, $val, 'header');
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

    protected function currentBaseUrl() {
        $schema = $_SERVER['REQUEST_SCHEME'];
        $host = $_SERVER['HTTP_HOST'];
        $uri = parse_url('http://example.com' . $_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return "$schema://$host";
    }
}
