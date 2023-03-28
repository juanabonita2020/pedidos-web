<?php require 'lib/kernel.php';

// obtenemos el listado de publicidades
class API extends JBSYSWEBAPI
{

protected $_input = array(
'all' => array(
),
'pg' => array(
)
);

protected function _process($input)
{
	$all = (! empty($input['all']) && $this->_userType == JBSYSWEBAPI::USR_ADMIN);

	$q =  'FROM web_publicidad'.($all ? '' : ' WHERE (desde <= NOW() OR desde = "0000-00-00") AND (hasta >= NOW() OR hasta = "0000-00-00")');

	if ($all)
	{
		$pager = $this->_dbPager($q, null, $input['pg'], 10, ' ORDER BY id_web_publicidad DESC');
		$res = array('pubs' => array(), 'pager' => $pager);
		$params = array();
	}
	else
	{
		$res = array();
		$q .= ' AND (id_web_campanias IS NULL OR id_web_campanias = 0 OR id_web_campanias = ?)';
		$params = array($this->_userCampaign);
	}

	foreach($this->_dbQuery('SELECT * '.$q, $params) as $p)
		if ($all)
		{
			$p['desde'] = $this->_fromDate($p['desde']);
			$p['hasta'] = $this->_fromDate($p['hasta']);
			$p['id_web_campanias'] *= 1;
			$res['pubs'][] = $p;
		}
		else
			$res[$p['espacio']][] = array($p['imagen'], $p['link']);

	return $res;
}

}

require 'lib/exe.php';