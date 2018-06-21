<?php
/**
 * Author: Lucus
 */

namespace services;

use GOD\Log;

use services\AuthorizeService;

class UserService {
    private $_user = null;
    public function __construct() {
        $this->_user = \UsersModel::getInstance();
    }

    public function getUserInfoById($id) {
        $ret = $this->_user->getUserById($id);
        if (!$ret) {
            return false;
        }
        return $ret;
    }

    public function getUserInfo() {
        $authToken = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : null;
        if (!$authToken) {
            return false;
        }

        $service = new AuthorizeService();
        $authorize = $service->isLogin($authToken);
        if (!$authorize) {
            return false;
        }

        $userId = $authorize['user_id'];
        $user   = $this->getUserInfoById($userId);
        return $user;
    }

    public function create($name, $phoneNum, $uuid, $password) {
        $ret = $this->_user->create($name, $phoneNum, $uuid, $password);
        if (false === $ret) {
            Log::warning(__FUNCTION__ . " create user failed");
            $errno = ErrorDefine::ERRNO_CREATE_USER_ERROR;
            return Util::returnResult($errno, ErrorDefine::getMsg($errno));
        }
        $data = [
            'id' => $ret,
        ];
        return Util::returnSucc($data);
    }
}
