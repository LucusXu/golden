<?php
namespace services;

use GOD\Log;

class SnsService {
    private $_objTwitter = null;
    public function __construct() {
         $this->_objTwitter = \TwittersModel::getInstance();
    }

    public function addTwitters($data) {
        $info = json_decode($data, true);
        $id = $this->_objTwitter->addTwitter($info);
        Log::warning("id:" . $id);
        return $id;
    }
}
