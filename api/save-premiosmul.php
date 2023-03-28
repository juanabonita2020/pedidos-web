<?php require 'lib/kernel.php';

// guardar premios múltiples
class API extends JBSYSWEBAPI
{

protected $_input = array(
'campania' => array(
),
'cliente' => array(
),
'sel' => array(
)
);

//~ protected $_logReq = true;

protected function _process($input)
{
	//TODO: verificar que los datos seleccionados sean validos; verificar que la campaña sea la correcta
	if ($this->_isMyClient($input['cliente']))
	{
		$data = array('id_cli_cliente' => $input['cliente'], 'prem_campania' => $input['campania'], 'fecha_carga' => date('Y-m-d H:i:s'));
		
		foreach(explode(',', $input['sel']) as $s) if ($s)
		{
			list($data['prem_codigo'], $data['prem_articulo_codigo11'], $q) = explode('|', $s);
			for($i = 0; $i < $q; $i++)
				$this->_dbInsert('web_prem_solicitados', $data);
		}
	}
}

}

require 'lib/exe.php';
