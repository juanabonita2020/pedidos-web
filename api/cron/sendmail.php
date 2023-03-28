<?php 

chdir('..');
require 'lib/kernel.php';

class CRON extends JBSYSWEBCRON
{

protected $_cronId = 'sendmail';
//protected $_logReq = true;

protected function _process($input)
{
	foreach($this->_dbGetAll('web_sendmail', array('status' => 'pendiente')) as $e)
	{
		$this->_logReqData[] = 'Email: '.print_r($e, true);
		$info = array();
		if (($res = $this->_sendmail($e['to'], $e['subject'], $e['body'], $e['smtp'], true, true, $info)) === true)
		{
			$this->_logReqData[] = 'Envío OK';
			$this->_dbDelete('web_sendmail', array('id' => $e['id']));
		}
		else
		{
			$this->_logReqData[] = 'Envío FALLIDO: '.$res;
			// algunos destinatarios fallaron
			if ($info['error'] == 1)
			{
				$failed = array();
				foreach($info['recps'] as $rec => $status)
					if (! $status)
						$failed[] = $rec;
				$this->_dbUpdate('web_sendmail', array('status' => 'fallido', 'debug' => 'Los siguientes destinatarios fueron rechazados: '.implode(', ', $failed)), array('id' => $e['id']));	
			}
			// sin asunto o cuerpo
			else if ($info['error'] == 2)
				$this->_dbDelete('web_sendmail', array('id' => $e['id']));
		}
	}
}

}

require 'lib/cron.php';