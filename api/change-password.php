<?php require 'lib/kernel.php';

// cambiar la contraseña del usuario logeado
class API extends JBSYSWEBAPI
{

protected $_input = array(
/*'password' => array(
	'required' => true
),*/
'newPassword' => array(
	'required' => true
)
);

//~protected $_logReq = true;

protected function _process($input)
{
	return array('ok' => $this->_setPassword($this->_userId, $input['newPassword']/*, $input['password']*/));
}

}

require 'lib/exe.php';