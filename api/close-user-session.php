<?php require 'lib/kernel.php';

// logout del usuario
class API extends JBSYSWEBAPI
{

protected function _process($input)
{
	unset($_SESSION['jbsysweb']);
	return array('res_ok' => true);
}

}

require 'lib/exe.php';