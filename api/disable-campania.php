<?php require 'lib/kernel.php';

// desactiva una campaña
class APICHGCAMP extends JBSYSWEBAPI
{

protected $_logReq = true;

protected function _postProcess($input, &$output)
{
	// determinamos el sistema de la campaña desactivada
	$r = $this->_dbGetOne('web_cache_campanias', array('id_web_cache_campanias' => $input['campania']), array('sistema'));
	if (empty($r['sistema'])) return false;

	// seleccionamos la campaña que reemplazará a la que desactivamos
	$this->_dbQuery('UPDATE web_campanias_zonas SET id_web_campanias = ? WHERE id_web_campanias = ?', array($this->_getMinorCamp($r['sistema']), $input['campania']));

	// actualizamos los pedidos y sus ítems que no hayan sido enviados al estado NO ENVIADO
	foreach($this->_dbQuery('SELECT id_web_pedidos, unidades, monto, puntos_total FROM web_pedidos WHERE id_web_campanias = ? AND estado NOT IN (?, ?, ?)', array($input['campania'], JBSYSWEBAPI::OS_REM_EMPRE, JBSYSWEBAPI::OS_SEN_EMPRE, JBSYSWEBAPI::OS_NSN_EMPRE)) as $p)
	{
		$this->_logReqData[] = 'Pedido: '.print_r($p, true);


/*****************************************************************************************************************************************************************/

					//DESCONTAMOS LA RESERVA DE LOS PEDIDOS QUE NO FUERON ENVIADOS AL MOMENTO DE DESHABILITAR LA CAMPAÑA

					//Buscamos todos los items del pedido que no estén en estado eliminado
					$items = $this->_dbQuery('SELECT id_web_pedidos_detalle, cod11, cantidad FROM web_pedidos_detalle WHERE id_web_pedidos = ? AND estado <> ?', array($p['id_web_pedidos'],JBSYSWEBAPI::OS_REM_EMPRE) );
 
					foreach($items as $item){
					      
					      //$r = $this->_dbGetOne('web_pedidos_detalle', array( 'id_web_pedidos_detalle' => $item['id_web_pedidos_detalle'] ));
					      $stock = $this->_dbGetOne('web_cache_articulos_stock', array('codigo11' => $item['cod11']));

					      //Verificamos si tiene stock y lo ajustamos en caso positivo
					      if($stock){
					          $reservaNueva = $stock['reserva'] - $item['cantidad'];
					          $cantidadTotalNueva = $stock['cantidad_total'] + $item['cantidad'];
					          $this->_dbQuery('UPDATE web_cache_articulos_stock SET reserva = ?, cantidad_total = ? WHERE id_web_cache_articulos_stock = ?', array( $reservaNueva, $cantidadTotalNueva, $stock['id_web_cache_articulos_stock'] ) );
					      }

					}

/*****************************************************************************************************************************************************************/
		

		// obtener suma de unidades, monto y puntos de los ítems del pedido que serán marcados como no enviados
		$r = $this->_dbQuery('SELECT SUM(cantidad) unidades, SUM(precio * cantidad) monto, SUM(ptos_unitarios * cantidad) puntos_total FROM web_pedidos_detalle WHERE id_web_pedidos = ? AND estado <> ?', array($p['id_web_pedidos'], JBSYSWEBAPI::OS_REM_EMPRE));
		$this->_logReqData[] = 'Suma de ítems a marcar como no enviados: '.print_r($r, true);

		$unidades = $p['unidades'] - $r[0]['unidades'];
		$monto = $p['monto'] - $r[0]['monto'];
		$puntos_total = $p['puntos_total'] - $r[0]['puntos_total'];

		// actualizar cabecera y detalle
		$this->_dbQuery('
		UPDATE web_pedidos SET estado = ?, unidades = ?, monto = ?, puntos_total = ? WHERE id_web_pedidos = ?;
		UPDATE web_pedidos_detalle SET estado = ?, fecha_descarte = ? WHERE id_web_pedidos = ? AND estado <> ?;
		', array(JBSYSWEBAPI::OS_NSN_EMPRE, $unidades, $monto, $puntos_total, $p['id_web_pedidos'], JBSYSWEBAPI::OS_NSN_EMPRE, date('Y-m-d H:i:s'), $p['id_web_pedidos'], JBSYSWEBAPI::OS_REM_EMPRE));
	}
}

}

require 'lib/chgCamp.php';
