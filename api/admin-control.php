<?php require 'lib/kernel.php';

// controles y modificaciones
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_ADMIN);

protected $_input = array(
'action' => array(
	'required' => true
),
'data1' => array(
),
'data2' => array(
),
'data3' => array(
),
'data4' => array(
),
'data5' => array(
)
);

protected $_logReq = true;

protected function _process($input)
{
	// corregir codificación de datos
	if ($input['action'] == 20)
	{
		$this->fixEncoding($input['data1']);
		return array('msg' => 'Codificación de catálogo de productos corregida.');
	}
	// manejar el control del catálogo y la comunidad
	else if ($input['action'] == 21 || $input['action'] == 22)
	{
		$var = ($input['action'] == 21 ? 'ocultar_catalogo' : 'desactivar_comunidad');
		$val = empty($this->_global[$var]) ? 1 : 0;
		$this->_saveGlobal($var, $val);
		return array('val' => $val);
	}
	else if ($input['action'] >= 10)
	{
		$cliente = intval($input['data2']);

		// blanquear contraseña
		if ($input['action'] == 10)
		{
			// buscar por email
			if (empty($cliente))
			{
				$q = 'b.mail = ?';
				$p = array($input['data1']);
			}
			// buscar por cliente
			else
			{
				$q = 'id_cli_zonas = ? AND numero_cliente = ?';
				$p = array($input['data1'], $cliente);
			}
			
			// buscar el usuario
			$r = $this->_dbQuery('SELECT id_web_usuarios FROM web_usuarios b LEFT JOIN web_cache_clientes a ON a.id_cli_clientes = b.id_cli_clientes WHERE '.$q, $p);
			if (! isset($r[0])) return array('msg' => 'No se encuentra el cliente.');

			if (empty($input['data3']) || empty($input['data4']) || $input['data3'] != $input['data4'])
				return array('msg' => 'Debe ingresar las contraseñas y deben coincidir.');

			//print_r($r[0]);

			$this->_setPassword($r[0]['id_web_usuarios'], $input['data3']);
			return array('msg' => 'Contraseña blanqueada.');
		}
		else
		{
			// buscar cliente (action=11,12)
			if ($input['action'] == 11 || $input['action'] == 12)
			{
				$q = 'SELECT dni, mail, baja, id_web_cache_clientes FROM web_cache_clientes WHERE ';
				$lbl = 'cliente';
				$tbl = 'web_cache_clientes';
			}
			// buscar usuario (action=13,14)
			else
			{
				$q = 'SELECT a.mail, id_web_usuarios FROM web_usuarios a, web_cache_clientes b WHERE a.id_cli_clientes = b.id_cli_clientes AND ';
				$lbl = 'usuario';
				$tbl = 'web_usuarios';
			}

			$r = $this->_dbQuery($q.'id_cli_zonas = ? AND numero_cliente = ?', array($input['data1'], $cliente));
			if (! isset($r[0]))
				return array('msg' => 'No se encuentra el '.$lbl.'.');

			// modificar datos de cliente/usuario
			if ($input['action'] == 11 || $input['action'] == 14)
			{
				$email = trim(strtolower($input['action'] == 11 ? $input['data4'] : $input['data3']));
				if (strpos($email, '@') === false)
					return array('msg' => 'Email inválido.');

				$r2 = $this->_dbQuery('SELECT mail FROM '.$tbl.' WHERE TRIM(LOWER(mail)) = ? LIMIT 1', array($email));
				if (isset($r2[0]) && $r[0]['mail'] != $email)
					return array('msg' => 'El e-mail se encuentra en uso.');

				// modificar datos de cliente
				if ($input['action'] == 11)
				{
					$dni = trim(str_replace('.', '', $input['data3']));
					$this->_dbUpdate('web_cache_clientes', array('dni' => $dni, 'mail' => $email, 'baja' => $input['data5']), array('id_web_cache_clientes' => $r[0]['id_web_cache_clientes']));
				}
				// modificar datos del usuario
				else
					$this->_dbUpdate('web_usuarios', array('mail' => $email), array('id_web_usuarios' => $r[0]['id_web_usuarios']));

				return array('msg' => 'Datos modificados.');
			}

			// obtener datos del cliente/usuario
			return $r;
		}

		die;
	}

	switch($input['action'])
	{
	case 8:
	{
		if (empty($input['data1']) && empty($input['data2'])) return array();
		
		$q = 'SELECT
		a.id_cli_clientes id, numero_cliente nro, nombre, id_web_pedidos pedido, fecha_carga fpedido, a.id_web_envios envio, fecha_creacion fenvio, c.id_cli_zonas clizona, b.id_cli_zonas envzona
		FROM 
		web_pedidos a,
		web_envios b,
		web_cache_clientes c
		WHERE
		a.id_web_envios	= b.id_web_envios	
		AND a.id_cli_clientes = c.id_cli_clientes
		AND b.id_cli_zonas <> c.id_cli_zonas';
		
		$params = array();
		
		if (! empty($input['data1']))
		{
			$q .= ' AND c.id_cli_zonas = ?';
			$params[] = $input['data1'];
		}
		
		if (! empty($input['data2']))
		{
			$q .= ' AND a.id_cli_clientes = ?';
			$params[] = $input['data2'];
		}
		
		$q .= ' ORDER BY a.id_cli_clientes, a.id_web_envios';
		
		break;
	}
	// estado general del sistema
	case 1:
	{
		$q = '
		select
		count(*) cantidad_clientes ,
		max(ts) ultima_importacion ,
		( select min(id_web_campanias) cp_ini from web_campanias where habilitado = 1 ) cp_ini ,
		( select max(id_web_campanias) cp_ini from web_campanias where habilitado = 1 ) cp_fin  ,
		( select max(id_web_campanias) max_cp_preventa from web_cache_preventa  ) max_cp_preventa  ,
		( select fecha from web_cache_preventa  where id_web_campanias = (select max(id_web_campanias) cp_ini from web_campanias where habilitado = 1  ) ) max_cp_preventa2
		from web_cache_clientes
		';

		$params = array();

		break;
	}
	// busqueda por zona y cliente
	case 5:
	case 7:
	{
		if (empty($input['data1']) || empty($input['data2'])) return array();

		if ($input['action'] == 5)
		{
			$q = '
			select
			case when u.mail is null then "SIN USUARIO" else "CON USUARIO" end tiene_usuario ,
			case when u.region is null and u.id_cli_clientes is null and u.mail is not null then "ADMIN"
				 when u.region is not null and u.mail is not null then "REGIONAL"
				 when c.numero_cliente = 1 and c.negocio = "D" then "LIDER"
				 when c.numero_cliente = 1 and c.negocio = "E" then "EMPRESARIA"
				 when c.numero_cliente <> 1 and c.es_coordinador = 1  then "COORDINADORA"
				when c.numero_cliente <> 1 and c.es_coordinador = 0  then "VENDEDORA"
				else "OTROS" end tipo_usuario ,
				c.id_cli_zonas,
			   c.numero_cliente,
			   u.mail as usuario ,
			   c.mail as mail_base_clientes,
				c.baja  ,
				u.ultimo_login,
				habilitada
			from web_cache_clientes c
			left join web_usuarios u on c.id_cli_clientes = u.id_cli_clientes
			where c.id_cli_zonas = ? and c.numero_cliente = ?
			order by ultimo_login desc
			LIMIT 20
			';

			$params = array($input['data1'], $input['data2']);
		}

		else
		{
			$q = '
			select CONCAT_WS("-", zona, clienta) "Zona-clienta", CONCAT_WS(", ", apellido, nombre) "Nombre completo", mail, dni, fecha, CONCAT_WS("<br />", causa, detalles) "Causa-Detalles", clienta_email, negocio, leido from web_registracion_manual
			where zona LIKE ? and clienta LIKE ?
			and fecha >= NOW() - INTERVAL 30 DAY
			order by fecha desc limit 20
			';

			$params = array('%'.$input['data1'].'%', '%'.$input['data2'].'%');
		}

		break;
	}
	// estado de la zona
	case 6:
	{
		$q = '
		select
			e.id_cli_zonas,
			e.id_web_campanias,
			e.id_web_envios,
			count(distinct p.id_cli_clientes ) pedidos ,
			sum( d.cantidad ) cantidad  , sum(d.cantidad_preventa) preventa ,
			case when e.fecha_recepcion is null and fecha_envio is null then "Abierto"
				when e.fecha_recepcion is null and fecha_envio is not null then "Cerrado"
				when e.fecha_recepcion is not null and fecha_envio is not null then "Recepcionado"
				else "otro" end caso ,
		   s.accion
		from web_envios e
		inner join web_pedidos p on e.id_web_envios = p.id_web_envios
		inner join web_pedidos_detalle d on d.id_web_pedidos = p.id_web_pedidos
		inner join web_estados s on s.estado = p.estado
		where  e.id_cli_zonas = ?
		group by     e.id_web_campanias , e.id_web_campanias,
			case when e.fecha_recepcion is null and fecha_envio is null then "Abierto"
				when e.fecha_recepcion is null and fecha_envio is not null then "Cerrado"
				when e.fecha_recepcion is not null and fecha_envio is not null then "Recepcionado"
				else "otro" end ,  s.accion , e.id_web_envios
		order by id_web_envios desc
		limit 20
		';

		$params = array($input['data1']);

		break;
	}
	default:
	{
		if (empty($input['data1'])) return array();

		switch($input['action'])
		{
		// búsqueda por correo en usuarios
		case 2:
			$q = '
			select
			case when u.region is null and u.id_cli_clientes is null then "ADMIN"
				 when u.region is not null then "REGIONAL"
				 when c.id_cli_zonas is null then "INHABILITADO"
				 when c.numero_cliente = 1 and c.negocio = "D" then "LIDER"
				 when c.numero_cliente = 1 and c.negocio = "E" then "EMPRESARIA"
				 when c.numero_cliente <> 1 and c.es_coordinador = 1  then "COORDINADORA"
				when c.numero_cliente <> 1 and c.es_coordinador = 0  then "VENDEDORA"
				else "OTROS" end tipo_usuario ,
				c.id_cli_zonas,
			c.numero_cliente,
			u.mail as usuario ,
			c.mail as mail_base_clientes,
			c.baja  ,
			u.ultimo_login
			from web_usuarios u
			left join web_cache_clientes c on c.id_cli_clientes = u.id_cli_clientes
			where u.mail like ?
			order by ultimo_login desc
			LIMIT 20
			';
			break;
		// búsqueda por correo en clientes
		case 3:
			$q = '
			select
			c.id_cli_zonas,
			c.numero_cliente,
			c.mail as mail_base_clientes,
			c.baja  ,
			case when u.mail is null then "SIN USUARIO" else "CON USUARIO" end tiene_usuario
			from web_cache_clientes c
			left join web_usuarios u on u.id_cli_clientes = c.id_cli_clientes
			where c.mail like ?
			order by u.ultimo_login desc
			LIMIT 20
			';
			break;
		// búsqueda de intentos de login
		case 4:
			$q = '
			select email, date_format(fecha_hora,  "%Y%m%d")  dia  , status , count(*) q
			from web_login_log
			where email like ?
			group by email,  date_format(fecha_hora, "%Y%m%d") , status
			order by dia desc
			LIMIT 20
			';
			break;
		}

		$params = array('%'.$input['data1'].'%');
	}
	}

	//print_r($params);die;

	if (isset($q)) return $this->_dbQuery($q, $params);
}

}

require 'lib/exe.php';