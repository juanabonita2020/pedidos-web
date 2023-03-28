<?php require 'lib/kernel.php';

// devuelve la lista de revendedoras
class API extends JBSYSWEBAPI
{

protected $_input = array(
'term' => array(
),
'pais' => array(
)
);

protected function _process($input)
{
	$locals = $this->_dbQuery('SELECT localidad label, localidad value FROM web_localidades WHERE localidad COLLATE UTF8_GENERAL_CI LIKE ? AND id_web_pais = ? LIMIT 15', array('%'.$input['term'].'%', $input['pais']));
	foreach($locals as $k => $v)
		$locals[$k]['label'] = $locals[$k]['value'] = utf8_encode($v['label']);
	return $locals;
}

}

require 'lib/exe.php';
