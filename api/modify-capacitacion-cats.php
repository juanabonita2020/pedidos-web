<?php require 'lib/kernel.php';

// modificar una capacitación
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN);

private $_imagesDir = 'images/capacitcats/';

protected $_input = array(
'id' => array(
	'required' => true
),
'titulo' => array(
	'required' => true
),
'orden' => array(
	'required' => true
),
'action' => array(
//	'required' => true
)
);

protected function _process($input)
{
	if (substr($input['action'], 0, 7) == 'remove_')
	{
		$id = substr($input['action'], 7);
		$this->_removeImage($id);
		//echo $id;die;
		$this->_dbDelete('web_capacitacion_cat', array('id_web_capacitacion_cat' => $id));
	}
	else
		foreach($input['titulo'] as $k => $f)
		{
			$data = array(
			'titulo' => $input['titulo'][$k],
			'orden' => $input['orden'][$k]
			);

			if (! empty($_FILES['imagen']['name'][$k]) && ! $_FILES['imagen']['error'][$k])
			{
				$filename = tempnam('../'.$this->_imagesDir, 'cat');
				unlink($filename);
				$filename .= '-'.$_FILES['imagen']['name'][$k];

				if (move_uploaded_file($_FILES['imagen']['tmp_name'][$k], $filename))
					//$data['imagen'] = $this->_config['baseURL'].$this->_imagesDir.basename($filename);
					$data['imagen'] = basename($filename);
			}

			// modificar publicidad
			if (isset($input['id'][$k]))
			{
				if (! empty($data['imagen'])) $this->_removeImage($input['id'][$k]);
				$this->_dbUpdate('web_capacitacion_cat', $data, array('id_web_capacitacion_cat' => $input['id'][$k]));
			}
			// agregar publicidad
			else
				$this->_dbInsert('web_capacitacion_cat', $data);
		}

	die('<script>parent.SISWEB.capacitacionCat._listCats();</script>');
}

private function _removeImage($id)
{
	$r = $this->_dbGetOne('web_capacitacion_cat', array('id_web_capacitacion_cat' => $id));
	//$filename = '../'.$this->_imagesDir.basename($r['imagen']);
	$filename = $this->_config['baseURL'].$this->_imagesDir.$r['imagen'];
	if (file_exists($filename)) unlink($filename);
}

}

require 'lib/exe.php';