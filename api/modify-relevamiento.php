<?php require 'lib/kernel.php';

// modifica parámetros de la campaña
class API extends JBSYSWEBAPI
{

protected $_input = array(
'campania' => array(
	'required' => true
),
'cantidad' => array(
	'required' => true
)
);

protected $_validUserTypes = array(JBSYSWEBAPI::USR_EMPRE);

protected function _process($input)
{
	// comprobar la campaña
	if (! $this->_validCamp($input['campania'])) return false;

	// buscar relevamiento
	$r = $this->_dbGetOne('web_catalogos', array('id_cli_zonas' => $this->_userZone, 'id_web_campanias' => $input['campania']));

	$update = (isset($r['id']) && $r['cantidad'] != $input['cantidad']);

	$q = ($update ? 'UPDATE' : 'INSERT INTO').' web_catalogos SET cantidad = ?, '.($update ? 'flag_modificacion = 1 WHERE id = ?' : 'id_cli_zonas = ?, id_web_campanias = ?, timestamp = NOW()');

	$params = array($input['cantidad']);

	if ($update)
		$params[] = $r['id'];
	else
	{
		$params[] = $this->_userZone;
		$params[] = $input['campania'];
	}

	$this->_dbQuery($q, $params);
}

}

require 'lib/exe.php';