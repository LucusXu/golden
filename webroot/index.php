<?php
    $libpath = ini_get('yaf.library');
    require $libpath.'/GOD/Init.php';
    \GOD\Init::init();
    $application = new \Yaf\Application(CONFIG_PATH . "/application.ini");
    $application->bootstrap()->run();
?>
