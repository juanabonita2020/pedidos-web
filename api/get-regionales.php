<?php require 'lib/kernel.php';

// devuelve el listado de usuarios regionales
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN);

protected $_input = array(
'type' => array(
	'required' => true
)
);

//protected $_logReq = true;

protected function _process($input)
{
	$regionales = $this->_dbQuery('SELECT mail, region, division, nombre, apellido, id_web_usuarios id, regional_negocio negocio FROM web_usuarios WHERE id_cli_clientes IS NULL AND '.($input['type'] == 1 ? 'region IS NOT NULL AND region <> 0' : 'division IS NOT NULL AND division <> 0 AND region = 0'));
	
	foreach($regionales as $k => $r)
	{
		$regionales[$k]['nombre'] = utf8_encode($r['nombre']);
		$regionales[$k]['apellido'] = utf8_encode($r['apellido'])/*.$r['region'].'-'.$r['division'].'-'.$field*/;
	}
	
	return array('regionales' => $regionales);
}

}

require 'lib/exe.php';