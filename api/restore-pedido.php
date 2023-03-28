<?php require 'lib/kernel.php';

// recupera los pedidos de un envío
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN);

protected $_input = array(
'envio' => array(
	'required' => true
),
'stage' => array(
	'required' => true
)
);

protected $_logReq = true;

protected function _process($input)
{
	$q = ' FROM web_envios a, web_pedidos b WHERE a.id_web_envios = b.id_web_envios AND a.id_web_envios = ? AND estado = ?';
	$params = array($input['envio'], JBSYSWEBAPI::OS_NSN_EMPRE);

	// STAGE 1: obtener la cantidad de pedidos a recuperar en el envío

	if ($input['stage'] == 1)
	{
		$r = $this->_dbQuery('SELECT COUNT(*) q, id_cli_zonas'.$q, $params);
		return array('q' => $r[0]['q'], 'zona' => $r[0]['id_cli_zonas']);
	}

	// STAGE 2: recuperar los pedidos

	// obtener los pedidos a recuperar
	$pedidos = $this->_dbQuery('SELECT id_web_pedidos id, unidades, puntos_total, monto, IF(fecha_envio IS NULL, 0, 1) nuevoenvio, a.id_web_campanias, id_cli_zonas'.$q, $params);

	$updateTpl = array(
	'estado' => JBSYSWEBAPI::OS_OPE_EMPRE
	);

	// determinar si hay que crear un nuevo envío
	foreach($pedidos as $p)
		if ($p['nuevoenvio'] == 1)
		{
			// crear el nuevo envío
			$updateTpl['id_web_envios'] = $this->_newEnvio($p['id_web_campanias'], null, $p['id_cli_zonas']);

			// obtener empresaria de la zona
			$r = $this->_dbQuery('SELECT id_web_usuarios, cantidad_cierres FROM web_usuarios a, web_cache_clientes b WHERE a.id_cli_clientes = b.id_cli_clientes AND numero_cliente = 1 AND id_cli_zonas = ?', array($p['id_cli_zonas']));

			// incrementar en 1 la cant. de cierres de la empresaria de la zona
			$this->_dbUpdate('web_usuarios', array('cantidad_cierres' => $r[0]['cantidad_cierres'] + 1), array('id_web_usuarios' => $r[0]['id_web_usuarios']));

			break;
		}


//reemplazado por lo de abajo  //versión anterior
/*
	// recuperar los pedidos del envío
	foreach($pedidos as $p)
	{
		// actualizar la cabecera
		$update = array_merge($updateTpl, array(
		'unidades' => $p['unidades'],
		'puntos_total' => $p['puntos_total'],
		'monto' => $p['monto']
		));

		foreach($this->_dbQuery('SELECT precio, cantidad, ptos_unitarios FROM web_pedidos_detalle WHERE id_web_pedidos = ? AND estado = ?', array($p['id'], JBSYSWEBAPI::OS_NSN_EMPRE)) as $d)
		{
			$update['unidades'] += $d['cantidad'];
			$update['puntos_total'] += $d['ptos_unitarios'] * $d['cantidad'];
			$update['monto'] += $d['precio'] * $d['cantidad'];
		}

		$this->_dbUpdate('web_pedidos', $update, array('id_web_pedidos' => $p['id']));

		// actualizar los ítems
		$this->_dbUpdate('web_pedidos_detalle', array('estado' => JBSYSWEBAPI::OS_OPE_EMPRE), array('id_web_pedidos' => $p['id'], 'estado' => JBSYSWEBAPI::OS_NSN_EMPRE));
	}
*/


/***************************************************************************************************************************************/


	// recuperar los pedidos del envío
	foreach($pedidos as $p)
	{

			$update = array_merge($updateTpl, array(
			'unidades' => $p['unidades'],
			'puntos_total' => $p['puntos_total'],
			'monto' => $p['monto']
			));



			$contItems = 0;

		  	foreach($this->_dbQuery('SELECT id_web_pedidos_detalle, cod11, precio, cantidad, ptos_unitarios FROM web_pedidos_detalle WHERE id_web_pedidos = ? AND estado = ?', array($p['id'], JBSYSWEBAPI::OS_NSN_EMPRE)) as $d)
		  	{
		      
		      $stock = $this->_dbGetOne('web_cache_articulos_stock', array('codigo11' => $d['cod11']));

		      if($stock  && $stock['stock_ilimitado'] != 1){
		       
		          $reservaNueva = $stock['reserva'] + $d['cantidad'];
		          if( ( $stock['cantidad_inicial'] - $reservaNueva - $stock['enviado'] ) >= 0){

			            $cantidadTotalNueva = $stock['cantidad_total'] - $d['cantidad']; 
			            $this->_dbQuery('UPDATE web_cache_articulos_stock SET reserva = ?, cantidad_total = ? WHERE id_web_cache_articulos_stock = ?', array( $reservaNueva, $cantidadTotalNueva, $stock['id_web_cache_articulos_stock'] ) );
			            
			            $update['unidades'] += $d['cantidad'];
			            $update['puntos_total'] += $d['ptos_unitarios'] * $d['cantidad'];
			            $update['monto'] += $d['precio'] * $d['cantidad'];

           		      	$this->_dbUpdate('web_pedidos_detalle', array('estado' => JBSYSWEBAPI::OS_OPE_EMPRE), array('id_web_pedidos_detalle' => $d['id_web_pedidos_detalle'], 'estado' => JBSYSWEBAPI::OS_NSN_EMPRE));

			            $contItems ++;

		          }
		          else{
		          		// SI NO HAY SUFICIENTE STOCK PARA RESTAURAR EL ITEM, ESTE NO SE AGREGA AL PEDIDO 
		          		continue;
		          }
		      }
		      else{

		          $update['unidades'] += $d['cantidad'];
		          $update['puntos_total'] += $d['ptos_unitarios'] * $d['cantidad'];
		          $update['monto'] += $d['precio'] * $d['cantidad'];

				  $this->_dbUpdate('web_pedidos_detalle', array('estado' => JBSYSWEBAPI::OS_OPE_EMPRE), array('id_web_pedidos_detalle' => $d['id_web_pedidos_detalle'], 'estado' => JBSYSWEBAPI::OS_NSN_EMPRE));

		          $contItems ++;
		      }
		  }

		  // si le quedan items al pedido, actualiza el estado 
		  if($contItems > 0){ 

		      $this->_dbUpdate('web_pedidos', $update, array('id_web_pedidos' => $p['id']));

		      // actualizar los ítems
//		      $this->_dbUpdate('web_pedidos_detalle', array('estado' => JBSYSWEBAPI::OS_OPE_EMPRE), array('id_web_pedidos' => $p['id'], 'estado' => JBSYSWEBAPI::OS_NSN_EMPRE));
		  }


	}	  

/***************************************************************************************************************************************/















}

}

require 'lib/exe.php';