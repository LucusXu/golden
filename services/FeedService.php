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

use services\UserService;

class FeedService {
    private $_newsObj = null;
    private $_newsLike = null;
    private $_newsActionRecord = null;
    public function __construct() {
        $this->_newsObj = \NewsModel::getInstance();
        $this->_newsLike = \LikeEventModel::getInstance();
        $this->_newsActionRecord = \ActionRecordsModel::getInstance();
    }

    public function detailFeed($id, $uid) {
        $article = $this->_newsObj->getNewsById($id);
        if (false === $article) {
            Log::warning(__FUNCTION__ . " add article failed");
            return null;
        }
        $service = new UserService();
        $author = $service->getUserInfoById($article['uid']);
        $article['user'] = $author;
        return Util::returnSucc($article);
    }

    public function create($uid, $content) {
        $new_id = $this->_newsObj->addArticle($uid, $content);
        if (false === $new_id) {
            Log::warning(__FUNCTION__ . " add article failed");
            return false;
        }
        $ret['id'] = $new_id;
        return Util::returnSucc($ret);
    }

    public function getFeedList(&$next_id, $user = null) {
        $limit = 20;
        $feeds = $this->_newsObj->getArticleList($next_id, $limit);
        if (!$feeds) {
            return null;
        }

        $data = [
            "list" => $feeds,
            "has_more" => false,
        ];
        $count = $this->_newsObj->nextCount($next_id);
        if ($count > $limit) {
            $data['has_more'] = true;
        }

        $new_feed = [];
        $service = new UserService();
        foreach ($feeds as $one) {
            if ($one['id'] > $next_id) {
                $next_id = $one['id'];
            }
            $tmp = [
                'fid' => intval($one['id']),
                'type' => intval($one['type']),
                'statistics' => [
                    'comment_cnt' => intval($one['comment_num']),
                    'like_cnt' => intval($one['like_num']),
                    'repost_cnt' => intval($one['quote_num']),
                    'collect_cnt' => intval($one['collect_num']),
                    'has_liked' => 0,
                    'has_collected' => 0,
                ],
                'talk' => [
                    'summary' => $one['summary'],
                    'imgs' => [],
                ],
            ];
            $user = $service->getUserInfoById($one['uid']);
            if ($user) {
                $author = [
                    'uid' => $user['id'],
                    'nickname' => $user['name'],
                    'avatar_url' => $user['avatar'],
                    // 'city' => $user['location'],
                    'follow_status' => 0,
                ];
                $tmp['author'] = $author;
            }

            $liked = $this->_newsLike->getUserArticleLike($one['id'], $one['uid']);
            if ($liked) {
                $tmp['statistics']['has_liked'] = 1;
            }
            $collected = $this->_newsActionRecord->getUserArticleRecord($one['id'], $one['uid'], 'collect');
            if ($collected) {
                $tmp['statistics']['has_collected'] = 1;
            }
            $tmp['created_at'] = strtotime($one['created_at']);
            $new_feed[] = $tmp;
        }

        $data = [
            "list" => $new_feed,
            "has_more" => false,
        ];
        return Util::returnSucc($data);
    }

    /**
     * @desc 点赞
     * @param $uid
     * @param $id
     * @param $status
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
        $cur_like_num = $article['like_num'];
        $ret = $this->_newsLike->getUserArticleLike($id, $uid, $status, 'feed');

        if ($ret) {
            Log::warning(__FUNCTION__ . " repeat like");
            $errno = ErrorDefine::ERRNO_HAS_LIKE;
            return Util::returnResult($errno);
        }

        $up_cnt_num = $cur_like_num + 1;
        $this->_newsObj->updateLikeNum($id, $up_cnt_num);
        // 记录点赞事件
        $like_id = $this->_newsLike->addLikeEvent($uid, $id, $status, 'feed');

        $data = [
            'uid' => $article['uid'],
        ];
        return Util::returnSucc($data);
    }

    /**
     * @desc 站内转发
     * @param $uid
     * @param $id
     * @return
     */
    public function quoteFeed($uid, $id) {
        $article = $this->_newsObj->getNewsById($id);
        if (!$article) {
            Log::warning(__FUNCTION__ . " no like article");
            $errno = ErrorDefine::ERRNO_NO_ARTICLE;
            return Util::returnResult($errno);
        }

        // TODO检查是否已经赞过
        $cur_quote_num = $article['quote_num'];
        $ret = $this->_newsActionRecord->getUserArticleRecord($uid, $id, 'quote');

        if ($ret) {
            Log::warning(__FUNCTION__ . " repeat quote");
            $errno = ErrorDefine::ERRNO_HAS_QUOTE;
            return Util::returnResult($errno);
        }

        $up_cnt_num = $cur_quote_num + 1;
        $this->_newsObj->updateQuoteNum($id, $up_cnt_num);
        // 记录事件
        $quote_id = $this->_newsActionRecord->addActionRecord($uid, $id, 'quote');

        $data = [
            'uid' => $article['uid'],
        ];
        return Util::returnSucc($data);
    }

    /**
     * @desc 站外分享
     * @param $uid
     * @param $id
     * @return
     */
    public function shareFeed($uid, $id) {
        $article = $this->_newsObj->getNewsById($id);
        if (!$article) {
            Log::warning(__FUNCTION__ . " no like article");
            $errno = ErrorDefine::ERRNO_NO_ARTICLE;
            return Util::returnResult($errno);
        }

        // TODO检查是否已经分享
        $cur_share_num = $article['share_num'];
        $ret = $this->_newsActionRecord->getUserArticleRecord($uid, $id, 'share');

        if ($ret) {
            Log::warning(__FUNCTION__ . " repeat share");
            $errno = ErrorDefine::ERRNO_HAS_SHARE;
            return Util::returnResult($errno);
        }

        $up_cnt_num = $cur_share_num + 1;
        $this->_newsObj->updateShareNum($id, $up_cnt_num);
        // 记录事件
        $share_id = $this->_newsActionRecord->addActionRecord($uid, $id, 'share');

        $data = [
            'uid' => $article['uid'],
        ];
        return Util::returnSucc($data);
    }

    /**
     * @desc 收藏
     * @param $uid
     * @param $id
     * @return
     */
    public function collectFeed($uid, $id) {
        $article = $this->_newsObj->getNewsById($id);
        if (!$article) {
            Log::warning(__FUNCTION__ . " no collect article");
            $errno = ErrorDefine::ERRNO_NO_ARTICLE;
            return Util::returnResult($errno);
        }

        $cur_collect_num = $article['collect_num'];
        $ret = $this->_newsActionRecord->getUserArticleRecord($uid, $id, 'collect');

        if ($ret) {
            Log::warning(__FUNCTION__ . " repeat collect");
            $errno = ErrorDefine::ERRNO_HAS_COLLECT;
            return Util::returnResult($errno);
        }

        $up_cnt_num = $cur_collect_num + 1;
        $this->_newsObj->updateCollectNum($id, $up_cnt_num);
        // 记录事件
        $collect_id = $this->_newsActionRecord->addActionRecord($uid, $id, 'collect');

        $data = [
            'uid' => $article['uid'],
        ];
        return Util::returnSucc($data);
    }

    /**
     * @desc 关注
     * @param $uid
     * @param $id
     * @return
     */
    public function followFeed($uid, $author_uid) {
        $article = $this->_newsObj->getNewsById($id);
        if (!$article) {
            Log::warning(__FUNCTION__ . " no collect article");
            $errno = ErrorDefine::ERRNO_NO_ARTICLE;
            return Util::returnResult($errno);
        }

        $cur_collect_num = $article['collect_num'];
        $ret = $this->_newsActionRecord->getUserArticleRecord($id, $uid, 'collect');

        if ($ret) {
            Log::warning(__FUNCTION__ . " repeat collect");
            $errno = ErrorDefine::ERRNO_HAS_COLLECT;
            return Util::returnResult($errno);
        }

        $up_cnt_num = $cur_collect_num + 1;
        $this->_newsObj->updateCollectNum($id, $up_cnt_num);
        // 记录事件
        $collect_id = $this->_newsActionRecord->addActionRecord($uid, $id, 'collect');

        $data = [
            'uid' => $article['uid'],
        ];
        return Util::returnSucc($data);
    }

    public function getLikeList($feed_id, $uid) {
        $limit = 20;
        $ret = $this->_newsLike->getArticleLikeUids($feed_id, $limit);
        $service = new UserService();
        $data = [];
        foreach ($ret as $one) {
            $user = $service->getUserInfoById($one['uid']);
            $tmp = [];
            if ($user) {
                $author = [
                    'uid' => $user['id'],
                    'nickname' => $user['name'],
                    'avatar_url' => $user['avatar'],
                    // 'city' => $user['location'],
                    'follow_status' => 0,
                ];
                $tmp['author'] = $author;
            }

            $tmp['published_at'] = $one['updated_at'];
            $data[] = $tmp;
        }
        return Util::returnSucc($data);
    }

    public function getQuoteList($feed_id, $uid) {
        $limit = 20;
        $ret = $this->_newsLike->getArticleLikeUids($feed_id, $limit);
        $service = new UserService();
        $data = [];
        foreach ($ret as $one) {
            $user = $service->getUserInfoById($one['uid']);
            $tmp = [];
            $tmp['user'] = $user;
            $tmp['published_at'] = $one['updated_at'];
            $data[] = $tmp;
        }
        return Util::returnSucc($data);
    }
}
