<?php require 'lib/kernel.php';

// procesa el login de un usuario
class API extends JBSYSWEBAPI
{

// servicio que funciona sin sesión
protected $_checkSession = false;

protected $_checkMaintMode = false;

protected $_input = array(
'email' => array(
	'required' => true
),
'password' => array(
	'required' => true
),
'crossDomain' => array(
	'required' => true
),
'bridge' => array(
)
);

//~ protected $_logReq = true;

protected function _process($input)
{
	$email = strtolower($input['email']);

	// log
	$log = array('email' => $email);

	// comprobamos que el usuario esté registrado
	$r = $this->_dbGetOne('web_usuarios', array('mail' => $email));
	$this->_logReqData[] = 'Usuario: '.print_r($r, true);
	//~$debug = array($r);

	// usuario registrado
	if (isset($r['id_web_usuarios']))
	{
		// verificamos que el usuario esté habilitado
		if ($r['habilitada'])
		{
			// verificamos que el cliente exista
			if (empty($r['id_cli_clientes']))
				$sistema = $r['sistema'];
			else
			{
				$r2 = $this->_dbGetOne('web_cache_clientes', array('id_cli_clientes' => $r['id_cli_clientes']));
				$sistema = $r2['sistema'];

				if ($r2['numero_cliente'] == 1 && $r2['baja'])
					$alert = 'Su usuario está bloqueado temporalmente.<br />Podrá utilizar el sistema normalmente pero no podrá realizar el envío de los pedidos.<br />Por favor contáctese con su Gerenta para regularizar su situación.';

				if (empty($r2['id_cli_clientes']))
				{
					$res = array('error' => 3);
					$log['status'] = 'usuario inhabilitado';
				}
			}

			if (! isset($res))
			{
				//$hash = $this->_hash($r['id_web_usuarios'].$input['password']);
				//$this->_logReqData[] = 'Hash='.$hash;

				$validated = false;
				$claveMaestra = false;

				// comprobar contraseña de usuario
				if (! ($validated = $this->_validateHash($r['id_web_usuarios'].$input['password'], $r['password'])))
					// comprobar contraseña maestra global
					if (! empty($this->_global['clave_maestra']))
						if ($validated = $this->_validateHash($input['password'], $this->_global['clave_maestra']))
							$claveMaestra = true;

				// comprobar contraseña maestra regional
				if (! $validated)
				{
					// es un usuario "no regional", usar la contraseña de su regional
					if (empty($r['region']))
					{
						if (! empty($r['id_cli_clientes']))
						{
							// obtenemos el usuario regional
							$r3 = $this->_dbGetOne('web_usuarios', array('region' => $r2['region']));
							//~$debug[] = $r2;
							$password_m = $r3['password_m'];
						}
					}
					// es un usuario regional, usar la contraseña maestra de su registro					
					else{
						$password_m = $r['password_m'];
					}
					
						

					//~$debug[] = $password_m;

					if (! empty($password_m))
						$validated = $this->_validateHash($input['password'], $password_m);
				}



		/**********************************************************************************************************************************************************/			
		
				// comprobar contraseña maestra divisional en usuario regional
				if (! $validated)
				{
						if (!empty($r['region']))
						{
							if( empty($r['id_cli_clientes']) ){

								$div = $this->_dbQuery('SELECT c.division FROM web_cache_clientes c WHERE c.region = ? AND numero_cliente = 1 AND baja = 0 LIMIT 1', array($r['region']));

								$r4 = $this->_dbGetOne('web_usuarios', array('division' => $div[0]['division'], 'region' => 0 ));
											
								$password_m = $r4['password_m'];	
							}
						}	

						if (! empty($password_m))
						$validated = $this->_validateHash($input['password'], $password_m);
				}

		/**********************************************************************************************************************************************************/




/********************************************************************************************************************************************************/

				/*		Validación agregada para que los usuarios divisionales puedan ingresar a los perfiles de los usuarios de su división	*/

				// comprobar contraseña maestra divisional
				if (! $validated)
				{
					// es un usuario "no divisional", usar la contraseña de su regional
					if (empty($r['division']))
					{
						if (! empty($r['id_cli_clientes']))
						{
							// obtenemos el usuario divisional
							$r3 = $this->_dbGetOne('web_usuarios', array('division' => $r2['division'], 'region' => 0 ));
							//~$debug[] = $r2;
							$password_m = $r3['password_m'];
						}
					}
					// es un usuario divisional, usar la contraseña maestra de su registro
					else
						$password_m = $r['password_m'];

					//~$debug[] = $password_m;

					if (! empty($password_m))
						$validated = $this->_validateHash($input['password'], $password_m);
				}

		

/********************************************************************************************************************************************************/


				// contraseña correcta
				if ($validated && ($claveMaestra || empty($this->_config['maintMode'])))
				{
					// actualizamos la fecha de login
					$this->_dbQuery('UPDATE web_usuarios SET ultimo_login = NOW() WHERE id_web_usuarios = ?', array($r['id_web_usuarios']));

					// guardar estadísticas
					if (! empty($this->_config['usageStats']))
					{
						$now = date('YmdH');
						$this->_saveStat('login-'.$now, 1, 'Login OK', 'Login', $now);
					}

					$this->_saveSessionVar('userId', $r['id_web_usuarios']);
                                        if( isset($r2) )
                                            $this->_saveSessionVar('userNro', sprintf('%04s', $r2['numero_cliente']));
					$this->_saveSessionVar('userSistema', sprintf('%04s', $sistema));
					$this->_saveSessionVar('closeAlert', ($r['alerta_cerrarpedido'] == 1));
					$this->_saveSessionVar('regionalNegocio', $r['regional_negocio']);
					$this->_saveSessionVar('claveMaestra', $claveMaestra);
					$this->_saveSessionVar('userName', $email);
					if (! empty($alert))
						$this->_saveSessionVar('userAlert', $alert);

					$type = $division = $region = 0;

					// usuario no cliente
					if (empty($r['id_cli_clientes']))
					{
						$this->_saveSessionVar('userClient', '');
						$this->_saveSessionVar('userZone', '');
						$this->_saveSessionVar('userParent', 0);

						// usuario de la API
						if (! empty($r['api_user']))
							$type = JBSYSWEBAPI::USR_API;
						// si no tiene region ni division es un usuario administrador
						else if (empty($r['region']) && empty($r['division']))
							$type = JBSYSWEBAPI::USR_ADMIN;
						// si no tiene region, es un usuario divisional
						else if (empty($r['region']))
						{
							$type = JBSYSWEBAPI::USR_DIVIS;
							$division = $r['division'];
						}
						// sino, es un usuario regional
						else
						{
							$type = JBSYSWEBAPI::USR_REGIO;
							$region = $r['region'];
						}
					}

					$this->_saveSessionVar('userRegion', $region);
					$this->_saveSessionVar('userDivision', $division);
					if (! empty($type))
						$this->_saveSessionVar('userType', $type);

					// usuario cliente
					if (! empty($r['id_cli_clientes']))
						$this->_loginUser($r['id_cli_clientes']);

					// sesión iniciada
					$res = array(
					'error' => 0,
					'global' => $this->_getPublicGlobal(),
					'alert' => isset($alert) ? $alert : null,
					'siteURL' => $this->_config['baseURL']
					);

					if (! empty($input['crossDomain']))
						$res['sessid'] = session_id();

					$log['status'] = 'exitoso';
				}
				// contraseña incorrecta
				else
				{
					$res = array('error' => 2);
					$log['status'] = 'password invalida';
				}
			}
		}
		else
		{
			$res = array('error' => 3);
			$log['status'] = 'usuario inhabilitado';
		}
	}
	// usuario no registrado
	else
	{
		$res = array('error' => 1);
		$log['status'] = 'usuario inexistente';
	}

	//$res['status'] = $log['status'];

	// log
	$this->_dbInsert('web_login_log', $log);

	//~$res['_debug'] = $debug;

	if (! empty($input['bridge']) && empty($res['error']))
		die('<script>window.location = ".."</script>');

	return $res;
}

}

require 'lib/exe.php';
