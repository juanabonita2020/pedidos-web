<?php 
require 'lib/kernel.php';

// devuelve los datos necesarios para el login de líderes
class API extends JBSYSWEBAPI
{
	
protected $_validUserTypes = array(JBSYSWEBAPI::USR_EMPRE);

protected function _process($input)
{
	// Cambio login de empresarias
	//if ($this->_userNegocio == 'D')
		return array('url' => $this->_config['loginLideresURL'], 'username' => sprintf('Z%05s', $this->_userZone), 'password' => md5($this->_userClient));
}

}

require 'lib/exe.php';