<?php 

chdir('..');
require 'lib/kernel.php';

class CRON extends JBSYSWEBCRON
{

private $_path = 'logs/';
private $_daysOld = 7;

protected $_cronId = 'clearlogs';
//protected $_logReq = true;

protected function _process($input)
{
	$q = 0;
	
	list($y, $m, $d) = explode('-', date('Y-m-d'));
	$ts = mktime(0, 0, 0, $m, $d, $y) - 86400 * ($this->_daysOld - 1);
	$handle = opendir($this->_path);
	
	$this->_logReqData[] = 'Timestamp: '.$ts;
	
	while (false !== ($entry = readdir($handle))) if (substr($entry, 0, 1) != '.')
	{
		$file = $this->_path.$entry;
		
		if (filemtime($file) < $ts)
		{
			if ($q++ < 11) $this->_logReqData[] = 'Fichero a eliminar (muestra): '.$file;
			unlink($file);
		}
	}		
    
	closedir($handle);
	
	$this->_logReqData[] = 'Ficheros eliminados: '.$q;
}

}

require 'lib/cron.php';