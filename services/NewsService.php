<?php
namespace services;

use GOD\Log;

class NewsService {
    private $_newsObj = null;
    public function __construct() {
        $this->_newsObj = \NewsModel::getInstance();
    }

	public function getNews() {
        $data = $this->_newsObj->getNews();
        return $data;
    }
}
