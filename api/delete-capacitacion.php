<?php require 'lib/kernel.php';

// eliminar una capacitación
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
	$this->_dbDelete('web_capacitacion', array('id_web_capacitacion' => $input['id']));
}

}

require 'lib/exe.php';