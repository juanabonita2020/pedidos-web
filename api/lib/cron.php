<?php if (empty($JBSYSWEBAPI)) die;

$api = new CRON();

require 'lib/config.php';

$api->process();