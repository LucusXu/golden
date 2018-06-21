<?php
/**
 * @Author : Lucus
 */

use GOD\Log;

use library\base\ModelBase;
use library\define\Constant;
use library\define\ErrorDefine;

class UsersModel extends ModelBase {
    public $_tableName = 'users';
    private $_fields = 'id, name, avatar, phone_number, head_pic, official, block, level, created_at';

	public function __construct() {
        parent::__construct(Constant::DB_NAME_GOLDEN);
	}

    public function getUserById($id) {
        $where = [
            'id=' => $id,
        ];
        $ret = $this->db->select($this->_tableName, $this->_fields, $where);

        if (false === $ret) {
            Log::warning("query failed");
            return false;
        }

        if (count($ret)) {
            return $ret[0];
        }
        return false;
    }

    public function create($name, $phoneNum, $uuid, $password) {
        $onDup = [
            "name" => $name,
            "password" => $password,
        ];
        $data = [
            'name' => $name,
            'phone_number' => $phoneNum,
            'uuid' => $uuid,
            'password' => $password,
        ];
        $ret = $this->db->insert($this->getTableName(), $data, null, $onDup);
        if (false === $ret) {
            return false;
        }
        $id = $this->db->getInsertID();
        return $id;
    }
}
