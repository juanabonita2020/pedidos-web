<?php require 'lib/kernel.php';

// devolvemos los usuarios de la región
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_REGIO, JBSYSWEBAPI::USR_DIVIS);

protected $_input = array(
'zona' => array(
),
'region' => array(
),
'cliente' => array(
),
'nombre' => array(
),
'bloq' => array(
),
'orderby' => array(
),
'pg' => array(
)
);

//protected $_logReq = true;

protected function _process($input)
{
	$field = ($this->_userType == JBSYSWEBAPI::USR_REGIO ? 'region' : 'division');
	
	$q = '
	FROM
		web_usuarios a
		LEFT JOIN web_cache_clientes b ON a.id_cli_clientes = b.id_cli_clientes
	WHERE
		(case when a.'.$field.' is null then b.'.$field.' else a.'.$field.' end) = ?';

	$params = array($this->_userType == JBSYSWEBAPI::USR_REGIO ? $this->_userRegion : $this->_userDivision);

	if (! empty($input['nombre']))
	{
		$q .= ' AND concat(upper(a.apellido),", ",upper(a.nombre)) LIKE ?';
		$params[] = '%'.$input['nombre'].'%';
	}

	foreach(array(
	'cliente' => 'b.numero_cliente',
	'zona' => 'b.id_cli_zonas',
	'region' => 'b.region',
	) as $f1 => $f2) if (! empty($input[$f1]))
	{
		$q .= ' AND '.$f2.' = ?';
		$params[] = $input[$f1];
	}
	
	if (! empty($input['bloq'])) $q .= ' AND baja = 1';

	$orderBy = ' ORDER BY ';
	if (empty($input['orderby']))
		$orderBy .= ' Ordenamiento_por_defecto asc, ultimo_ingreo desc ';
	else
	{
		/*switch(abs($input['orderby']))
		{
		case 1: $orderBy .= 'e.fecha_envio'; break;
		case 2: $orderBy .= 'a.id_web_campanias'; break;
		case 3: $orderBy .= 'c.id_cli_zonas'; break;
		case 4: $orderBy .= 'c.nombre'; break;
		case 5: $orderBy .= 's.accion'; break;
		default:
		}

		if ($input['orderby'] < 0) $orderBy .= ' DESC';*/
	}

	$pager = $this->_dbPager($q, $params, empty($input['pg']) ? 1 : $input['pg'], 20, $orderBy, 'b.numero_cliente');

	$q = 'SELECT		 
	case 
    when a.region is null and a.id_cli_clientes is null and b.id_cli_zonas is null then "Administrador"
    when a.region = 0  and a.id_cli_clientes is null and b.id_cli_zonas is null and a.division is not null then "Divisional"    
    when a.region > 0 and a.id_cli_clientes is null and b.id_cli_zonas is null and a.division is not null then "Regional"
    when a.region is null and a.id_cli_clientes is not null and b.id_cli_zonas is not null and numero_cliente = 1 and b.negocio = "D" then "Lider"
    when a.region is null and a.id_cli_clientes is not null and b.id_cli_zonas is not null and numero_cliente = 1 and b.negocio = "E" then "Empresaria"
    when a.region is null and a.id_cli_clientes is not null and b.id_cli_zonas is not null and numero_cliente <> 1 then "Vendedora"
    when a.region is null and a.id_cli_clientes is not null and     b.id_cli_zonas is null and numero_cliente is null then "Cliente Eliminado"
    else "Sin Identificar" end TipoUsuario ,
    b.id_cli_zonas AS zona ,
    b.numero_cliente ,
	case when a.region is null then b.region else a.region end  region,
    concat(upper(a.apellido),", ",upper(a.nombre) )  AS nombre,
    a.mail AS mail  ,
    case when a.habilitada = 1 then "SI" ELSE "NO" END AS habilitada,
    ultimo_login as ultimo_ingreo,
    IF(baja IS NULL, 0, baja) bloqueado,
    case when a.region is null and a.id_cli_clientes is null and b.id_cli_zonas is null then 1
    when a.region = 0  and a.id_cli_clientes is null and b.id_cli_zonas is null and a.division is not null then 2
    when a.region > 0 and a.id_cli_clientes is null and b.id_cli_zonas is null and a.division is not null then 3
    when a.region is null and a.id_cli_clientes is not null and b.id_cli_zonas is not null and numero_cliente = 1 and b.negocio = "D" then 4
    when a.region is null and a.id_cli_clientes is not null and b.id_cli_zonas is not null and numero_cliente = 1 and b.negocio = "E" then 4
    when a.region is null and a.id_cli_clientes is not null and b.id_cli_zonas is not null and numero_cliente <> 1 then 5
    when a.region is null and a.id_cli_clientes is not null and     b.id_cli_zonas is null and numero_cliente is null then 5 
    else 9 end Ordenamiento_por_defecto '.$q;

	$usuarios = $this->_dbQuery($q, $params);

	foreach($usuarios as $k => $r)
	{
		$usuarios[$k]['nombre'] = utf8_encode($r['nombre']);
		$usuarios[$k]['ultimo_ingreo'] = $this->_fromDate($r['ultimo_ingreo']);
		$usuarios[$k]['zona'] = intval($r['zona']);
		$usuarios[$k]['numero_cliente'] = intval($r['numero_cliente']);
	}

	return array('usuarios' => $usuarios, 'pager' => $pager);
}

}

require 'lib/exe.php';