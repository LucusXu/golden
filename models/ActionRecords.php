<?php
/**
 * Author: Lucus
 */

use GOD\Log;

use library\base\ModelBase;
use library\define\Constant;
use library\define\ErrorDefine;

class ActionRecordsModel extends ModelBase {
    protected $_tableName = 'action_records';
    private $_fields = 'id, relate_type, relate_id, action, feed_id, status';

    public function __construct() {
        parent::__construct(Constant::DB_NAME_GOLDEN);
    }

    public function getUserArticleRecord($feed_id, $uid, $action = "quote") {
        $sql ='SELECT id from ' . $this->_tableName
            .' WHERE action="'. $action .'" and feed_id=' . $feed_id . ' and relate_id=' . $uid;
        $ret = $this->db->query($sql);
        if (false === $ret) {
            Log::warning("execute sql fail");
            return false;
        }
        return $ret;
    }

    public function addActionRecord($uid, $feed_id, $action = 'quote') {
        $onDup = [
            'action' => $action,
        ];
        $data = [
            "relate_type" => "user_id",
            "relate_id" => $uid,
            "feed_id" => $feed_id,
            "action" => $action,
        ];

        $ret = $this->db->insert($this->_tableName, $data, null, $onDup);
        if (false === $ret) {
            Log::warning("execute sql fail");
            return false;
        }
        return $this->db->getInsertID();
    }
}
