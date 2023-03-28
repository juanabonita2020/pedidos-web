<?php require 'lib/kernel.php';

class API extends JBSYSWEBAPI
{

	protected $_input = array(
		'campania' => array(
			'required' => true
		),
		'idPromPromocion' => array(
			'required' => true
		)
	);

//	protected $tipo_venta = 50;
	protected $_validUserTypes = array(JBSYSWEBAPI::USR_EMPRE, JBSYSWEBAPI::USR_REVEN, JBSYSWEBAPI::USR_COORD);

	protected function _process($input)	{
/*
		$q = " SELECT 		a.id_web_cache_articulos, a.id_web_campanias, a.tipo_venta, a.descripcion, a.cod11, a.Code, a.Tipo, a.Color, a.Talle, a.precio, 
				CONCAT(ti.codigo_tipo , '-' , ti.descripcion) AS tipo_str, ti.codigo_tipo,  CONCAT(c.codigo_color , '-' , c.descripcion) AS color_str, c.codigo_color,  ta.descripcion talle_str  
				FROM 			web_cache_articulos a
				INNER JOIN	web_cache_tipos ti ON ti.tipo = a.Tipo AND ti.id_tab_campanias = a.id_web_campanias
				INNER JOIN	web_cache_colores c ON c.color = a.Color AND c.id_tab_campanias = a.id_web_campanias
				INNER JOIN 	web_cache_talles ta ON ta.talle = a.Talle	AND ta.id_tab_campanias = a.id_web_campanias
				WHERE 		a.id_web_campanias = ? AND a.tipo_venta = ?
				ORDER BY 	a.Code, a.Tipo, a.Color, a.Talle";
		
		$params[] = $input["campania"];
		$params[] = $this->tipo_venta;
*/

		$q = "	SELECT 		a.id_web_cache_articulos, a.id_web_campanias, a.tipo_venta, a.descripcion, a.cod11, a.Code, a.Tipo, a.Color, a.Talle, a.precio, 
							CONCAT(ti.codigo_tipo , '-' , ti.descripcion) AS tipo_str, ti.codigo_tipo,  
							CONCAT(c.codigo_color , '-' , c.descripcion) AS color_str, c.codigo_color,  ta.descripcion talle_str  
		
				FROM 		web_cache_articulos_promocion ap
				INNER JOIN	web_cache_promocion p ON p.id_prom_promocion = ap.id_prom_promocion
				INNER JOIN 	web_cache_articulos a ON a.cod11 = ap.codigo11
				INNER JOIN	web_cache_tipos ti ON ti.tipo = a.Tipo AND ti.id_tab_campanias = a.id_web_campanias
				INNER JOIN	web_cache_colores c ON c.color = a.Color AND c.id_tab_campanias = a.id_web_campanias
				INNER JOIN 	web_cache_talles ta ON ta.talle = a.Talle	AND ta.id_tab_campanias = a.id_web_campanias
				WHERE 		p.id_web_campanias = ? 
							AND a.id_web_campanias = ?
							AND p.id_prom_promocion = ?	
							AND ap.activo = 1	
							AND p.activa = 1
				ORDER BY 	a.Code, a.Tipo, a.Color, a.Talle";
		$params[] = $input["campania"];
		$params[] = $input["campania"];
		$params[] = $input["idPromPromocion"];
		$articulos = /*$this->_dbGetAll('web_cache_articulos', array('tipo_venta' => $this->tipo_venta) );*/ $this->_dbQuery( $q, $params);

		return array('articulos' => $articulos);
	}

}

require 'lib/exe.php';
