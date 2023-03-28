<?php require 'lib/kernel.php';

// obtenemos el listado de provincias
class API extends JBSYSWEBAPI
{

protected $_input = array(
'pais' => array(
)
);

protected function _process($input)
{
	// obtenemos las provincias
	$res = array('provincias' => $this->_dbGetAll('web_provincias', array('id_web_paises' => $input['pais']), array('descripcion', 'id_web_provincias' => 'idProvincias'), 'descripcion'));

	// obtenemos la leyenda de la provincia
	$r = $this->_dbGetOne('web_paises', array('id_web_paises' => $input['pais']), array('leyenda'));
	$res['leyenda'] = $r['leyenda'];

	return $res;
}

}

require 'lib/exe.php';
