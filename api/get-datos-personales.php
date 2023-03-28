<?php require 'lib/kernel.php';

// obtener los datos personales del usuario logeado
class API extends JBSYSWEBAPI
{

//protected $_logReq = true;

protected function _process($input)
{
	$res = $this->_dbGetOne('web_usuarios',
	array('id_web_usuarios' => $this->_userId),
	array(
	'nombre',
	'apellido',
	'direccion',
	'localidad',
	'cel_area' => 'celularArea',
	'cel_prefijo' => 'celularPrefijo',
	'cel_sufijo' => 'celularSufijo',
	'tel_area1' => 'telefonoArea1',
	'tel_prefijo1' => 'telefonoPrefijo1',
	'tel_sufijo1' => 'telefonoSufijo1',
	'tel_area2' => 'telefonoArea2',
	'tel_prefijo2' => 'telefonoPrefijo2',
	'tel_sufijo2' => 'telefonoSufijo2',
	'fecha_nacimiento' => 'fechaNacimiento',
	'id_web_provincias' => 'provincia',
	'id_cli_clientes' => 'id',
	'codigopostal' => 'codigoPostal',
	'cantidad_abiertas' => 'openedOrders',	
	'altura' => 'altura',
	'piso' => 'piso',
	'departamento' => 'departamento',
	'barrio' => 'barrio',
	'id_web_paises' => 'id_web_paises',
	'tiene_instagram' => 'tiene_instagram',
	'usuario_instagram' => 'usuario_instagram',
	'tiene_facebook' => 'tiene_facebook',
	'usuario_facebook' => 'usuario_facebook',
	'id_sistema_operativo' => 'id_sistema_operativo',		
	'alerta_cerrarpedido' => 'closeAlert',
	'fecha_datos_personales'
	));

	$res2 = $this->_dbGetOne('web_cache_clientes', array('id_cli_clientes' => $this->_userClient));

	$res['refId'] = $this->_userId;
	$res['type'] = $this->_userType;
	$res['parent'] = $this->_userParent;
	$res['id'] = $this->_userClient;
	$res['idF'] = sprintf('%04s', $res['id']);
	$res['nroCliente'] = sprintf('%04s', $res2['numero_cliente']);
	$res['habilitada'] = true;
	$res['zone'] = $this->_userZone;
	$res['zona'] = sprintf('%04s', $this->_userZone);
	$res['negocio'] = $res2['negocio'];

	if (! empty($res['fechaNacimiento']))
	{
		list($y, $m, $d) = explode('-', substr($res['fechaNacimiento'], 0, 10));
		$res['fechaNacimiento'] = date('d/m/Y', mktime(0, 0, 0, $m, $d, $y));
	}
	
	$res['refererClientName'] = $this->_getSessionVar('refererFullName');
	
	$res['direccion'] = utf8_encode($res['direccion']);

	if ($res['fecha_datos_personales'] == '0000-00-00')
		$res['forzarDatosPersonales'] = '1';
	else
	{
                if( $res['fecha_datos_personales'] != null ){
                    list($y, $m, $d) = explode('-', $res['fecha_datos_personales']);
                    $res['forzarDatosPersonales'] = ((time() - mktime(0, 0, 0, $m, $d, $y)) / 86400 >= $this->_global['dias_forzar_datos_personales'] ? '1' : '0');
                }
	}

	return $res;
}

}

require 'lib/exe.php';
