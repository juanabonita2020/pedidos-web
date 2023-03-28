<?php require 'lib/kernel.php';

// obtenemos el listado de capacitaciones
class API extends JBSYSWEBAPI
{

protected $_checkSession = false;

protected $_input = array(
'pg' => array(
),
'all' => array(
)
);

//protected $_logReq = true;

protected function _process($input)
{
	$q = 'FROM web_capacitacion_cat';

	if (empty($input['all']))
		$res = array(
		'pager' => $this->_dbPager($q, null, $input['pg'], 10, ' ORDER BY orden')
		);
	else
		$res = array();
	
	$res['cats'] = $this->_dbQuery('SELECT * '.$q, array());
	
	return $res;
}

}

require 'lib/exe.php';