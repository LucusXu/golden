<?php
/**
 * @author Lucus
 */

use GOD\Log;

use library\base\Basecontroller;

class DemoController extends Basecontroller {
    public function demoAction() {
        this->returnResult(0, "success");
    }
}
