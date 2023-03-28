<?php require 'lib/kernel.php';

// Aprueba un pedido
class APICHGSTATUS extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_EMPRE, JBSYSWEBAPI::USR_REVEN, JBSYSWEBAPI::USR_COORD);

protected $_newStatus;
protected $_oldStatus;
protected $_isItem;
protected $_changed = false;

function __construct()
{
	parent::__construct();

	// pasamos de un estado CERRADO/RECHAZADO a APROBADO
	$this->_oldStatus = array_merge($this->_validRejectedStatus, $this->_validClosedStatus);
	$this->_newStatus = $this->_approvedStatus;
}

protected function _validateProcess($input)
{
	// la actualizacion del pedido (cabecera) sólo lo puede hacer la empresaria
	if (! $this->_isItem && $this->_userType != JBSYSWEBAPI::USR_EMPRE)
		return false;

	// si se actualiza un item (detalle) cambiamos los estados viejos válidos
	if ($this->_isItem)
		$this->_oldStatus = array_merge($this->_validRejectedStatus, array(JBSYSWEBAPI::OS_CLO_CONSU));
	else
		$this->_oldStatus[] = JBSYSWEBAPI::OS_NSN_EMPRE;
	
	return true;
}

protected function _postProcess($input, &$output)
{	
	$this->_accRejPostProcess($input, 1);
}

}

require 'lib/chgStatus.php';