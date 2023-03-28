<?php require 'lib/kernel.php';

// obtenemos el listado de capacitaciones
class API extends JBSYSWEBAPI
{

protected $_checkSession = false;

protected $_input = array(
'tipo' => array(
),
'sistema' => array(
),
'id' => array(
),
'preview' => array(
),
'category' => array(
)
);

//protected $_logReq = true;

protected function _process($input)
{
	$q = 'SELECT a.titulo, texto contenido, id_web_capacitacion id, categoria, b.titulo titulocat, a.orden'.($this->_userType == JBSYSWEBAPI::USR_ADMIN ? ', t_empresaria, t_revendedora, t_coordinadora, t_consumidora, t_regional, t_regional_e, t_anonimo, t_division, t_lider, t_revdeli' : '').', sistemas FROM web_capacitacion a LEFT JOIN web_capacitacion_cat b ON categoria = id_web_capacitacion_cat';

	$negocio = (empty($this->_userNegocio) ? $this->_getSessionVar('negocio') : $this->_userNegocio);

	$type = (
		$this->_userType == JBSYSWEBAPI::USR_ADMIN
		? $input['tipo']
		: (
			// lider
			$this->_userType == JBSYSWEBAPI::USR_EMPRE && $negocio == 'D'
			? 8
			: (
				// revendedora deli
				$this->_userType == JBSYSWEBAPI::USR_REVEN && $negocio == 'D'
				? 9
				: (
					// regional empresaria
					$this->_userType == JBSYSWEBAPI::USR_REGIO && $this->_regionalNegocio == 'E'
					? 10
					: (
						// divisional
						$this->_userType == JBSYSWEBAPI::USR_DIVIS
						? 11
						// otras
						: $this->_userType
					)
				)
			)
		)
	);

	if (! empty($type))
	{
		// lider
		if ($type == 8)
			$q .= ' WHERE t_lider = 1';
		// revendedora deli
		else if ($type == 9)
			$q .= ' WHERE t_revdeli = 1';
		// regional empresaria
		else if ($type == 10)
			$q .= ' WHERE t_regional_e = 1';
		// divisional
		else if ($type == 11)
			$q .= ' WHERE t_division = 1';
		// otras
		else
		{
			$fld = array('', 'empresaria', 'revendedora', '', 'coordinadora', 'consumidora', 'regional', 'regional_e', 'anonimo', 'lider');
			$q .= ' WHERE t_'.$fld[$type].' = 1';
		}
	}
	elseif (! empty($input['id']) && $this->_userType == JBSYSWEBAPI::USR_ADMIN)
	{
		$q .= ' WHERE id_web_capacitacion = ?';
		$params[] = $input['id'];
	}
	elseif ($this->_userType != JBSYSWEBAPI::USR_ADMIN)
		$q .= ' WHERE t_anonimo = 1';

	if (! empty($input['category']))
	{
		$q .= ' AND categoria = ?';
		$params[] = $input['category'];

		$logId = 'CAP'.$input['category'];
		$this->_saveStat($logId);
		$this->_addAuditLog($logId);
	}

	$sistema = ($this->_userType == JBSYSWEBAPI::USR_ADMIN ? $input['sistema'] : $_userSistema);

	$list = array();
	foreach($this->_dbQuery($q.' ORDER BY a.orden', $params) as $v)
	{
		$v['sistemas'] = explode(',', $v['sistemas']);
		if (empty($sistema) || in_array($sistema, $v['sistemas']))
			$list[] = $v;
	}

	if (! empty($input['id']) && ! empty($input['preview']))
		die($list[0]['contenido']);

	return array(
	//~'_debug' => array($type, $negocio, $q, $params),
	'regionalNegocio' => $this->_regionalNegocio,
	'capacitaciones' => $list,
	'cats' => $this->_dbQuery('SELECT id_web_capacitacion_cat id, titulo FROM web_capacitacion_cat')
	);
}

}

require 'lib/exe.php';
