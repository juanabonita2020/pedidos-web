<?php require 'lib/kernel.php';

// modifica un ítem del pedido
class API extends JBSYSWEBAPI
{

protected $_input = array(
'muestrario' => array(
),
'cuotas' => array(
),
'idItem' => array(
	'required' => true
)
);

//protected $_logReq = true;

protected function _process($input)
{
	// verificamos que el usuario tenga acceso al ítem
	if (! $this->_isMyOrderItem($input['idItem'])) $this->_forbidden();

	$muestrario = ($input['muestrario'] == 'true' || $input['muestrario'] == '1' ? 1 : 0);
/*
	$r = $this->_dbQuery('SELECT id_cli_clientes, b.id_web_campanias, muestrario, cantidad, c.Tipo, tipo_venta, idArticulo FROM web_pedidos_detalle a, web_pedidos b, web_cache_articulos c WHERE a.id_web_pedidos = b.id_web_pedidos AND idArticulo = id_web_cache_articulos AND id_web_pedidos_detalle = ?', array($input['idItem']));
*/
/******************************************************************************************************************************/
	$r = $this->_dbQuery('SELECT id_cli_clientes, b.id_web_campanias, muestrario, cantidad, c.Tipo, tipo_venta, idArticulo, IF(a.id_web_promocion_relacion IS NULL, 0, 1) AS es_promo FROM web_pedidos_detalle a, web_pedidos b, web_cache_articulos c WHERE a.id_web_pedidos = b.id_web_pedidos AND idArticulo = id_web_cache_articulos AND id_web_pedidos_detalle = ?', array($input['idItem']));

	$esPromocion = $r[0]['es_promo'];
/******************************************************************************************************************************/
	if ($muestrario)
	{
		if ($r[0]['tipo_venta'] == 1 && $r[0]['es_promo'] == 0 )
		{
			if ($maxMuestrario = $this->_getMaxMuestrario($r[0]['id_cli_clientes']))
			{
				$totMuestrario = $this->_getTotMuestrario($r[0]['id_cli_clientes'], $r[0]['id_web_campanias']);
				if ($this->_logReq)
					$log = print_r($r, true).' - '.$maxMuestrario.' - '.$totMuestrario;
				if ($r[0]['muestrario'] == 0) $totMuestrario += $r[0]['cantidad'];
				if ($this->_logReq)
					$this->_logReqData[] = $log.' - '.$totMuestrario;
				if ($totMuestrario > $maxMuestrario) return array('msg' => 'No puede activar el muestrario porque estaría superando el máximo permitido de '.$maxMuestrario.' unidades.');
			}
			else
				return array('msg' => 'Este tipo de artículos no tiene permitido activar el muestrario.');
		}
		else
			return array('msg' => 'Este tipo de artículos no tiene permitido activar el muestrario.');
	}
/******************************************************************************************************************************/	
	if(	/*$input['cuotas'] == 1 && */ $esPromocion == 1){
		return array('msg' => 'Este artículo no tiene permitido activar las cuotas ya que pertenece a una promoción.');
	}
/******************************************************************************************************************************/	

	$cuotasEnabled = $this->_cuotasEnabled($r[0]['id_web_campanias']);
	$r = $this->_dbGetOne('web_cache_articulos', array('id_web_cache_articulos' => $r[0]['idArticulo']), array('cuotas'));
	$cuotasArtEnabled = $r['cuotas'];
	
	if ($this->_logReq)
		$this->_logReqData[] = 'Cuotas activas para la campaña: '.$cuotasEnabled."\n".'Cuotas activas para el artículo: '.$cuotasArtEnabled;

	$data = array('muestrario' => $muestrario, 'cuotas' => $cuotasEnabled && $cuotasArtEnabled ? $input['cuotas'] : 0);

	$this->_dbUpdate('web_pedidos_detalle', $data, array('id_web_pedidos_detalle' => $input['idItem']));
}

}

require 'lib/exe.php';