<?php require 'lib/kernel.php';

// obtener el listado histórico de pedidos
class API extends JBSYSWEBAPI
{

	protected $_validUserTypes = array(JBSYSWEBAPI::USR_EMPRE, JBSYSWEBAPI::USR_REVEN, JBSYSWEBAPI::USR_COORD);

	protected $_input = array(
		'campania' => array(
		),
		'codigo11' => array(
		)
	);


	protected function _process($input)	{

/*		$q = " SELECT p.id_web_cache_promocion, p.id_web_campanias, p.fecha, p.codigo_promocion, p.descripcion, p.cantidad_articulos FROM web_cache_promocion p WHERE p.id_web_campanias = ?";
		$params[] = $input["campania"];
*/
		$q = " 	SELECT 		p.id_web_cache_promocion, p.id_web_campanias, p.fecha_inicio, p.fecha_fin, p.codigo_promocion, p.descripcion, p.cantidad_articulos, p.activa, p.id_prom_promocion
				FROM 		web_cache_promocion p 
				INNER JOIN	web_cache_articulos_promocion ap ON ap.id_prom_promocion = p.id_prom_promocion
				WHERE 		p.id_web_campanias = ? AND ap.codigo11 = ? AND  p.activa = 1 AND p.fecha_inicio <= NOW() AND p.fecha_fin >= NOW()";
		$params[] = $input["campania"];
		$params[] = $input["codigo11"];
		$promociones = $this->_dbQuery( $q, $params);

		return array('promociones' => $promociones);
	}

}

require 'lib/exe.php';
