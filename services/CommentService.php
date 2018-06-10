<?php
/**
 * Author: Lucus
 */

namespace services;

use GOD\Log;

use library\define\Constant;
use library\define\ErrorDefine;
use library\util\Util;

use services\SpamService;

class CommentService {
    private $_comment = null;
    private $_commentLike = null;
    public function __construct() {
        $this->_comment = \CommentModel::getInstance();
        $this->_commentLike = \CommentLikeModel::getInstance();
    }

    /**
     * @desc 创建评论
     * @param $uid
     * @param $feed_id
     * @param $content
     * @param $quote_id
     * @return
     */
    public function createComment($user, $pid, $content, $quote_id = 0) {
        // check是否合法
        $ret = [];
        if ($quote_id) {
            $quote = $this->_comment->getCommentById($quote_id);
            if (!$quote) {
                Log::warning(__FUNCTION__ . " quote comment failed");
                return ErrorDefine::ERRNO_QUOTE_COMMENT_ERROR;
            }
            $ret['quote'] = [
                'id' => $quote['id'],
                'uid' => $quote['uid'],
                'content' => $quote['content'],
            ];
        }
        $uid = $user['id'];

        // antispam
        $service = new SpamService();
        $check_res = $service->check($user, $content);
        if (0 != $check_res['errno']) {
            Log::warning(__FUNCTION__ . " check failed");
            return false;
        }
        $status = $check_res['data']['status'];
        $ret['status'] = $status;

        $new_id = $this->_comment->addComment($uid, $pid, $content, $quote_id, $tags, $status);
        Log::warning("new id:" . $new_id);
        if (false === $new_id) {
            Log::warning(__FUNCTION__ . " addComment failed");
            return false;
        }

        $ret['id'] = $new_id;
        return $ret;
    }

    /**
     * @desc 评论点赞
     * @param $uid
     * @param $id
     * @return
     */
    public function likeComment($uid, $id, $status) {
        // check是否合法
        $comment = $this->_comment->getCommentById($id);
        if (!$comment) {
            Log::warning(__FUNCTION__ . " no like comment");
            $errno = ErrorDefine::ERRNO_NO_COMMENT;
            return Util::returnResult($errno);
        }

        $codis = RdsManager::getCoinM2Codis();
        if (!$codis) {
            Log::warning(__FUNCTION__ . " codis connect fail");
            $errno = ErrorDefine::ERRNO_REDIS_CONNENT_ERROR;
            return Util::returnResult($errno);
        }

        // 检查是否已经赞过
        $key = Constant::REDIS_USER_UP_RECORD . $uid;
        $ckey = Constant::REDIS_COMMENT_UP_CNT . $id;
        $has = $codis->Sismember($key, $id);
        if (1 == $status) {
            if ($has) {
                Log::warning(__FUNCTION__ . " can not repeat like");
                $errno = ErrorDefine::ERRNO_HAS_UP;
                return Util::returnResult($errno);
            }
            $codis->Sadd($key, $id);
            $up_cnt_cache = intval($comment['like_num']) + 1;
        } else {
            if (!$has) {
                Log::warning(__FUNCTION__ . " can not cancel like");
                $errno = ErrorDefine::ERRNO_NO_UP;
                return Util::returnResult($errno);
            }
            $codis->Srem($key, $id);
            $up_cnt_cache = intval($comment['like_num']) - 1;
        }

        // 更新
        $codis->set($ckey, $up_cnt_cache);
        $this->_comment->updateLikeNum($id, $up_cnt_cache);
        // 记录点赞事件
        $this->_commentLike->addLikeEvent($uid, $id, $status);

        $data = [
            'uid' => $comment['uid'],
        ];
        return Util::returnSucc($data);
    }

    /**
     * @desc 查询评论列表
     * @param $topic_id
     * @param $uid
     * @param $next_id
     * @return
     */
    public function commentList($topic_id, $uid, $next_id, $comment_id = null) {
        $limit = Constant::COMMENT_PAGE_SIZE;
        $comments = $this->_comment->getComments($topic_id, $next_id, $limit);
        if (!$comments) {
            return null;
        }
        $quote_ids = [];
        // 通过uid查询点赞的评论id
        $codis = RdsManager::getCoinM2Codis();
        if (!$codis) {
            Log::warning(__FUNCTION__ . " codis connect fail");
            $errno = ErrorDefine::ERRNO_REDIS_CONNENT_ERROR;
            return Util::returnResult($errno);
        }
        $key = Constant::REDIS_USER_UP_RECORD . $uid;
        $records = $codis->Smembers($key);

        if ($comment_id) {
            $has_find = false;
            foreach ($comments as $one) {
                if ($one['id'] == $comment_id) {
                    $has_find = true;
                    break;
                }
            }
            if (!$has_find) {
                $current_comment = $this->_comment->getCommentById($comment_id);
                if ($current_comment) {
                    $comments[] = $current_comment;
                }
            }
        }

        foreach ($comments as $key => &$one) {
            if ($one['status'] != 1 && $one['status'] != 3 && $one['uid'] != $uid) {
                unset($comments[$key]);
                continue;
            }
            // 引用
            if ($one['quote_id']) {
                $quote_ids[] = $one['quote_id'];
            }
            // 是否已经赞过
            $one['is_like'] = 0;
            if ($uid && in_array($one['id'], $records)) {
                $one['is_like'] = 1;
            }
        }

        $quote_ids = array_unique($quote_ids);
        $quote_comments = [];
        if ($quote_ids) {
            $quote_comments = $this->_comment->getCommentsByIds($quote_ids);
        }

        foreach ($comments as &$one) {
            if ($one['quote_id'] && $quote_comments) {
                foreach ($quote_comments as $qone) {
                    if ($one['quote_id'] == $qone['id']) {
                        if ($qone['status'] != 1 && $qone['status'] != 3 && $qone['uid'] != $uid) {
                            break;
                        }
                        $one['quote']['id'] = intval($qone['id']);
                        $one['quote']['uid'] = intval($qone['uid']);
                        $one['quote']['content'] = $qone['content'];
                        break;
                    }
                }
            }
        }

        $i = 1;
        $new_comments = [];
        foreach ($comments as $piece) {
            if ($i > $limit) {
                break;
            }
            $new_comments[] = $piece;
            $i ++;
        }
        $data = [
            "comments" => $new_comments,
            "has_more" => false,
        ];
        if (count($comments) > $limit) {
            $data['has_more'] = true;
        }
        return Util::returnSucc($data);
    }

    /**
     * @desc 创建评论
     * @param $uid
     * @param $id
     * @return
     */
    public function delComment($id, $uid) {
        $ret = [];
        $comment = $this->_comment->getCommentById($id);
        if (!$comment) {
            Log::warning(__FUNCTION__ . " no comment");
            $errno = ErrorDefine::ERRNO_NO_COMMENT;
            return Util::returnResult($errno, ErrorDefine::getMsg($errno));
        }

        if ($uid != $comment['uid']) {
            Log::warning(__FUNCTION__ . " no right del comment," . $uid . " " . $comment['uid']);
            $errno = ErrorDefine::ERRNO_NO_RIGHT_DEL_COMMENT;
            return Util::returnResult($errno, ErrorDefine::getMsg($errno));
        }

        $ret = $this->_comment->delComment($id);
        if (false === $ret) {
            Log::warning(__FUNCTION__ . " delComment failed");
            $errno = ErrorDefine::ERRNO_DEL_COMMENT_ERROR;
            return Util::returnResult($errno, ErrorDefine::getMsg($errno));
        }
        $data = [
            "pid" => $comment['pid'],
        ];
        return Util::returnSucc($data);
    }
}
