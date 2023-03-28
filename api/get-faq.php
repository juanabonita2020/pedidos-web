<?php require 'lib/kernel.php';

// obtenemos el listado de provincias
class API extends JBSYSWEBAPI
{
	
protected $_checkSession = false;

protected $_input = array(
'tipo' => array(
),
'id' => array(
)
);

//protected $_logReq = true;

protected function _process($input)
{
	$q = 'SELECT pregunta, respuesta, id_web_faq id, orden'.($this->_userType == JBSYSWEBAPI::USR_ADMIN ? ', t_empresaria, t_revendedora, t_coordinadora, t_consumidora, t_regional, t_anonimo' : '').' FROM web_faq';
	
	$type = ($this->_userType == JBSYSWEBAPI::USR_ADMIN ? $input['tipo'] : $this->_userType);
	
	if (! empty($type))
	{
		$fld = array('', 'empresaria', 'revendedora', '', 'coordinadora', 'consumidora', 'regional', 'anonimo');
		$q .= ' WHERE t_'.$fld[$type].' = 1';
	}
	elseif (! empty($input['id']) && $this->_userType == JBSYSWEBAPI::USR_ADMIN)
	{
		$q .= ' WHERE id_web_faq = ?';
		$params[] = $input['id'];
	}
	elseif ($this->_userType != JBSYSWEBAPI::USR_ADMIN)
		$q .= ' WHERE t_anonimo = 1';
		
	return array('preguntas' => $this->_dbQuery($q.' ORDER BY orden', $params));
}

}

require 'lib/exe.php';