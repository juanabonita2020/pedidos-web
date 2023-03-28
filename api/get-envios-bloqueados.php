<?php require 'lib/kernel.php';

// obtener el listado de envíos bloqueados
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN);

protected $_input = array(
'zona' => array(
),
'region' => array(
)
);

protected function _process($input)
{
	/*return array('envios' => array(
	array('campania' => '4848', 'cliente' => '48', 'zona' => 'ZONA1', 'intento' => '11/04/1948', 'baja' => 1, 'aprobados' => 10, 'en_proceso' => 15),
	array('campania' => '4849', 'cliente' => '50', 'zona' => 'ZONA2', 'intento' => '11/04/1950', 'baja' => 0, 'aprobados' => 1, 'en_proceso' => 0)
	));*/

	$res = array('envios' => array());
	$row = null;
	$where = $webEnvio = '';
	$params = array();

	if (! empty($input['zona']))
	{
		$where .= ' AND a.id_cli_zonas = ?';
		$params[] = $input['zona'];
	}

	if (! empty($input['region']))
	{
		$where .= ' AND b.region = ?';
		$params[] = $input['region'];
	}

	foreach($this->_dbQuery('
	SELECT
		a.id_web_envios, campania, zona, intento, baja, IF(estado = 60, 1, 2) estado, cliente, region
	FROM
	(
		SELECT
			id_web_envios, id_web_campanias campania, b.region, a.id_cli_zonas zona, fecha_intento_envio intento, baja, id_web_cache_clientes cliente
		FROM
			web_envios a,
			web_cache_clientes b
		WHERE
			a.id_cli_zonas = b.id_cli_zonas
			AND numero_cliente = 1
			AND fecha_envio IS NULL
			AND fecha_intento_envio IS NOT NULL
		'.$where.'
	) a
	LEFT JOIN web_pedidos b
		ON a.id_web_envios = b.id_web_envios
			AND estado NOT IN (80, 160)
			AND a.intento >= NOW() - 60
	ORDER BY
		intento DESC
	', $params) as $r)
	{
		if ($webEnvio != $r['id_web_envios'])
		{
			if ($row != null) $res['envios'][] = $row;
			$row = array('campania' => $r['campania'], 'cliente' => $r['cliente'], 'zona' => $r['zona'], 'intento' => $this->_fromDate($r['intento']), 'baja' => $r['baja'], 'region' => $r['region'], 'aprobados' => 0, 'en_proceso' => 0);
		}

		if ($r['estado'] == 1)
			$row['aprobados']++;
		else
			$row['en_proceso']++;

		$webEnvio = $r['id_web_envios'];
	}

	if ($row != null) $res['envios'][] = $row;

	return $res;
}

}

require 'lib/exe.php';
