<?php require 'lib/kernel.php';

// obtener artículos de feria que no tienen stock
class API extends JBSYSWEBAPI
{

protected $_input = array(
'code' => array(
),
'color' => array(
)
);

protected function _process($input)
{
	$q = '
	SELECT Code code, Color idcolor, id_web_campanias campania, a.*
	FROM (
	SELECT imagen_url img, thumb_url thumb, a.descripcion, descripcion_rango_talle, SUM(stock) stock, DATEDIFF(NOW(), fecha_inicio) dias, precio_original precioOriginal, '.($this->_userType == JBSYSWEBAPI::USR_CONSU ? 'precio_compradora' : 'a.precio').' precio, cod11
	FROM
	web_cache_feria_articulos a, web_cache_feria_stock b
	WHERE
	a.cod8 = b.cod8 AND stock AND fecha_inicio <= NOW() AND fecha_fin >= NOW()
	GROUP BY a.cod8
	) a, web_cache_articulos c
	WHERE a.cod11 = c.cod11
	';//
	
	/*SELECT Code code, Color idcolor, precio_original precioOriginal, '.($this->_userType == JBSYSWEBAPI::USR_CONSU ? 'precio_compradora' : 'a.precio').' precio, imagen_url img, thumb_url thumb, a.descripcion, descripcion_rango_talle, id_web_campanias campania, SUM(stock) stock, DATEDIFF(NOW(), fecha_inicio) dias, b.cod8
	FROM
	web_cache_feria_articulos a, web_cache_feria_stock b, web_cache_articulos c
	WHERE
	a.cod8 = b.cod8 AND stock AND fecha_inicio <= NOW() AND fecha_fin >= NOW()
	AND b.cod11 = c.cod11*/
	
	$params = array();

	if (! empty($input['code']))
	{
		$q .= ' AND Code = ?';
		$params[] = $input['code'];
	}
	
	if (! empty($input['color']))
	{
		$q .= ' AND Color = ?';
		$params[] = $input['color'];
	}

	$q .= ' GROUP BY Code, Color';
	
	//echo $q;print_r($params);
	
	$res = array('productos' => $this->_dbQuery($q, $params));
	$colors = array();
	
	foreach($res['productos'] as $k => $p)
	{
		if (! isset($colors[$p['campania']][$p['idcolor']]))
		{
			$r = $this->_dbGetOne('web_cache_colores', array('color' => $p['idcolor'], 'id_tab_campanias' => $p['campania']), array('descripcion'));
			$colors[$p['campania']][$p['idcolor']] = $r['descripcion'];		
		}
		
		$res['productos'][$k]['color'] = $colors[$p['campania']][$p['idcolor']];
		$res['productos'][$k]['nuevo'] = (! empty($this->_global['dias_nuevo']) && $p['dias'] <= $this->_global['dias_nuevo']);
		$res['productos'][$k]['ultimas'] = (! empty($this->_global['ultimas_unidades']) && $p['stock'] <= $this->_global['ultimas_unidades']);
	}
	
	return $res;
}

}

require 'lib/exe.php';