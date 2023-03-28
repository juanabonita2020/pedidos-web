<?php require 'lib/kernel.php';

// eliminar el pedido
class APICHGSTATUS extends JBSYSWEBAPI
{

protected $_newStatus;
protected $_oldStatus;
protected $_changed = false;

function __construct()
{
	parent::__construct();

	// pasamos de un estado ELIMINADO (sin importar su estado actual)
	$this->_newStatus = $this->_removedStatus;
	$this->_oldStatus = null;
}

protected function _postProcess($input, &$output)
{
	if (! $this->_changed) return;
	
	// actualizamos la cantidad de pedidos abiertos
	$r = $this->_dbGetOne('web_pedidos', array('id_web_pedidos' => $input['numeroPedido']));
	$this->_updateOpenedOrders($r['id_cli_clientes']);
	
	$data = array('estado' => $this->_removedStatus[$this->_userType]);
	$where = array('id_web_pedidos' => $input['numeroPedido']);
	
	$deletedItems = array();
	$itemsAEliminar = array();
	
	// si se está eliminando un ítem, actualizar el pedido sólo si todos los ítems están borrados
	if ($this->_isItem)
	{
		$query = ' FROM web_pedidos_detalle WHERE id_web_pedidos = ? ';
		
		$r = $this->_dbQuery('SELECT estado, id_web_pedidos_detalle'.$query.'AND estado <> ?', array($input['numeroPedido'], $this->_removedStatus[$this->_userType]));
		
		// todos los items del pedido están borrados
		if (! isset($r[0]))
		{
			$data['unidades'] = 0;
			$data['monto'] = 0;
			$data['puntos_total'] = 0;
			$this->_logReqData[] = 'Actualización de cabecera: '.print_r($data, true);
			$this->_dbUpdate('web_pedidos', $data, $where);	
		}

		//$query = 'SELECT cod11, cantidad'.$query;
		$params = array($input['numeroPedido']);
		$this->_getItemWhere($input, $query, $params);		
		$r2 = $this->_dbQuery('SELECT cod11, cantidad, idArticulo, id_web_pedidos_detalle, estado'.$query, $params);
		$deletedItems[] = array($r2[0]['cod11'], $r2[0]['cantidad']);
		$this->_logReqData[] = 'Detalle: '.print_r($r2, true);
		

/***************************************************************************************************************************************/

	
		//Agergo el item para ajustar el stock
		$itemsAEliminar[] = array($r2[0]['cod11'], $r2[0]['cantidad'], $r2[0]['estado']);	


		
/***************************************************************************************************************************************/


		// actualizar cantidad y monto de la cabecera
		if (isset($r2[0]) && isset($r[0]))
			foreach($r2 as $r3)
				if ($r3['estado'] != $this->_removedStatus[$this->_userType])
					$this->_restoreOrderQ($r3['id_web_pedidos_detalle']);
	}
	// sino, borrar todos los ítems del pedido
	else
	{


/***************************************************************************************************************************************/

		//excluyo a los items ya eliminados en estado 80

		$vals = array($input['numeroPedido'], $this->_removedStatus[$this->_userType]);
		$res = $this->_dbQuery('SELECT cod11, cantidad, idArticulo, id_web_pedidos_detalle, estado FROM web_pedidos_detalle WHERE id_web_pedidos = ? AND estado <> ? ', $vals);
		
		//antes de hacer el update, porque sino no va a haber items para jaustarle el stock
		foreach($res as $i){
			// agrego los items para ajustar el stock
			$itemsAEliminar[] = array($i['cod11'], $i['cantidad'], $i['estado']);
		}


/***************************************************************************************************************************************/


		$this->_dbUpdate('web_pedidos_detalle', $data, $where);
		foreach($this->_dbGetAll('web_pedidos_detalle', $where) as $i)
			$deletedItems[] = array($i['cod11'], $i['cantidad']);
	}
		
	foreach($deletedItems as $i)
		$this->_updateStock($i[0], $i[1]);


/***************************************************************************************************************************************/


	//hacemos el ajuste del stock si ese item tiene stock cargado
	foreach($itemsAEliminar as $i){

        $stock = $this->_dbGetOne('web_cache_articulos_stock', array('codigo11' => $i[0] )); // $i[0] --> codigo11

        if($stock  && $stock['stock_ilimitado'] != 1 && $i[2] != $this->_removedStatus[$this->_userType] ){
            $reservaNueva = $stock['reserva'] - $i[1];  // $i[1] --> cantidad
            $cantidadTotalNueva = $stock['cantidad_total'] + $i[1];  // $i[1] --> cantidad
            $this->_dbQuery('UPDATE web_cache_articulos_stock SET reserva = ?, cantidad_total = ? WHERE id_web_cache_articulos_stock = ?', array( $reservaNueva, $cantidadTotalNueva, $stock['id_web_cache_articulos_stock'] ) );
        }
    } 


/***************************************************************************************************************************************/



}

protected function _getItemWhere($input, &$query, &$params)
{
}

}

require 'lib/chgStatus.php';