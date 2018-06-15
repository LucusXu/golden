<?php
/**
 * @date 2017-10-08 09:10:30
 * @last Modified xuliqiu
 * @last Modified 2017-10-08 08:15:16
 */

use GOD\Log;
use GOD\RpcClient;
use library\base\ModelBase;
use library\define\Constant;
use library\define\ErrorDefine;

class TwittersModel extends ModelBase {
    public $_tableName = 'twitters';
    private $_fields = 'user_id, text, published_at, user_name';

	public function __construct() {
        parent::__construct(Constant::DB_NAME_WUYA);
	}

    public function getTwitters() {
        $where = [];
        $ret = $this->db->select($this->_tableName, $this->_fields, $where);
        if ($ret === false) {
            throw new \Exception('查询失败', ErrorDefine::ERRNO_MYSQL_CONNECT_ERROR);
        }

        if (count($ret)) {
            return $ret;
        } else {
            return false;
        }
    }

    public function addTwitter($twitter) {
        /*
        $onDup = [
            "text" => $twitter['text'],
            "source" => $twitter['source'],
            "published_at" => $twitter['published_at'],
        ];
        */
        // var_dump($twitter);
        // var_dump($this->_tableName);
        $ret = $this->db->insert($this->_tableName, $twitter);
        // $ret = $this->db->insert($this->_tableName, $twitter, null, $onDup);

        if (false === $ret) {
            throw new \Exception('创建失败', ErrorDefine::ERRNO_MYSQL_CONNECT_ERROR);
        }
        return $ret;
    }
}
