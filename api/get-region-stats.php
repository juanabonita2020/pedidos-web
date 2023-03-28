<?php require 'lib/kernel.php';

// devolvemos las estadísticas de la región
class API extends JBSYSWEBAPI
{

//protected $_disabled = true;

protected $_validUserTypes = array(JBSYSWEBAPI::USR_REGIO, JBSYSWEBAPI::USR_DIVIS);

protected $_input = array(
'estado' => array(
),
'accion' => array(
),
'campania' => array(
),
'zona' => array(
),
'region' => array(
),
'empresaria' => array(
),
'orderby' => array(
),
'pg' => array(
)
);

//protected $_logReq = true;

protected function _process($input)
{
	$fk = $this->_userType == JBSYSWEBAPI::USR_REGIO ? 'region' : 'division';
	$fv = $this->_userType == JBSYSWEBAPI::USR_REGIO ? $this->_userRegion : $this->_userDivision;

	$q = '
	FROM
		web_pedidos a
		INNER JOIN web_envios e on e.id_web_envios = a.id_web_envios
		INNER JOIN web_estados s on s.estado = a.estado
		INNER JOIN web_cache_clientes b on a.id_cli_clientes = b.id_cli_clientes
		INNER JOIN web_cache_campanias d on a.id_web_campanias = d.id_web_cache_campanias
	WHERE
		b.'.$fk.' = ? AND a.estado NOT IN ('.self::OS_REM_EMPRE.', '.self::OS_NSN_EMPRE.')
	';

	$params = array($fv);

	if (! empty($input['estado']))
		$q .= ' AND e.fecha_envio IS'.($input['estado'] == 'e' ? ' NOT' : '').' NULL';

	if (! empty($input['empresaria']))
	{
		$q .= ' AND b.nombre LIKE ?';
		$params[] = '%'.$input['empresaria'].'%';
	}

	foreach(array(
	'accion' => 's.accion',
	'campania' => 'a.id_web_campanias',
	'zona' => 'b.id_cli_zonas',
	'region' => 'b.region',
	) as $f1 => $f2) if (! empty($input[$f1]))
	{
		$q .= ' AND '.$f2.' = ?';
		$params[] = $input[$f1];
	}

	$q .= ' GROUP BY a.id_web_campanias, b.id_cli_zonas, e.fecha_envio, s.accion, b.region, case when e.fecha_envio IS NULL then "Pedidos En Carga" else "Pedidos Enviados" end';

	$orderBy = ' ORDER BY ';
	if (empty($input['orderby']))
		//~$orderBy .= 'campania desc, zona asc, estado, ordenamiento asc ';
		$orderBy .= 'e.fecha_envio DESC';
	else
	{
		switch(abs($input['orderby']))
		{
		case 1: $orderBy .= 'e.fecha_envio'; break;
		case 2: $orderBy .= 'orden_absoluto'; break;
		case 3: $orderBy .= 'b.id_cli_zonas'; break;
		case 4: $orderBy .= 'b.nombre'; break;
		case 5: $orderBy .= 's.accion'; break;
		case 6: $orderBy .= 'b.region'; break;
		default:
		}

		if ($input['orderby'] < 0) $orderBy .= ' DESC';
	}

	$pager = $this->_dbPager($q, $params, empty($input['pg']) ? 1 : $input['pg'], 20, $orderBy, 'a.id_web_campanias');

	$q = 'SELECT
	a.id_web_campanias campania,
	"---" empresaria,
	case when e.fecha_envio IS NULL then "Pedidos En Carga" else "Pedidos Enviados" end estado,
	sum(unidades) unidades,
	sum(monto) monto,
	b.id_cli_zonas zona,
	e.fecha_envio fecha,
	s.accion,
	count(distinct(a.id_cli_clientes)) pedidos,
	b.region,
	case when e.fecha_envio IS NULL then "Pedidos En Carga" else "Pedidos Enviados" end estado'.$q;

	$pedidos = $this->_dbQuery($q, $params);

	$nombres = array();
	foreach($this->_dbQuery('SELECT id_cli_zonas, nombre FROM web_cache_clientes WHERE numero_cliente = 1 AND '.$fk.' = ?', array($fv)) as $r)
		$nombres[$r['id_cli_zonas']] = $r['nombre'];

	foreach($pedidos as $k => $r)
	{
		$pedidos[$k]['empresaria'] = utf8_encode($nombres[$r['zona']]);
		$pedidos[$k]['fecha'] = $this->_fromDate($r['fecha']);
		$pedidos[$k]['monto'] = number_format($pedidos[$k]['monto'], 2, ',', '.');
	}

	return array('pedidos' => $pedidos, 'pager' => $pager);
}

}

require 'lib/exe.php';
