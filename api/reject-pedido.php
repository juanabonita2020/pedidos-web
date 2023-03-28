<?php require 'lib/kernel.php';

// rechaza un pedido
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

	// pasamos de un estado APROBADO o CERRADO a RECHAZADO
	$this->_oldStatus = array_merge($this->_validApprovedStatus, $this->_validClosedStatus);
	$this->_newStatus = $this->_openedStatus;
}

protected function _validateProcess($input)
{	
	// la actualizacion del pedido (cabecera) sólo lo puede hacer la empresaria
	if (! $this->_isItem && $this->_userType != JBSYSWEBAPI::USR_EMPRE)
		return false;

	// si se actualiza un item (detalle) cambiamos los estados viejos válidos
	if ($this->_isItem)
		$this->_oldStatus = array_merge($this->_validApprovedStatus, array(JBSYSWEBAPI::OS_CLO_CONSU));
	else
		$this->_oldStatus[] = JBSYSWEBAPI::OS_NSN_EMPRE;
	
	return true;
}

protected function _postProcess($input, &$output)
{
	$this->_accRejPostProcess($input);
}

}

require 'lib/chgStatus.php';