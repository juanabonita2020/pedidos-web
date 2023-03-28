<?php require 'lib/kernel.php';

// eliminar un flyer
class API extends JBSYSWEBAPI
{
	
protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN);

protected $_input = array(
'flyer' => array(
	'required' => true
)
);

//protected $_logReq = true;

protected function _process($input)
{
	$this->_dbDelete('web_flyers', array('id_web_flyers' => $input['flyer']));
}

}

require 'lib/exe.php';