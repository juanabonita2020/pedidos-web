<?php

require 'lib/kernel.php';

// obtenemos el listado de amigas
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_EMPRE, JBSYSWEBAPI::USR_COORD, JBSYSWEBAPI::USR_REGIO, JBSYSWEBAPI::USR_DIVIS, JBSYSWEBAPI::USR_REVEN, JBSYSWEBAPI::USR_ADMIN);


protected $_input = array(
'id_cli_clientes' => array( )
);

protected function _process($input){

	$res = $this->_dbGetOne('web_cache_clientes', array('id_cli_clientes' => $input['id_cli_clientes']), array('id_cli_clientes', 'baja') );	
	return $res;

}

}

require 'lib/exe.php';
