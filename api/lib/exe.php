<?php if (empty($JBSYSWEBAPI)) die;

$api = new API();

require '../version.php';
require 'lib/config.php';

$api->process();