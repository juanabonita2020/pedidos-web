<?php require 'lib/kernel.php';

// obtenemos el listado o detalle de productos del catálogo de premios
class API extends JBSYSWEBAPI
{

protected $_input = array(
'detalle' => array(
),
'cliente' => array(
),
'pg' => array(
)
);

//~ protected $_logReq = true;

protected function _process($input)
{
	$params = array($this->_userType == JBSYSWEBAPI::USR_ADMIN ? $input['cliente'] : $this->_userClient);

	// obtenemos los puntos obtenidos del usuario
	$r = $this->_dbQuery('SELECT SUM(valor) valor FROM web_cache_pto_log WHERE id_web_cache_pto_estado = 3 AND id_cli_clientes = ?', $params);
	$res = array('pts' => $r[0]['valor']);
	$r = $this->_dbQuery('SELECT SUM(valor) valor FROM web_cache_pto_log WHERE id_web_cache_pto_estado = 1 AND id_cli_clientes = ?', $params);
	$res['ptsp'] = round($r[0]['valor']);
	//$res['pts'] = 5;

	// restamos los puntos canjeados
	$r = $this->_dbQuery('SELECT SUM(valor) valor FROM web_pto_canje WHERE id_cli_cliente = ?', $params);
	//$res['pts2'] = $res['pts'];
	$res['pts'] -= $r[0]['valor'];

	// obtener listado
	if (empty($input['detalle']))
	{
		$res['premios'] = array();

		$cod11 = array();

		//Version 1.3.14
		/*$q = '
		FROM web_cache_pto_catalogo a
		LEFT JOIN web_cache_pto_electros_bonificados b
			ON cod11 = codigo11 AND NOW() BETWEEN fecha_desde AND fecha_hasta AND id_cli_clientes = ?
		WHERE
			fecha_inicio <= NOW() + INTERVAL 30 DAY AND fecha_fin >= NOW()
		GROUP BY
			IF(eleccion_talles, cod8, cod11)
		ORDER BY cantidad_puntos, cod11
		';*/
		 $q = " 
                FROM            web_cache_pto_catalogo c 
                INNER JOIN      web_cache_pto_catalogo_stock cs ON cs.id_web_cache_pto_catalogo = c.id_web_cache_pto_catalogo
                WHERE           c.fecha_inicio <= NOW() + INTERVAL 30 DAY AND c.fecha_fin >= NOW()
                GROUP BY        IF(c.eleccion_talles, c.cod8, c.cod11)
                ORDER BY        c.cantidad_puntos, c.cod11 ";
		

		if ($input['pg'] >= 0)
			//Version 1.3.14
			//~ $res['pager'] = $this->_dbPager($q, $params, $input['pg'], 20, '', 'cod11');
			$res['pager'] = $this->_dbPager($q, array(), $input['pg'], 20, '', 'cod11');

		// obtenemos la lista de premios
		foreach($this->_dbQuery('
                    SELECT
                       c.id_web_cache_pto_catalogo,
                        c.cod11,
                        c.cod8,
                        c.descripcion,
                        c.imagen_url imagen,
                        c.cantidad_puntos pts,
                        SUM(c.stock) stock_viejo,
                        cs.cantidad_stock_real AS stock,
                        cs.fecha_sincronizacion as fecha_sincro_stock,
                        c.eleccion_talles,
                        c.ts,
                        IF(c.fecha_inicio <= NOW(), 0, 1) comingSoon,
                        IF(c.bonificado IS NULL, 0, bonificado) bonificado,
                        c.cantidad_puntos - IF(c.bonificado IS NULL, 0, c.bonificado) ptsb  
                    '.$q, $params) as $k => $p)   
		{
			if (in_array($p['cod11'], $cod11)) continue;

			// ajustamos el stock
			$r = $this->_dbQuery('SELECT COUNT(*) q FROM web_pto_canje WHERE '.($p['eleccion_talles'] ? 'SUBSTRING(cod11, 1, 8)' : 'cod11').' = ? AND fecha >= ?', array(($p['eleccion_talles'] ? $p['cod8'] : $p['cod11']), $p['ts']));
			//$p['stock2'] = $p['stock'];
			
//                        $p['stock'] -= $r[0]['q'];

			// excluimos premios sin stock
			//if (! $p['stock']) continue;

			//$p['descripcion'] = utf8_decode($p['descripcion']);
			//$res['imagen'] = utf8_decode($res['imagen']);
			$p['pts'] = round($p['pts']);
			$p['ptsb'] = round($p['ptsb']);

			// determinamos si el premio es canjeable
			if (! ($p['canjeable'] = ($p['ptsb'] <= $res['pts'] && $p['comingSoon'] == 0)))
				$p['ptsF'] = $p['ptsb'] - $res['pts'];

			unset($p['ts']);
			$cod11[] = $p['cod11'];

			$p['comingSoon'] = ($p['comingSoon'] == 1);

			$res['premios'][] = $p;
		}
	}
	// obtener detalle
	else
	{
//		$res = array_merge($res, $this->_dbGetOne('web_cache_pto_catalogo', array('cod11' => $input['detalle']), array('descripcion', 'detalle', 'imagen_url' => 'imagen', 'eleccion_talles' => 'talles', 'cantidad_puntos', 'voucher', 'stock', 'ts', 'fecha_inicio')));

                
                $r1 = $this->_dbQuery('SELECT      pc.id_web_cache_pto_catalogo,
                                          pc.descripcion,
                                          pc.detalle,
                                          pc.imagen_url AS imagen,
                                          pc.eleccion_talles AS talles,
                                          pc.cantidad_puntos,
                                          pc.voucher,
                                          pc.stock AS stock_viejo,
                                          pc.ts,
                                          pc.fecha_inicio,
                                          pcs.cantidad_stock_real AS stock
                             FROM         web_cache_pto_catalogo pc
                             INNER JOIN   web_cache_pto_catalogo_stock pcs ON pcs.id_web_cache_pto_catalogo = pc.id_web_cache_pto_catalogo      
                             WHERE        pc.cod11 = ?',
                        array( $input['detalle'] ));
                
                $res = array_merge($res, $r1[0]);
    
     
                $r = $this->_dbQuery('SELECT bonificado FROM web_cache_pto_electros_bonificados WHERE id_cli_clientes = ? AND codigo11 = ? AND ? BETWEEN fecha_desde AND fecha_hasta', array($params[0], $input['detalle'], date('Y-m-d')));
		$res['bonificado'] = floatval($r[0]['bonificado']);         
/*                $res['bonificado'] = 0;
 */               
                
                
		$res['cantidad_puntos'] = round($res['cantidad_puntos'] - $res['bonificado']);

		//$res['descripcion'] = utf8_encode($res['descripcion']);
		//$res['detalle'] = utf8_encode($res['detalle']);
		//$res['imagen'] = utf8_encode($res['imagen']);
		if (! ($res['canjeable'] = ($res['cantidad_puntos'] <= $res['pts'] && $res['fecha_inicio'] <= date('Y-m-d', time() + 86400 * 30))))
			$res['ptsF'] = $res['cantidad_puntos'] - $res['pts'];

		if ($res['talles'])
		{
			$cod8 = substr($input['detalle'], 0, 8);
			$res['talles'] = array();

			foreach($this->_dbGetAll('web_cache_pto_catalogo', array('cod8' => $cod8), array('cod11', 'stock', 'ts'), 'cod11') as $t)
			{
				$r = $this->_dbQuery('SELECT COUNT(*) q FROM web_pto_canje WHERE cod11 = ? AND fecha >= ?', array($t['cod11'], $t['ts']));
				if ($t['stock'] - $r[0]['q'])
					$res['talles'][] = array('cod11' => $t['cod11'], 'talle' => substr($t['cod11'], -3));
			}
			$res['stock_viejo'] = 1;
		}
		else
		{
			// ajustamos el stock
			$r = $this->_dbQuery('SELECT COUNT(*) q FROM web_pto_canje WHERE cod11 = ? AND fecha >= ?', array($input['detalle'], $res['ts']));
			$res['stock_viejo'] -= $r[0]['q'];
		}
	}

	return $res;
}

}

require 'lib/exe.php';
