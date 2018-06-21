<?php

use GOD\Log;

use library\base\Basecontroller;
use library\define\ErrorDefine;

use services\UserService;

class RegisterController extends Basecontroller {
    public function createAction() {
        if(!$this->getRequest()->isPost()) {
            $this->returnResult(ErrorDefine::ERRNO_FAIL, 'request method 必须是post');
        }

        $mobile = $this->getParam('phone_number');
        if (!$mobile || !is_numeric($mobile)) {
            $this->returnResult(ErrorDefine::ERRNO_PARAMETER, '非法手机号');
        }
        if ($mobile) {
            if (!preg_match('/^\d{6,20}$/', $mobile)) {
                $this->returnResult(ErrorDefine::ERRNO_MOBILE_INVALID, '手机号格式错误');
            }
        }
        $uuid = $this->uuid;
        if (!$uuid) {
            $this->returnResult(ErrorDefine::ERRNO_PARAMETER, 'uuid必传');
        }

        $name = $this->getParam('name');
        $password = $this->getParam('password');
        if (!$name) {
            $this->returnResult(ErrorDefine::ERRNO_PARAMETER, 'name不能为空');
        }

        $service = new UserService();
        $ret = $service->create($name, $mobile, $uuid, $password);
        if (0 != $ret['errno']) {
            $this->returnResult($ret['errno'], $ret['errmsg']);
        }
        $errno = ErrorDefine::ERRNO_SUCCESS;
        $errmsg = ErrorDefine::getMsg($errno);
        $this->returnResult($errno, $errmsg, $ret['data']);
    }
}
