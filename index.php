<?php
// init wm
$wm = dirname(__FILE__)."/lib/wm.php";
$config = dirname(__FILE__)."/config/config.php";
require($wm);
WM::createWebApp($config)->run();