<?php require 'lib/kernel.php';

// procesa la registración manual
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
'nombre' => array(
	'required' => true
),
'apellido' => array(
	'required' => true
),
'tel' => array(
	'required' => true
),
'error' => array(
	'required' => true
)
);

protected function _process($input)
{
	$data = array(
	'zona' => $input['zona'],
	'clienta' => $input['clienta'],
	'mail' => $input['email']
	);

	if (
	($r = $this->_dbGetOne('web_registracion_manual', $data)) === false
	|| empty($r['fecha'])
	|| time() - strtotime($r['fecha']) > 86400
	)
	{
		$negocio = '';
		$region = 0;
		$email = '';
		$regionCorreo = '';
		$regionGerente = '';

		if ($input['error'] == 3)
			$causa = 'IMPOSIBLE DESCUBRIR ERROR AUTOMATICAMENTE';
		else if ($input['error'] == 4)
			$causa = 'ZONA + CLIENTE - NO EXISTE EN LA TABLA DE CLIENTES';
		else if ($input['error'] == 8)
		{
			$r = $this->_dbQuery('SELECT id_cli_zonas, numero_cliente FROM web_cache_clientes WHERE LOWER(mail) = ? OR LOWER(dni) = ?', array(strtolower(trim($input['email'])), strtolower(trim($input['dni']))));
			$causa = 'Usuario ingreso la ZONA-CLIENTE equivocada ('.$r[0]['id_cli_zonas'].'-'.$r[0]['numero_cliente'].')';
		}
		else
		{
			$r = $this->_dbGetOne('web_cache_clientes',
			array('id_cli_zonas' => $input['zona'], 'numero_cliente' => $input['clienta']),
			array('mail', 'dni', 'negocio', 'region', 'mail'));

			$negocio = $r['negocio'];
			$region = $r['region'];
			$email = $r['mail'];

			$causa = 'NO COINCIDE '.($input['error'] == 1 ? 'DNI' : 'EMAIL').' ('.($input['error'] == 1 ? $r['dni'] : $r['mail']).')';

			$r = $this->_dbGetOne('web_usuarios', array('region' => $region), array('mail', 'nombre', 'apellido'));

			$regionCorreo = $r['mail'];
			$regionGerente = $r['nombre'].' '.$r['apellido'];
		}

		$detalles = '
		<li>Zona: '.$input['zona'].'</li>
		<li>Clienta: '.$input['clienta'].'</li>
		<li>Email ingresado: '.$input['email'].'</li>
		';

		$this->_dbInsert('web_registracion_manual', array_merge($data, array(
		'nombre' => $input['nombre'],
		'apellido' => $input['apellido'],
		'dni' => $input['dni'],
		'telefono' => $input['tel'],
		'fecha' => date('Y-m-d H:i:s'),
		'negocio' => $negocio,
		'region' => $region,
		'region_correo' => $regionCorreo,
		'region_gerente' => $regionGerente,
		'clienta_email' => $email,
		'causa' => $causa,
		'detalles' => $detalles
		)));

		$body = '<p>Ha recibido una registración manual</p><p>Posible causa: <b>'.$causa.'</b></p><p>Datos ingresados:<ul>'.$detalles.'
		<li>Región: '.$region.'</li>
		<li>Negocio: '.$negocio.'</li>
		<li>DNI: '.$input['dni'].'</li>
		<li>Email de clienta: '.$email.'</li>
		<li>Nombre: '.$input['nombre'].'</li>
		<li>Apellido: '.$input['apellido'].'</li>
		<li>Teléfono: '.$input['tel'].'</li>
		</ul></p>';

		$this->_sendmail($this->_config['notifyEmailRegister'], 'Nueva registración manual', $body);

		return array('ok' => true);
	}

	return array('ok' => false);
}

}

require 'lib/exe.php';