<?php
/**
 * Author: Lucus
 */

use GOD\Log;

use library\base\ModelBase;
use library\define\Constant;
use library\define\ErrorDefine;

class AuthorizeModel extends ModelBase {
    public $_tableName = 'authorize';
    private $_fields = 'id, user_id, device_id, access_token';

	public function __construct() {
        parent::__construct(Constant::DB_NAME_GOLDEN);
	}

    public function createAuthorize($userId, $accessToken, $deviceId) {
        if (!$userId || !$deviceId) {
            Log::warning('authorize:create:--lost params');
            return false;
        }
        $data = ['user_id' => $userId, 'access_token' => $accessToken, 'device_id' => $deviceId];
        $ok = $this->db->insert($this->getTableName(), $data);
        if ($ok === false) {
            throw new \Exception('authorize:create: mysql error', ErrorDefine::ERRNO_MYSQL_CONNECT_ERROR);
        }
        $lastId = $this->db->getInsertID();
        $data['id'] = $lastId;
        return $data;
    }

    public function findAuthorizeByDeviceId($userId, $deviceId) {
        if (!$userId || !$deviceId) {
            Log::warning('authorize:find:--lost params');
            return false;
        }

        $where = [
            'user_id=' => $userId,
            'device_id=' => $deviceId,
        ];
        $ret = $this->db->select($this->_tableName, $this->_fields, $where);
        if (false === $ret) {
            Log::warning("query failed");
        }
        if (count($ret)) {
            return $ret[0];
        }
        return $ret;
    }

    public function findAuthByToken($accessToken) {
        if (!$accessToken) {
            Log::warning('no access token');
            return false;
        }
        $where = [
            'access_token=' => $accessToken,
        ];
        $ret = $this->db->select($this->_tableName, $this->_fields, $where);

        if (false === $ret) {
            Log::warning("query failed");
            return false;
        }
        if (count($ret)) {
            return $ret[0];
        }
        return $ret;
    }
}
