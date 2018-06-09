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

    public function getFeedList(&$next_id, $user = null) {
        $limit = 50;
        $feeds = $this->_newsObj->getNews($next_id, $limit);
        if (!$feeds) {
            return null;
        }

        $i = 1;
        $new_comments = [];
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
}
