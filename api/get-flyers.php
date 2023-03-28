<?php require 'lib/kernel.php';

// obtener los flyers
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN);

protected $_input = array(
'flyer' => array(
)
);

private $_pages = array(
'panel' => 'Panel del usuario',
'carga' => 'Carga/edición del pedido',
'personal' => 'Datos personales',
'clientes' => 'Gestión de clientes',
'regionales' => 'Gestión de regionales',
'gestion' => 'Gestión de pedidos',
'usuarios' => 'Listado de usuarios',
'historial' => 'Historial de pedidos',
'comunidad' => 'Comunidad inicio',
'canje' => 'Canje',
'accion-enviar pedido' => 'Acción: enviar pedido',
'accion-cerrar pedido' => 'Acción: cerrar pedido',
'accion-cerrar pedido propio' => 'Acción: cerrar pedido propio'
);

private $_userTypes = array('', 'Empresaria', 'Revendedora', 'Administrador', 'Coordinadora', 'Consumidora', 'Regional');

//protected $_logReq = true;

protected function _process($input)
{
	if (! empty($input['flyer'])) $params = array('id_web_flyers' => $input['flyer']);

	$res = array('flyers' => array(), 'sistemas' => array());
	$paises = $sispais = array();

	foreach($this->_dbGetAll('web_paises') as $f)
		$paises[$f['id_web_paises']] = $f['pais'];

	foreach($this->_dbGetAll('web_sistemas') as $f)
	{
		if (! isset($res['sistemas'][$f['pais']]))
			$res['sistemas'][$f['pais']] = array($paises[$f['pais']], array());
		$res['sistemas'][$f['pais']][1][] = $f['sistema'];
		$sispais[$f['sistema']] = $f['pais'];
	}
        
	foreach($this->_dbGetAll('web_flyers', isset($params) ? $params : null) as $f)
	{
		$p = $sispais[$f['sistema']];
		$res['flyers'][] = array(
		'id' => $f['id_web_flyers'],
		'titulo' => $f['titulo'],
		'sistema' => $f['sistema'],
		'pagina' => $this->_pages[$f['pagina']],
		'pagina_o' => $f['pagina'],
		'fecha_desde' => empty($f['fecha_desde']) ? '--' : $this->_fromDate($f['fecha_desde']),
		'fecha_hasta' => empty($f['fecha_hasta']) ? '--' : $this->_fromDate($f['fecha_hasta']),
		'dest' => (empty($f['dest_usuario']) ? (empty($f['dest_tipousuario']) ? 'Todos' : $this->_userTypes[$f['dest_tipousuario']]) : 'Usuario ID:'.$f['dest_usuario']),
		'auto_borrar' => (empty($f['auto_borrar']) ? 'No' : 'Sí'),
		'auto_borrar_o' => $f['auto_borrar'],
		'dest_usuario' => $f['dest_usuario'],
		'dest_tipousuario' => $f['dest_tipousuario'],
		'contenido' => $f['contenido'],
		'pais' => $p.' - '.$res['sistemas'][$p][0]
		);
	}

	if (empty($input['flyer'])) return $res;

	return $res['flyers'][0];
}

}

require 'lib/exe.php';
