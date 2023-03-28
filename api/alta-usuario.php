<?php require 'lib/kernel.php';

// dar de alta un usuario que está de baja
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
	$this->_dbUpdate('web_cache_clientes', array('baja' => 0), array('id_web_cache_clientes' => $input['id']));
}

}

require 'lib/exe.php';