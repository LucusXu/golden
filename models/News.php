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
    private $_fields = 'title, summary, content, published_at, uid, like_num, comment_num, status, quote_num, share_num, type, collect_num';

	public function __construct() {
        parent::__construct(Constant::DB_NAME_GOLDEN);
	}

    public function getNews() {
        $where = [
            'site='=> 'bishijie',
        ];
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

    public function addArticle($uid, $content) {
        $data = [
            'uid' => $uid,
            'content' => $content,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $onDup = [
            "uid" => $uid,
            "content" => $content,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $ret = $this->db->insert($this->_tableName, $data, null, $onDup);
        if (false === $ret) {
            Log::warning("connect db fail");
            return false;
        }
        return $this->db->getInsertID();
    }

    public function getArticleList($next, $limit) {
        $sql = "SELECT `id`, `title`, `content`, `summary`, `source`, `created_at`"
            . " FROM {$this->_tableName} where status=1"
            . " order by `id` LIMIT {$limit}";
        if (isset($next)) {
            $sql = "SELECT `id`, `type`, `uid`, `content`, `summary`, `created_at`, comment_num, like_num, collect_num"
                . ",quote_num FROM {$this->_tableName} where id>{$next} and `status`=1"
                . " order by id limit {$limit}";
        }
        $ret = $this->db->query($sql);
        if (false === $ret) {
            Log::warning("db connect fail");
            return false;
        }
        return $ret;
    }

    public function nextCount($next) {
        $sql = "SELECT count(id) FROM {$this->_tableName} where `id` > {$next}";
        $ret = $this->db->query($sql);
        if (false === $ret) {
            Log::warning("db connect fail");
            return false;
        }
        return $ret[0]['count(id)'];
    }

    public function getNewsById($id) {
        $where = [
            'id='=> $id,
        ];
        $ret = $this->db->select($this->_tableName, $this->_fields, $where);
        if (false === $ret) {
            throw new \Exception('查询失败', ErrorDefine::ERRNO_MYSQL_CONNECT_ERROR);
        }

        if (count($ret)) {
            return $ret[0];
        }
        return $ret;
    }

    public function updateLikeNum($id, $like_num) {
        $sql = "update {$this->_tableName} set like_num={$like_num} where `id` = {$id}";
        $ret = $this->db->query($sql);
        if (false === $ret) {
            Log::warning("db connect fail");
            return false;
        }
        return true;
    }

    public function updateQuoteNum($id, $quote_num) {
        $sql = "update {$this->_tableName} set quote_num={$quote_num} where `id` = {$id}";
        $ret = $this->db->query($sql);
        if (false === $ret) {
            Log::warning("db connect fail");
            return false;
        }
        return true;
    }

    public function updateShareNum($id, $share_num) {
        $sql = "update {$this->_tableName} set share_num={$share_num} where `id` = {$id}";
        $ret = $this->db->query($sql);
        if (false === $ret) {
            Log::warning("db connect fail");
            return false;
        }
        return true;
    }

    public function updateCollectNum($id, $collect_num) {
        $sql = "update {$this->_tableName} set collect_num={$collect_num} where `id` = {$id}";
        $ret = $this->db->query($sql);
        if (false === $ret) {
            Log::warning("db connect fail");
            return false;
        }
        return true;
    }
}
