<?php require 'lib/kernel.php';

// obtener los colores asociados a un tipo
class APIGETATTRIB extends JBSYSWEBAPI
{

protected $_ids = array(
array('color', 'Color', 'colores', 'color', false, 'codigo_color'),
array('talle', 'Talle', 'talles', 'tall', true)
);

protected $_input = array(
'tipo' => array(
	'required' => true
)
);

}

require 'lib/getAttrib.php';