<?php require 'lib/kernel.php';

// cierra un pedido
class APICHGSTATUS extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_EMPRE, JBSYSWEBAPI::USR_REVEN, JBSYSWEBAPI::USR_COORD);

protected $_newStatus;
protected $_oldStatus;
protected $_changed = false;

//~ protected $_logReq = true;

function __construct()
{
	parent::__construct();

	// pasamos de un estado ABIERTO o RECHAZADO a CERRADO
	$this->_oldStatus = array_merge($this->_validOpenedStatus, $this->_validRejectedStatus);
	$this->_newStatus = $this->_closedStatus;
}

// valida si la acción se debe o no procesar
protected function _validateProcess($input)
{
	$params = array($input['numeroPedido']);

	// obtenemos datos del pedido
	$res1 = $this->_dbQuery('SELECT id_cli_zonas, a.id_cli_clientes, a.id_web_campanias, minimo_monto_E, minimo_monto_D, minimo_unidades_E, minimo_unidades_D, numero_cliente, nombre FROM web_pedidos a, web_cache_clientes b, web_campanias c WHERE a.id_cli_clientes = b.id_cli_clientes AND a.id_web_campanias = c.id_web_campanias AND id_web_pedidos = ?', $params);
	//print_r($res1);
	$this->_logReqData[] = 'Cabecera del pedido: '.print_r($res1, true);

	// pedido propio?
	$myOwnOrder = ($res1[0]['id_cli_clientes'] == $this->_userClient);

	//--- Verificar estados de los ítems

	if (empty($input['confirm']) && ! $this->_isItem)
	{
		// Comprobar si hay items abiertos
		$r = $this->_dbGetOne('web_pedidos_detalle', array('id_web_pedidos' => $input['numeroPedido'], 'estado' => JBSYSWEBAPI::OS_CLO_CONSU));
		if (isset($r['id_web_pedidos']))
		{
			$msg = 'El pedido tiene ítems sin autorizar.';
			$confirm = false;
			// si el pedido NO es propio
			if (! $myOwnOrder)
			{
				$msg .= ' ¿Desea cerrar el pedido?';
				$confirm = true;
			}
			return array('msg' => $msg, 'confirm' => $confirm);
		}

		// Comprobar si todos los ítems están rechazados o borrados (sólo si el pedido es propio)
		if (! empty($input['usr1']) && $myOwnOrder)
		{
			$r = $this->_dbQuery('SELECT id_web_pedidos_detalle, estado FROM web_pedidos_detalle WHERE id_web_pedidos = ? AND estado NOT IN (?, ?, ?, ?, ?)', array($input['numeroPedido'], JBSYSWEBAPI::OS_REJ_REVEN, JBSYSWEBAPI::OS_REJ_EMPRE, JBSYSWEBAPI::OS_REJ_COORD, JBSYSWEBAPI::OS_REM_EMPRE, JBSYSWEBAPI::OS_NSN_EMPRE));
				//print_r($r);
			$this->_logReqData[] = 'Items ni rechazados ni borrados: '.print_r($r, true);
			if (! isset($r[0]))
				return array('msg' => 'Todos sus pedidos se encuentran rechazados. Si Ud. cierra ahora, serán descartados. Vuelva a autorizar algunos pedidos antes de cerrar.', 'myOwnOrder' => true);
		}
	}


	// Comprobar si el pedido tiene al menos 1 item en estado válido para cerrar
	$items = $this->_dbQuery('SELECT a.id_web_pedidos_detalle FROM web_pedidos_detalle a, web_cache_articulos b WHERE idArticulo = id_web_cache_articulos AND id_web_pedidos = ? AND estado NOT IN ( ?, ?, ? )  ', array( $input['numeroPedido'], JBSYSWEBAPI::OS_REM_EMPRE, JBSYSWEBAPI::OS_REJ_EMPRE, JBSYSWEBAPI::OS_NSN_EMPRE ) );

	if(	!$items ){
		return array('msg' => 'El pedido del cliente ' . $res1[0]['numero_cliente'] . ' - ' . $res1[0]['nombre'] . ' no puede cerrarse debido a que no tiene items en algún estado válido para realizar esta accción' );
	}


	//--- Comprobar mínimos

	// si el pedido a cerrar es el primero continuar, sino aceptarlo sin controles
	$res = $this->_dbQuery('SELECT id_web_pedidos FROM web_pedidos WHERE id_cli_clientes = ? AND id_web_campanias = ? AND estado <> ? ORDER BY id_web_pedidos LIMIT 1', array($res1[0]['id_cli_clientes'], $res1[0]['id_web_campanias'], JBSYSWEBAPI::OS_REM_EMPRE));
	//print_r($res);die;

	if ($res[0]['id_web_pedidos'] == $input['numeroPedido'])
	{
		// obtenemos el mínimo de monto y unidades asociado a la campaña y tipo de negocio de la empresaria
		$res = $this->_dbGetOne('web_cache_clientes', array('id_cli_zonas' => $res1[0]['id_cli_zonas'], 'numero_cliente' => '1'));
		//print_r($res);
		$min_monto = $res1[0]['minimo_monto_'.$res['negocio']] * 1;
		$min_unidades = $res1[0]['minimo_unidades_'.$res['negocio']] * 1;
		//echo $min_monto.' - '.$min_unidades;

		// sólo controlamos los mínimos si al menos uno de ellos es distinto de 0
		if ($min_monto || $min_unidades)
		{
			// obtenemos el total de unidades y monto del pedido sólo considerando artículos de tipo de venta 1 o 2

//Comentado por Danny para corregir el envío de pedidos que no cumplen con los mínimos
/*
			$res = $this->_dbQuery('SELECT SUM(cantidad) unidades, SUM(cantidad * a.precio) monto FROM web_pedidos_detalle a, web_cache_articulos b WHERE idArticulo = id_web_cache_articulos AND (tipo_venta = 1 OR tipo_venta = 2) AND id_web_pedidos = ?', $params);
*/

			//print_r($res);
			//echo ($min_unidades || $res[0]['unidades'] < $min_unidades) && ($min_monto || $res[0]['monto'] < $min_monto);

//esta es la versión nueva del query
			 $res = $this->_dbQuery('SELECT SUM(cantidad) unidades, SUM(cantidad * a.precio) monto FROM web_pedidos_detalle a, web_cache_articulos b WHERE idArticulo = id_web_cache_articulos AND (tipo_venta = 1 OR tipo_venta = 2 OR tipo_venta = 10 OR tipo_venta = 20 ) AND id_web_pedidos = ? AND estado <> ? ', array( $input['numeroPedido'], JBSYSWEBAPI::OS_REM_EMPRE ) );	


			// verificamos que cumpla el mínimo
			if ((! $min_unidades || $res[0]['unidades'] * 1 < $min_unidades) && (! $min_monto || $res[0]['monto'] * 1 < $min_monto))
				return array('msg' => '<center>Para poder cerrar el pedido de '.$res1[0]['numero_cliente'].'-'.utf8_encode($res1[0]['nombre']).' debe cumplir con '.($min_unidades && $min_monto ? 'al menos uno de estos mínimos' : 'este mínimo').': '.($min_unidades ? 'unidades='.$min_unidades : '').($min_unidades && $min_monto ? ', ' : '').($min_monto ? 'monto='.$min_monto : '').'<br /><br /><small>Sólo se consideran productos de catálogo y feria para los mínimos.</small></center>', 'myOwnOrder' => $myOwnOrder);
		}
	}

	//--- Comprobar estados

	$ok = true;
	$itemChanges = array();
	$noSndItems = array();

	// estados válidos: aceptados, rechazados, cargando, borrado, no enviado
	$validStatus = array_merge($this->_validApprovedStatus, $this->_validRejectedStatus, $this->_validOpenedStatus, array(JBSYSWEBAPI::OS_REM_EMPRE, JBSYSWEBAPI::OS_NSN_EMPRE));
	$this->_logReqData[] = 'Valid status: '.print_r($validStatus, true);

	// si confirma, se acepta el estado el estado 10
	if (! empty($input['confirm'])) $validStatus[] = JBSYSWEBAPI::OS_CLO_CONSU;

	foreach($this->_dbGetAll('web_pedidos_detalle', array('id_web_pedidos' => $input['numeroPedido'])) as $d)
	{
		$this->_logReqData[] = 'Item status: '.$d['estado'];

		// si el ítem no está en un estado válido => cancelar acción
		if (! in_array($d['estado'], $validStatus))
		{
			$ok = false;
			break;
		}
		// sino, cerrar pedido
		else
		{
			$itemChange = array($d['id_web_pedidos_detalle']);

			$this->_logReqData[] = 'User client: '.$this->_userClient;

			// si el pedido es propio
			if ($myOwnOrder)
			{
				$this->_logReqData[] = 'Pedido propio.';
				// determinar el nuevo estado de cada ítem del pedido
				if (in_array($d['estado'], $this->_validApprovedStatus) || in_array($d['estado'], $this->_validOpenedStatus))
					$itemChange[] = $this->_closedStatus[$this->_userType];
				else if (in_array($d['estado'], $this->_validRejectedStatus))
				{
					$itemChange[] = JBSYSWEBAPI::OS_NSN_EMPRE;
					$itemChange[] = date('Y-m-d H:i:s');
					$noSndItems[] = $d['id_web_pedidos_detalle'];
				}
				else
					continue;
			}
			// si el pedido es de terceros y el item esta borrado o no enviado, no lo alteramos
			else if ($d['estado'] == JBSYSWEBAPI::OS_NSN_EMPRE || $d['estado'] == JBSYSWEBAPI::OS_REM_EMPRE)
				continue;
			else
				$itemChange[] = $this->_closedStatus[$this->_userType];

			$itemChanges[] = $itemChange;
		}
	}

	if (! $ok) return false;
	//return false;

	// actualizamos la cabecera para los ítems no enviados
	$this->_logReqData[] = 'No send items: '.print_r($noSndItems, true);
	foreach($noSndItems as $i) $this->_restoreOrderQ($i);

	// actualizamos el estado de los ítems
	$this->_logReqData[] = 'Items changes: '.print_r($itemChanges, true);
	foreach($itemChanges as $i)
	{
		$data = array('estado' => $i[1]);
		if (! empty($i[2])) $data['fecha_descarte'] = $i[2];
		$this->_dbUpdate('web_pedidos_detalle', $data, array('id_web_pedidos_detalle' => $i[0]));
	}

	$this->_extraOutput = array('myOwnOrder' => $myOwnOrder);

	return true;
}

// enviar email
protected function _postProcess($input, &$output)
{
	if (! $this->_changed) return;

	$res = $this->_dbGetOne('web_pedidos', array('id_web_pedidos' => $input['numeroPedido']), array('id_web_usuarios', 'unidades', 'id_web_campanias', 'id_cli_clientes'));
	$this->_logReqData[] = 'Pedido: '.print_r($res, true);

	// sólo enviamos el email si es un pedido propio y tengo activado el envío
	if ($res['id_web_usuarios'] == $this->_userId && $this->_userCloseAlert/* && $this->_userType == JBSYSWEBAPI::USR_REVEN*/)
	{
		//$res2 = $this->_dbGetOne('web_usuarios', array('id_web_usuarios' => $res['id_web_usuarios']));
		//$fullName = $res2['nombre'].' '.$res2['apellido'];
		$res2 = $this->_dbGetOne('web_cache_clientes', array('id_web_cache_clientes' => $res['id_cli_clientes']));
		$fullName = $res2['nombre'];

		$body = '<p>Hola, este es el detalle de tu pedido:</p><ul>
		<li>Vendedora: '.$fullName.'</li>
		<li>Total de unidades: '.$res['unidades'].'</li>
		</ul><p>Items:</p>'.$this->_getHTMLOrderItems($input['numeroPedido'], $res['id_web_campanias'], array(JBSYSWEBAPI::OS_REM_EMPRE, JBSYSWEBAPI::OS_NSN_EMPRE)).'</ul><p><small>Este mensaje fue originado automáticamente. Por favor, no responder al mismo.</small></p>';

		$subject = 'Pedido cerrado de '.$fullName.' '.$this->_userZone.'-'.$res2['numero_cliente'];

		if (empty($this->_global['email_close_pedido']))
			$this->_sendmailToMe($subject, $body, 2);
		else
			$this->_sendmail($this->_global['email_close_pedido'], $subject, $body, 2);
	}
}

}

require 'lib/chgStatus.php';
