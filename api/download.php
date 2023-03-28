<?php require 'lib/kernel.php';

// descargar contenido
class API extends JBSYSWEBAPI
{

protected $_checkSession = false;

protected $_input = array(
'path' => array(
)
);

//protected $_logReq = true;

protected function _process($input)
{
	if (strpos($input['path'], '..') !== false) return false;
	$file = '../'.$input['path'];
	if (! file_exists($file)) return false;
	if (substr($input['path'], 0, 24) == 'contenidos/capacitacion/')
	{
		$filename = basename($file);
		$logId = 'guion-'.$filename;
		$this->_saveStat($logId, 1, 'Guión - '.$filename);
		$this->_addAuditLog($logId);
	}
	return $this->_downloadFile($file);
}

}

require 'lib/exe.php';