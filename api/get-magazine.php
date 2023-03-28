<?php require 'lib/kernel.php';

// devuelve los detalles de la revista virtual
class API extends JBSYSWEBAPI
{

protected $_input = array(
'campania' => array(
	'required' => true
)
);

protected function _process($input)
{
	return array('pages' => 8, 'prop' => .7276);
}

}

require 'lib/exe.php';