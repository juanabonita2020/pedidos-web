<?php require 'lib/kernel.php';

// obtenemos detalle de amiga
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_EMPRE, JBSYSWEBAPI::USR_COORD, JBSYSWEBAPI::USR_REGIO, JBSYSWEBAPI::USR_REVEN, JBSYSWEBAPI::USR_ADMIN, JBSYSWEBAPI::USR_DIVIS);

protected $_input = array(
'id' => array(
)
);

//~ protected $_logReq = true;

protected function _process($input)
{
	$clientes = array();
	$r = $this->_dbGetOne('web_cache_pto_log', array('id_web_cache_pto_log' => $input['id']), array('observacion'));
	$res = array('det' => array(), 'det2' => array(), 'obs' => $r['observacion']);

	foreach($this->_dbQuery('
	SELECT
		descripcion, a.valor, unidad_medida, a.id_web_cache_pto_concepto, id_cli_clientes, IF(descripcion = "id_cli_clientes", 0, id_web_cache_pto_detalle) `order`, b.observacion obs, id_web_campanias
	FROM
		web_cache_pto_detalle a, web_cache_pto_log b
	WHERE
		a.id_web_cache_pto_log = b.id_web_cache_pto_log
		AND a.id_web_cache_pto_log = ?
	ORDER BY
		`order`
	', array($input['id'])) as $k => $d)
	{
		// control de acceso
		if (! $k && $this->_userType != JBSYSWEBAPI::USR_ADMIN && $d['id_cli_clientes'] != $this->_userClient)
		{
			$r = $this->_dbGetOne('web_cache_clientes', array('id_cli_clientes' => $d['id_cli_clientes']), array('id_cli_zonas'));
			if (! $this->_isMyZone($r['id_cli_zonas']))
				return $this->_forbidden();
		}

		switch($d['id_web_cache_pto_concepto'])
		{
		case 1:
		{
			$value = $d['valor'];

			if ($d['unidad_medida'] == 'unidades')
			{
				list($pre, $ord, $foo, $dev) = explode('_', $d['descripcion']);
				$label = ucfirst($ord).' Cp';
				if (! isset($res['det2'][$label]))
					$res['det2'][$label] = array($label, 0, 0, 0, 0, $d['id_web_campanias']);
				if ($dev)
				{
					if ($pre == 'presentadora')
						$res['det2'][$label][2] = $value;
					else
						$res['det2'][$label][4] = $value;
				}
				if ($pre == 'presentadora')
					$res['det2'][$label][1] += $value;
				else
					$res['det2'][$label][3] += $value;
			}
			else
			{
				if ($d['unidad_medida'] == 'descripcion')
					switch($d['descripcion'])
					{
					case 'id_cli_clientes':
					{
						if (! isset($clientes[$d['valor']]))
						{
							$r = $this->_dbGetOne('web_cache_clientes', array('id_cli_clientes' => $d['valor']), array('id_cli_zonas', 'numero_cliente', 'nombre'));
							if (isset($r['numero_cliente']))
								$clientes[$d['valor']] = $r;
						}

						$label = 'Vendedora Nueva';
						if (isset($clientes[$d['valor']]))
							$value = $clientes[$d['valor']]['id_cli_zonas'].' - '.$clientes[$d['valor']]['numero_cliente'].' '.utf8_encode($clientes[$d['valor']]['nombre']);

						break;
					}
					case 'estado_presentada':
					{
						if (! isset($estados))
						{
							$estados = array();
							foreach($this->_dbGetAll('web_cache_pto_estado') as $e)
								$estados[$e['id_web_cache_pto_estado']] = $e['descripcion'];
						}

						$label = 'Estado Vendedora Nueva';
						if (isset($estados[$d['valor']])) $value = $estados[$d['valor']];

						break;
					}
					default:
						$label = $d['descripcion'];
					}
				else
					$label = $d['unidad_medida'];

				$res['det'][] = array($label, $value);
			}

			break;
		}
		}
	}

	$res['det2'] = array_values($res['det2']);

	return $res;
}

}

require 'lib/exe.php';
