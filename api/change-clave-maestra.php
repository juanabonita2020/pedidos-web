<?php require 'lib/kernel.php';

// actualiza un usuario regional
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN, JBSYSWEBAPI::USR_REGIO);

protected $_input = array(
'pass' => array(
	'required' => true
)
);

protected $_logReq = true;

protected function _process($input)
{
	$hash = (empty($input['pass']) ? '' : $this->_hash($input['pass']));

	if ($this->_userType == JBSYSWEBAPI::USR_ADMIN)
		$this->_saveGlobal('clave_maestra', $hash);
	else
		$this->_updateMyData(array('password_m' => $hash));
}

}

require 'lib/exe.php';