<?php
namespace library\base;

use GOD\DB;
use GOD\Factory;
use GOD\Log;
use GOD\RpcClient;

use library\define\Constant;

// 基类
class ModelBase {
    protected $db = null;
    protected $tableName = '';
    private static $_models = array();

    protected function __construct($dbName = '') {
        if (!$dbName) {
            $dbName = Constant::DB_NAME_GOLDEN;
        }
        // disallow new instance
        $this->db = RpcClient::getDb($dbName);
        if (false === $this->db) {
            throw new \Exception("can not connect to db", 1);
        }

        $this->db->addHook(DB::HK_AFTER_QUERY, 0, function (DB $db, $ret) {
            $costTime = ceil($db->getLastCost() / 1000) . 'ms';
        });
    }

    final private function __clone() {
        // disallow clone
    }

    /**
     * @param string $className
     * @return ModelBase
     */
    public static function getModel($className = __CLASS__) {
        if (!isset(self::$_models[$className])) {
            self::$_models[$className] = new $className();
        }
        return self::$_models[$className];
    }

    public static function getInstance() {
        return Factory::create(static::class);
    }

    /**
     * 获取表名
     * @return string
     */
    public function getTableName() {
        return $this->tableName;
    }

    /**
     * 按主键查询
     * @param $id
     * @return array
     */
    public function getInfoByID($id) {
        $ret = $this->db->select($this->getTableName(), array('*'), array("id = $id"));
        return empty($ret) ? array() : $ret[0];
    }

    /**
     * 批量按主键查询
     * @param $idList
     * @return array|bool|\DQD\结果数组
     */
    public function getInfoByIDList($idList) {
        $idList = array_unique(array_map('intval', $idList));
        if (empty($idList)) {
            return [];
        }
        $sql = sprintf("id in (%s)", implode(',', $idList));
        $ret = $this->db->select($this->getTableName(), array('*'), $sql);
        return empty($ret) ? array() : $ret;
    }

    /**
     * 查询
     * @param $fields
     * @param $conds
     * @param $appends
     * @param $options
     * @return array
     */
    public function select($fields, $conds = null, $appends = null, $options = null) {
        $ret = $this->db->select($this->getTableName(), $fields, $conds, $options, $appends);
        if (false === $ret) {
            Log::fatal("select failed, sql:" . $this->db->getLastSQL() . ' Error:'
                . $this->db->getError() . '(' . $this->db->getErrno() . ')');
            return [];
        }
        return empty($ret) ? array() : $ret;
    }

    /**
     * insert
     * @param $data
     * @param null $options
     * @param null $onDup
     * @return bool|int
     */
    public function insert($data, $options = null, $onDup = null) {
        return $this->db->insert($this->getTableName(), $data, $options, $onDup);
    }

    /**
     * count
     * @param $conds
     * @param null $options
     * @param null $appends
     * @return int
     */
    public function count($conds, $options = NULL, $appends = NULL) {
        $total = $this->db->selectCount($this->getTableName(), $conds, $options, $appends);
        if (false === $total) {
            Log::fatal("count failed, sql:" . $this->db->getLastSQL() . ' Error:'
                . $this->db->getError() . '(' . $this->db->getErrno() . ')');
            return 0;
        }
        return (int)$total;
    }

    /**
     * 事务开启
     * @param string $dbRpcName
     * @return mixed
     * @throws \Exception
     */
    public static function startTransaction($dbRpcName = '') {
        $dbRpcName = $dbRpcName ?: Constant::DB_NAME_GOLDEN;
        $db = RpcClient::getDb($dbRpcName);
        if (false === $db) {
            throw new \Exception("can not start transaction");
        }
        return $db->startTransaction();
    }

    /**
     * 事务提交
     * @param string $dbRpcName
     * @return mixed
     * @throws \Exception
     */
    public static function commit($dbRpcName = '') {
        $dbRpcName = $dbRpcName ?: Constant::DB_NAME_GOLDEN;
        $db = RpcClient::getDb($dbRpcName);
        if (false === $db) {
            throw new \Exception("can not commit transaction");
        }
        return $db->commit();
    }

    /**
     * 事务回滚
     * @param string $dbRpcName
     * @return mixed
     * @throws \Exception
     */
    public static function rollback($dbRpcName = '') {
        $dbRpcName = $dbRpcName ?: Constant::DB_NAME_GOLDEN;
        $db = RpcClient::getDb($dbRpcName);
        if (false === $db) {
            throw new \Exception("can not rollback transaction");
        }
        return $db->rollback();
    }

    /**
     * 代理DB的方法
     * @param $method
     * @param $arguments
     * @return mixed|null
     */
    public function __call($method, $arguments) {
        if (!method_exists($this->db, $method)) {
            return null;
        }
        $arguments AND array_unshift($arguments, $this->getTableName());
        return call_user_func_array([$this->db, $method], $arguments);
    }
}
