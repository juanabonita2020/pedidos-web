<?php require 'lib/kernel.php';

// registra un usuario
class API extends JBSYSWEBAPI
{

protected $_checkSession = false;

protected $_input = array(
'email' => array(
	'required' => true
),
'dni' => array(
	'required' => true
),
'zona' => array(
	'required' => true
),
'clienta' => array(
	'required' => true
),
'password' => array(
	'required' => true
)
);

protected $_logReq = true;

protected function _process($input)
{
	$email = trim(strtolower($input['email']));
	$dni = trim(str_replace('.', '', $input['dni']));
	$clienta = intval($input['clienta']);

	$q = 'SELECT * FROM web_cache_clientes WHERE id_cli_zonas = ? AND numero_cliente = ? AND dni = ?';
	$params = array($input['zona'], $clienta, $dni);

	// empresaria
	if ($clienta == 1)
	{
		$q .= ' AND LOWER(mail) = ?';
		$params[] = $email;
	}
	// revendedora => chequear que no sea el primer usuario de la zona
	else
	{
		$r = $this->_dbQuery('SELECT id_web_usuarios FROM web_usuarios a, web_cache_clientes b WHERE a.id_cli_clientes = b.id_cli_clientes AND id_cli_zonas = ? LIMIT 1', array($input['zona']));
		$this->_logReqData[] = 'Búsqueda: '.print_r($r, true);

		if (! isset($r[0]))
		{
			$r = $this->_dbGetOne('web_cache_clientes', array('id_cli_zonas' => $input['zona'], 'numero_cliente' => 1), array('negocio'));
			return array('error' => 7, 'msg' => $r['negocio'] == 'D' ? 'líder' : 'empresaria');
		}
	}

	$r = $this->_dbQuery($q, $params);
	$this->_logReqData[] = 'Cliente: '.print_r($r, true);

	// no se encontró cliente con los datos ingresados
	if (! isset($r[0]))
	{
		if (($r = $this->_dbGetOne('web_cache_clientes',
		array('id_cli_zonas' => $input['zona'], 'numero_cliente' => $clienta),
		array('mail', 'dni'))) === false)
		{
			// verificar si el DNI o EMAIL se encuentra en otra zona-clienta
			$r = $this->_dbQuery('SELECT id_cli_zonas FROM web_cache_clientes WHERE LOWER(mail) = ? OR LOWER(dni) = ?', array($email, $dni));
			if (isset($r[0])) return array('error' => 8);
			return array('error' => 4);
		}

		// email coincide, dni NO coincide
		if (strtolower($r['mail']) == $email || $clienta != 1)
			return array('error' => 1);
		// dni coincide, email NO coincide
		else if ($r['dni'] == $dni)
			return array('error' => 2);

		// NO coincide ni email ni dni
		return array('error' => 3);
	}

	if ($this->_dbGetOne('web_usuarios', array('id_cli_clientes' => $r[0]['id_cli_clientes'])) !== false)
		return array('error' => 5);

	if ($this->_dbGetOne('web_usuarios', array('mail' => $email)) !== false)
		return array('error' => 6);

	$this->_dbInsert('web_usuarios', array('id_cli_clientes' => $r[0]['id_cli_clientes'], 'mail' => $email, 'nombre' => '', 'apellido' => '', 'habilitada' => 1, 'cantidad_cierres' => $this->_global['cant_cierres_defecto']));

	$this->_setPassword($this->_dbInsertId, $input['password']);

	// empresaria
	if ($clienta == 1 && $this->_dbGetOne('web_campanias_zonas', array('id_cli_zonas' => $input['zona'])) === false)
	{
		$r = $this->_dbGetOne('web_cache_clientes', array('id_cli_clientes' => $r[0]['id_cli_clientes']));
		$this->_dbInsert('web_campanias_zonas', array('id_cli_zonas' => $input['zona'], 'id_web_campanias' => $this->_getMinorCamp($r['sistema'])));
	}

	return array('error' => 0);
}

}

require 'lib/exe.php';