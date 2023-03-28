<?php require 'lib/kernel.php';

// devuelve el estado del sistema
class API extends JBSYSWEBAPI
{

protected $_checkSession = false;
protected $_checkMaintMode = false;

protected $_input = array(
);

protected function _process($input)
{
	return array('maintMode' => ! empty($this->_config['maintMode']));
}

}

require 'lib/exe.php';