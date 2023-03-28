<?php

chdir('..');
require 'lib/kernel.php';

class CRON extends JBSYSWEBCRON
{

protected $_cronId = 'changecamp';
protected $_logReq = true;

protected function _process($input)
{
	$q = '
	SELECT *
	FROM
	(
		SELECT *
		FROM
		(
			SELECT a.id_cli_zonas, a.id_web_campanias, fecha_envio, valor
			FROM web_envios a, web_campanias_zonas b, web_cache_campanias c, web_variables_globales d
			WHERE
			a.id_cli_zonas = b.id_cli_zonas
			AND b.id_web_campanias IS NOT NULL
			AND a.id_web_campanias = b.id_web_campanias
			AND a.id_cli_zonas <> 0
			AND fecha_envio IS NOT NULL
			AND a.id_web_campanias = id_web_cache_campanias
			AND parametro = CONCAT("dias_nuevo", sistema)
			ORDER BY a.id_cli_zonas, fecha_envio DESC
		) a
		GROUP BY id_cli_zonas
	) a
	WHERE
	fecha_envio < NOW() - INTERVAL valor DAY
	';

	foreach($this->_dbQuery($q) as $z)
	{
		//print_r($z);

		$r = $this->_dbQuery('SELECT id_web_usuarios FROM web_usuarios a, web_cache_clientes b WHERE a.id_cli_clientes = b.id_cli_clientes AND id_cli_zonas = ?', array($z['id_cli_zonas']));
		//print_r($r);

		$camps = array();
		$res = $this->_getCampanias($z['id_cli_zonas'], false, $r[0]['id_web_usuarios'], JBSYSWEBAPI::USR_EMPRE);
		foreach($res['campanias'] as $c)
			$camps[$c['campania']] = $c['cierresOk'];
		ksort($camps);
		//print_r($camps);
		$f = false;
		$newCamp = '';
		foreach($camps as $c => $cierresOk)
			if ($c == $z['id_web_campanias'])
				$f = true;
			else if ($f && $cierresOk)
			{
				$newCamp = $c;
				break;
			}

		$log = 'Zona: '.$z['id_cli_zonas'].', Campaña actual: '.$z['id_web_campanias'].', Usuario empresaria de la zona: '.$r[0]['id_web_usuarios'];

		if ($newCamp)
		{
			$log .= ' => CAMBIO A NUEVA CAMPAÑA: '.$newCamp;
			$this->_changeCampaign($newCamp, $z['id_cli_zonas'], $r[0]['id_web_usuarios']);
		}

		$this->_logReqData[] = $log;
	}
}

}

require 'lib/cron.php';