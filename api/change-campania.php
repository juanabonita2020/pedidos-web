<?php require 'lib/kernel.php';

// cambiamos la campaña actual
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_EMPRE);

protected $_input = array(
'campania' => array(
	'required' => false
)
);

protected function _process($input)
{
	// comprobar la campaña
	if (! $this->_validCamp($input['campania'])) return false;

	if (($r = $this->_changeCampaign($input['campania'], $this->_userZone) !== true))
		return $r;
}

}

require 'lib/exe.php';