<?php require 'lib/kernel.php';

// actualiza un usuario regional
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN);

protected $_input = array(
'id' => array(
	'required' => true
),
'data' => array(
	'required' => true
),
'value' => array(
	'required' => true
)
);

protected $_logReq = true;

protected function _process($input)
{
	if ($input['data'] == 'pass')
		$this->_setPassword($input['id'], $input['value']);
	else
	{
		$value = $input['value'];
		
		switch($input['data'])
		{
		case 'email': $fld = 'mail'; break;
		case 'passm':
		{
			$fld = 'password_m';
			if (! empty($value)) $value = $this->_hash($value);
			break;
		}
		case 'region': case 'division': case 'nombre': case 'apellido': $fld = $input['data']; break;
		case 'negocio':
		{
			$fld = 'regional_negocio';
			if (empty($value)) $value = null;
			break;
		}
		default: return;
		}
		
		$this->_dbUpdate('web_usuarios', array($fld => $value), array('id_web_usuarios' => $input['id']));
	}
}

}

require 'lib/exe.php';