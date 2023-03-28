<?php

require 'lib/kernel.php';

// obtenemos el listado de amigas
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_EMPRE, JBSYSWEBAPI::USR_COORD, JBSYSWEBAPI::USR_REGIO, JBSYSWEBAPI::USR_DIVIS, JBSYSWEBAPI::USR_REVEN, JBSYSWEBAPI::USR_ADMIN);


protected $_input = array(
'codigo11' => array( ),
'campania' => array( )
);

protected function _process($input){

	$res = $this->_guardarSolicitudArticuloFaltante($input['campania'], $input['codigo11']);	
	return $res;

}

}

require 'lib/exe.php';

