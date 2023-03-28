<?php require 'lib/kernel.php';

// obtener el listado de campañas
class API extends JBSYSWEBAPI
{
	
protected $_input = array(
'check_envio' => array(
),
'check_usertype' => array(
)
);

//protected $_logReq = true;

protected function _process($input)
{
	return $this->_getCampanias($this->_userZone, $input['check_envio'], null, null, ! empty($input['check_usertype']) && $this->_userType != JBSYSWEBAPI::USR_ADMIN && $this->_userType != JBSYSWEBAPI::USR_EMPRE);
}

}

require 'lib/exe.php';