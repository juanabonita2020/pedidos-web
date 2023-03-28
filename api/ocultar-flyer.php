<?php require 'lib/kernel.php';

// ocultar un flyer para un usuario
class API extends JBSYSWEBAPI
{

protected $_input = array(
'flyer' => array(
)
);

//protected $_logReq = true;

protected function _process($input)
{
	$r = $this->_dbGetOne('web_flyers', array('id_web_flyers' => $input['flyer']));
	
	if ($r['dest_usuario'] == $this->_userId)
		$this->_dbDelete('web_flyers_ocultar', array('id_web_flyers' => $input['flyer']));
	else
		$this->_dbInsert('web_flyers_ocultar', array('id_web_flyers' => $input['flyer'], 'id_web_usuarios' => $this->_userId));
}

}

require 'lib/exe.php';