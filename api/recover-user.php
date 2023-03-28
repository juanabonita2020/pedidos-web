<?php require 'lib/kernel.php';

// procesa la recuperación de contraseña
class API extends JBSYSWEBAPI
{

protected $_checkSession = false;

protected $_input = array(
'zona' => array(
	'required' => true
),
'cliente' => array(
	'required' => true
),
'stage' => array(
	'required' => true
),
'dni' => array(
),
'localidad' => array(
),
'nacimiento' => array(
),
'telefono' => array(
)
);

protected function _process($input)
{
	$r1 = $this->_dbGetOne('web_cache_clientes', array('id_cli_zonas' => $input['zona'], 'numero_cliente' => $input['cliente']), array('id_web_cache_clientes', 'dni'));
	
	if ($input['stage'] == 1)
	{	
		$res = array(
		'nac' => array(),
		'dni' => array(),
		'loc' => array(),
		//'log' => array()
		);
		
		$ni = 3;
		$li = 3;
		$di = 3;
		$ti = 0;
		
		if ($r1['id_web_cache_clientes'])
		{
			//$res['log']['cliente'] = $r1;
			
			$res['dni'][] = $r1['dni']; 
			$di = 2;
			
			$r2 = $this->_dbGetOne('web_usuarios', array('id_cli_clientes' => $r1['id_web_cache_clientes']), array('localidad', 'fecha_nacimiento', 'tel_area1', 'tel_area2', 'cel_area', 'tel_prefijo1', 'tel_prefijo2', 'cel_prefijo', 'tel_sufijo1', 'tel_sufijo2', 'cel_sufijo'));
			
			//$res['log']['usuario'] = $r2;
			
			if ($r2['fecha_nacimiento'])
			{
				$res['nac'][] = $r2['fecha_nacimiento']; 
				$ni = 2;
			}
			else
			{
				unset($res['nac']);
				$ni = 0;
			}
				
			if ($r2['localidad'])
			{
				$res['loc'][] = $r2['localidad']; 
				$li = 2;
			}
			else
			{
				unset($res['loc']);
				$li = 0;
			}
			
			if ($ni == 0 || $li == 0)
			{
				if (! empty($r2['tel_area1']) && ! empty($r2['tel_sufijo1']) && ! empty($r2['tel_prefijo1']))
				{
					$res['tel'] = array($r2['tel_area1'].$r2['tel_prefijo1'].$r2['tel_sufijo1']);
					$ti = 2;
				}
				else if (! empty($r2['tel_area2']) && ! empty($r2['tel_sufijo2']) && ! empty($r2['tel_prefijo2']))
				{
					$res['tel'] = array($r2['tel_area2'].$r2['tel_prefijo2'].$r2['tel_sufijo2']);
					$ti = 2;
				}
				else if (! empty($r2['cel_area']) && ! empty($r2['cel_sufijo']) && ! empty($r2['cel_prefijo']))
				{
					$res['tel'] = array($r2['cel_area'].$r2['cel_prefijo'].$r2['cel_sufijo']);
					$ti = 2;
				}
			}
		}
		
		if ($ni)
		{
			for($i = 0; $i < $ni; $i++) while(true)
			{
				$nacimiento = rand(1940, 1998).'-'.rand(1, 12).'-'.rand(1, 28);
				if (! in_array($nacimiento, $res['nac']))
				{
					$res['nac'][] = $nacimiento;
					break;
				}
			}
			sort($res['nac']);
			foreach($res['nac'] as $k => $n)
			{
				list($y, $m, $d) = explode('-', $n);
				$res['nac'][$k] = sprintf('%02d/%02d/%s', $d, $m, $y);
			}
		}
			
		for($i = 0; $i < $di; $i++) while(true)
		{
			$dni = strval(rand(20345321, 40873911));
			if (! in_array($dni, $res['dni']))
			{
				$res['dni'][] = $dni;
				break;
			}
		}
		sort($res['dni']);
			
		if ($li)
		{
			foreach($this->_dbQuery('SELECT DISTINCT localidad FROM web_usuarios WHERE localidad IS NOT NULL'.($li == 3 ? '' : ' AND localidad <> ?').' ORDER BY RAND() LIMIT '.$li, $li == 3 ? null : array($r2['localidad'])) as $l)
				$res['loc'][] = $l['localidad'];
				
			sort($res['loc']);
		}
		
		if ($ti)
		{
			for($i = 0; $i < $ti; $i++) while(true)
			{
				$tel = strval(rand(40310000, 43949999));
				if (! in_array($tel, $res['tel']))
				{
					$res['tel'][] = $tel;
					break;
				}
			}

			sort($res['tel']);	
		}		
			
		return $res;
	}
	else
	{
		if (! $input['dni'] || ! $input['localidad'] || ! $input['nacimiento'] || ! $r1['id_web_cache_clientes'] || $r1['dni'] != $input['dni'])
			return array('username' => null);
		
		$r2 = $this->_dbGetOne('web_usuarios', array('id_cli_clientes' => $r1['id_web_cache_clientes']), array('mail', 'localidad', 'fecha_nacimiento', 'tel_area1', 'tel_area2', 'cel_area', 'tel_prefijo1', 'tel_prefijo2', 'cel_prefijo', 'tel_sufijo1', 'tel_sufijo2', 'cel_sufijo'));

		if ($r2['localidad'] && $r2['localidad'] != $input['localidad'])
			return array('username' => null);
		
		if ($r2['fecha_nacimiento'])
		{
			list($d, $m, $y) = explode('/', $input['nacimiento']);
			if ($y.'-'.$m.'-'.$d != substr($r2['fecha_nacimiento'], 0, 10))
				return array('username' => null);
		}
		
		if (! $r2['localidad'] && ! $r2['fecha_nacimiento'])
		{
			if ($input['telefono'] != $r2['tel_area1'].$r2['tel_prefijo1'].$r2['tel_sufijo1'] && $input['telefono'] != $r2['tel_area2'].$r2['tel_prefijo2'].$r2['tel_sufijo2'] && $input['telefono'] != $r2['cel_area'].$r2['cel_prefijo'].$r2['cel_sufijo'])
				return array('username' => null);
		}
		
		return array('username' => $r2['mail']);
	}
}

}

require 'lib/exe.php';