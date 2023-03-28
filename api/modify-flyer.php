<?php require 'lib/kernel.php';

// agregar/modificar un flyer
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN);

protected $_input = array(
'flyer' => array(
),
'titulo' => array(
	'required' => true
),
'contenido' => array(
	'required' => true
),
'pagina' => array(
	'required' => true
),
'auto_borrar' => array(
	'required' => true
),
'fecha_desde' => array(
),
'fecha_hasta' => array(
),
'dest_usuario' => array(
),
'dest_tipousuario' => array(
),
'sistema' => array(
)
);

//protected $_logReq = true;

protected function _process($input)
{
	$data = array();

	foreach(array('titulo', 'contenido', 'pagina', 'auto_borrar', 'sistema') as $k)
		$data[$k] = $input[$k];

	foreach(array('fecha_desde', 'fecha_hasta', 'dest_usuario', 'dest_tipousuario') as $k)
		if (empty($input[$k]))
		{
			if (! empty($input['flyer'])) $data[$k] = null;
		}
		else
			$data[$k] = (substr($k, 0, 5) == 'fecha' ? $this->_toDate($input[$k]) : $input[$k]);

	if (empty($input['flyer']))
		$this->_dbInsert('web_flyers', $data);
	else
		$this->_dbUpdate('web_flyers', $data, array('id_web_flyers' => $input['flyer']));
}

}

require 'lib/exe.php';