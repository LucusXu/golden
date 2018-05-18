<?php

use GOD\Log;
use GOD\DRedis;
use GOD\DException;
use GOD\RpcClient;

use library\base\Basecontroller;
use services\NewsService;

class FeedController extends Basecontroller {
    /**
     * @brief 初始化接口
     */
    public function initAction() {
        $service = new services\NewsService();
        $data = $service->getNews();
        return $this->returnResult(0, 'success', $data);
    }
}
