<?php
require 'lib/kernel.php';

// devuelve el estado de la sesión
class API extends JBSYSWEBAPI
{

//protected $_checkSession = false;

protected function _process($input)
{
	if ($this->_userId)
		return array('loggedInUsername' => $this->_userName, 'verifiedUserAccount' => true, 'global' => $this->_getPublicGlobal(), 'siteURL' => $this->_config['baseURL'], 'alert' => $this->_getSessionVar('userAlert'));

	return array('loggedInUsername' => null, 'verifiedUserAccount' => false);
}

}

require 'lib/exe.php';
