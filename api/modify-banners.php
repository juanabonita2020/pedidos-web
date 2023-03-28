<?php require 'lib/kernel.php';

// obtenemos el listado de publicidades
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN);

private $_imagesDir = 'images/ads/';

protected $_input = array(
'id' => array(
),
'espacio' => array(
),
'campania' => array(
),
'desde' => array(
),
'hasta' => array(
),
'link' => array(
),
'action' => array(
)
);

protected function _process($input)
{
	//print_r($input);print_r($_FILES);die;

	// eliminar publicidad
	if (substr($input['action'], 0, 7) == 'remove_')
	{
		$id = substr($input['action'], 7);
		$this->_removeImage($id);
		$this->_dbDelete('web_publicidad', array('id_web_publicidad' => $id));
	}
	else
		foreach($input['espacio'] as $k => $f)
		{
			$data = array(
			'espacio' => $input['espacio'][$k],
			'id_web_campanias' => $input['campania'][$k],
			'link' => $input['link'][$k],
			'desde' => $this->_toDate($input['desde'][$k]),
			'hasta' => $this->_toDate($input['hasta'][$k])
			);

			if (! empty($_FILES['imagen']['name'][$k]) && ! $_FILES['imagen']['error'][$k])
			{
				$filename = tempnam('../'.$this->_imagesDir, 'pub');
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
				$this->_dbUpdate('web_publicidad', $data, array('id_web_publicidad' => $input['id'][$k]));
			}
			// agregar publicidad
			else if (! empty($input['espacio'][$k]))
				$this->_dbInsert('web_publicidad', $data);
		}

	die('<script>parent.SISWEB.banners._listBanners();</script>');
}

private function _removeImage($id)
{
	$r = $this->_dbGetOne('web_publicidad', array('id_web_publicidad' => $id));
	//$filename = '../'.$this->_imagesDir.basename($r['imagen']);
	$filename = $this->_config['baseURL'].$this->_imagesDir.$r['imagen'];
	if (file_exists($filename)) unlink($filename);
}

}

require 'lib/exe.php';