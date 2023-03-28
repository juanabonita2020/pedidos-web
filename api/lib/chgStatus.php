<?php if (empty($JBSYSWEBAPI)) die;

// procesamos el cambio de estado de un pedido
class API extends APICHGSTATUS
{

protected $_input = array(
'numeroPedido' => array(
	'required' => false
),
'isFeria' => array(
),
'compradora' => array(
),
'confirm' => array(
),
'usr1' => array(
)
);

protected $_isItem;
protected $_query;
protected $_params;
protected $_changed = false;
protected $_oldItemStatus;
protected $_extraOutput = array();

//protected $_logReq = true;

protected function _process($input)
{
	$this->_logReqData[] = 'OldStatus: '.print_r($this->_oldStatus, true);
	$this->_logReqData[] = 'NewStatus: '.print_r($this->_newStatus, true);
	
	if (! empty($this->_newStatus[$this->_userType]))
	{
		// verificamos que el usuario tenga permiso sobre el pedido
		if (! $this->_isMyOrder($input['numeroPedido'])) $this->_forbidden();

		$query = ' SET estado = ?, ip = ? WHERE id_web_pedidos = ?';
		$params = array($this->_newStatus[$this->_userType], $this->_ip, $input['numeroPedido']);

		// si se especifican estado actuales válidos, se verifica que el pedido esté en alguno de esos estados
		if ($this->_oldStatus != null)
		{
			$query .= ' AND estado IN (?'.str_repeat(', ?', count($this->_oldStatus) - 1).')';
			$params = array_merge($params, $this->_oldStatus);
		}
		
		$this->_query = $query;
		$this->_params = $params;
		
		// actualizamos el estado de un item de detalle, dejando la cabecera intacta
		if ($this->_isItem)
		{
			$q = ' WHERE id_web_pedidos = ?';
			$p = array($input['numeroPedido']);
			$this->_addItemWhere($input, $q, $p);
			$r = $this->_dbQuery('SELECT estado FROM web_pedidos'.$q, $p);
			$this->_oldItemStatus = $r[0]['estado'];
			$this->_logReqData[] = 'Old item status: '.$this->_oldItemStatus;
			$this->_addItemWhere($input, $query, $params);		
		}
				
		$this->_logReqData[] = 'NewStatus: '.print_r($this->_newStatus, true);

		$this->_dbQuery('UPDATE web_pedidos'.$query, $params);
		
		$this->_changed = true;
	}
	
	$this->_logReqData[] = 'Changed: '.$this->_changed;

	return $this->_extraOutput;
}

protected function _addItemWhere($input, &$query, &$params)
{
	$this->_getItemWhere($input, $query, $params);	
	$query = '_detalle'.$query;
}

protected function _validateProcess($input)
{
	$this->_isItem = (isset($input['isFeria']) && $input['isFeria'] != '*');
	$this->_logReqData[] = 'Is item: '.$this->_isItem;
	return parent::_validateProcess($input);
}

protected function _getItemWhere($input, &$query, &$params)
{
	$query .= ' AND es_feria = ? AND compradora ';
			
	$params[] = $input['isFeria'];
	
	if (empty($input['compradora']))
		$query .= 'IS NULL';
	else
	{
		$query .= '= ?';
		$params[] = $input['compradora'];
	}
}

protected function _accRejPostProcess($input, $op = -1)
{
	/*// si se acepta/rechaza un ítem
	if ($this->_isItem)
	{
		// buscamos el id del ítem
		$query = ' WHERE id_web_pedidos = ?';
		$params = array($input['numeroPedido']);
		$this->_addItemWhere($input, $query, $params);
		$r = $this->_dbQuery('SELECT id_web_pedidos_detalle FROM web_pedidos'.$query, $params);
		$this->_logReqData[] = 'Item ID: '.$r[0]['id_web_pedidos_detalle'];
		
		// restauramos el stock y las cabecera
		$this->_restoreOrderQ($r[0]['id_web_pedidos_detalle'], $op);
	}
	else */
	// si se acepta/rechaza un pedido 
	if (! $this->_isItem && $this->_changed)
	{
		// no actualizamos ítems no enviados
		$query = 'UPDATE web_pedidos_detalle'.$this->_query.' AND estado <> ?';
		$params = $this->_params;
		$params[] = JBSYSWEBAPI::OS_NSN_EMPRE;
		$this->_dbQuery($query, $params);
	}
}

}

require 'lib/exe.php';
