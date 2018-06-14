<?php

use GOD\Log;

use library\base\Basecontroller;
use library\define\ErrorDefine;

use services\NewsService;
use services\FeedService;

class FeedController extends Basecontroller {
    /**
     * @desc 发表
     */
    public function createAction() {
        $user = $this->getUserInfo();
        if (!$user) {
            $this->returnResult(ErrorDefine::ERRNO_NO_LOGIN);
        }
        if (!isset($user['id'])) {
            $this->returnResult(ErrorDefine::ERRNO_FAIL);
        }
        $uid = $user['id'];
        $content = $this->getParam('content', '');

        if (!$content) {
            $this->returnResult(ErrorDefine::ERRNO_PARAMETER, 'content不能为空');
        }

        $service = new services\FeedService();
        $ret = $service->create($uid, $content);
        if (0 != $ret['errno']) {
            $this->returnResult($ret['errno'], $ret['errmsg']);
        }
        $errno = ErrorDefine::ERRNO_SUCCESS;
        $errmsg = ErrorDefine::getMsg($errno);
        $this->returnResult($errno, $errmsg);
    }

    /**
     * Feed列表
     */
    public function listAction() {
        $user = $this->getUserInfo();
        $next = intval($this->getParam('next', 0));
        if (!$next) {
            $next = 0;
        }

        try {
            $service = new services\FeedService();
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
        if ($ret['data']['has_more']) {
            $schema = $_SERVER['REQUEST_SCHEME'];
            $host = $_SERVER['HTTP_HOST'];
            $uri = parse_url('http://example.com' . $_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $next = "$schema://$host$uri?next=$next";
            $ret['data']['next'] = $next;
        }
        unset($ret['data']['has_more']);
        $errno = ErrorDefine::ERRNO_SUCCESS;
        $errmsg = ErrorDefine::getMsg($errno);
        $this->returnResult($errno, $errmsg, $ret['data']);
    }

    /**
     * @desc 点赞
     */
    public function likeAction() {
        $user = $this->getUserInfo();
        if (!$user) {
            $this->returnResult(ErrorDefine::ERRNO_NO_LOGIN);
        }
        if (!isset($user['id'])) {
            $this->returnResult(ErrorDefine::ERRNO_FAIL);
        }
        $uid = $user['id'];
        $id = intval($this->getParam('feed_id', ''));
        $status = intval($this->getRequest()->getPost('status', 1));
        if (!$id) {
            $this->returnResult(ErrorDefine::ERRNO_PARAMETER, 'id不能为空');
        }

        $service = new services\FeedService();
        $ret = $service->likeFeed($uid, $id, $status);
        if (0 != $ret['errno']) {
            $this->returnResult($ret['errno'], $ret['errmsg']);
        }
        $errno = ErrorDefine::ERRNO_SUCCESS;
        $errmsg = ErrorDefine::getMsg($errno);
        $this->returnResult($errno, $errmsg, $ret['data']);
    }

    /**
     * @desc 站内转发
     */
    public function quoteAction() {
        $user = $this->getUserInfo();
        if (!$user) {
            $this->returnResult(ErrorDefine::ERRNO_NO_LOGIN);
        }
        if (!isset($user['id'])) {
            $this->returnResult(ErrorDefine::ERRNO_FAIL);
        }
        $uid = $user['id'];
        $id = intval($this->getParam('feed_id', ''));
        if (!$id) {
            $this->returnResult(ErrorDefine::ERRNO_PARAMETER, 'id不能为空');
        }

        $service = new services\FeedService();
        $ret = $service->quoteFeed($uid, $id);
        if (0 != $ret['errno']) {
            $this->returnResult($ret['errno'], $ret['errmsg']);
        }
        $errno = ErrorDefine::ERRNO_SUCCESS;
        $errmsg = ErrorDefine::getMsg($errno);
        $this->returnResult($errno, $errmsg);
    }

    /**
     * @desc 站外分享
     */
    public function shareAction() {
        $user = $this->getUserInfo();
        if (!$user) {
            $this->returnResult(ErrorDefine::ERRNO_NO_LOGIN);
        }
        if (!isset($user['id'])) {
            $this->returnResult(ErrorDefine::ERRNO_FAIL);
        }
        $uid = $user['id'];
        $id = intval($this->getParam('feed_id', ''));
        if (!$id) {
            $this->returnResult(ErrorDefine::ERRNO_PARAMETER, 'id不能为空');
        }

        $service = new services\FeedService();
        $ret = $service->shareFeed($uid, $id);
        if (0 != $ret['errno']) {
            $this->returnResult($ret['errno'], $ret['errmsg']);
        }
        $errno = ErrorDefine::ERRNO_SUCCESS;
        $errmsg = ErrorDefine::getMsg($errno);
        $this->returnResult($errno, $errmsg);
    }

    /**
     * @desc 收藏
     */
    public function collectAction() {
        $user = $this->getUserInfo();
        if (!$user) {
            $this->returnResult(ErrorDefine::ERRNO_NO_LOGIN);
        }
        if (!isset($user['id'])) {
            $this->returnResult(ErrorDefine::ERRNO_FAIL);
        }
        $uid = $user['id'];
        $id = intval($this->getParam('id', ''));
        if (!$id) {
            $this->returnResult(ErrorDefine::ERRNO_PARAMETER, 'id不能为空');
        }

        $service = new services\FeedService();
        $ret = $service->collectFeed($uid, $id);
        if (0 != $ret['errno']) {
            $this->returnResult($ret['errno'], $ret['errmsg']);
        }
        $errno = ErrorDefine::ERRNO_SUCCESS;
        $errmsg = ErrorDefine::getMsg($errno);
        $this->returnResult($errno, $errmsg);
    }

    /**
     * @desc 关注
     */
    public function followAction() {
        $user = $this->getUserInfo();
        if (!$user) {
            $this->returnResult(ErrorDefine::ERRNO_NO_LOGIN);
        }
        if (!isset($user['id'])) {
            $this->returnResult(ErrorDefine::ERRNO_FAIL);
        }
        $uid = $user['id'];
        $user_id = intval($this->getParam('user_id', ''));
        if (!$id) {
            $this->returnResult(ErrorDefine::ERRNO_PARAMETER, 'id不能为空');
        }

        $service = new services\FeedService();
        $ret = $service->followFeed($uid, $id);
        if (0 != $ret['errno']) {
            $this->returnResult($ret['errno'], $ret['errmsg']);
        }
        $errno = ErrorDefine::ERRNO_SUCCESS;
        $errmsg = ErrorDefine::getMsg($errno);
        $this->returnResult($errno, $errmsg);
    }

    /**
     * @desc 举报
     */
    public function reportAction() {
        $user = $this->getUserInfo();
        if (!$user) {
            $this->returnResult(ErrorDefine::ERRNO_NO_LOGIN);
        }
        if (!isset($user['id'])) {
            $this->returnResult(ErrorDefine::ERRNO_FAIL);
        }
        $uid = $user['id'];
        $user_id = intval($this->getParam('user_id', ''));
        if (!$id) {
            $this->returnResult(ErrorDefine::ERRNO_PARAMETER, 'id不能为空');
        }

        $service = new services\FeedService();
        $ret = $service->reportFeed($uid, $id);
        if (0 != $ret['errno']) {
            $this->returnResult($ret['errno'], $ret['errmsg']);
        }
        $errno = ErrorDefine::ERRNO_SUCCESS;
        $errmsg = ErrorDefine::getMsg($errno);
        $this->returnResult($errno, $errmsg);
    }

    /**
     * @desc 文章详情
     */
    public function detailAction() {
        $user = $this->getUserInfo();
        if (!$user) {
            $this->returnResult(ErrorDefine::ERRNO_NO_LOGIN);
        }
        if (!isset($user['id'])) {
            $this->returnResult(ErrorDefine::ERRNO_FAIL);
        }
        $uid = $user['id'];
        $id = intval($this->getParam('id', ''));
        if (!$id) {
            $this->returnResult(ErrorDefine::ERRNO_PARAMETER, 'id不能为空');
        }

        $service = new services\FeedService();
        $ret = $service->detailFeed($uid, $id);
        if (0 != $ret['errno']) {
            $this->returnResult($ret['errno'], $ret['errmsg']);
        }
        $errno = ErrorDefine::ERRNO_SUCCESS;
        $errmsg = ErrorDefine::getMsg($errno);
        $this->returnResult($errno, $errmsg);
    }

    /**
     * @desc 文章点赞列表
     */
    public function likeListAction() {
        $user = $this->getUserInfo();
        if (!$user) {
            $this->returnResult(ErrorDefine::ERRNO_NO_LOGIN);
        }
        if (!isset($user['id'])) {
            $this->returnResult(ErrorDefine::ERRNO_FAIL);
        }
        $uid = $user['id'];
        $feed_id = intval($this->getParam('feed_id', ''));
        if (!$id) {
            $this->returnResult(ErrorDefine::ERRNO_PARAMETER, 'id不能为空');
        }

        $service = new services\FeedService();
        $ret = $service->getLikeList($uid, $id);
        if (0 != $ret['errno']) {
            $this->returnResult($ret['errno'], $ret['errmsg']);
        }
        $errno = ErrorDefine::ERRNO_SUCCESS;
        $errmsg = ErrorDefine::getMsg($errno);
        $this->returnResult($errno, $errmsg);
    }

    /**
     * @desc 文章转发列表
     */
    public function quoteListAction() {
        $user = $this->getUserInfo();
        if (!$user) {
            $this->returnResult(ErrorDefine::ERRNO_NO_LOGIN);
        }
        if (!isset($user['id'])) {
            $this->returnResult(ErrorDefine::ERRNO_FAIL);
        }
        $uid = $user['id'];
        $quote_id = intval($this->getParam('quote_id', ''));
        if (!$id) {
            $this->returnResult(ErrorDefine::ERRNO_PARAMETER, 'id不能为空');
        }

        $service = new services\FeedService();
        $ret = $service->getQuoteList($uid, $id);
        if (0 != $ret['errno']) {
            $this->returnResult($ret['errno'], $ret['errmsg']);
        }
        $errno = ErrorDefine::ERRNO_SUCCESS;
        $errmsg = ErrorDefine::getMsg($errno);
        $this->returnResult($errno, $errmsg);
    }
}
