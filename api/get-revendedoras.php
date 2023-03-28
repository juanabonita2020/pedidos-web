<?php require 'lib/kernel.php';

// devuelve la lista de revendedoras
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_EMPRE, JBSYSWEBAPI::USR_COORD, JBSYSWEBAPI::USR_REGIO, JBSYSWEBAPI::USR_DIVIS);

protected $_input = array(
'term' => array(
)
);

protected function _process($input)
{
	$q = '
	SELECT
		id_cli_clientes id, numero_cliente idCliente, nombre, mail, 1 habilitada
	FROM
		web_cache_clientes
	WHERE
	';

	if ($this->_userType == JBSYSWEBAPI::USR_REGIO)
	{
		$q .= 'region = ?';
		$params = array($this->_userRegion);
	}
	
        else if ($this->_userType == JBSYSWEBAPI::USR_DIVIS)
        {
          $q .= 'division = ?';
          $params = array($this->_userDivision);
        }
        
        else
	{
		$q .= 'id_cli_zonas = ?';
		$params = array($this->_userZone);
	}
	
	// filtramos por texto (autocompletar)
	if (is_numeric($input['term']))
	{
		$term = '%'.intval($input['term']).'%';
		$q .= 'AND (nombre LIKE ? OR numero_cliente LIKE ?)';
		$params = array_merge($params, array($term, $term));
	}
	else
		foreach(preg_split('/\s+/', trim($input['term'])) as $p)
		{
			$q .= ' AND nombre LIKE ?';
			$params[] = '%'.$p.'%';
		}
		
	// si el usuario es coordinador, filtramos por sus revendedoras
	if ($this->_userType == JBSYSWEBAPI::USR_COORD)
	{
		$q .= 'AND ((es_coordinador = 0 AND coordinador = ?) OR id_cli_clientes = ?)';
		$params[] = $this->_userCoordinadorNro;
		$params[] = $this->_userClient;
	}
	
	//echo $q;print_r($params);
	
	$res = $this->_dbQuery($q.(isset($input['term']) ? ' ORDER BY idCliente' : '').' LIMIT 20', $params);

	foreach($res as $k => $v)
	{
		$res[$k]['nombre'] = utf8_encode($v['nombre']);
		$res[$k]['id2'] = sprintf('%04d', $v['idCliente']);
		$res[$k]['label'] = $v['id'].' - '.$res[$k]['nombre'].' - '.$res[$k]['id2'];
		$res[$k]['value'] = $res[$k]['label'];
		$res[$k]['mail'] = utf8_encode($v['mail']);
		//utf8_decode
	}

	return $res;
}

}

require 'lib/exe.php';