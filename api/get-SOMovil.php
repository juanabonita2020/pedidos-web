<?php require 'lib/kernel.php';

// obtenemos el listado de SO movil
class API extends JBSYSWEBAPI
{

protected function _process($input)
{
	return array('SOMovil' => $this->_dbGetAll('web_sistema_operativo', null, array('sistema_operativo', 'id_sistema_operativo'), 'sistema_operativo'));
}

}

require 'lib/exe.php';
