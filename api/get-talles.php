<?php require 'lib/kernel.php';

// devuelve los talles asociados a un tipo y color
class APIGETATTRIB extends JBSYSWEBAPI
{

protected $_ids = array(
array('talle', 'Talle', 'talles', 'tall', true)
);

protected $_idFmt = '%03d';

protected $_input = array(
'tipo' => array(
	'required' => true
),
'color' => array(
	'required' => true
)
);

}

require 'lib/getAttrib.php';