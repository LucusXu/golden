<?php
/**
 * @author Lucus
 */

use GOD\Log;
use GOD\RpcClient;

use library\base\ModelBase;
use library\define\Constant;
use library\define\ErrorDefine;

class NewsModel extends ModelBase {
    public $_tableName = 'coin_news_content';
    private $_fields = 'title, summary, content, source, published_at, tags';

	public function __construct() {
        parent::__construct(Constant::DB_NAME_GOLDEN);
	}

    public function getNews() {
        $where = [];
        $ret = $this->db->select($this->_tableName, $this->_fields, $where);
        if (false === $ret) {
            throw new \Exception('查询失败', ErrorDefine::ERRNO_MYSQL_CONNECT_ERROR);
        }

        if (count($ret)) {
            return $ret;
        } else {
            return false;
        }
    }
}
