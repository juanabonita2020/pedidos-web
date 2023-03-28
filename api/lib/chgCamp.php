<?php if (empty($JBSYSWEBAPI)) die;

// cambia el estado de una campaña
class API extends APICHGCAMP
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN);

protected $_input = array(
'campania' => array(
)
);

protected function _process($input)
{
	// comprobar la campaña
	if (! $this->_validCamp($input['campania'], true)) return false;

	// si la campaña no existe se la crea con los mínimos por defecto
	if ($this->_dbGetOne('web_campanias', array('id_web_campanias' => $input['campania'])) === false)
	{
		$r = $this->_dbGetOne('web_cache_campanias', array('id_web_cache_campanias' => $input['campania']));
		$this->_dbInsert('web_campanias', array('id_web_campanias' => $input['campania'], 'minimo_monto_E' => $this->_global['minimo_monto_E'], 'minimo_monto_D' => $this->_global['minimo_monto_D'], 'minimo_unidades_E' => $this->_global['minimo_unidades_E'], 'minimo_unidades_D' => $this->_global['minimo_unidades_D'], 'orden' => $r['orden'], 'orden_absoluto' => $r['orden_absoluto'], 'sistema' => $r['sistema'], 'fecha_inicio' => $r['fecha_inicio']));
	}

	// actualizamos el estado de la campaña
	$this->_dbUpdate('web_campanias', array('habilitado' => $this->_value), array('id_web_campanias' => $input['campania']));
}

}

require 'lib/exe.php';
