<?php require 'lib/kernel.php';

// devuelve detalles de un artículo de feria
class API extends JBSYSWEBAPI
{

protected $_input = array(
'code' => array(
	'required' => true
),
'campania' => array(
	'required' => true
)
);

protected function _process($input)
{
	// comprobar la campaña
	if (! $this->_validCamp($input['campania'])) return false;

	//sleep(5);
	$res = array('talles' => $this->_dbQuery('
	SELECT DISTINCT id_web_cache_articulos idArt, Tipo, Color, Talle, stock
	FROM web_cache_articulos a, web_cache_feria_stock b
	WHERE Code = ? AND id_web_campanias = ? AND stock > 0 AND a.cod11 = b.cod11
	', array($input['code'], $input['campania'])));

	$tipos = array();
	$color = array();
	$talles = array();

	foreach($res['talles'] as $k => $v)
	{
		$res['talles'][$k]['Tipo'] = sprintf('%02d', $v['Tipo']);
		$res['talles'][$k]['Color'] = sprintf('%02d', $v['Color']);

		if (! isset($tipos[$v['Tipo']]))
		{
			$r = $this->_dbGetOne('web_cache_tipos', array('id_web_cache_tipos' => $v['Tipo']));
			$tipos[$v['Tipo']] = $r['descripcion'];
		}

		if (! isset($tipos[$v['Color']]))
		{
			$r = $this->_dbGetOne('web_cache_colores', array('id_web_cache_colores' => $v['Color']));
			$color[$v['Color']] = $r['descripcion'];
		}

		if (! isset($tipos[$v['Talle']]))
		{
			$r = $this->_dbGetOne('web_cache_talles', array('id_web_cache_talles' => $v['Talle']));
			$talles[$v['Talle']] = $r['descripcion'];
		}

		$res['talles'][$k]['tipoString'] = $tipos[$v['Tipo']];
		$res['talles'][$k]['colorString'] = $color[$v['Color']];
		$res['talles'][$k]['talleString'] = $talles[$v['Talle']];
	}

	return $res;
}

}

require 'lib/exe.php';