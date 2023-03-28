<?php require 'lib/kernel.php';

// agregar capacitación
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN);

protected $_input = array(
'orden' => array(
	'required' => true
),
'titulo' => array(
	'required' => true
),
'contenido' => array(
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
't_regional_e' => array(
	'required' => true
),
't_anonimo' => array(
	'required' => true
),
't_division' => array(
	'required' => true
),
't_lider' => array(
	'required' => true
),
't_revdeli' => array(
	'required' => true
),
'categoria' => array(
	'required' => true
),
'sistemas' => array(
	'required' => true
)
);

protected function _process($input)
{
	$this->_dbInsert('web_capacitacion', array('orden' => $input['orden'], 'titulo' => $input['titulo'], 'texto' => $input['contenido'], 't_empresaria' => $input['t_empresaria'], 't_revendedora' => $input['t_revendedora'], 't_coordinadora' => $input['t_coordinadora'], 't_consumidora' => $input['t_consumidora'], 't_regional' => $input['t_regional'], 't_regional_e' => $input['t_regional_e'], 't_anonimo' => $input['t_anonimo'], 't_division' => $input['t_division'], 't_lider' => $input['t_lider'], 't_revdeli' => $input['t_revdeli'], 'categoria' => $input['categoria'], 'sistemas' => $input['sistemas']));
}

}

require 'lib/exe.php';
