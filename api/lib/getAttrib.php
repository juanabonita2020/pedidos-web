<?php if (empty($JBSYSWEBAPI)) die;

// procesamos los atributos
class API extends APIGETATTRIB
{

function __construct()
{
	parent::__construct();

	$this->_input = array_merge($this->_input, array(
	'campania' => array(
		'required' => true
	),
	'code' => array(
		'required' => true
	),
	'autocomplete' => array(
		'required' => true
	)
	));

	if (! isset($this->_idFmt)) $this->_idFmt = '%02d';
}

protected function _process($input)
{
	// comprobar la campaña
	if (! $this->_validCamp($input['campania'])) return false;

	//sleep(4);
	$res = $this->_getAttribs($input, $this->_ids[0]);

	if ($input['autocomplete'])
	{
		$res2 = $res;

		for($i = 0; $i < 3; $i++)
		{
			if ($i > 0)
			{
				$input = array_merge($input, array($g2 => $res2[$g1][0][$g2]));
				$res2 = $this->_getAttribs($input, $this->_ids[$i]);
			}

			$g1 = $this->_ids[$i][2];

			if (count($res2[$g1]) != 1) break;

			$g2 = $this->_ids[$i][0];
			$g3 = ($g2 == 'tipo' ? 'type' : ($g2 == 'talle' ? 'tall' : 'color'));

			if (! isset($res['autocomplete'])) $res['autocomplete'] = array();
/*
			$res['autocomplete'][$g3] = array(
				$res2[$g1][0][$g2],
				$this->_ids[$i][4] ? $res2[$g1][0]['label'] : $res2[$g1][0][$g2.'String']
			);
*/


/***************************************************************************************************************************************************/


			$res['autocomplete'][$g3] = array(
				$res2[$g1][0][$g2]
//				,$this->_ids[$i][4] ? $res2[$g1][0]['label'] : $res2[$g1][0][$g2.'String']  
			);

			if(	isset($this->_ids[$i][5]) && isset($res2[$g1][0]['codigo_tipo']) ){
				$res['autocomplete'][$g3] = array_merge($res['autocomplete'][$g3], array($res2[$g1][0]['codigo_tipo']));
			}

			if(	isset($this->_ids[$i][5]) && isset($res2[$g1][0]['codigo_color']) ){
				$res['autocomplete'][$g3] = array_merge($res['autocomplete'][$g3], array($res2[$g1][0]['codigo_color']));
			}

			$res['autocomplete'][$g3] = array_merge($res['autocomplete'][$g3], 		array( $this->_ids[$i][4] ? $res2[$g1][0]['label'] : $res2[$g1][0][$g2.'String'] ) );


/***************************************************************************************************************************************************/


			if (! isset($this->_ids[$i + 1])) break;
		}
	}

	return $res;
}

private function _getAttribs($input, $ids)
{

	$qr = '';
	if($ids[0] != 'talle'){
		$qr = ', b.' .$ids[5];
	}	


	$q = '
	SELECT descripcion '.$ids[0].'String, b.'.$ids[0]. $qr . ' FROM
	(
	SELECT DISTINCT '.$ids[1].'
	FROM web_cache_articulos
	WHERE id_web_campanias = ? AND Code = ?
	'.(isset($this->_input['tipo']) ? 'AND Tipo = ?' : '').'
	'.(isset($this->_input['color']) ? 'AND Color = ?' : '').'
	) a, web_cache_'.$ids[2].' b
	WHERE
	a.'.$ids[1].' = id_web_cache_'.$ids[2].' AND id_tab_campanias = ?
	';

	$params = array($input['campania'], $input['code']);
	if (isset($this->_input['tipo'])) $params[] = $input['tipo'];
	if (isset($this->_input['color'])) $params[] = $input['color'];
	$params[] = $input['campania'];

	$res = array($ids[2] => $this->_dbQuery($q, $params));

	foreach($res[$ids[2]] as $k => $v)
	{
		$res[$ids[2]][$k]['campania'] = $input['campania'];
		$res[$ids[2]][$k]['code'] = $input['code'];
		if (isset($this->_input['tipo']))
			$res[$ids[2]][$k]['tipo'] = $input['tipo'];
		if (isset($this->_input['color']))
			$res[$ids[2]][$k]['color'] = $input['color'];

		$res[$ids[2]][$k]['descripcion'] = $v[$ids[0].'String'];

		$res[$ids[2]][$k][$ids[0]] = sprintf($this->_idFmt, $v[$ids[0]]);


		if($ids[0] == 'talle'){
			$res[$ids[2]][$k]['label'] = ($ids[4] ? $v[$ids[0].'String'] : $res[$ids[2]][$k][$ids[0]].' ('.$v[$ids[0].'String'].')');
		}
		
		else{
			$res[$ids[2]][$k]['label'] = ($ids[4] ? $v[$ids[0].'String'] : $res[$ids[2]][$k][$ids[5]].' ('.$v[$ids[0].'String'].')');
		}

		
	}

	return $res;
}

}

require 'lib/exe.php';