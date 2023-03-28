<?php require 'lib/kernel.php';

// devolvemos las estadísticas de contenidos
class API extends JBSYSWEBAPI
{

//protected $_disabled = true;

protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN);

protected $_input = array(
);

protected function _process($input)
{
	return array('stats' => $this->_dbGetAll('web_estadisticas'));
}

}

require 'lib/exe.php';