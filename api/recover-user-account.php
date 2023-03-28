<?php require 'lib/kernel.php';

// procesa la recuperación de contraseña
class API extends JBSYSWEBAPI
{

protected $_checkSession = false;

protected $_input = array(
'email' => array(
	'required' => true
),
'code' => array(
)
);

protected $_logReq = true;

protected function _process($input)
{
	if (empty($input['code']))
		$email = $input['email'];
	else
		list($userId, $email) = explode('-', $input['code']);

	if (($r = $this->_dbGetOne('web_usuarios', array('mail' => $email, 'habilitada' => '1'))) !== false)
	{
		if (empty($input['code']))
		{
			$url = $this->_config['baseURL'].'#newPass/'.$r['id_web_usuarios'].'-'.$email;
			$body = '<p>Hola,<br />siga el siguiente enlace para recuperar su contraseña: <a href="'.$url.'">'.$url.'</a></p>';
		}
		else
		{
			$newpass = mt_rand();
			$body = '<p>Hola,<br />su nueva contraseña es: <b>'.$newpass.'</b><br /><br />La misma la puede cambiar una vez que ingresa al sistema.</p>';
			$this->_setPassword($userId, $newpass);
		}

		$this->_sendmail($email, 'Su nueva contraseña', $body);
	}
}

}

require 'lib/exe.php';