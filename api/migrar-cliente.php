<?php require 'lib/kernel.php';

// migra un cliente
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_API);

protected $_input = array(
'id_cli_cliente' => array(
	'required' => true
),
'zona' => array(
	'required' => true
)
);

protected $_logReq = true;

protected function _process($input)
{
	// bloqueo por IP
	if (! empty($this->_global['api_ips_validas']))
	{
		$valid = false;
		foreach(explode(',', str_replace(array(' ', ';'), array(',', ','), trim($this->_global['api_ips_validas']))) as $ip)
			if (trim($ip) == $_SERVER['REMOTE_ADDR'])
			{
				$valid = true;
				break;
			}
		if (! $valid) die;
	}
	
	// obtener pedidos no enviados del cliente
	$orders = $this->_dbQuery('
	SELECT  
		id_web_pedidos id,
		a.id_web_campanias campania
	FROM
		web_pedidos a,
		web_envios b
	WHERE
		a.id_web_envios = b.id_web_envios
		AND estado NOT IN (80, 160)
		AND fecha_envio IS NULL
		AND id_cli_clientes = ?
	', array($input['id_cli_cliente']));

	$res = array();
	
	// procesamos cada orden
	foreach($orders as $o)
	{
		// determinar si existe el nuevo envío
		$r = $this->_dbQuery('SELECT id_web_envios FROM web_envios WHERE fecha_envio IS NULL AND id_web_campanias = ? AND id_cli_zonas = ?', array($o['campania'], $input['zona']));
		
		// si existe, lo tomamos
		if (isset($r[0]))
		{
			$l = 'existente';
			$envio = $r[0]['id_web_envios'];
		}
		else
		{
			$l = 'creado';
			//$envio = 'XXX';
			$envio = $this->_newEnvio($o['campania'], null, $input['zona']);
		}
			
		// cambiamos el envío del pedido
		$this->_dbUpdate('web_pedidos', array('id_web_envios' => $envio), array('id_web_pedidos' => $o['id']));
		//echo '- Pedido:'.$o['id'].', Campaña:'.$o['campania'].', Envío nuevo:'.$envio.' ('.$l.')<br />';
		
		$res[] = array('pedido' => $o['id'], 'campania' => $o['campania'], 'envio' => $l, 'envio_id' => $envio);
	}
	
	$this->_dbUpdate('web_usuarios', array('habilitada' => 1), array('id_cli_clientes' => $input['id_cli_cliente']));
	
	return $res;
}

}

require 'lib/exe.php';