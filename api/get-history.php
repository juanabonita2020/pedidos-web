<?php require 'lib/kernel.php';

// obtener el listado histórico de pedidos
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_EMPRE, JBSYSWEBAPI::USR_REVEN, JBSYSWEBAPI::USR_COORD);

protected $_input = array(
'cliente' => array(
),
'campania' => array(
),
'pg' => array(
)
);

//protected $_logReq = true;

protected function _process($input)
{
	// comprobar la campaña
	if (! $this->_validCamp($input['campania'])) return false;

	$q = '
	FROM
	web_pedidos a
	LEFT JOIN web_estados c
		ON a.estado = c.estado
	LEFT JOIN web_cache_clientes b
		ON a.id_cli_clientes = b.id_cli_clientes
	LEFT JOIN web_cache_preventa e
		ON a.id_web_campanias = e.id_web_campanias
	, web_envios d
	WHERE
	a.id_web_envios = d.id_web_envios
	AND a.estado NOT IN (?, ?)
	AND ';

	// descartamos los pedidos borrados y no enviados
	$params = array(JBSYSWEBAPI::OS_REM_EMPRE, JBSYSWEBAPI::OS_NSN_EMPRE);

	if ($this->_userType == JBSYSWEBAPI::USR_EMPRE)
	{
		$q .= 'd.id_cli_zonas = ?';
		$params[] = $this->_userZone;

		if (! empty($input['cliente']))
		{
			$q .= ' AND a.id_cli_clientes = ?';
			$params[] = $input['cliente'];
		}
	}
	else
	{
		$q .= '(a.id_cli_clientes = ?';

		// se muestran todos los pedidos asociados al usuario logeado...
		if (empty($input['cliente']))
		{
			/*
			$q .= ' OR a.id_web_usuarios = ?';
			$params = array_merge($params, array($this->_userClient, $this->_userId));
			*/

			$params = array_merge($params, array( $this->_userClient ) );

		}
		// ... o se filtra por cliente
		else
			$params[] = $input['cliente'];

		$q .= ')';
	}

	if (! empty($input['campania']))
	{
		$q .= ' AND a.id_web_campanias = ?';
		$params[] = $input['campania'];
	}

	$q .= ' GROUP BY id_web_pedidos ORDER BY fecha_carga DESC';

	$pager = $this->_dbPager($q, $params, $input['pg'], 20, '', 'fecha_carga');

	//echo $q;print_r($params);die;

	$pedidos = $this->_dbQuery('SELECT DATE_FORMAT(fecha_carga, "%d/%m/%Y") fecha_carga_f, fecha_carga, unidades, monto, numero_cliente, IF(nombre IS NULL, "(nombre no encontrado)", nombre) nombre, id_web_pedidos id, a.id_web_campanias campania, accion, IF(fecha_carga < fecha, 1, 0) preventa '.$q, $params);

	foreach($pedidos as $k => $p)
	{
		$pedidos[$k]['nombre'] = utf8_encode($p['nombre']);
		$pedidos[$k]['numero_cliente_f'] = sprintf('%04d', $pedidos[$k]['numero_cliente']);
	}

	return array('pedidos' => $pedidos, 'pager' => $pager);
}

}

require 'lib/exe.php';
