<?php require 'lib/kernel.php';

// quita un ítem del pedido
class API extends JBSYSWEBAPI
{

protected $_input = array(
'idItem' => array(
	'required' => true
)
);

//protected $_logReq = true;

protected function _process($input)
{
	// verificamos que el usuario tenga acceso al ítem
	if (! $this->_isMyOrderItem($input['idItem'])) $this->_forbidden();
	
	// obtener estado del ítem
	//$r = $this->_dbGetOne('web_pedidos_detalle', array('id_web_pedidos_detalle' => $input['idItem']), array('estado'));
	//$this->_logReqData[] = 'Estado: '.$r['estado'];
	
	// restauramos el stock y las cabecera (salvo si el ítem fue rechazado)
	//if (! in_array($r['estado'], $this->_validRejectedStatus))
	//$this->_restoreOrderQ($input['idItem']);

	// eliminamos el item
	//$this->_dbDelete('web_pedidos_detalle', array('id_web_pedidos_detalle' => $input['idItem']));
	
	$this->_removeOrderItem($input['idItem']);
}

}

require 'lib/exe.php';