<?php require 'lib/kernel.php';

// obtenemos el listado de provincias
class API extends JBSYSWEBAPI
{
	
protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN);
	
protected $_input = array(
'orden' => array(
	'required' => true
),
'pregunta' => array(
	'required' => true
),
'respuesta' => array(
	'required' => true
),
't_empresaria' => array(
	'required' => true
),
't_revendedora' => array(
	'required' => true
),
't_coordinadora' => array(
	'required' => true
),
't_consumidora' => array(
	'required' => true
),
't_regional' => array(
	'required' => true
),
't_anonimo' => array(
	'required' => true
)
);

protected function _process($input)
{
	$this->_dbInsert('web_faq', array('orden' => $input['orden'], 'pregunta' => $input['pregunta'], 'respuesta' => $input['respuesta'], 't_empresaria' => $input['t_empresaria'], 't_revendedora' => $input['t_revendedora'], 't_coordinadora' => $input['t_coordinadora'], 't_consumidora' => $input['t_consumidora'], 't_regional' => $input['t_regional'], 't_anonimo' => $input['t_anonimo']));
}

}

require 'lib/exe.php';