<?php require 'lib/kernel.php';

// devuelve los detalles de un artículo
class API extends JBSYSWEBAPI
{

protected $_input = array(
'campania' => array(
	'required' => true
),
'code' => array(
	'required' => true
),
'tipo' => array(
	'required' => true
),
'color' => array(
	'required' => true
),
'talle' => array(
),
'talleString' => array(
),
'q' => array(
	'required' => true
)
);

protected function _process($input)
{
	// comprobar la campaña
	if (! $this->_validCamp($input['campania'])) return false;

	if (empty($input['talle']))
	{
                $tail = '';
                if( is_numeric($input['talleString']) ){
                    $tail = sprintf('%03d', $input['talleString']);
                }
                else{
                    $tail = $input['talleString']; 
                }
                     
//		$r = $this->_dbGetOne('web_cache_talles', array('id_tab_campanias' => $input['campania'], 'descripcion' =>   sprintf('%03d', $input['talleString'])  ));
                $r = $this->_dbGetOne('web_cache_talles', array('id_tab_campanias' => $input['campania'], 'descripcion' =>   $tail  ));  
		$talle = (isset($r['talle']) ? $r['talle'] : 0);
	}
	else
		$talle = $input['talle'];

	$cuotas = $this->_cuotasEnabled($input['campania']);
	
	if (($res = $this->_dbGetOne('web_cache_articulos', array(
	'id_web_campanias' => $input['campania'],
	'Code' => $input['code'],
	'Tipo' => $input['tipo'],
	'Color' => $input['color'],
	'Talle' => $talle,
	), array(
	'precio',
	'cod11',
	'descripcion',
	'cuotas',
	'id_web_campanias' => 'campania',
	'Code' => 'code',
	'Talle' => 'talle',
	'Color' => 'color',
	'Tipo' => 'tipo',
	'feria' => 'isFeria',
	'id_web_cache_articulos' => 'idArticulo',
	'tipo_venta' => 'tipoVenta',
	'puntos' => 'ptos'
	))) !== false)
	{
                $res['code'] = $res['code'];
	//	$res['code'] = sprintf('%04d', $res['code']);
		$res['idTalle'] = sprintf('%03d', $res['talle']);
		$res['idColor'] = sprintf('%02d', $res['color']);
		$res['idTipo'] = sprintf('%02d', $res['tipo']);
		$res['campania'] = sprintf('%02d', $res['campania']);
		$res['descripcion'] = utf8_encode($res['descripcion']);

		$r = $this->_dbGetOne('web_cache_tipos', array('tipo' => $res['tipo'], 'id_tab_campanias' => $input['campania']));
		$res['tipoString'] = utf8_encode($r['descripcion']);

		$r = $this->_dbGetOne('web_cache_colores', array('color' => $res['color'], 'id_tab_campanias' => $input['campania']));
		$res['colorString'] = utf8_encode($r['descripcion']);

		$r = $this->_dbGetOne('web_cache_talles', array('talle' => $res['talle'], 'id_tab_campanias' => $input['campania']));
		$res['talleString'] = utf8_encode($r['descripcion']);

/********************************************************************************************/
			/* Cambio promoción */

		$q = " 	SELECT 		p.id_web_cache_articulos_promocion, p.id_prom_promocion, p.codigo11, p.activo 
				FROM 		web_cache_articulos_promocion p 
				INNER JOIN	web_cache_promocion cp ON cp.id_prom_promocion = p.id_prom_promocion
				WHERE 		cp.id_web_campanias = ? AND p.codigo11 = ? AND p.activo = 1";
		$params[] = $res['campania'];
		$params[] = $res['cod11'];
		$promo = $this->_dbQuery( $q, $params);

		if( isset($promo[0]) ){
			$res['tiene_promo'] = $promo[0]['activo'];
		}
		else{
			$res['tiene_promo'] = 0;
		}


/********************************************************************************************/
		if (! $cuotas) $res['cuotas'] = 0;
	}
	else
	{
		$q = 'SELECT Code FROM web_cache_articulos WHERE id_web_campanias = ? AND Code = ? AND Tipo = ?';
		$params = array($input['campania'], $input['code'], $input['tipo']);
		$r = $this->_dbQuery($q.' LIMIT 1', $params);
		if (! isset($r[0])) return array('msg' => 'El tipo ingresado es inválido.');

		$q .= ' AND Color = ?';
		$params[] = $input['color'];
		$r = $this->_dbQuery($q.' LIMIT 1', $params);
		if (! isset($r[0])) return array('msg' => 'El color ingresado es inválido.');

		$q .= ' AND Talle = ?';
		$params[] = $talle;
		//echo $q;print_r($params);
		$r = $this->_dbQuery($q.' LIMIT 1', $params);
		if (! isset($r[0])) return array('msg' => 'El talle ingresado es inválido.');
	}

	return $res;
}

}

require 'lib/exe.php';