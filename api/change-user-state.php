<?php require 'lib/kernel.php';

// habilitamos o no a un usuario
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_EMPRE);

protected $_input = array(
'idUsuario' => array(
	'required' => true
),
'habilitado' => array(
	'required' => true
)
);

protected function _process($input)
{
	// verificamos que la empresaria tenga acceso sobre el usuario
	if (! $this->_isMyClient($input['idUsuario'])) $this->_forbidden();

	$this->_dbUpdate('web_usuarios', array('habilitada' => $input['habilitado'] == 'false' ? 0 : 1), array('id_cli_clientes' => $input['idUsuario']));
}

}

require 'lib/exe.php';