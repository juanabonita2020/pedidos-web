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
'idArticulo' => array(
//	'required' => true
),
'cantidad' => array(
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
),
'premios' => array(
),
'premioInc' => array(
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
	// verificar máximo de unidades por ítem
	if (! empty($this->_global['max_unid_item']) && $input['cantidad'] > $this->_global['max_unid_item'])
		return false;
	
	// comprobar la campaña
	if (! $this->_validCamp($input['campania'], false, true)) return false;

	// comprobar si la campaña tiene habilitado el nuevo esquema de premios
	$this->_premios = $this->_checkCampPrem($input['campania']);
	
	// se debe especificar el artículo o bien todos los talles (sólo si usa el esquema viejo de premios)
	if (! $this->_premios && empty($input['idArticulo']) && (empty($input['soutien']) || empty($input['bombacha']) || empty($input['inferior']) || empty($input['superior']) || empty($input['jean']) || empty($input['numeroPedido'])))
		return false;

	// determinamos el cliente a asociar el pedido
	$this->_oClientId = (($this->_userType == JBSYSWEBAPI::USR_EMPRE || $this->_userType == JBSYSWEBAPI::USR_COORD) && ! empty($input['idCliente']) ? $input['idCliente'] : $this->_userClient);
	$this->_logReqData[] = 'clientId: '.$this->_oClientId;

	// verificamos que el usuario tenga permiso sobre el cliente
	if (! $this->_isMyClient(trim($this->_oClientId))) $this->_forbidden();

	// obtenemos el artículo
	if (! empty($input['idArticulo']))
	{
		$this->_art = $this->_dbGetOne('web_cache_articulos', array('id_web_cache_articulos' => $input['idArticulo']));
		$this->_logReqData[] = 'Art: '.print_r($this->_art, true);
	}

	// buscamos la cabecera
	//$this->_header = $this->_dbQuery('SELECT * FROM web_pedidos WHERE id_cli_clientes = ? AND estado IN (?, ?, ?, ?) AND id_web_campanias = ? LIMIT 1', array($this->_oClientId, JBSYSWEBAPI::OS_OPE_EMPRE, JBSYSWEBAPI::OS_OPE_REVEN, JBSYSWEBAPI::OS_OPE_COORD, JBSYSWEBAPI::OS_CLO_CONSU, $input['campania']));
	$this->_header = $this->_dbQuery('SELECT * FROM web_pedidos WHERE id_cli_clientes = ? AND estado IN (?, ?, ?, ?, ?, ?, ?) AND id_web_campanias = ? LIMIT 1', array($this->_oClientId, JBSYSWEBAPI::OS_OPE_EMPRE, JBSYSWEBAPI::OS_OPE_REVEN, JBSYSWEBAPI::OS_OPE_COORD, JBSYSWEBAPI::OS_CLO_CONSU, JBSYSWEBAPI::OS_REJ_REVEN, JBSYSWEBAPI::OS_REJ_EMPRE, JBSYSWEBAPI::OS_REJ_COORD, $input['campania']));
	$this->_logReqData[] = 'Cabecera: '.print_r($this->_header, true);

	// si es feria, comprobar si hay stock
	if (! empty($input['idArticulo']) && $this->_art['feria'])
	{
		$stockOk = false;
		$cantidad = $input['cantidad'];

		// si se encuentra la cabecera...
		if (isset($this->_header[0]))
		{
			$r = $this->_dbQuery('SELECT SUM(cantidad) c FROM web_pedidos_detalle WHERE idArticulo = ?', array($input['idArticulo']));
			// si ya existe el item en el pedido
			if (isset($r[0]))
			{
				$this->_logReqData[] = 'Cantidad existente en el pedido: '.$r[0]['c'];
				if ($r[0]['c'] >= $cantidad)
					$stockOk = true;
				else
					$cantidad -= $r[0]['c'];
			}
		}

		if (! $stockOk)
		{
			$r = $this->_dbGetOne('web_cache_feria_stock', array('cod11' => $this->_art['cod11']));
			$this->_logReqData[] = 'Stock necesario: '.$cantidad.', stock real:'.$r['stock'];
			if ($r['stock'] < $cantidad) return false;
		}
	}

	// si no se especifica el artículo es porque estamos sólo guardando los talles
	$this->_onlySaveTalles = (empty($input['idArticulo']));

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

		$stockAmt = -$input['cantidad'];

		// si se encuentra la cabecera...
		if (isset($this->_header[0]))
		{
			$orderId = $this->_header[0]['id_web_pedidos'];
			$totalQ = $this->_header[0]['unidades'];
			$totalAmount = $this->_header[0]['monto'];
			$ptosTotal = $this->_header[0]['puntos_total'];
			$campania = $this->_header[0]['id_web_campanias'];


			/************************************************************  CAMBIO PROMOCIONES *********************************************************/

/*			
			$where = array('id_web_pedidos' => $orderId, 'idArticulo' => $input['idArticulo']);

			if ($this->_art['feria'])
			{
				$where['es_feria'] = 1;
				$where['compradora'] = ($this->_userType == JBSYSWEBAPI::USR_CONSU ? $input['mail'] : null);
			}

			$this->_logReqData[] = 'Detalle WHERE: '.print_r($where, true);

			// buscamos si el item ya existe en el pedido
			if (($r = $this->_dbGetOne('web_pedidos_detalle', $where)) !== false && in_array($r['estado'], array_merge($this->_validOpenedStatus, array(JBSYSWEBAPI::OS_REM_EMPRE))))
			{
*/			

			$query = "  SELECT  d.estado, d.cantidad, d.cantidad_preventa, d.muestrario, d.talle, d.code, d.color, d.tipo, d.cod11,
			                	 d.idArticulo, d.compradora, d.fecha_alta, d.es_feria, d.id_web_pedidos_detalle, d.id_web_pedidos,
				                 d.ptos_unitarios, d.precio, d.cuotas, d.id_web_promocion_relacion
			            FROM 	 web_pedidos_detalle d 
			            WHERE    d.id_web_promocion_relacion IS NULL AND d.id_web_pedidos = ? AND d.idArticulo = ? AND d.estado IN (?,?,?) ";	

			$params = array( $orderId, $input['idArticulo'] );          
			$params = array_merge($params,$this->_validOpenedStatus );

			if ($this->_art['feria']){
			        $query .= " d.es_feria = ? AND d.compradora = ? ";
			        array_push($params, 1);
			        array_push($params, ($this->_userType == JBSYSWEBAPI::USR_CONSU ? $input['mail'] : null) );
//			        $where['es_feria'] = 1;
//			        $where['compradora'] = ($this->_userType == JBSYSWEBAPI::USR_CONSU ? $input['mail'] : null);
	      	}            

	      	$r = $this->_dbQuery($query, $params);

	   	 	if ($r !== false && count( $r ) > 0 && in_array($r[0]['estado'], array_merge($this->_validOpenedStatus, array(JBSYSWEBAPI::OS_REM_EMPRE))) && $r[0]['id_web_promocion_relacion'] == null ) {

/************************************************************  CAMBIO PROMOCIONES *********************************************************/
				
				$this->_logReqData[] = 'Item encontrado en detalle: '.print_r($r, true);

				$found = true;
				$idItem = $r[0]['id_web_pedidos_detalle'];

				$stockAmt += $r[0]['cantidad'];

				$data = array(
				'cantidad' => $input['cantidad']
				);
				
				// si el ítem fue borrado restaurar su estado
				if ($r[0]['estado'] == JBSYSWEBAPI::OS_REM_EMPRE)
					$data['estado'] = $this->_openedStatus[$this->_userType];




/***************************************************************************************************************************************/

				//Excluímos las zapatos de la preventa

			    $articulo = $this->_dbGetOne('web_cache_articulos', array( 'cod11' => $r[0]['cod11'], 'id_web_campanias' => $input['campania']  ));
			    if( $articulo['tipo_venta'] != 10  && $articulo['tipo_venta'] != 20  ){
					$this->_calcUnidPrev($input['campania'], $data, $r[0]['cantidad_preventa']);
				}	

				//reemplazado por el condicional de arriba

				// calculamos las unidades de preventa
				/*$cancelPreventa = */ /*  $this->_calcUnidPrev($input['campania'], $data, $r['cantidad_preventa']); */

                           /*Nueva validación para poner fecha tope a la carga de calzados*/     
                            if( $articulo['tipo_venta'] == 10 ){    	

                                $cli = $this->_dbGetOne('web_cache_clientes', array('id_web_cache_clientes' => $this->_oClientId ));  

                                $diaCalendario = $this->_dbGetOne('web_cache_campanias_dia_calendario', array('id_web_campanias' => $input['campania'], 'region' => $cli['region'] ));

                                if($diaCalendario){
                                    if( !is_null($diaCalendario['fecha_tope_carga']) ){	
                                        $fechaActual = date("Y-m-d H:i:s");
                                        if($fechaActual >= $diaCalendario['fecha_tope_carga']){
                                             return array('msgError' => ' Se ha superado la fecha y hora l&iacute;mite para agregar art&iacute;culos de calzado para esta campaña.  Solicite este art&iacute;culo en la siguiente oportunidad'  );
                                        }
                                    } 
                                }
                            }    
                                

/***************************************************************************************************************************************/


				if ($this->_userType == JBSYSWEBAPI::USR_CONSU)
					$data['compradora'] = $input['mail'];

				$this->_logReqData[] = 'Datos actualizados en detalle: '.print_r($data, true);




/***************************************************************************************************************************************/


	/*      actualización de cantidad de un tiem dentro del pedido    */
      //si el item está borrado no hay q restarle la cantidad


      $r = $this->_dbGetOne('web_pedidos_detalle', array( 'id_web_pedidos_detalle' => $idItem ));
      $stock = $this->_dbGetOne('web_cache_articulos_stock', array('codigo11' => $r['cod11']));

      if($stock && $stock['stock_ilimitado'] != 1){

          $reservaNueva = $stock['reserva'];
          $cantidadTotalNueva = $stock['cantidad_total'];

          if(  $r['estado'] != JBSYSWEBAPI::OS_REM_EMPRE ){
              $reservaNueva -= $r['cantidad'];
              $cantidadTotalNueva += $r['cantidad'];
          } 

          $reservaNueva += $input['cantidad'];
          $cantidadTotalNueva -= $input['cantidad'];

          if( ( $stock['cantidad_inicial'] - $reservaNueva - $stock['enviado']) >= 0 ){

              $this->_dbQuery('UPDATE web_cache_articulos_stock SET reserva = ?, cantidad_total = ? WHERE id_web_cache_articulos_stock = ?', array( $reservaNueva, $cantidadTotalNueva, $stock['id_web_cache_articulos_stock'] ) );
          }
          else{

          		$this->_guardarSolicitudArticuloFaltante($input['campania'], $this->_art['cod11']);

              return array('msgError' => 'En estos momentos el art&iacute;culo '. $r['cod11'] .' no cuenta con suficiente stock. Te invitamos a ingresar nuevamente en los pr&oacute;ximos d&iacute;as para concretar tu compra' );
          }    
      }




/***************************************************************************************************************************************/



				// agregamos el ítem al pedido
				$this->_dbUpdate('web_pedidos_detalle', $data, array(
				'id_web_pedidos_detalle' => $idItem
				));

				// si se cancela la preventa, entonces todos los items del pedido pierden la preventa
				/*if ($cancelPreventa)
					$this->_dbUpdate('web_pedidos_detalle', array('cantidad_preventa' => 0), array(
					'id_web_pedidos' => $orderId
					));*/

				$precio = $r['precio'];
				$puntos = $r['ptos_unitarios'];

				if ($r['estado'] != JBSYSWEBAPI::OS_REM_EMPRE)
				{
					$totalQ -= $r['cantidad'];
					$totalAmount -= $r['cantidad'] * $precio;
					$ptosTotal -= $r['cantidad'] * $puntos;	
				}
			}
		}
		// sino se encuentra la cabecera...
		else
		{
			if ($this->_art['feria'])
			{
				$r = $this->_dbGetOne('web_campanias_zonas', array('id_cli_zonas' => $this->_userZone));
				$campania = $r['id_web_campanias'];
			}
			else
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
				/*$this->_dbInsert('web_envios', array(
				'id_cli_zonas' => $this->_userZone,
				'id_web_campanias' => $campania,
				'id_web_usuarios' => $this->_userId,
				'tipo_pedido' => $r[0]['c'] > 0 ? 'agregado' : 'pedido'
				));
				$envioId = $this->_dbInsertId;*/
			}

			$this->_logReqData[] = 'Envío ID: '.$envioId;

			$userType = ($this->_userType == JBSYSWEBAPI::USR_CONSU ? $this->_getUserType($this->_oClientId) : $this->_userType);

			$data = array(
			'id_web_usuarios' => $this->_userId,
			'id_cli_clientes' => $this->_oClientId,
			'id_web_campanias' => $campania,
			'es_feria' => $this->_art['feria'],
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




/***************************************************************************************************************************************/


		 /*      Agregando un item nuevo al pedido   */

		$stock = $this->_dbGetOne('web_cache_articulos_stock', array('codigo11' => $this->_art['cod11'] ));

      	if($stock  && $stock['stock_ilimitado'] != 1){
          $reservaNueva = $stock['reserva'] + $input['cantidad'];
          $cantidadTotalNueva = $stock['cantidad_total'] - $input['cantidad'];

          if( ( $stock['cantidad_inicial'] - $reservaNueva  - $stock['enviado'] )  >= 0 ){

              $this->_dbQuery('UPDATE web_cache_articulos_stock SET reserva = ?, cantidad_total = ? WHERE id_web_cache_articulos_stock = ?', array( $reservaNueva, $cantidadTotalNueva, $stock['id_web_cache_articulos_stock'] ) );
          }
          else{

          		$this->_guardarSolicitudArticuloFaltante($input['campania'], $this->_art['cod11']);

              return array('msgError' => ' En estos momentos el art&iacute;culo '. $this->_art['cod11'] .' no cuenta con suficiente stock. Te invitamos a ingresar nuevamente en los pr&oacute;ximos d&iacute;as para concretar tu compra' );
          }    

      	}

		



/***************************************************************************************************************************************/





			$precio = $this->_art['precio'];
			$puntos = $this->_art['puntos'];

			$data = array(
			'id_web_pedidos' => $orderId,
			'idArticulo' => $input['idArticulo'],
			'cod11' => $this->_art['cod11'],
			'code' => $this->_art['Code'],
			'tipo' => $this->_art['Tipo'],
			'color' => $this->_art['Color'],
			'talle' => $this->_art['Talle'],
			'cantidad' => $input['cantidad'],
			'estado' => $this->_openedStatus[$this->_userType],
			'es_feria' => $this->_art['feria'],
			'precio' => $precio,
			'ptos_unitarios' => $puntos
			);





/***************************************************************************************************************************************/


		    $articulo = $this->_dbGetOne('web_cache_articulos', array( 'cod11' => $this->_art['cod11'], 'id_web_campanias' => $input['campania']  ));

			//Excluímos las zapatos de la preventa
		    if( $articulo['tipo_venta'] != 10  && $articulo['tipo_venta'] != 20 ){
				$this->_calcUnidPrev($input['campania'], $data);
			}

			//reemplazado por el condicional de arriba
//			$this->_calcUnidPrev($input['campania'], $data);

                        
                     /*Nueva validaci{on para poner fecha tope a la carga de calzados*/     

                    if( $articulo['tipo_venta'] == 10 ){    	

		        $cli = $this->_dbGetOne('web_cache_clientes', array('id_web_cache_clientes' => $this->_oClientId ));  

		        $diaCalendario = $this->_dbGetOne('web_cache_campanias_dia_calendario', array('id_web_campanias' => $input['campania'], 'region' => $cli['region'] ));

		        if($diaCalendario){
                            if( !is_null($diaCalendario['fecha_tope_carga']) ){	    
                                $fechaActual = date("Y-m-d H:i:s");
                                if($fechaActual >= $diaCalendario['fecha_tope_carga']){
                                     return array('msgError' => ' Se ha superado la fecha y hora l&iacute;mite para agregar art&iacute;culos de calzado para esta campaña.  Solicite este art&iacute;culo en la siguiente oportunidad'  );
                                }
                            }    
		        }
		    }    
                        

/***************************************************************************************************************************************/





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

		// si es un artículo de feria, actualizamos el stock
		if ($this->_art['feria']) $this->_updateStock($this->_art['cod11'], $stockAmt);

		$this->_logReqData[] = 'Item ID: '.$idItem;

		$orderData = array_merge($orderData, array(
		'unidades' => $totalQ + $input['cantidad'],
		'monto' => $totalAmount + $input['cantidad'] * $precio,
		'puntos_total' => $ptosTotal + $input['cantidad'] * $puntos
		));
	}

	$this->_logReqData[] = 'Datos actualizados en cabecera: '.print_r($orderData, true);

	// actualizamos el encabezado
	$this->_dbUpdate('web_pedidos', $orderData, array(
	'id_web_pedidos' => $orderId
	));

	// guardarmos los premios del nuevo esquema
	if ($this->_premios)
	{
		$data = array('id_cli_cliente' => $this->_oClientId, 'prem_campania' => $input['campania']);
		
		// borramos los premios seleccionados previamente
		$this->_dbDelete('web_prem_solicitados', $data);
		
		$data['fecha_carga'] = date('Y-m-d H:i:s');
		
		// guardamos los premios seleccionados
		foreach(explode(',', $input['premios']) as $p)
		{
			$p = trim($p);
			if ($p)
			{
				$r = $this->_dbGetOne('web_cache_prem_articulos', array('id_web_cache_prem_articulos' => $p, 'prem_campania' => $input['campania']), array('prem_codigo', 'prem_articulo_codigo11'));
				$data['prem_codigo'] = $r['prem_codigo'];
				$data['prem_articulo_codigo11'] = $r['prem_articulo_codigo11'];
				$this->_dbInsert('web_prem_solicitados', $data);
			}
		}
	}
	
	// guardamos el premio incentivo
	if (($r = $this->_checkIncPrem($input['campania'], $this->_oClientId)) !== false)
	{
		$premioInc = explode(',', $input['premioInc']);
		$p = array(
		'id_cli_cliente' => $this->_oClientId,
		'prem_campania' => $input['campania']
		);
		$_p = array('fecha_carga' => date('Y-m-d'));
		foreach($r as $k => $r2)
		{
			$p['prem_codigo'] = $r2['id_web_cache_prem_incentivo'];
			$_p['prem_articulo_codigo11'] = $premioInc[$k];
			if (($r3 = $this->_dbGetOne('web_prem_solicitados', $p, array('id_web_prem_solicitados'))) === false)
				$this->_dbInsert('web_prem_solicitados', array_merge($p, $_p));
			else
				$this->_dbUpdate('web_prem_solicitados', array('id_web_prem_solicitados' => $r3['id_web_prem_solicitados']), $_p);
		}
	}
		
	return array('idItem' => $idItem, 'numeroPedido' => $orderId);
}

}

require 'lib/exe.php';