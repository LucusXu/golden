<?php

namespace library\util;

use library\define\Constant;
use library\define\ErrorDefine;
class Util {
    /**
     * @param int $errno
     * @param string $errmsg
     * @param string $data
     * @return array|void
     */
    public function returnResult($errno = 0, $errmsg = '', $data = '') {
        $errno = intval($errno);
        $errmsg = $errmsg ? $errmsg : ErrorDefine::getMsg($errno);
        $data   = $data ? $data : null;
        $result = [
            'errno' => $errno,
            'errmsg' => $errmsg,
            'data' => $data,
        ];
        return $result;
    }

    /**
     * @desc 返回成功的data
     * @param string $data
     * @return array|void
     */
    public function returnSucc($data = '') {
        $errno = ErrorDefine::ERRNO_SUCCESS;
        $errmsg = ErrorDefine::getMsg($errno);
        $data   = $data ? $data : null;

        $result = [
            'errno' => $errno,
            'errmsg' => $errmsg,
            'data' => $data,
        ];
        return $result;
    }
}
