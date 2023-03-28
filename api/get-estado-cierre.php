<?php require 'lib/kernel.php';

// obtener el estado de cierre de una campaña-zona
class API extends JBSYSWEBAPI
{

protected $_input = array(
'campania' => array(
	'required' => false
)
);

protected function _process($input)
{
	$r = $this->_dbQuery('SELECT activo, habilitado, cantidadCierresRestantes, DATEDIFF(fecha_hasta, fecha_desde) cantidadDiasRestantes, activo activa, a.id_web_campanias campania FROM web_campanias a, web_campanias_zonas b WHERE a.id_web_campanias = b.id_web_campanias AND a.id_web_campanias = ? AND id_cli_zonas = ?', array($input['campania'], $this->_userZone));
	$r[0]['cantidadDiasRestantes'] *= 1;
	
	return $r[0];
}

}

require 'lib/exe.php';