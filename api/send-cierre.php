<?php require 'lib/kernel.php';

// envía pedidos a cierre
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_EMPRE, JBSYSWEBAPI::USR_ADMIN);

protected $_input = array(
'campania' => array(
	'required' => false
),
'usr1' => array( //zona
)
);

protected $_logReq = true;

protected function _process($input)
{
	// comprobar la campaña
	if (! $this->_validCamp($input['campania'])) return false;

	// verificar que la campaña esté vigente
	$r = $this->_dbQuery('SELECT id_web_cache_campanias FROM web_cache_campanias WHERE fecha_inicio <= NOW() AND id_web_cache_campanias = ?', array($input['campania']));
	if (! isset($r[0]))
	{
		$r = $this->_dbQuery('SELECT fecha_inicio FROM web_cache_campanias WHERE id_web_cache_campanias = ?', array($input['campania']));
		return array('msg' => 'Aun no se ha habilitado el envío de pedidos de la campaña seleccionada. Por favor pruebe nuevamente en su fecha de calendario.');
	}

	$now = date('Y-m-d H:i:s');

	$zona = ($this->_userType == JBSYSWEBAPI::USR_ADMIN && ! empty($input['usr1']) ? $input['usr1'] : $this->_userZone);

	// verificamos que el usuario no esté de baja
	if ($this->_userType == JBSYSWEBAPI::USR_ADMIN)
		$r = array('baja' => false);
	else
		$r = $this->_dbGetOne('web_cache_clientes', array('id_cli_clientes' => $this->_userClient), array('baja', 'nombre'));

	// determinamos si este es el último cierre de la campaña
	$cierre = $this->_getCierre($input['campania']);
	//print_r($cierre);die;
	$this->_logReqData[] = 'Cierre: '.print_r($cierre, true);

	$envios = array();
	$clientes = array();
	$found = false;
	$body = 'Hola, aquí la lista de los pedidos enviados a la empresa:<ul>';

	// Obtenemos los pedidos de la zona y campaña cuyos estado sea APROBADO y además todos sus ítems NO estén en estado CERRADO o ABIERTO
	if (($orders = $this->_dbQuery('
	SELECT DISTINCT a.id_web_pedidos, id_web_envios, numero_cliente, nombre, unidades, monto, a.id_cli_clientes
	FROM web_pedidos a, web_cache_clientes b, web_pedidos_detalle c
	WHERE
	a.id_cli_clientes = b.id_cli_clientes
	AND a.id_web_pedidos = c.id_web_pedidos
	AND a.estado IN (?, ?, ?) AND c.estado NOT IN (?, ?, ?, ?, ?, ?, ?)
	AND id_cli_zonas = ? AND id_web_campanias = ?
	', array(
	JBSYSWEBAPI::OS_APP_REVEN, JBSYSWEBAPI::OS_APP_EMPRE, JBSYSWEBAPI::OS_APP_COORD,
	JBSYSWEBAPI::OS_OPE_REVEN, JBSYSWEBAPI::OS_CLO_REVEN, JBSYSWEBAPI::OS_OPE_EMPRE, JBSYSWEBAPI::OS_CLO_EMPRE, JBSYSWEBAPI::OS_OPE_COORD, JBSYSWEBAPI::OS_CLO_COORD, JBSYSWEBAPI::OS_CLO_CONSU,
	$zona, $input['campania']
	))) !== false){
		foreach($orders as $o){ 
			if ($r['baja']){ 
				// actualizamos la fecha de envío del envío relacionado al pedido
				$this->_dbQuery('UPDATE web_envios SET fecha_intento_envio = ? WHERE id_web_envios = ?', array($now, $o['id_web_envios']));
			}
			else
			{
				// por cada orden encontrada ponemos su estado como ENVIADO
				$this->_dbUpdate('web_pedidos', array('estado' => JBSYSWEBAPI::OS_SEN_EMPRE), array('id_web_pedidos' => $o['id_web_pedidos']));
	




/***************************************************************************************************************************************/


	                //COMENTADO PORQUE SE CAMBIÓ LA DEFINICIÓN DE CUANDO SE PASA UN ÍTEM A "ENVIADO"                


				$items = $this->_dbQuery('SELECT id_web_pedidos_detalle, cod11, cantidad FROM web_pedidos_detalle WHERE id_web_pedidos = ? AND estado NOT IN (?, ?, ?, ?, ?)', array( $o['id_web_pedidos'], JBSYSWEBAPI::OS_REJ_REVEN, JBSYSWEBAPI::OS_REJ_EMPRE, JBSYSWEBAPI::OS_REJ_COORD, JBSYSWEBAPI::OS_REM_EMPRE, JBSYSWEBAPI::OS_NSN_EMPRE) );
	 
	          	foreach($items as $item){
	                  
	                  $stock = $this->_dbGetOne('web_cache_articulos_stock', array('codigo11' => $item['cod11']));

	                  if($stock  && $stock['stock_ilimitado'] != 1){
	                      $reservaNueva = $stock['reserva'] - $item['cantidad'];
	                      $enviadoNuevo = $stock['enviado'] + $item['cantidad'];
	                      $this->_dbQuery('UPDATE web_cache_articulos_stock SET reserva = ?, enviado = ? WHERE id_web_cache_articulos_stock = ?', array( $reservaNueva, $enviadoNuevo, $stock['id_web_cache_articulos_stock'] ) );
	                  }

	        	}




/***************************************************************************************************************************************/




				// actualizamos el estado de los ítems como ENVIADO siempre y cuando no estén rechazados ni borrados
				$this->_dbQuery('UPDATE web_pedidos_detalle SET estado = ? WHERE id_web_pedidos = ? AND estado NOT IN (?, ?, ?, ?, ?)', array(JBSYSWEBAPI::OS_SEN_EMPRE, $o['id_web_pedidos'], JBSYSWEBAPI::OS_REJ_REVEN, JBSYSWEBAPI::OS_REJ_EMPRE, JBSYSWEBAPI::OS_REJ_COORD, JBSYSWEBAPI::OS_REM_EMPRE, JBSYSWEBAPI::OS_NSN_EMPRE));

				// actualizamos la fecha de envío del envío relacionado al pedido
				$this->_dbQuery('UPDATE web_envios SET fecha_envio = ? WHERE id_web_envios = ?', array($now, $o['id_web_envios']));

				// acumulamos aquí la lista de envíos enviados
				if (! in_array($o['id_web_envios'], $envios)) $envios[] = $o['id_web_envios'];

				// acumulamos aquí la lista de clientes
				if (! in_array($o['id_cli_clientes'], $clientes)) $clientes[] = $o['id_cli_clientes'];

				// armamos el email
				$body .= '<li>Nro de cliente: '.$o['numero_cliente'].', Nombre: '.$o['nombre'].', Unidades: '.$o['unidades'].'</li>';

				$found = true;
			}
		}	
	}

	if ($r['baja'])
	{
		$r2 = $this->_dbGetOne('web_cache_clientes', array('id_cli_zonas' => $zona), array('region'));
		$r2 = $this->_dbGetOne('web_usuarios', array('region' => $r2['region']), array('mail'));
		$this->_sendmail($this->_config['notifyControlEnvios']/*.','.$r2['mail'].','.($this->_userNegocio == 'D' ? $this->_config['notifyBlockedZoneD'] : $this->_config['notifyBlockedZoneE'])*/, 'Cierre de usuario bloqueado - Negocio '.$this->_userNegocio.' - Zona '.$zona, 'El usuario '.$this->_userName.' (zona: '.$zona.', cliente: '.$r['nombre'].') intentó enviar sus pedidos y no pudo hacerlo por estar bloqueado.');
		return array('msg' => 'Su usuario se encuentra temporalmente bloqueado para realizar el Envío de Pedidos a la empresa. En general, se debe a que en nuestro sistema figura una deuda, o porque su Zona no presenta actividad hace mucho tiempo.<b>Su Gerenta fue notificada automáticamente para que se contacte con Ud. y la ayude a regularizar su situación</b>. Disculpe las molestias.');
	}

	if (! $found) return array('msg' => 'Todos los pedidos de la zona-campaña no están aprobados o tienen ítems abiertos o cerrados');

	//$this->_log('send-cierre', 'Envios:'.print_r($envios, true).', Clientes:'.print_r($clientes, true));

	//print_r($envios);die;

	$q = 'UPDATE web_pedidos SET ';

	// procesamos los pedidos de los envíos enviados que no hayan sido puestos como ENVIADOS
	foreach($envios as $e)
	{
		$where = ' WHERE id_web_envios = ? AND estado <> ?';
		$params = array($e, JBSYSWEBAPI::OS_SEN_EMPRE);

		// pedidos del envío no enviados
		$noSndOrders = $this->_dbQuery('SELECT id_web_pedidos, estado FROM web_pedidos '.$where, $params);
		//print_r($noSndOrders);die;

		if (isset($noSndOrders[0]))
		{
			// si es el último cierre
			if ($cierre['lastCierre'])
			{
				// los items de los pedidos no enviados los ponemos también como no enviados
				foreach($noSndOrders as $p)
				{


/*****************************************************************************************************************************************************************/

					//Buscamos todos los items del pedido que no estén en estado eliminado
					$items = $this->_dbQuery('SELECT id_web_pedidos_detalle, cod11, cantidad FROM web_pedidos_detalle WHERE id_web_pedidos = ? AND estado <> ?', array($p['id_web_pedidos'],JBSYSWEBAPI::OS_REM_EMPRE) );
 
					foreach($items as $item){
					      
					      //$r = $this->_dbGetOne('web_pedidos_detalle', array( 'id_web_pedidos_detalle' => $item['id_web_pedidos_detalle'] ));
					      $stock = $this->_dbGetOne('web_cache_articulos_stock', array('codigo11' => $item['cod11']));

					      //Verificamos si tiene stock y lo ajustamos en caso positivo
					      if($stock  && $stock['stock_ilimitado'] != 1){
					          $reservaNueva = $stock['reserva'] - $item['cantidad'];
					          $cantidadTotalNueva = $stock['cantidad_total'] + $item['cantidad'];
					          $this->_dbQuery('UPDATE web_cache_articulos_stock SET reserva = ?, cantidad_total = ? WHERE id_web_cache_articulos_stock = ?', array( $reservaNueva, $cantidadTotalNueva, $stock['id_web_cache_articulos_stock'] ) );
					      }

					}

/*****************************************************************************************************************************************************************/




					$this->_logReqData[] = 'Pedido no enviado: '.print_r($p, true);
					$this->_logReqData[] = 'Items del pedido: '.print_r($this->_dbQuery('SELECT id_web_pedidos_detalle, estado FROM web_pedidos_detalle WHERE id_web_pedidos = ?', array($p['id_web_pedidos'])), true);
					//$this->_dbUpdate('web_pedidos_detalle', array('estado' => JBSYSWEBAPI::OS_NSN_EMPRE), array('id_web_pedidos' => $p['id_web_pedidos']));
					$this->_dbQuery('UPDATE web_pedidos_detalle SET estado = ?, fecha_descarte = ? WHERE id_web_pedidos = ? AND estado <> ?', array(JBSYSWEBAPI::OS_NSN_EMPRE, date('Y-m-d H:i:s'), $p['id_web_pedidos'], JBSYSWEBAPI::OS_REM_EMPRE));
				}

				// colocamos los pedidos del envío como no enviados
				array_unshift($params, JBSYSWEBAPI::OS_NSN_EMPRE);
				$q .= 'estado = ?, unidades = 0, monto = 0, puntos_total = 0';
			}
			// si NO es el último cierre
			else
			{
				// creamos un nuevo envío para la zona-campaña
				//$this->_dbInsert('web_envios', array('id_cli_zonas' => $zona, 'id_web_usuarios' => $this->_userId, 'id_web_campanias' => $input['campania']));
				$envioId = $this->_newEnvio($input['campania']);

				// asociamos los pedidos no enviados a este nuevo envío
				array_unshift($params, $envioId/*$this->_dbInsertId*/);
				$q .= 'id_web_envios = ?';
			}

			$this->_dbQuery($q.$where, $params);
		}
	}

	// actualizamos la cantidad de pedidos abiertos
	foreach($clientes as $clientId) $this->_updateOpenedOrders($clientId);

	// si es el último cierre asociamos una nueva campaña a la zona
	if ($cierre['lastCierre'])
	{
		// buscamos la próxima campaña habilitada
		$r = $this->_dbQuery('SELECT id_web_campanias FROM web_campanias WHERE habilitado AND id_web_campanias > ? ORDER BY id_web_campanias LIMIT 1', array($input['campania']));
		if (isset($r[0]))
			$this->_dbUpdate('web_campanias_zonas', array('id_web_campanias' => $r[0]['id_web_campanias']), array('id_cli_zonas' => $zona));
	}

	$body .= '</ul><p><small>Este mensaje fue originado automáticamente. Por favor, no responder al mismo.</small></p>';

	// enviar email

	if (empty($this->_global['email_send_cierre']))
		$this->_sendmailToMe('Resumen de los pedidos enviados a la empresa', $body, 2);
	else
		$this->_sendmail($this->_global['email_send_cierre'], 'Detalle de los pedidos enviados - '.$input['campania'], $body, 2);
}

}

require 'lib/exe.php';
