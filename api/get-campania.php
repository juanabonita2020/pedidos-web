<?php require 'lib/kernel.php';

// obtener detalles de una campania
class API extends JBSYSWEBAPI
{

protected $_input = array(
'campania' => array(
),
'cliente' => array(
)
);

//~ protected $_logReq = true;

protected function _process($input)
{
	$res = array('premios' => array(), 'premios_m' => array(), 'incentivo_premio' => 0, 'incentivo_premios' => array());

	//$input['campania'] = '1802';
	
	if (! empty($input['campania']))
	{
		// obtener los limites de incentivos
		$res['popup_unidades_premio_c'] = $res['popup_unidades_premio'] = array();
		foreach($this->_dbQuery('SELECT limite, cantidad_premios FROM web_cache_prem_limites WHERE prem_campania = ? ORDER BY limite', array($input['campania'])) as $r)
		{
			$res['popup_unidades_premio'][] = $r['limite'];
			$res['popup_unidades_premio_c'][] = $r['cantidad_premios'];
		}
		$res['popup_unidades_premio'] = implode(',', $res['popup_unidades_premio']);
		$res['popup_unidades_premio_c'] = implode(',', $res['popup_unidades_premio_c']);
		
		// obtener los premios de la campaña
		if (! empty($input['cliente']) && $this->_isMyClient($input['cliente']))
		{
			// obtener los premios (incentivo)
			if (($r = $this->_checkIncPrem($input['campania'], $input['cliente'])) !== false)
			{
				$res['incentivo_premios'] = array();
				
				foreach($r as $k => $r2)
				{
					$r3 = $this->_dbGetOne('web_cache_prem_incentivo_titulo', array('id_web_cache_prem_incentivo' => $r2['id_web_cache_prem_incentivo']), array('titulo'));
					
					$arts = $this->_dbQuery('
					SELECT
						cod11,
						descripcion `desc`,
						IF(id_web_prem_solicitados IS NULL, 0, 1) sel,
						IF(fecha_recepcion IS NULL OR fecha_recepcion = "", 1, 0) `mod`
					FROM
						web_cache_prem_incentivo_articulo a
						LEFT JOIN web_prem_solicitados b
							ON id_cli_cliente = ?
							AND a.prem_campania = b.prem_campania
							AND id_web_cache_prem_incentivo = prem_codigo
							AND cod11 = prem_articulo_codigo11
					WHERE
						a.id_web_cache_prem_incentivo = ?
						AND a.prem_campania = ?
					', array($input['cliente'], $r2['id_web_cache_prem_incentivo'], $input['campania']));
					
					$mod = 1;
					foreach($arts as $r4)
						if ($r4['mod'] == 0)
						{
							$mod = 0;
							break;
						}
					
					$res['incentivo_premios'][] = array(
					'desc' => $r3['titulo'],
					'arts' => $arts,
					'mod' => $mod
					);
				}
			}
			
			// obtener los premios
			if ($this->_checkCampPrem($input['campania']))
			{
				$lastMul = $lastDesc = $lastCod = '';
				$lastMod = '1';
				$arts = array();

				foreach($this->_dbQuery('
				SELECT
					a.prem_codigo,
					prem_descripcion,
					b.prem_articulo_codigo11,
					prem_articulo_descripcion,
					id_web_cache_prem_articulos,
					IF(id_web_prem_solicitados IS NULL, 0, 1) sel,
					IF(fecha_recepcion IS NULL OR fecha_recepcion = "", 1, 0) `mod`,
					multiple
				FROM
					(web_cache_prem_campanias a,
					web_cache_prem_articulos b)
					LEFT JOIN web_prem_solicitados c
						ON a.prem_codigo = c.prem_codigo
						AND b.prem_campania = c.prem_campania
						AND b.prem_articulo_codigo11 = c.prem_articulo_codigo11
						AND id_cli_cliente = ?
				WHERE
					a.prem_campania = ?
					AND a.prem_campania = b.prem_campania
					AND a.prem_codigo = b.prem_codigo
				ORDER BY
				a.prem_codigo, b.prem_articulo_codigo11
				', array($input['cliente'], $input['campania'])) as $c)
				{
					if (! empty($lastCod) && $c['prem_codigo'] != $lastCod)
					{
						$res['premios'][] = array('cod' => $lastCod, 'desc' => $lastDesc, 'multiple' => $lastMul, 'arts' => $arts, 'mod' => $lastMod);
						$arts = array();
					}
					$arts[] = array('id' => $c['id_web_cache_prem_articulos'], 'desc' => $c['prem_articulo_descripcion'], 'cod11' => $c['prem_articulo_codigo11'], 'sel' => $c['sel']);
					$lastCod = $c['prem_codigo'];
					$lastDesc = utf8_decode($c['prem_descripcion']);
					$lastMul = $c['multiple'];
					if ($c['mod'] == '0') $lastMod = '0';
				}
				$res['premios'][] = array('cod' => $lastCod, 'desc' => $lastDesc, 'multiple' => $lastMul, 'arts' => $arts, 'mod' => $lastMod);
				
				$premios = array();
				foreach($res['premios'] as $r)
					if ($r['multiple'] == 1)
						$res['premios_m'][] = $r;
					else
						$premios[] = $r;
				$res['premios'] = $premios;
			}
		}
	}
	
	return $res;
}

}

require 'lib/exe.php';
