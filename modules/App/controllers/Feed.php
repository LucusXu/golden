<?php

use GOD\Log;

use library\base\Basecontroller;
use library\define\ErrorDefine;

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

    /**
     * Feed列表
     */
    public function listsAction() {
        $user = $this->getUserInfo();

        $next = intval($this->getParam('next', 0));
        if (!$next) {
            $next = 0;
        }

        try {
            $service = new FeedService();
            $ret = $service->getFeedList($next, $user);
        } catch (\Exception $e) {
            Log::warning('exception code:' . $e->getCode() . ',msg:' . $e->getMessage());
            $this->returnResult($e->getCode(), $e->getMessage());
        }

        if (!$ret) {
            $errno = ErrorDefine::ERRNO_FAIL;
            $errmsg = ErrorDefine::getMsg($errno);
            $this->returnResult($errno, $errmsg);
        }
        $this->returnResult($ret['errno'], $ret['errmsg'], $ret['data']);
    }
}
