<?php

use GOD\Log;
use GOD\DRedis;
use GOD\DException;
use GOD\RpcClient;

use library\base\Basecontroller;
use services\SnsService;

class SnsController extends Basecontroller {
    /**
     * @brief 新增twitter
     */
    public function addTwittersAction() {
        $data = $this->getParam('data', '');
        // $data = $_POST;
        if (!$data) {
            return $this->returnResult(1, '参数缺失');
        }
        $service = new services\SnsService();
        $ret = $service->addTwitters($data);
        Log::warning("add twitter " . $ret);
        return $this->returnResult(0, 'success', true);
    }
}
