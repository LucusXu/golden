<?php
/**
 * Author : Lucus
 */

use GOD\Log;

use library\base\ModelBase;
use library\define\ErrorDefine;
use library\define\Constant;

class CommentModel extends ModelBase {
    protected $_tableName = 'comment';
    private $_fields = 'id, uid, feed_id, content, status, quote_id, like_num, created_at';

    public function __construct() {
        parent::__construct(Constant::DB_NAME_GOLDEN);
    }

    /**
     * @param $feed_id
     * @param $uid
     * @param $content
     * @param $quote_id
     * @param $tags
     * @return
     */
    public function addComment($uid, $feed_id, $content, $quote_id) {
        $data = [
            'uid' => $uid,
            'feed_id' => $feed_id,
            'content' => $content,
            'status' => 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($quote_id) {
            $data['quote_id'] = $quote_id;
        }

        $onDup = [
            "uid" => $uid,
            "feed_id" => $feed_id,
            "content" => $content,
            'status' => 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $ret = $this->db->insert($this->_tableName, $data, null, $onDup);
        if (false === $ret) {
            Log::warning("connect db fail");
            return false;
        }
        return $this->db->getInsertID();
    }

    /**
     * @param $id
     * @return
     */
    public function getCommentById($id) {
        $where = [
            'id=' => $id,
        ];
        $ret = $this->db->select($this->_tableName, $this->_fields, $where);
        if ($ret === false) {
            Log::warning("execute query fail");
            return false;
        }

        if (count($ret)) {
            return $ret[0];
        }
        return null;
    }

    /**
     * @param $id
     * @return
     */
    public function updateLikeNum($id, $up_cnt_count) {
        $sql = "update " . $this->_tableName . " set `like_num`=" . $up_cnt_count
            . " where `id`=" . $id;
        $ret = $this->db->query($sql);
        if (false === $ret) {
            Log::warning("connect db fail");
            return false;
        }
        return $ret;
    }

    /**
     * @desc 查询评论列表
     * @param $pid
     * @param $next_id
     * @param $limit
     * @return
     */
    public function getComments($feed_id, $next_id, $limit = 20) {
        $sql = "SELECT `id`, `uid`, `content`, `status`, `quote_id`, `like_num`, `created_at`"
            . " FROM {$this->_tableName} where `feed_id`=$feed_id and `deleted_at` is NULL"
            . " order by `id` LIMIT {$limit}";
        if (isset($next_id)) {
            $sql = "SELECT `id`, `uid`, `content`, `status`, `quote_id`, `like_num`, `created_at`"
                . " FROM {$this->_tableName} where `feed_id`=$feed_id and `id` > {$next_id} and `deleted_at` is NULL"
                . " order by `id`";
        }
        $ret = $this->db->query($sql);
        if (false === $ret) {
            Log::warning("db connect fail");
            return false;
        }
        return $ret;
    }

    /**
     * @desc 删除评论
     * @param $id
     * @return
     */
    public function delComment($id) {
        return $this->db->update($this->_tableName, 'deleted_at=CURRENT_TIMESTAMP()', [['id','=', $id]]);
    }

    /**
     * @desc 某个用户评论数
     * @param $uid
     * @param $from
     * @return
     */
    public function commentCount($uid, $from = null) {
        $sql = "select count(id) from {$this->_tableName} where uid = $uid";
        if (null != $from) {
            $sql .= " and created_at>$from";
        }
        $ret = $this->db->query($sql);
        if (false === $ret) {
            Log::warning("db connect fail");
            return false;
        }
        return $ret[0]['count(id)'];
    }
}
