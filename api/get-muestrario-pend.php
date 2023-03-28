<?php require 'lib/kernel.php';

// devuelve la cantidad de muestrario pendiente
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_EMPRE, JBSYSWEBAPI::USR_COORD);

protected $_input = array(
'campania' => array(
	'required' => true
),
'cliente' => array(
	'required' => true
)
);

protected function _process($input)
{
	// comprobar la campaña
	if (! $this->_validCamp($input['campania'])) return false;

	$res = array('max' => $this->_getMaxMuestrario($input['cliente']), 'act' => $this->_getTotMuestrario($input['cliente'], $input['campania']));
	$res['cantidad'] = $res['max'] - $res['act'];
	if ($res['cantidad'] < 0) $res['cantidad'] = 999999;
	$res['cuotas'] = $this->_cuotasEnabled($input['campania']);
	return $res;
}

}

require 'lib/exe.php';