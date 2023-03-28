<?php require 'lib/kernel.php';

class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_CONSU);

protected $_input = array(
'items' => array(
	'required' => true
),
'conMail' => array(
	'required' => true
),
'nombre' => array(
	'required' => true
),
'campania' => array(
	'required' => true
)
);

protected function _process($input)
{
	// comprobar la campaña
	if (! $this->_validCamp($input['campania'])) return false;

	$items = $this->_dbQuery('SELECT estado, cantidad, muestrario, talle, code, color, tipo, idArticulo, compradora, es_feria isFeria, id_web_pedidos_detalle idItem, ptos_unitarios ptos FROM web_pedidos_detalle WHERE id_web_pedidos_detalle IN ('.implode(', ', $input['items']).')');
	//print_r($items);

	// enviar email a la consumidora
	$body = 'Gracias por comprar Juana Bonita. La vendedora '.$this->_getSessionVar('refererFullName').' recibirá tu pedido. Ella se contactara contigo para coordinar la entrega y el pago del mismo. A continuación, te mostramos el detalle de tu pedido:'.$this->_getHTMLOrderItems(0, $input['campania'], null, array('Precio' => 'precioCompradora'), $items);
	//echo $body;
	$this->_sendmail($input['conMail'], 'Su compra en Juana Bonita', $body, 2);

	// enviar email a la empresaria
	$body = $input['nombre'].' ha realizado un pedido de Feria Americana Web. Recordá que debés aprobarlo para recibir esta prenda junto con tu cierre. A continuación, te mostramos el detalle de tu pedido:'.$this->_getHTMLOrderItems(0, $campania, null, array('Valor de venta' => 'precioCompradora', 'Valor a Pagar' => 'precio'), $items);
	//echo $this->_getSessionVar('refererMail').'-'.$body;
	$this->_sendmail($this->_getSessionVar('refererMail'), 'Compra en Feria Americana Web', $body, 2);
}

}

require 'lib/exe.php';