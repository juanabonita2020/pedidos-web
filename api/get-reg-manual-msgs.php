<?php require 'lib/kernel.php';

// devolver los mensajes enviados de registraciones manuales
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN);

protected $_input = array(
'id' => array(
	'required' => true
)
);

protected function _process($input)
{
	$res = array('msgs' => array());
	
	foreach($this->_dbGetAll('web_registracion_manual_mensajes', array('id_web_registracion_manual' => $input['id']), null, 'timestamp DESC') as $r)
	{
		$r['timestamp'] = $this->_fromDate(substr($r['timestamp'], 0, 10)).substr($r['timestamp'], 10);
		$res['msgs'][] = $r;
	}
	
	return $res;
}

}

require 'lib/exe.php';