<?php require 'lib/kernel.php';

// obtener un cliente
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN);

protected $_input = array(
'zona' => array(
),
'cliente' => array(
)
);

protected function _process($input)
{
	$res = $this->_dbGetOne('web_cache_clientes', array('id_cli_zonas' => $input['zona'], 'numero_cliente' => $input['cliente']));
	$res['nombre'] = utf8_encode($res['nombre']);
	return $res;
}

}

require 'lib/exe.php';