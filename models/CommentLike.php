<?php
/**
 * Author: Lucus
 */

use GOD\Log;

use library\base\ModelBase;
use library\define\Constant;
use library\define\ErrorDefine;

class CommentLikeModel extends ModelBase {
    protected $_tableName = 'comment_likes';
    private $_fields = 'id, relate_type, relate_id, comment_id, status';

    public function __construct() {
        parent::__construct(Constant::DB_NAME_GOLDEN);
    }

    public function getNewLikes($id) {
        $sql ='SELECT relate_id from ' . $this->_tableName
            .' WHERE comment_id=' . $id . " order by id desc limit 10";
        $ret = $this->db->query($sql);
        if (false === $ret) {
            Log::warning("execute sql fail");
            return false;
        }
        return $ret;
    }

    /**
     * @param $feed_id
     * @param $uid
     * @param $content
     * @param $quote_id
     * @param $tags
     * @return
     */
    public function addLikeEvent($uid, $comment_id, $status) {
        $onDup = [
            'status' => $status,
        ];
        $data = [
            "relate_id" => $uid,
            "comment_id" => $comment_id,
            "status" => $status,
        ];

        $ret = $this->db->insert($this->_tableName, $data, null, $onDup);
        if (false === $ret) {
            Log::warning("execute sql fail");
            return false;
        }
        return $this->db->getInsertID();
    }
}
