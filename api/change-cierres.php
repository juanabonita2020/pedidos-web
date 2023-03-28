<?php require 'lib/kernel.php';

// actualizamos la cantidad máxima de envíos para un usuario
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN);

protected $_input = array(
'usuario' => array(
	'required' => false
),
'cierres' => array(
	'required' => false
)
);

protected $_logReq = true;

protected function _process($input)
{
	$this->_dbUpdate('web_usuarios', array('cantidad_cierres' => $input['cierres']), array('id_web_usuarios' => $input['usuario']));
}

}

require 'lib/exe.php';