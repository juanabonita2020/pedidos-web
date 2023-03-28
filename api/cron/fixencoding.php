<?php

chdir('..');
require 'lib/kernel.php';

class CRON extends JBSYSWEBCRON
{

protected $_cronId = 'fixencoding';
//protected $_logReq = true;

protected function _process($input)
{
	$this->fixEncoding('pto-catalogo');
}

}

require 'lib/cron.php';