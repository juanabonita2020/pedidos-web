<?php require 'lib/kernel.php';

// obtenemos los detalles de un pedido
class API extends JBSYSWEBAPI
{

protected $_input = array(
'numeroPedido' => array(
),
'cliente' => array(
),
'nuevaCampania' => array(
),
'warning' => array(
),
'campania' => array(
),
'search' => array(
)
);

//~ protected $_logReq = true;

protected function _process($input)
{
	// comprobar la campaña
	if (! empty($input['nuevaCampania']) && ! $this->_validCamp($input['nuevaCampania'])) return false;

	// si se especifica se devuelve el pedido
	if (isset($input['numeroPedido']))
		$where = array('id_web_pedidos' => $input['numeroPedido']);
	// sino, el que esté abierto
	else
	{
		$where = array('id_cli_clientes' => empty($input['cliente']) ? $this->_userClient : $input['cliente']);
		// buscar en una campaña específica
		if (! empty($input['campania'])) $where['id_web_campanias'] = $input['campania'];
	}

	//~ if ((empty($input['cliente']) || $input['cliente'] == $this->_userClient) && ! isset($input['numeroPedido']))
		//~ $where['estado'] = $this->_openedStatus[$this->_userType];

	if (! empty($input['search'])) $res = array();

	if (($orders = $this->_dbGetAll('web_pedidos', $where)) !== false)
		foreach($orders as $r)
		{
			//~ print_r($r);die;
			// verificamos que el usuario tenga acceso al pedido
			if (! $this->_isMyClient($r['id_cli_clientes'])) $this->_forbidden();

			//if ((empty($input['cliente']) || $input['cliente'] == $this->_userClient) && ! isset($input['numeroPedido']) && ! in_array($r['estado'], $this->_validOpenedStatus) && ! in_array($r['estado'], $this->_validRejectedStatus))
			//	continue;

			// excluimos ciertos pedidos
			if (! empty($input['cliente']) && empty($input['numeroPedido']) && ! in_array($r['estado'], $this->_validOpenedStatus) && ! in_array($r['estado'], $this->_validRejectedStatus))
				continue;

			// buscamos un sólo pedido
			if (empty($input['search']))
			{
				// calculamos los parámetros de muestrario
				$maxMuestrario = $this->_getMaxMuestrario($input['cliente']);
				$totMuestrario = $this->_getTotMuestrario($input['cliente'], $r['id_web_campanias']);

				$this->_logReqData[] = 'maxMuestrario: '.$maxMuestrario.', totMuestrario: '.$totMuestrario;

				$res = array(
				'id' => $r['id_web_pedidos'],
				'campania' => $r['id_web_campanias'],
				'cliente' => sprintf('%04s', $r['id_cli_clientes']),
				'soutien' => $r['soutien_talle'],
				'bombacha' => $r['bombacha_talle'],
				'inferior' => $r['inferior_talle'],
				'superior' => $r['superior_talle'],
				'soutien_f' => (intval($r['soutien_talle']) ? $r['soutien_talle'] : '--'),
				'bombacha_f' => (intval($r['bombacha_talle']) ? $r['bombacha_talle'] : '--'),
				'inferior_f' => (intval($r['inferior_talle']) ? $r['inferior_talle'] : '--'),
				'superior_f' => (intval($r['superior_talle']) ? $r['superior_talle'] : '--'),
				'jean_f' => (intval($r['jean_talle']) ? $r['jean_talle'] : '--'),
				'jean' => $r['jean_talle'],
				'estado' => $r['estado'],
				'warning' => false,
				'muestrarioPend' => $maxMuestrario - $totMuestrario
				);

				$r2 = $this->_dbGetOne('web_envios', array('id_web_envios' => $r['id_web_envios']));
				$res['zona_a'] = ($r2['id_cli_zonas'] != $this->_userZone);

				$r2 = $this->_dbGetOne('web_cache_clientes', array('id_cli_clientes' => $r['id_cli_clientes']));
				$res['nombreCliente'] = utf8_encode($r2['nombre']);
				$res['numeroCliente'] = sprintf('%04s', $r2['numero_cliente']);

				$r2 = $this->_dbGetOne('web_usuarios', array('id_web_usuarios' => $r['id_web_usuarios']));

				$res = array_merge($res, array(
				'apellidoUsuarioAlta' => utf8_encode($r2['apellido']),
				'nombreUsuarioAlta' => utf8_encode($r2['nombre']),
				'items' => $this->_getOrderItems($r['id_web_pedidos'], $res['campania'], array(JBSYSWEBAPI::OS_REM_EMPRE), null, empty($input['nuevaCampania']) ? null : $input['nuevaCampania'])
				));

				// determinamos si la campaña acepta cuotas
				if (! ($res['cuotas'] = $this->_cuotasEnabled($r['id_web_campanias'])))
					foreach($res['items'] as $k => $v)
						$res['items'][$k]['cuotas'] = 0;

				break;
			}
			// buscamos todos los pedidos
			else
				$res[] = $r['id_web_campanias'];
		}

	if (! empty($input['warning']) && empty($input['search']))
	{
		if (! isset($res)) $res = array();
		//$params = array_merge($this->_validOpenedStatus, array(self::OS_REM_EMPRE, self::OS_NSN_EMPRE), array($input['cliente']));
		$params = array_merge($this->_closedStatus, $this->_approvedStatus, $this->_sendedStatus, array($input['cliente'], $input['campania']));
		$r2 = $this->_dbQuery('SELECT estado FROM web_pedidos WHERE estado IN (?, ?, ?, ?, ?, ?) AND id_cli_clientes = ? AND id_web_campanias = ? LIMIT 1', array(JBSYSWEBAPI::OS_CLO_REVEN, JBSYSWEBAPI::OS_APP_EMPRE, JBSYSWEBAPI::OS_APP_COORD, JBSYSWEBAPI::OS_CLO_COORD, JBSYSWEBAPI::OS_CLO_EMPRE, JBSYSWEBAPI::OS_SEN_EMPRE, $input['cliente'], $input['campania']));
		if (isset($r2[0]['estado'])) $res['warning'] = 1;
	}

	return $res;
}

}

require 'lib/exe.php';
