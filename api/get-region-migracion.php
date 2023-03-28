<?php require 'lib/kernel.php';

// devolvemos el estado de la migración de la región
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_REGIO, JBSYSWEBAPI::USR_DIVIS);

protected $_input = array(
'orderby' => array(
),
'pg' => array(
),
'estado' => array(
),
'region' => array(
)
);

protected function _process($input)
{
	$q = '
	FROM
		web_cache_clientes c
		LEFT JOIN (
				SELECT
					zona,
					count(distinct case when numero_cliente_usuario_carga = 1 then id_web_pedidos else null end ) cant_cargados_por_cabeza,
					count(distinct case when numero_cliente_usuario_carga <> 1 then id_web_pedidos else null end ) cant_cargados_vendedora  ,
					max(id_web_campanias) maxcp_cargada
				FROM (
					SELECT
						e.id_cli_zonas zona,
						e.id_web_campanias ,
						p.id_web_pedidos,
						c1.numero_cliente numero_cliente_usuario_carga
				   --     c1.region
					FROM web_envios e
					INNER JOIN web_pedidos p ON p.id_web_envios = e.id_web_envios
					INNER JOIN web_usuarios u ON u.id_web_usuarios = p.id_web_usuarios
					INNER JOIN web_cache_clientes c1 ON c1.id_cli_clientes =  u.id_cli_clientes
				   ) x
				GROUP BY zona
			) pn ON pn.zona = c.id_cli_zonas
		LEFT JOIN web_cache_zonas_pedidos_web_viejo pv ON pv.id_cli_zonas = c.id_cli_zonas
	WHERE
		numero_cliente = 1 and '.($this->_userType == JBSYSWEBAPI::USR_REGIO ? 'region' : 'division').' = ? ';

	//if (! empty($input['faltaMigrar'])) $q .= ' AND pn.zona IS NULL AND pv.id_cli_zonas IS NOT NULL';
		
	if (! empty($input['estado']))
		$q .= 'AND pn.zona IS '.(($input['estado'] == 'M' || $input['estado'] == 'I') ? 'NOT ' : '').'NULL AND pv.id_cli_zonas IS '.(($input['estado'] == 'F' || $input['estado'] == 'M') ? 'NOT ' : '').'NULL';
		
	/*switch($input['estado'])
	{
	case 'F': $q .= 'AND pn.zona IS NULL AND pv.id_cli_zonas IS NOT NULL'; break;
	case 'N': $q .= 'AND pn.zona IS NULL AND pv.id_cli_zonas IS NULL'; break;
	case 'M': $q .= 'AND pn.zona IS NOT NULL AND pv.id_cli_zonas IS NOT NULL'; break;
	case 'I': $q .= 'AND pn.zona IS NOT NULL AND pv.id_cli_zonas IS NULL'; break;
	case 'S': $q .= ''; break;
	}*/
	
	$params = array($this->_userType == JBSYSWEBAPI::USR_REGIO ? $this->_userRegion : $this->_userDivision);

	$orderBy = ' ORDER BY ';
	if (empty($input['orderby']))
		$orderBy .= ' zona';
	else
	{
		/*switch(abs($input['orderby']))
		{
		case 1: $orderBy .= 'e.fecha_envio'; break;
		case 2: $orderBy .= 'a.id_web_campanias'; break;
		case 3: $orderBy .= 'c.id_cli_zonas'; break;
		case 4: $orderBy .= 'c.nombre'; break;
		case 5: $orderBy .= 's.accion'; break;
		default:
		}

		if ($input['orderby'] < 0) $orderBy .= ' DESC';*/
	}

	$pager = $this->_dbPager($q, $params, empty($input['pg']) ? 1 : $input['pg'], 20, $orderBy, 'c.region');

	$q = 'SELECT
	c.region,
	c.id_cli_zonas zona,
	region,
	c.nombre,
	c.mail,
	c.dni,
	case
		when pn.zona is not null and pv.id_cli_zonas is not null then "MIGRADA"
		when pn.zona is null and pv.id_cli_zonas is not null then "FALTA MIGRAR"
		when pn.zona is null and pv.id_cli_zonas is null then "NO USABA PEDIDOS WEB"
		when pn.zona is NOT null and pv.id_cli_zonas is null then "INICIO CON SISTEMA NUEVO"
	ELSE "SIN DEFINIR" END estado_migracion ,
	maxcp_cargada as ultima_campania_cargada,
	cant_cargados_por_cabeza as cantidad_pedidos_cargado_por_lider,
	cant_cargados_vendedora  as cantidad_pedidos_cargado_por_vendedoras  '.$q;

	$estados = $this->_dbQuery($q, $params);

	foreach($estados as $k => $r)
	{
		$estados[$k]['nombre'] = utf8_encode($r['nombre']);
		$estados[$k]['ultima_campania_cargada'] = intval($r['ultima_campania_cargada']);
		$estados[$k]['cantidad_pedidos_cargado_por_lider'] = intval($r['cantidad_pedidos_cargado_por_lider']);
		$estados[$k]['cantidad_pedidos_cargado_por_vendedoras'] = intval($r['cantidad_pedidos_cargado_por_vendedoras']);
	}

	return array('estados' => $estados, 'pager' => $pager);
}

}

require 'lib/exe.php';