<?php require 'lib/kernel.php';

// obtener el listado histórico de pedidos
class API extends JBSYSWEBAPI
{

	protected $_validUserTypes = array(JBSYSWEBAPI::USR_EMPRE, JBSYSWEBAPI::USR_REVEN, JBSYSWEBAPI::USR_COORD);

	protected $_input = array(
		'cliente' => array(
		),
		'campania' => array(
		),
		'pg' => array(
		)
	);


	protected function _process($input)	{
		$q = " 	,e.id_web_campanias AS campania, e.fecha_fin AS fecha_vigencia, e.url 
				FROM 	web_cache_encuesta_clientes e 
				WHERE 	e.fecha_inicio <= NOW() AND e.fecha_fin >= NOW() AND e.id_cli_clientes = ? ";
		$params = array($this->_userClient);

		$pager = $this->_dbPager($q, $params, $input['pg'], 20, '', 'fecha_fin');

		$q1 = " SELECT 	e.id_web_campanias AS campania, e.fecha_fin AS fecha_vigencia, e.url 
				FROM 	web_cache_encuesta_clientes e 
				WHERE 	e.fecha_inicio <= NOW() AND e.fecha_fin >= NOW() AND e.id_cli_clientes = ? ";

		$encuestas = $this->_dbQuery( $q1, $params);

		return array('encuestas' => $encuestas, 'pager' => $pager);
	}

}

require 'lib/exe.php';
