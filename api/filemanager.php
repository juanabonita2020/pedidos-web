<?php require 'lib/kernel.php';

// administrador de ficheros
class API extends JBSYSWEBAPI
{

protected $_input = array(
'root' => array(
	'required' => true
),
'action' => array(
	'required' => true
),
'path' => array(
	'required' => true
),
'src' => array(
),
'dst' => array(
)
);

private $_roots = array(
'CNT' => array(array(JBSYSWEBAPI::USR_ADMIN), 'contenidos/')
);

private $_imageExt = array('PNG', 'JPG', 'JPEG', 'GIF');

private $_sizeU = array('bytes', 'kB', 'MB', 'GB');

protected function _process($input)
{
	// normalizar path
	$path = trim($input['path']);
	if (substr($path, 0, 1) == '/') $path = substr($path, 1);

	if (
	// root no existe
	! isset($this->_roots[$input['root']])
	// acceso no permitido
	|| ! in_array($this->_userType, $this->_roots[$input['root']][0])
	// path inválido
	|| strpos($path, '.') !== false
	) return false;

	$isRoot = empty($path);
	$path = '../'.$this->_roots[$input['root']][1].$path;

	// path no existe o no es un directorio
	if (! file_exists($path) || ! is_dir($path)) return false;

	// verificar fichero origen
	$src = trim($input['src']);
	if (! empty($src) && ($src == '.' || $src == '..' || strpos($src, '/') !== false || ! file_exists($path.$src))) return false;

	// verificar fichero destino
	$dst = trim($input['dst']);
	if (! empty($dst) && ($dst == '.' || $dst == '..' || strpos($dst, '/') !== false || file_exists($path.$dst)))
		return array('error' => 'Nombre de fichero inválido.');

	switch($input['action'])
	{
	// obtener listado
	case 1:
	{
		$res = array('raiz' => $isRoot, 'ficheros' => array());

		$dirs = array();

		// recorrer el directorio
		foreach(scandir($path) as $d) if (substr($d, 0, 1) != '.')
		{
			$file = $path.$d;
			$info = pathinfo($file);

			$item = array(
			'nombre' => $d,
			'isDir' => is_dir($file),
			'url' => substr($file, 3)
			);

			$item['imagen'] = (! $item['isDir'] && in_array(strtoupper($info['extension']), $this->_imageExt));

			$item['bytes'] = filesize($file);
			$i = 0;
			while($item['bytes'] > 1024 && $i < 3)
			{
				$item['bytes'] /= 1024;
				$i++;
			}
			$item['bytes'] = number_format($item['bytes'], 2, ',', '.').' '.$this->_sizeU[$i];

			if ($item['isDir'])
			{
				$files = scandir($file);
				$item['del'] = (count($files) == 2);
				$dirs[] = $item;
			}
			else
			{
				$item['del'] = true;
				$res['ficheros'][] = $item;
			}
		}

		$res['ficheros'] = array_merge($dirs, $res['ficheros']);

		break;
	}
	// obtener miniatura
	//case 2:
	// crear carpeta
	case 3:
		mkdir($path.$dst);
		break;
	// eliminar carpeta
	case 4:
		rmdir($path.$src);
		break;
	// crear fichero
	case 5:
	{
		$filename = $path.$_FILES['fichero']['name'];
		if ($_FILES['fichero']['error'] || ! move_uploaded_file($_FILES['fichero']['tmp_name'], $filename)) return false;
		die('<script>parent.SISWEB.ficheros.list()</script>');
	}
	// eliminar fichero
	case 6:
		unlink($path.$src);
		break;
	// renombrar
	case 7:
		rename($path.$src, $path.$dst);
		break;
	default:
		return false;
	}

	return $res;
}

}

require 'lib/exe.php';