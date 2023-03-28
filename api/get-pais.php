<?php require 'lib/kernel.php';

// obtenemos el listado de paises
class API extends JBSYSWEBAPI
{

protected function _process($input)
{
	return array('pais' => $this->_dbGetAll('web_paises', null, array('pais', 'id_web_paises'), 'pais'));
}

}

require 'lib/exe.php';
