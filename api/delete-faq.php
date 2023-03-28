<?php require 'lib/kernel.php';

// obtenemos el listado de provincias
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
	$this->_dbDelete('web_faq', array('id_web_faq' => $input['id']));
}

}

require 'lib/exe.php';