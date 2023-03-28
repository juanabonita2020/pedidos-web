<?php require 'lib/kernel.php';

// probar envío de correo
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN);

protected $_input = array(
'dest' => array(
	'required' => true
),
'account' => array(
	'required' => true
)
);

protected function _process($input)
{
	$data = array(
	'host' => $this->_config['smtpHost'],
	'port' => $this->_config['smtpPort']
	);
	
	switch($input['account'])
	{
	case 1:
	{
		$data['username'] = $this->_config['smtpUser'];
		$data['password'] = $this->_config['smtpPass'];
		break;
	}
	case 2:
	{
		$data['username'] = $this->_config['smtpUser2'];
		$data['password'] = $this->_config['smtpPass2'];
		break;
	}
	case 3:
	{
		$data['username'] = $this->_config['smtpUser3'];
		$data['password'] = $this->_config['smtpPass3'];
		break;
	}
	case 4:
	{
		$data['username'] = $this->_config['smtpUser4'];
		$data['password'] = $this->_config['smtpPass4'];
		break;
	}
	}

	$data['result'] = $this->_sendmail($input['dest'], 'Prueba SMTP - Cuenta '.$input['account'], 'Prueba de envío de correo utilizando la cuenta '.$input['account'], $input['account'], false, true);
	
	if ($data['result'] === true) $data['result'] = 'OK!';
	
	return $data;
}

}

require 'lib/exe.php';