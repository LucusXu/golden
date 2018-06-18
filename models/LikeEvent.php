<?php
/**
 * Author: Lucus
 */

use GOD\Log;

use library\base\ModelBase;
use library\define\Constant;
use library\define\ErrorDefine;

class LikeEventModel extends ModelBase {
    protected $_tableName = 'like_events';
    private $_fields = 'id, relate_type, relate_id, event_type, event_id, status';

    public function __construct() {
        parent::__construct(Constant::DB_NAME_GOLDEN);
    }

    public function getUserArticleLike($event_id, $uid) {
        $sql ='SELECT id from ' . $this->_tableName
            .' WHERE event_type="feed" and event_id=' . $event_id . ' and relate_id=' . $uid;
        $ret = $this->db->query($sql);
        if (false === $ret) {
            Log::warning("execute sql fail");
            return false;
        }
        return $ret;
    }

    public function addLikeEvent($uid, $event_id, $status, $event_type) {
        $onDup = [
            'status' => $status,
        ];
        $data = [
            "relate_type" => "user_id",
            "relate_id" => $uid,
            "event_id" => $event_id,
            "event_type" => $event_type,
            "status" => $status,
        ];

        $ret = $this->db->insert($this->_tableName, $data, null, $onDup);
        if (false === $ret) {
            Log::warning("execute sql fail");
            return false;
        }
        return $this->db->getInsertID();
    }

    public function getArticleLikeUids($event_id, $limit) {
        $sql ='SELECT uid, updated_at from ' . $this->_tableName
            .' WHERE event_type="feed" and event_id=' . $event_id . " limit " . $limit;
        $ret = $this->db->query($sql);
        if (false === $ret) {
            Log::warning("execute sql fail");
            return false;
        }
        return $ret;
    }
}
