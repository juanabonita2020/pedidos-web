<?php require 'lib/kernel.php';

// eliminar un usuario regional
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN);

protected $_input = array(
'id' => array(
	'required' => true
)
);

protected $_logReq = true;

protected function _process($input)
{
	if (! empty($input['id']))
		$this->_dbDelete('web_usuarios', array('id_web_usuarios' => $input['id']));
}

}

require 'lib/exe.php';