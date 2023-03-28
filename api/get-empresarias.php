<?php require 'lib/kernel.php';

// obtener el listado de empresarias
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN);

protected function _process($input)
{
	$empresarias = $this->_dbQuery('
	SELECT
		b.nombre, cantidad_cierres cierres, id_cli_zonas zona, id_web_usuarios usuario
	FROM
		web_usuarios a, web_cache_clientes b
	WHERE
		a.id_cli_clientes = b.id_cli_clientes
		AND numero_cliente = 1
	');
	
	foreach($empresarias as $k => $r)
		$empresarias[$k]['nombre'] = utf8_encode($r['nombre']);
	
	return array('empresarias' => $empresarias);
}

}

require 'lib/exe.php';