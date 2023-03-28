<?php require 'lib/kernel.php';

// enviar mensaje de registraciones manuales
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN);

protected $_input = array(
'id' => array(
	'required' => true
),
'to' => array(
	'required' => true
),
'subject' => array(
	'required' => true
),
'msg' => array(
	'required' => true
)
);

protected function _process($input)
{
	//foreach(explode(',', $input['to']) as $to)
	//	if ($to = trim($to))
	$this->_sendmail($input['to'], $input['subject'], $input['msg']);
	
	$this->_dbInsert('web_registracion_manual_mensajes', array(
	'id_web_registracion_manual' => $input['id'],
	'to' => $input['to'],
	'subject' => $input['subject'],
	'body' => $input['msg']
	));
	
	$this->_dbUpdate('web_registracion_manual', array('leido' => '1', 'respondido_por' => $this->_userId), array('id_web_registracion_manual' => $input['id']));
}

}

require 'lib/exe.php';