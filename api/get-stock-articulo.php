<?php

require 'lib/kernel.php';

// obtenemos el listado de amigas
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_EMPRE, JBSYSWEBAPI::USR_COORD, JBSYSWEBAPI::USR_REGIO, JBSYSWEBAPI::USR_DIVIS, JBSYSWEBAPI::USR_REVEN, JBSYSWEBAPI::USR_ADMIN);


protected $_input = array(
'idArticulo' => array( )
);

protected function _process($input){

	$res = array(
	'stock' => array(),
	'codigo11' => ''
	);

	$articulo = $this->_dbGetOne('web_cache_articulos', array('id_web_cache_articulos' => $input['idArticulo'] ));

	$stock = $this->_dbGetOne('web_cache_articulos_stock', array('codigo11' => $articulo['cod11'] ));

	$res['codigo11'] = $articulo['cod11'];

	if($stock && $stock['stock_ilimitado'] != 1){
		$res['stock'] = $stock;
	}
	else{
		$res['stock'] = false;
	}


//	var_dump($res);

	return $res;

}

}

require 'lib/exe.php';

