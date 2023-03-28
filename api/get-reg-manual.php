<?php require 'lib/kernel.php';

// devolvemos las registraciones manuales
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN);

protected $_input = array(
'estado' => array(
),
'zona' => array(
),
'cliente' => array(
),
'orderby' => array(
),
'pg' => array(
),
'id' => array(
),
'fltid' => array(
)
);

protected function _process($input)
{
	if (empty($input['id']))
	{
		$q = 'FROM web_registracion_manual a LEFT JOIN web_usuarios b ON respondido_por = id_web_usuarios';

		$where = array();
		$params = array();

		if (! empty($input['estado']))
			$where[] = 'leido = '.($input['estado'] == 'L' ? 1 : 0);

		if (! empty($input['zona']))
		{
			$where[] = 'zona = ?';
			$params[] = $input['zona'];
		}
		
		if (! empty($input['cliente']))
		{
			$where[] = 'clienta = ?';
			$params[] = $input['cliente'];
		}

		if (! empty($input['fltid']))
		{
			$where[] = 'id_web_registracion_manual LIKE ?';
			$params[] = '%'.$input['fltid'].'%';
		}

		if (count($where)) $q .= ' WHERE '.implode(' AND ', $where);

		$orderBy = ' ORDER BY ';
		if (empty($input['orderby']))
			$orderBy .= 'fecha DESC';
		else
		{
			switch(abs($input['orderby']))
			{
			case 1: $orderBy .= 'fecha'; break;
			}

			if ($input['orderby'] < 0) $orderBy .= ' DESC';
		}

		$pager = $this->_dbPager($q, $params, empty($input['pg']) ? 1 : $input['pg'], 20, $orderBy/*, 'a.id_web_campanias'*/);

		$q = 'SELECT fecha, zona, clienta, causa, leido, negocio, CONCAT_WS(" ", b.nombre, b.apellido) respondido_por, id_web_registracion_manual id '.$q;

		$registros = $this->_dbQuery($q, $params);

		$zonas = array();

		foreach($registros as $k => $r)
			//$registros[$k]['empresaria'] = utf8_encode($r['empresaria']);
			$registros[$k]['fecha'] = $this->_fromDate($r['fecha']);

		return array('registros' => $registros, 'pager' => $pager);
	}

	$res = $this->_dbQuery('
	SELECT
		a.*, IF (clienta = 1, "", b.mail) empresaria_correo
	FROM
		web_registracion_manual a
		LEFT JOIN web_cache_clientes b
			ON zona = id_cli_zonas AND numero_cliente = 1
	WHERE
		id_web_registracion_manual = ?
	', array($input['id']));

	return $res[0];
}

}

require 'lib/exe.php';