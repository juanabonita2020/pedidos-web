<?php require 'lib/kernel.php';

// obtener detalles de cierre de una campaña
class API extends JBSYSWEBAPI
{

//protected $_logReq = true;

protected $_validUserTypes = array(JBSYSWEBAPI::USR_EMPRE, JBSYSWEBAPI::USR_ADMIN);

protected $_input = array(
'campania' => array(
	'required' => false
),
'zona' => array(
)
);

protected function _process($input)
{
	if ($this->_userType == JBSYSWEBAPI::USR_ADMIN)
		$zona = $input['zona'];
	else
		$zona = null;
	
	return $this->_getCierre($input['campania'], $zona);
}

}

require 'lib/exe.php';