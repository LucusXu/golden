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
        $limit = 50;
        $feeds = $this->_newsObj->getNews($next_id, $limit);
        if (!$feeds) {
            return null;
        }

        $i = 1;
        $new_feed = [];
        foreach ($feeds as $piece) {
            if ($i > $limit) {
                break;
            }
            $new_feed[] = $piece;
            $i ++;
        }
        $data = [
            "feed" => $new_feed,
            "has_more" => false,
        ];
        if (count($feeds) > $limit) {
            $data['has_more'] = true;
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
