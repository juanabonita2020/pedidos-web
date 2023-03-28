<?php require 'lib/kernel.php';

// cambiar la campaña de un pedido
class API extends JBSYSWEBAPI
{

protected $_input = array(
'numeroPedido' => array(
	'required' => true
),
'campania' => array(
	'required' => true
)
);

protected $_logReq = true;

protected function _process($input)
{
	// solo modificar un pedido propio y si la campaña tiene envio abierto o se le puede crear uno nuevo
	if (
	! $this->_validCamp($input['campania'])
	||
	! $this->_isMyOrder($input['numeroPedido'])
	||
	($envioId = $this->_checkCampEnvio($input['campania'])) === false
	) $this->_forbidden();

	$this->_logReqData[] = 'Envío ID: '.$envioId;

	// obtenemos el pedido
	$r = $this->_dbGetOne('web_pedidos', array('id_web_pedidos' => $input['numeroPedido']), array('id_web_campanias', 'estado'));
	$this->_logReqData[] = 'Pedido: '.print_r($r, true);

	// sólo procesar el pedido si está abierto o rechazado
	if (! in_array($r['estado'], $this->_validOpenedStatus) && ! in_array($r['estado'], $this->_validRejectedStatus))
		$this->_forbidden();

	// crear un nuevo envío
	if ($envioId === true)
	{
		$envioId = $this->_newEnvio($input['campania']);
		$this->_logReqData[] = 'Nuevo Envío ID: '.$envioId;
	}

	// armamos la nueva cabecera del pedido
	$update = array('id_web_envios' => $envioId, 'id_web_campanias' => $input['campania'], 'unidades' => 0, 'monto' => 0, 'puntos_total' => 0);

	// procesamos los ítems del pedido
	foreach($this->_getOrderItems($input['numeroPedido'], $r['id_web_campanias'], null, null, $input['campania']) as $i)
	{
		$this->_logReqData[] = 'Item: '.print_r($i, true);

		$updateDet = array();
		$continue = false;

		// item no existe en nueva campaña => pasamos a estado "no enviado"
		if (empty($i['nuevaCampaniaPrecio']))
		{
			//$this->_removeOrderItem($i['idItem']);
			//$this->_dbUpdate('web_pedidos_detalle', array('estado' => self::OS_NSN_EMPRE), array('id_web_pedidos_detalle' => $i['idItem']));
			$updateDet['estado'] = self::OS_NSN_EMPRE;
			$updateDet['fecha_descarte'] = date('Y-m-d H:i:s');
			$continue = true;
		}
		else
		{
			// item existe en nueva campaña, pero con distinto precio => actualizar
			if ($i['nuevaCampaniaPrecio'] != $i['precioDb'])
			{
				//$this->_dbUpdate('web_pedidos_detalle', array('precio' => $i['nuevaCampaniaPrecio']), array('id_web_pedidos_detalle' => $i['idItem']));
				$updateDet['precio'] = $i['nuevaCampaniaPrecio'];
				$i['precioDb'] = $i['nuevaCampaniaPrecio'];
			}

			// recuperamos los items descartados
			if ($i['estado'] == self::OS_NSN_EMPRE)
				$updateDet['estado'] = self::OS_OPE_EMPRE;
		}

		if (count($updateDet))
			$this->_dbUpdate('web_pedidos_detalle', $updateDet, array('id_web_pedidos_detalle' => $i['idItem']));

		if (! $continue)
		{
			$update['unidades'] += $i['cantidad'];
			$update['puntos_total'] += $i['ptos'] * $i['cantidad'];
			$update['monto'] += $i['precioDb'] * $i['cantidad'];
		}
	}

	// actualizamos la cabecera del pedido
	$this->_dbUpdate('web_pedidos', $update, array(
	'id_web_pedidos' => $input['numeroPedido']
	));
}

}

require 'lib/exe.php';