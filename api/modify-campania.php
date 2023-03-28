<?php require 'lib/kernel.php';

// modifica parámetros de la campaña
class API extends JBSYSWEBAPI
{

protected $_input = array(
'tipo' => array(
	'required' => true
),
'negocio' => array(
	'required' => true
),
'campania' => array(
	'required' => true
),
'val' => array(
	'required' => true
)
);

protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN);

protected function _process($input)
{
	// comprobar la campaña
	if (! $this->_validCamp($input['campania'])) return false;

	if ($input['tipo'] == 'rel')
		$data = array('relevamiento' => $input['val'] == 'false' ? 0 : 1);
	else
	{
		$var = 'minimo_'.($input['tipo'] == 'unid' ? 'unidades' : 'monto').'_'.($input['negocio'] == 'e' ? 'E' : 'D');
		$data = array($var => $input['val']);
	}

	//var_dump($input['val']);print_r($data);

	$this->_dbUpdate('web_campanias', $data, array('id_web_campanias' => $input['campania']));
}

}

require 'lib/exe.php';