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
}
