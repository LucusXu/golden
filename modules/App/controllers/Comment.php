<?php
/**
 * @desc 评论接口
 * @author Lucus
 */

use GOD\Log;

use library\base\Basecontroller;
use library\define\ErrorDefine;

use services\CommentService;

class CommentController extends Basecontroller {
    /**
     * @desc 评论列表
     */
    public function listAction() {
        $user = $this->getUserInfo();
        $uid = 0;
        if ($user) {
            if (!isset($user['id'])) {
                $this->returnResult(ErrorDefine::ERRNO_FAIL, '账户异常');
            }
            $uid = $user['id'];
        }
        $feed_id  = intval($this->getParam('feed_id', ''));
        $next_id   = intval($this->getParam('next', 0));

        $service = new services\CommentService();
        $ret = $service->newCommentList($topic_id, $uid, $next_id, $comment_id);
        if (!$ret) {
            $errno = ErrorDefine::ERRNO_FAIL;
            $errmsg = ErrorDefine::getMsg($errno);
            $this->returnResult($errno, $errmsg);
        }
        if ($ret['data']['has_more']) {
            $schema = $_SERVER['REQUEST_SCHEME'];
            $host = $_SERVER['HTTP_HOST'];
            $uri = parse_url('http://example.com' . $_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $next = "$schema://$host$uri?feed_id=$feed_id&next=$next_id";
            $ret['data']['next'] = $next;
        }
        unset($ret['data']['has_more']);
        $errno = ErrorDefine::ERRNO_SUCCESS;
        $errmsg = ErrorDefine::getMsg($errno);
        $this->returnResult($errno, $errmsg, $ret['data']);
    }

    /**
     * @desc 提交评论
     */
    public function addAction() {
        $user = $this->getUserInfo();
        if (!$user) {
            $this->returnResult(ErrorDefine::ERRNO_NO_LOGIN);
        }

        if (isset($user['block']) && $user['block'] == 1) {
            $this->returnResult(ErrorDefine::ERRNO_FAIL, '账户异常');
        }

        if (!isset($user['id'])) {
            $this->returnResult(ErrorDefine::ERRNO_FAIL, '账户异常');
        }
        $uid = $user['id'];

        $content = $this->post('content', '');
        if (!$content) {
            $this->returnResult(ErrorDefine::ERRNO_PARAMETER, '评论不能为空');
        }

        $feed_id = intval($this->getParam('feed_id', ''));
        $reply_id = intval($this->getParam('reply_id', ''));

        $service = new services\CommentService();
        $ret = $service->addComment($feed_id, $user, $content, $quote_id, $cid);

        if (0 != $ret['errno']) {
            $this->returnResult($ret['errno'], $ret['errmsg']);
        }
        $data = [
            'user'      => $user,
            'id'        => strval($cid),
            'content'   => $content,
            'created_at'=> strval(time()),
        ];
        if (isset($ret['data']['quote'])) {
            $arrData['quote'] = $ret['data']['quote'];
        }
        $errno = ErrorDefine::ERRNO_SUCCESS;
        $errmsg = ErrorDefine::getMsg($errno);
        $this->returnResult($errno, $errmsg, $data);
    }

    public function delAction() {
        $user = $this->getUserInfo();
        if (!$user) {
            $this->returnResult(ErrorDefine::ERRNO_NO_LOGIN);
        }
        $uid = $user['id'];

        $id = $this->getParam('id', '');
        $service = new services\CommentService();
        $ret = $service->delComment($id, $uid);

        $errno = ErrorDefine::ERRNO_SUCCESS;
        if (false === $ret) {
            $errno = ErrorDefine::ERRNO_FAIL;
        }
        $this->returnResult($errno, ErrorDefine::$messageMap[$errno]);
    }

    /**
     * @desc 点赞评论
     */
    public function likeAction() {
        $user = $this->getUserInfo();
        if (!$user) {
            $this->returnResult(ErrorDefine::ERRNO_NO_LOGIN);
        }

        if (!isset($user['id'])) {
            $this->returnResult(ErrorDefine::ERRNO_FAIL, '账户异常');
        }
        $uid = $user['id'];
        $id = intval($this->getParam('id', ''));
        $status = intval($this->getRequest()->getPost('status', 1));
        if (!$id) {
            $this->returnResult(ErrorDefine::ERRNO_PARAMETER, 'id不能为空');
        }

        $service = new services\CommentService();
        $ret = $service->likeComment($uid, $id, $status);
        if (0 != $ret['errno']) {
            $this->returnResult($ret['errno'], $ret['errmsg']);
        }
        $errno = ErrorDefine::ERRNO_SUCCESS;
        $errmsg = ErrorDefine::getMsg($errno);
        $this->returnResult($errno, $errmsg);
    }
}
