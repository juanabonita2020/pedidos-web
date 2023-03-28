<?php require 'lib/kernel.php';

// Agrega un ítem a un pedido
class API extends JBSYSWEBAPI
{

protected $_input = array(
'idCliente' => array(
	'required' => true
),
'campania' => array(
	'required' => true
),
'articulos' => array(
	'required' => true
),
'idPromocion' => array(
	'required' => true
),
'soutien' => array(
),
'bombacha' => array(
),
'inferior' => array(
),
'superior' => array(
),
'jean' => array(
),
'mail' => array(
),
'nombre' => array(
),
'dni' => array(
),
'tel' => array(
),
'numeroPedido' => array(
)
);

private $_art;
private $_oClientId;
private $_header;
private $_onlySaveTalles;
private $_premios;

protected $_logReq = true;

protected function _validateProcess($input)
{
	// comprobar la campaña
	if (! $this->_validCamp($input['campania'], false, true)) return false;

		// comprobar si la campaña tiene habilitado el nuevo esquema de premios
	$this->_premios = $this->_checkCampPrem($input['campania']);
	
	// determinamos el cliente a asociar el pedido
	$this->_oClientId = (($this->_userType == JBSYSWEBAPI::USR_EMPRE || $this->_userType == JBSYSWEBAPI::USR_COORD) && ! empty($input['idCliente']) ? $input['idCliente'] : $this->_userClient);
	$this->_logReqData[] = 'clientId: '.$this->_oClientId;

	// verificamos que el usuario tenga permiso sobre el cliente
	if (! $this->_isMyClient(trim($this->_oClientId))) $this->_forbidden();

	// obtenemos el artículo
	if (! empty($input['articulos']))
	{
		$arts = explode(",",$input['articulos']);
		$this->_art = array();

		foreach ($arts as $key => $value) {
			$this->_art[] = $this->_dbGetOne('web_cache_articulos', array('id_web_cache_articulos' => $value));			
		}

/*
		foreach ($this->_art as $key) {
			var_dump($key["cod11"]);
		}


		var_dump($this->_art);
		die();
*/
//		$this->_art = $this->_dbGetOne('web_cache_articulos', array('id_web_cache_articulos' => $input['idArticulo']));
		$this->_logReqData[] = 'Art: '.print_r($this->_art, true);
	
	}

	// buscamos la cabecera
	//$this->_header = $this->_dbQuery('SELECT * FROM web_pedidos WHERE id_cli_clientes = ? AND estado IN (?, ?, ?, ?) AND id_web_campanias = ? LIMIT 1', array($this->_oClientId, JBSYSWEBAPI::OS_OPE_EMPRE, JBSYSWEBAPI::OS_OPE_REVEN, JBSYSWEBAPI::OS_OPE_COORD, JBSYSWEBAPI::OS_CLO_CONSU, $input['campania']));
	$this->_header = $this->_dbQuery('SELECT * FROM web_pedidos WHERE id_cli_clientes = ? AND estado IN (?, ?, ?, ?, ?, ?, ?) AND id_web_campanias = ? LIMIT 1', array($this->_oClientId, JBSYSWEBAPI::OS_OPE_EMPRE, JBSYSWEBAPI::OS_OPE_REVEN, JBSYSWEBAPI::OS_OPE_COORD, JBSYSWEBAPI::OS_CLO_CONSU, JBSYSWEBAPI::OS_REJ_REVEN, JBSYSWEBAPI::OS_REJ_EMPRE, JBSYSWEBAPI::OS_REJ_COORD, $input['campania']));
	$this->_logReqData[] = 'Cabecera: '.print_r($this->_header, true);

	
	return true;
}

protected function _process($input)
{
	// con el nuevo esquema de premios estos datos se guardan NULL, sino con el valor ingresado
	$orderData = array(
	'soutien_talle' => $this->_premios ? null : $input['soutien'],
	'bombacha_talle' => $this->_premios ? null : $input['bombacha'],
	'inferior_talle' => $this->_premios ? null : $input['inferior'],
	'superior_talle' => $this->_premios ? null : $input['superior'],
	'jean_talle' => $this->_premios ? null : $input['jean']
	);

	if ($this->_onlySaveTalles)
	{
		$orderId = $input['numeroPedido'];
		$idItem = 0;
	}
	else
	{
		$found = false;

	// si se encuentra la cabecera...
		if (isset($this->_header[0]))
		{
			$orderId = $this->_header[0]['id_web_pedidos'];
			$totalQ = $this->_header[0]['unidades'];
			$totalAmount = $this->_header[0]['monto'];
			$ptosTotal = $this->_header[0]['puntos_total'];
			$campania = $this->_header[0]['id_web_campanias'];

			

		}
		// sino se encuentra la cabecera...
		else
		{
			$campania = $input['campania'];

			// buscamos un envío para la zona-campaña
			$r = $this->_dbQuery('SELECT id_web_envios FROM web_envios WHERE fecha_envio IS NULL AND id_cli_zonas = ? AND id_web_campanias = ?', array($this->_userZone, $campania));

			$this->_logReqData[] = 'Envío: '.print_r($r, true);

			// si encontramos el envío, lo usamos
			if (isset($r[0]['id_web_envios']))
				$envioId = $r[0]['id_web_envios'];
			// sino, creamos uno nuevo
			else
			{
				// verificamos si este pedido será el primero del envío
				$r = $this->_dbQuery('SELECT COUNT(*) c FROM web_pedidos WHERE id_cli_clientes = ? AND id_web_campanias = ?', array($this->_oClientId, $campania));

				$this->_logReqData[] = 'Cant. de pedidos en el envío: '.print_r($r, true);

				// creamos el envío
				$envioId = $this->_newEnvio($campania, $r[0]['c'] > 0 ? 'agregado' : 'pedido');
				
			}

			$this->_logReqData[] = 'Envío ID: '.$envioId;

			$userType = ($this->_userType == JBSYSWEBAPI::USR_CONSU ? $this->_getUserType($this->_oClientId) : $this->_userType);

			$data = array(
			'id_web_usuarios' => $this->_userId,
			'id_cli_clientes' => $this->_oClientId,
			'id_web_campanias' => $campania,
			'es_feria' => 0,
			'estado' => $this->_openedStatus[$userType],
			'id_web_envios' => $envioId
			);

			$this->_logReqData[] = 'Cabecera nueva: '.print_r($data, true);

			// creamos la cabecera
			$this->_dbInsert('web_pedidos', $data);

			$orderId = $this->_dbInsertId;
			$totalQ = 0;
			$totalAmount = 0;

			$this->_dbQuery('UPDATE web_pedidos SET fecha_carga = NOW() WHERE id_web_pedidos = ?', array($orderId));

			// actualizamos la cantidad de pedidos abiertos
			$this->_updateOpenedOrders($this->_oClientId);
		}

		$this->_logReqData[] = 'Pedido ID: '.$orderId;

		// si el usuario es una consumidora
		if ($this->_userType == JBSYSWEBAPI::USR_CONSU)
		{
			// buscamos si esta en contactos
			$data = array('mail' => $input['mail']);
			$res = $this->_dbGetOne('web_contactos', $data);
			$data['nombre'] = $input['nombre'];
			$data['dni'] = $input['dni'];
			$data['tel'] = $input['tel'];

			// si existe actualizamos
			if (isset($res['id_web_contactos']))
				$this->_dbUpdate('web_contactos', $data, array('id_web_contactos' => $res['id_web_contactos']));
			// sino, lo insertamos
			else
			{
				$data['clienta'] = $this->_oClientId;
				$data['zona'] = $this->_userZone;
				$this->_dbInsert('web_contactos', $data);
			}

			$this->_logReqData[] = 'Datos en contactos: '.print_r($data, true);
		}

		// si el ítem es nuevo...
		if (! $found)
		{


			//PRIMERO CONTROLO CADA ITEM QUE TENGA STOCK PORQUE AL SER 2 ARTÍCULOS, NECESITO VALIDAR EL STOCK DE AMBOS ARTÍCULOS ANTES DE DESCONTAR EL STOCK
			foreach ($this->_art as $k ) {

		          $stock = $this->_dbGetOne('web_cache_articulos_stock', array('codigo11' => $k['cod11']));

		          if($stock  && $stock['stock_ilimitado'] != 1 ){

		              $reservaNueva = $stock['reserva'];
		              $cantidadTotalNueva = $stock['cantidad_total'];

		              $reservaNueva += 1;
		              $cantidadTotalNueva -= 1;

		              if( ( $stock['cantidad_inicial'] - $reservaNueva - $stock['enviado']) < 0 ){

		                  return array('msgError' => 'En estos momentos el art&iacute;culo '. $k['cod11'] .' no cuenta con suficiente stock. Te invitamos a ingresar nuevamente en los pr&oacute;ximos d&iacute;as para concretar tu compra' );
		              }
		              
		          }

		    }

			//LUEGO, UNA VEZ QUE YA CONTROLÉ EL STOCK, DESCUENTO LA CANTIDAD SOLICITADA
			foreach ($this->_art as $k ) {
        
		          $stock = $this->_dbGetOne('web_cache_articulos_stock', array('codigo11' => $k['cod11']));

		          if($stock  && $stock['stock_ilimitado'] != 1 ){

		              $reservaNueva = $stock['reserva'];
		              $cantidadTotalNueva = $stock['cantidad_total'];

		              $reservaNueva += 1;
		              $cantidadTotalNueva -= 1;

		              if( ( $stock['cantidad_inicial'] - $reservaNueva - $stock['enviado']) >= 0 ){

		                  $this->_dbQuery('UPDATE web_cache_articulos_stock SET reserva = ?, cantidad_total = ? WHERE id_web_cache_articulos_stock = ?', array( $reservaNueva, $cantidadTotalNueva, $stock['id_web_cache_articulos_stock'] ) );
		              }
		              else{

		                  return array('msgError' => 'En estos momentos el art&iacute;culo '. $k['cod11'] .' no cuenta con suficiente stock. Te invitamos a ingresar nuevamente en los pr&oacute;ximos d&iacute;as para concretar tu compra' );
		              }    
		          }

		    }


			$promocion = $this->_dbGetOne('web_cache_promocion', array('id_web_cache_promocion' => $input["idPromocion"]));			


			$this->_dbInsert('web_promocion_relacion', array(
					        'id_web_cache_promocion' => $promocion["id_web_cache_promocion"],
					        'fecha_alta' => date("Y-m-d H:i:s")
	        ));
	        $idRelacion = $this->_dbInsertId;
	        $precioAcumulado = 0;

			foreach ($this->_art as $k ) {
				
			
					$precio = $k['precio'];
					$puntos = $k['puntos'];

					$data = array(
					'id_web_pedidos' => $orderId,
					'idArticulo' => $k['id_web_cache_articulos'],
					'cod11' => $k['cod11'],
					'code' => $k['Code'],
					'tipo' => $k['Tipo'],
					'color' => $k['Color'],
					'talle' => $k['Talle'],
					'cantidad' => 1,
					'estado' => $this->_openedStatus[$this->_userType],
					'es_feria' => $k['feria'],
					'precio' => $precio,
					'ptos_unitarios' => $puntos,
					'id_web_promocion_relacion' => $idRelacion
					);

					$precioAcumulado = $precioAcumulado + $precio;

					if ($this->_userType == JBSYSWEBAPI::USR_CONSU)
					{
						$data['estado'] = JBSYSWEBAPI::OS_CLO_CONSU;
						$data['compradora'] = $input['mail'];
					}

					$this->_logReqData[] = 'Datos nuevos en detalle: '.print_r($data, true);

					// insertamos el ítem en el detalle
					$this->_dbInsert('web_pedidos_detalle', $data);

					$idItem = $this->_dbInsertId;

					$this->_dbQuery('UPDATE web_pedidos_detalle SET fecha_alta = NOW() WHERE id_web_pedidos_detalle = ?', array($idItem));

			}	

		}

	
		$this->_logReqData[] = 'Item ID: '.$idItem;

		if( isset($this->_header[0]['puntos_total']) ){
			$ptosTotal = $this->_header[0]['puntos_total'];
		}
		else{
			$ptosTotal = 0;
		}

		$orderData = array_merge($orderData, array(
		'unidades' => $totalQ + count($this->_art),
		'monto' => $totalAmount + $precioAcumulado,
		'puntos_total' => $ptosTotal + count($this->_art) * $puntos
		));
	}

	$this->_logReqData[] = 'Datos actualizados en cabecera: '.print_r($orderData, true);

	// actualizamos el encabezado
	$this->_dbUpdate('web_pedidos', $orderData, array(
	'id_web_pedidos' => $orderId
	));


		
	return array('idItem' => $idItem, 'numeroPedido' => $orderId);

	
}

}

require 'lib/exe.php';