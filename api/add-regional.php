<?php require 'lib/kernel.php';

// agrega un usuario regional
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN);

protected $_input = array(
'email' => array(
	'required' => true
),
'pass' => array(
	'required' => true
),
'passm' => array(
),
'region' => array(
//	'required' => true
),
'division' => array(
//	'required' => true
),
'nombre' => array(
	'required' => true
),
'apellido' => array(
	'required' => true
),
'negocio' => array(
//	'required' => true
)
);

protected $_logReq = true;

protected function _process($input)
{
	$r = $this->_dbGetOne('web_usuarios', array('mail' => $input['email']), array('mail'));
	if (empty($r['mail']))
	{	
		$data = array('region' => $input['region'], 'division' => $input['division'], 'habilitada' => 1, 'mail' => $input['email'], 'nombre' => $input['nombre'], 'apellido' => $input['apellido']);
		if (! empty($input['negocio'])) $data['regional_negocio'] = $input['negocio'];
		$data['password_m'] = (empty($input['passm']) ? '' : $this->_hash($input['passm']));
		$this->_dbInsert('web_usuarios', $data);
		$this->_setPassword($this->_dbInsertId, $input['pass']);
	}
	else
		return array('msg' => 'Regional ya existe.');
}

}

require 'lib/exe.php';
