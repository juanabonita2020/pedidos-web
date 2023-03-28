<?php require 'lib/kernel.php';

// devuelve los tipos
class APIGETATTRIB extends JBSYSWEBAPI
{

protected $_ids = array(
array('tipo', 'Tipo', 'tipos', 'type', false, 'codigo_tipo'),
array('color', 'Color', 'colores', 'color', false, 'codigo_color'),
array('talle', 'Talle', 'talles', 'tall', true)
);

}

require 'lib/getAttrib.php';