<?php require 'lib/kernel.php';

// devolvemos el listado de revendedoras sin pedido
class API extends JBSYSWEBAPI
{

protected function _process($input)
{
	/*$r1 = $this->_dbGetOne('web_campanias', array('id_web_campanias' => $this->_userCampaign), array('orden_absoluto'));
	$r2 = $this->_dbQuery('SELECT orden_absoluto FROM web_campanias WHERE habilitado AND orden_absoluto < ? ORDER BY orden_absoluto DESC LIMIT 2', array($r1['orden_absoluto']));
	$campania = empty($r2[1]['orden_absoluto']) ? $r2[0]['orden_absoluto'] : $r2[1]['orden_absoluto'];
	if (empty($campania)) return array();*/

	$res = array('revendedoras' => $this->_dbQuery('

	select idCliente, numero_cliente, nombre, mail, id_web_campanias campania, tipo
	from (
		select a.* , c.id_web_campanias, c.orden
		from
		(
		SELECT
			bb.id_web_cache_clientes_all idCliente,
			bb.clienta numero_cliente,
			bb.nombre , ifnull(c.mail,"") mail,
			MAX(cp.orden_absoluto) orden_absoluto,
			case when  c.id_web_usuarios IS NULL then 2  else 1 end tipo
		FROM web_pedidos a
		LEFT JOIN web_cache_clientes_all bb ON bb.id_web_cache_clientes_all = a.id_cli_clientes
		LEFT JOIN web_usuarios c ON bb.id_web_cache_clientes_all = c.id_cli_clientes
		INNER JOIN web_campanias cp ON cp.id_web_campanias = a.id_web_campanias
		INNER JOIN web_envios d ON a.id_web_envios = d.id_web_envios
		WHERE bb.clienta <> 1
		AND ifnull(c.habilitada,1)  = 1
		AND IFNULL(c.cantidad_abiertas,0) = 0
		AND d.id_cli_zonas = ?
		GROUP BY   bb.clienta, bb.nombre
		) a
		inner join web_campanias  c on c.orden_absoluto = a.orden_absoluto
	) b
	cross join (
			select c1.id_web_campanias cp1, c2.id_web_campanias cp2, c1.orden orden_max, c2.orden orden_min
			from web_campanias c1
			inner join web_campanias c2 on c1.orden -2  = c2.orden and c1.sistema = c2.sistema
			where c1.id_web_campanias = ?
			) cps
	where b.orden >= orden_min and b.orden < orden_max
	ORDER BY b.orden DESC
	', array($this->_userZone, $this->_userCampaign)));

	foreach($res['revendedoras'] as $k => $r)
	{
		$res['revendedoras'][$k]['numeroClienta'] = sprintf('%04s', $r['numero_cliente']);
		$res['revendedoras'][$k]['nombre'] = utf8_encode($r['nombre']);
	}

	//$res['debug'] = $this->_userCampaign.' - '.$campania;

	return $res;
}

}

require 'lib/exe.php';
