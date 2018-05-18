<?php
use GOD\Log;

/**
 * Bootstrap类中, 以_init开头的方法, 都会按顺序执行
 */
class Bootstrap extends \Yaf\Bootstrap_Abstract {
    public function _initLoader (\Yaf\Dispatcher $dispatcher) {
        // 注册本地类名前缀, 这部分类名将会在本地类库查找
        Yaf\Loader::getInstance()->registerLocalNameSpace(['library','services','define']);
    }

    public function _initPlugin(\Yaf\Dispatcher $dispatcher) {
    }

    public function _initRoute(\Yaf\Dispatcher $dispatcher) {
    }

    public function _initView(Yaf\Dispatcher $dispatcher) {
        // 关闭试图自动渲染
        $dispatcher->disableView();
    }
}
