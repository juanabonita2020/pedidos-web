<?php require 'lib/kernel.php';

// devuelve la lista de usuarios
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_EMPRE);

protected $_input = array(
'cliente' => array(
),
'numeroClienta' => array(
),
'term' => array(
),
'pg' => array(
),
'orderBy' => array(
)
);

protected function _process($input)
{
	if (! empty($input['cliente']))
	{
		$cliente = $input['cliente'];
		$clienteField = 'a.id_cli_clientes';
	}
	else if (! empty($input['numeroClienta']))
	{
		$cliente = $input['numeroClienta'];
		$clienteField = 'numero_cliente';
	}
	else
		$cliente = '';

	$searchTerm = (empty($input['term']) ? '' : ' AND (numero_cliente = ? OR b.nombre LIKE ?)');

	$q = 'FROM (
	SELECT
		a.mail, b.nombre, habilitada habilitado, a.id_cli_clientes idCliente, numero_cliente numeroClienta, 1 tipo
	FROM
		web_usuarios a, web_cache_clientes b
	WHERE
		a.id_cli_clientes = b.id_cli_clientes
		AND id_cli_zonas = ? AND a.id_cli_clientes <> 1 AND a.id_cli_clientes <> ?'.(empty($cliente) ? '' : ' AND '.$clienteField.' = ?').$searchTerm.'

	UNION

	SELECT
		"" mail, IF(b.nombre IS NULL, "(nombre no encontrado)", b.nombre) nombre, 0 habilitado, a.id_cli_clientes idCliente, numero_cliente numeroClienta, 2 tipo
	FROM
		web_pedidos a
		LEFT JOIN web_cache_clientes b
			ON a.id_cli_clientes = b.id_cli_clientes
		, web_envios c
	WHERE
		a.id_web_envios = c.id_web_envios
		AND c.id_cli_zonas = ? AND a.id_cli_clientes <> 1 AND a.id_cli_clientes <> ?'.(empty($cliente) ? '' : ' AND '.$clienteField.' = ?').$searchTerm.'
	) a';

	$params = array($this->_userZone, $this->_userClient);
	if (! empty($cliente)) $params[] = $cliente;
	if (! empty($input['term'])) $params = array_merge($params, array($input['term'], '%'.$input['term'].'%'));
	$params[] = $this->_userZone;
	$params[] = $this->_userClient;
	if (! empty($cliente)) $params[] = $cliente;
	if (! empty($input['term'])) $params = array_merge($params, array($input['term'], '%'.$input['term'].'%'));

	$pager = $this->_dbPager($q, $params, $input['pg'], 20, '', 'mail');

	$q = 'SELECT a.*, MAX(fecha_carga) fechaCarga FROM
	(SELECT * '.$q.') a
	LEFT JOIN web_pedidos ON idCliente = id_cli_clientes AND estado NOT IN ('.self::OS_REM_EMPRE.', '.self::OS_NSN_EMPRE.')
	GROUP BY idCliente ORDER BY ';

	$orderBy = (empty($input['orderBy']) ? -1 : $input['orderBy']);
	switch(abs($orderBy))
	{
	case 1: $q .= 'fechaCarga'; break;
	case 2: $q .= 'nombre'; break;
	case 3: $q .= 'habilitado'; break;
	}
	if ($orderBy < 0) $q .= ' DESC';

	$res = array('usuarios' => $this->_dbQuery($q, $params), 'pager' => $pager);

	foreach($res['usuarios'] as $k => $v)
	{
		$res['usuarios'][$k]['nombre'] = utf8_encode($v['nombre']);
		$res['usuarios'][$k]['habilitado'] = ($v['habilitado'] == 1);
		$res['usuarios'][$k]['idCliente'] = sprintf('%04d', $v['idCliente']);
		$res['usuarios'][$k]['numeroClienta'] = sprintf('%04d', $v['numeroClienta']);
		$res['usuarios'][$k]['fechaCarga'] = (empty($res['usuarios'][$k]['fechaCarga']) ? '' : $this->_fromDate($res['usuarios'][$k]['fechaCarga']));
	}

	return $res;
}

}

require 'lib/exe.php';