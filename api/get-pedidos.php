<?php require 'lib/kernel.php';

// devolver la cantidad de pedidos
class API extends JBSYSWEBAPI
{

protected $_input = array(
'cliente' => array(
)
);

//~ protected $_logReq = true;

protected function _process($input)
{
	$query = 'SELECT id_web_pedidos numeroPedido, estado, p.id_cli_clientes client, IF(u.nombre IS NULL, "(nombre no encontrado)", u.nombre) nombre, p.id_web_usuarios userId, p.id_web_campanias campania, UNIX_TIMESTAMP(fecha_carga) fecha_carga, es_feria isFeria, monto, p.tipo_pedido tipoPedido, unidades, numero_cliente nroCliente, negocio, baja
	FROM
		web_pedidos p
		LEFT JOIN web_cache_clientes u
			ON p.id_cli_clientes = u.id_cli_clientes
		, web_cache_campanias c, web_envios d';

	// en cualquier caso se excluyen los pedidos enviados, no enviados o removidos
	$where = 'p.id_web_envios = d.id_web_envios AND estado NOT IN (?, ?, ?) AND p.id_web_campanias = id_web_cache_campanias';
	$params = array(JBSYSWEBAPI::OS_SEN_EMPRE, JBSYSWEBAPI::OS_REM_EMPRE, JBSYSWEBAPI::OS_NSN_EMPRE);

	// si el usuario es una empresaria o coordinadora devolver los pedidos de su zona
	if ($this->_userType == JBSYSWEBAPI::USR_EMPRE || $this->_userType == JBSYSWEBAPI::USR_COORD)
	{
		$where .= ' AND d.id_cli_zonas = ?';
		$params[] = $this->_userZone;

		if (! empty($input['cliente']))
		{
			$where .= ' AND p.id_cli_clientes = ?';
			$params[] = $input['cliente'];
		}

		if ($this->_userType == JBSYSWEBAPI::USR_COORD)
		{
			$where .= ' AND (p.id_cli_clientes = ? OR coordinador = ?)';
			$params[] = $this->_userClient;
			$params[] = $this->_userCoordinadorNro;
		}
	}
	// sino, enviar sus propios pedidos que estén abiertos o rechazados
	else
	{
		$where .= ' AND p.id_cli_clientes = ? AND estado IN (?, ?, ?, ?, ?, ?)';
		$params[] = $this->_userClient;
		$params[] = JBSYSWEBAPI::OS_OPE_REVEN;
		$params[] = JBSYSWEBAPI::OS_OPE_EMPRE;
		$params[] = JBSYSWEBAPI::OS_OPE_COORD;
		$params[] = JBSYSWEBAPI::OS_REJ_REVEN;
		$params[] = JBSYSWEBAPI::OS_REJ_EMPRE;
		$params[] = JBSYSWEBAPI::OS_REJ_COORD;
	}

	$res = array('pedidos' => $this->_dbQuery($query.' WHERE '.$where.' ORDER BY orden_absoluto, numero_cliente, fecha_carga', $params));

	$userTypes = array();

	foreach($res['pedidos'] as $k => $p)
	{
		$p['nombre'] = utf8_encode($p['nombre']);
		$res['pedidos'][$k]['nombre'] = $p['nombre'];

		$res['pedidos'][$k]['client'] = sprintf('%04s', $p['client']);
		$res['pedidos'][$k]['nroCliente'] = sprintf('%04s', $p['nroCliente']);
		$res['pedidos'][$k]['nombreCliente'] = $p['nombre'];
		$res['pedidos'][$k]['usuarioAlta'] = $p['userId'];
		$res['pedidos'][$k]['fechaCarga'] = date('d/m/Y', $p['fecha_carga']);

		if (! isset($userTypes[$p['userId']])) $userTypes[$p['userId']] = $this->_getUserType(null, $p['userId']);
		$res['pedidos'][$k]['usuarioAltaTipo'] = $userTypes[$p['userId']];

		// buscamos sub-pedidos de feria
		$res['pedidos'][$k]['sub'] = $this->_dbQuery('
		SELECT
			nombre, compradora, es_feria esFeria, SUM(cantidad) unidades, estado
		FROM
			web_pedidos_detalle
			LEFT JOIN web_contactos
				ON compradora = mail
		WHERE
			id_web_pedidos = ?
			AND estado NOT IN (?, ?, ?)
		GROUP BY compradora, es_feria
		', array($p['numeroPedido'], JBSYSWEBAPI::OS_REM_EMPRE, JBSYSWEBAPI::OS_SEN_EMPRE, JBSYSWEBAPI::OS_NSN_EMPRE)); //IF(compradora IS NULL OR mail IS NULL, null, nombre)
	}

	return $res;
}

}

require 'lib/exe.php';
