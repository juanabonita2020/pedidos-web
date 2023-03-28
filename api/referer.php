<?php require 'lib/kernel.php';

// logea a un referido
class API extends JBSYSWEBAPI
{

protected $_checkSession = false;

protected $_input = array(
'id' => array(
	'required' => true
)
);

protected function _process($input)
{
	$res = array('ok' => false);

	if (($r = $this->_dbGetOne('web_usuarios', array('id_web_usuarios' => $input['id'], 'habilitada' => '1'))) !== false)
	{
		$this->_loginUser($r['id_cli_clientes']);

		$refererFullName = $r['nombre'].' '.$r['apellido'];
		
		$this->_saveSessionVar('userType', JBSYSWEBAPI::USR_CONSU);
		$this->_saveSessionVar('userId', $input['id']);
		$this->_saveSessionVar('userName', '');
		$this->_saveSessionVar('anon', true);
		$this->_saveSessionVar('refererFullName', $refererFullName);
		$this->_saveSessionVar('refererMail', $r['mail']);

		$res = array('ok' => true, 'clientName' => $refererFullName);

		//~ $_SESSION['sisweb_anon'] = true;
	}

	return $res;
}

}

require 'lib/exe.php';