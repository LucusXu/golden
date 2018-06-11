<?php
/**
 * Author: Lucus
 */

namespace services;

use GOD\Log;

use library\define\Constant;
use library\define\ErrorDefine;
use library\util\RdsManager;
use library\util\Util;

class FeedService {
    private $_newsObj = null;
    public function __construct() {
        $this->_newsObj = \NewsModel::getInstance();
    }

    public function create($uid, $content) {
        $new_id = $this->_newsObj->addArticle($uid, $content);
        if (false === $new_id) {
            Log::warning(__FUNCTION__ . " add article failed");
            return false;
        }
        $ret['id'] = $new_id;
        return $ret;
    }

    public function getFeedList(&$next_id, $user = null) {
        $limit = 2;
        $feeds = $this->_newsObj->getArticleList($next_id, $limit);
        if (!$feeds) {
            return null;
        }

        $data = [
            "feed" => $feeds,
            "has_more" => false,
        ];
        $count = $this->_newsObj->nextCount($next_id);
        if ($count > $limit) {
            $data['has_more'] = true;
        }

        foreach ($feeds as $one) {
            if ($one['id'] > $next_id) {
                $next_id = $one['id'];
            }
        }
        return Util::returnSucc($data);
    }

    /**
     * @desc 点赞
     * @param $uid
     * @param $id
     * @return
     */
    public function likeFeed($uid, $id, $status) {
        $article = $this->_newsObj->getNewsById($id);
        if (!$article) {
            Log::warning(__FUNCTION__ . " no like article");
            $errno = ErrorDefine::ERRNO_NO_ARTICLE;
            return Util::returnResult($errno);
        }

        // TODO检查是否已经赞过
        $this->__newsObj->updateLikeNum($id, $up_cnt_cache);
        // 记录点赞事件
        $this->__newsLike->addLikeEvent($uid, $id, $status);

        $data = [
            'uid' => $article['uid'],
        ];
        return Util::returnSucc($data);
    }
}
