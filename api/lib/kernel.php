<?php $JBSYSWEBAPI = 1;

require 'framework/core.php';
require 'lib/Log.php';

class JBSYSWEBAPI extends FRAMEWORK
{

// tipos de usuarios
const USR_EMPRE = 1;
const USR_REVEN = 2;
const USR_ADMIN = 3;
const USR_COORD = 4;
const USR_CONSU = 5;
const USR_REGIO = 6;
const USR_DIVIS = 7;
const USR_API = 8;

// estados de pedidos
// revendedoras (clienta)
const OS_APP_REVEN = 20; //autorizado
const OS_REJ_REVEN = 30; //rechazado
const OS_OPE_REVEN = 40; //cargando/abierto
const OS_CLO_REVEN = 50; //cerrado
// empresaria
const OS_APP_EMPRE = 60; //autorizado
const OS_REJ_EMPRE = 70; //rechazado
const OS_OPE_EMPRE = 130; //cargando/abierto
const OS_CLO_EMPRE = 140; //cerrado
const OS_REM_EMPRE = 80; //borrado
const OS_SEN_EMPRE = 150; //enviado
const OS_NSN_EMPRE = 160; //no enviado
// coordinadora
const OS_APP_COORD = 110; //autorizado
const OS_REJ_COORD = 120; //rechazado
const OS_OPE_COORD = 90; //cargando/abierto
const OS_CLO_COORD = 100; //cerrado
// consumidora
const OS_CLO_CONSU = 10; //cerrado

protected $_userParent;
protected $_userClient;
protected $_userZone;
protected $_userCampaign;
protected $_userNegocio;
protected $_userCoordinadorNro;
protected $_userRegion;
protected $_userDivision;
protected $_userCloseAlert;
protected $_userSistema;
protected $_regionalNegocio;
protected $_openedStatus;
protected $_closedStatus;
protected $_validOpenedStatus;
protected $_approvedStatus;
protected $_validClosedStatus;
protected $_rejectedStatus;
protected $_validApprovedStatus;
protected $_validRejectedStatus;
protected $_global = array();
protected $_checkMaintMode = true;
protected $_sessionName = 'jbsysweb'; 

protected $_startTime = 0;
protected $_logEnabled = true;

private $_statsTitles = array(
'CAP1' => 'Capacitaciones',
'CAP2' => 'Material para descargar',
'CAP3' => 'Guiones'
);
private $_ip = 0;

function __construct()
{
	parent::__construct();

        $this->_startTime = microtime(TRUE);
           
	if (! $this->_isCron && ! isset($_SESSION['jbsysweb']))
		$_SESSION['jbsysweb'] = array();

	// estados según tipo de usuario
	$this->_openedStatus = array(0, self::OS_OPE_EMPRE, self::OS_OPE_REVEN, 0, self::OS_OPE_COORD, self::OS_OPE_REVEN, 0);
	$this->_closedStatus = array(0, self::OS_CLO_EMPRE, self::OS_CLO_REVEN, 0, self::OS_CLO_COORD, self::OS_CLO_CONSU, 0);
	$this->_approvedStatus = array(0, self::OS_APP_EMPRE, self::OS_APP_REVEN, 0, self::OS_APP_COORD, 0, 0);
	$this->_rejectedStatus = array(0, self::OS_REJ_EMPRE, self::OS_REJ_REVEN, 0, self::OS_REJ_COORD, 0, 0);
	$this->_sendedStatus = array(0, self::OS_SEN_EMPRE, 0, 0, 0, 0, 0);
	$this->_removedStatus = array(self::OS_REM_EMPRE, self::OS_REM_EMPRE, self::OS_REM_EMPRE, self::OS_REM_EMPRE, self::OS_REM_EMPRE, self::OS_REM_EMPRE, self::OS_REM_EMPRE);

	// agrupamos estados según su tipo
	$this->_validOpenedStatus = array(self::OS_OPE_REVEN, self::OS_OPE_EMPRE, self::OS_OPE_COORD);
	$this->_validClosedStatus = array(self::OS_CLO_REVEN, self::OS_CLO_EMPRE, self::OS_CLO_COORD, self::OS_CLO_CONSU);
	$this->_validApprovedStatus = array(self::OS_APP_REVEN, self::OS_APP_EMPRE, self::OS_APP_COORD);
	$this->_validRejectedStatus = array(self::OS_REJ_REVEN, self::OS_REJ_EMPRE, self::OS_REJ_COORD);
        
}

function __destruct() {
    if($this->_logEnabled){
        $dbName = $this->_config['dbName'];
        $dbHost = $this->_config['dbHost']; 
        if($dbName == 'juana_pw' && $dbHost == 'localhost' ){
            $log = new Log($this->_startTime);
            $log->loguear();
        } 
    }    
}

// procesa una llamada a la API
function process()
{
	// obtener la IP del cliente
	$this->_ip = inet_pton($_SERVER['REMOTE_ADDR']);

	// si se ingresa con clave maestra, desactivamos el modo mantenimiento
	if ($this->_getSessionVar('claveMaestra')) $this->_checkMaintMode = false;

	// ejecutar primera etapa del framework
	$input = array();
	if (($res = $this->_processStage1($input)) !== true) return $res;

	// si la sesión está abierta se cargan los datos del usuario
	if ($this->_userId !== null)
	{
		$this->_userCloseAlert = $this->_getSessionVar('closeAlert');
		$this->_userParent = $this->_getSessionVar('userParent');
		$this->_userClient = $this->_getSessionVar('userClient');
		$this->_userZone = $this->_getSessionVar('userZone');
		$this->_userNegocio = $this->_getSessionVar('negocio');
		$this->_userRegion = $this->_getSessionVar('userRegion');
		$this->_userDivision = $this->_getSessionVar('userDivision');
		$this->_userCoordinadorNro = $this->_getSessionVar('userCoordinadorNro');
		$this->_userSistema = intval($this->_getSessionVar('userSistema'));
	}

	$this->_regionalNegocio = $this->_getSessionVar('regionalNegocio');

	if ($this->_logReq)
		$this->_logReqData[] = 'User client: '.$this->_userClient;

	// cargamos la campaña de la zona del usuario
	$r = $this->_dbGetOne('web_campanias_zonas', array('id_cli_zonas' => $this->_userZone), array('id_web_campanias', 'id_web_campanias_zonas'));
	if (empty($r['id_web_campanias'])) // zona sin campaña
	{
		$r2 = $this->_dbQuery('SELECT a.id_web_campanias, orden_absoluto FROM web_campanias a, web_envios b WHERE a.id_web_campanias = b.id_web_campanias AND id_cli_zonas = ? AND fecha_envio IS NOT NULL GROUP BY a.id_web_campanias HAVING COUNT(*) >= ? ORDER BY orden_absoluto DESC LIMIT 1', array($this->_userZone, $this->_getCantCierres($this->_userId)));
		if ($this->_logReq) $this->_logReqData[] = 'R1: '.print_r($r2, true);
		if (empty($r2[0]['orden_absoluto']))
			$r2 = $this->_dbQuery('SELECT id_web_campanias FROM web_campanias WHERE habilitado AND sistema = ? ORDER BY orden_absoluto DESC LIMIT 1', array($this->_userSistema));
		else
			$r2 = $this->_dbQuery('SELECT id_web_campanias FROM web_campanias WHERE habilitado AND orden_absoluto > ? AND sistema = ? ORDER BY orden_absoluto LIMIT 1', array($r2[0]['orden_absoluto'], $this->_userSistema));
		if ($this->_logReq) $this->_logReqData[] = 'R2: '.print_r($r2, true);
		if (! empty($r2[0]['id_web_campanias']))
		{
			$this->_userCampaign = $r2[0]['id_web_campanias'];
			if (empty($r['id_web_campanias_zonas']))
				$this->_dbInsert('web_campanias_zonas', array('id_cli_zonas' => $this->_userZone, 'id_web_campanias' => $r2[0]['id_web_campanias']));
			else
				$this->_dbUpdate('web_campanias_zonas', array('id_web_campanias' => $r2[0]['id_web_campanias']), array('id_cli_zonas' => $this->_userZone));
		}
	}
	else
		$this->_userCampaign = $r['id_web_campanias'];

	// ejecutar segunda etapa del framework
	$this->_processStage2($input);
}

// devuelve los valores globales publicos
protected function _getPublicGlobal()
{
	$global = array();
	foreach(array('bombacha', 'inferior', 'jean', 'superior', 'soutien') as $t)
		foreach(array('minimo', 'maximo', 'progresion') as $s)
			$global[$t.'_'.$s] = $this->_global[$t.'_'.$s];
	$global['max_unid_item'] = $this->_global['max_unid_item'];
	$global['comunidad'] = $this->_getComunidad();
	$global['catalogo'] = empty($this->_global['ocultar_catalogo']);
	$global['prem_min_campania'] = $this->_global['prem_min_campania'];
	//~ $global['popup_unidades_premio'] = $this->_global['popup_unidades_premio'];
	return $global;
}

// devuelve si la comunidad está activa o no
protected function _getComunidad()
{
	return (($this->_userSistema == 12 || $this->_userSistema == 18) && empty($this->_global['desactivar_comunidad'])) || $this->_userType == JBSYSWEBAPI::USR_ADMIN;
}

// determina si un cliente pertenece al usuario logeado
protected function _isMyClient($clientId)
{
	if ($this->_logReq)
		$this->_logReqData[] = '_userClient='.$this->_userClient.', clientId='.$clientId;

	if ($this->_userClient == $clientId) return true;

	if ($this->_userType == JBSYSWEBAPI::USR_EMPRE || $this->_userType == JBSYSWEBAPI::USR_COORD)
	{
		$where = array('id_cli_clientes' => $clientId, 'id_cli_zonas' => $this->_userZone);

		if ($this->_userType == JBSYSWEBAPI::USR_COORD)
		{
			$where['es_coordinador'] = 0;
			$where['coordinador'] = $this->_userCoordinadorNro;
		}

		//print_r($where);

		if ($this->_dbGetOne('web_cache_clientes', $where) === false)
			return false;
	}
	else if ($this->_userType == JBSYSWEBAPI::USR_REGIO || $this->_userType == JBSYSWEBAPI::USR_DIVIS)
		return $this->_checkClientRegDiv($clientId, null);
	else if ($this->_userClient != $clientId)
		return false;

	return true;
}

// determina si un pedido pertenece al usuario logeado
protected function _isMyOrder($orderId)
{
	if (($r = $this->_dbGetOne('web_pedidos', array('id_web_pedidos' => $orderId))) === false) return false;

	return $this->_isMyClient($r['id_cli_clientes']);
}

// determina si un ítem de un pedido pertenece al usuario logeado
protected function _isMyOrderItem($itemId)
{
	if (($r = $this->_dbGetOne('web_pedidos_detalle', array('id_web_pedidos_detalle' => $itemId))) === false) return false;

	return $this->_isMyOrder($r['id_web_pedidos']);
}

// determina si una zona me pertenece
protected function _isMyZone($zoneId)
{
	// si soy regional sólo permito zonas que contengan clientes de mi región o división
	if ($this->_userType == JBSYSWEBAPI::USR_REGIO || $this->_userType == JBSYSWEBAPI::USR_DIVIS)
		return $this->_checkClientRegDiv(null, $zoneId);

	// si soy empresaria o coordinadora, sólo es válida mi zona
	return ($this->_userZone == $zoneId);
}

// envía un email al usuario logeado
protected function _sendmailToMe($subject, $body, $smtp = 1)
{
	return $this->_sendmail($this->_userName, $subject, $body, $smtp);
}

// obtiene la menor campaña habilitada
protected function _getMinorCamp($sistema = null)
{
	if ($sistema == null) $sistema = $this->_userSistema;
	$r = $this->_dbQuery('SELECT id_web_campanias FROM web_campanias WHERE habilitado AND sistema = ? ORDER BY orden_absoluto LIMIT 1', array($sistema));
	return $r[0]['id_web_campanias'];
}

// obtiene la cantidad de cierres del usuario
protected function _getCantCierres($userid = null)
{
	if ($userid == null) $userid = $this->_userId;
	$r = $this->_dbGetOne('web_usuarios', array('id_web_usuarios' => $userid), array('cantidad_cierres'));
	return $r['cantidad_cierres'];
}

// obtiene datos de cierre de una campaña
protected function _getCierre($campania, $zona = null)
{
	if ($zona == null) $zona = $this->_userZone;

	$r1 = $this->_dbQuery('SELECT COUNT(*) c FROM web_envios WHERE fecha_envio IS NOT NULL AND id_cli_zonas = ? AND id_web_campanias = ?', array($zona, $campania));
	//print_r($r1);
	//echo $this->_getCantCierres();

	$stts = array_merge(array($campania, $zona, JBSYSWEBAPI::OS_REM_EMPRE, JBSYSWEBAPI::OS_SEN_EMPRE), $this->_validApprovedStatus);

	//$r2 = $this->_dbQuery('SELECT id_web_pedidos, estado FROM web_pedidos WHERE id_web_campanias = ? AND estado NOT IN (?'.str_repeat(', ?', count($stts) - 2).')', $stts);
	$r2 = $this->_dbQuery('SELECT COUNT(*) c FROM web_pedidos a, web_cache_clientes b WHERE a.id_cli_clientes = b.id_cli_clientes AND id_web_campanias = ? AND id_cli_zonas = ? AND estado NOT IN (?'.str_repeat(', ?', count($stts) - 3).')', $stts);
	//print_r($r2);

	return array(
	'lastCierre' => ($r1[0]['c'] == $this->_getCantCierres() - 1 ? 1 : 0),
	'notToSendOrders' => ($r2[0]['c'] == null ? 0 : $r2[0]['c'])
	);
}

// obtiene el máximo de muestrarios para un determinado cliente y según el usuario logeado
protected function _getMaxMuestrario($cliId)
{
	$var = 'muestrario_';

	switch($this->_getUserType($cliId))
	{
	case self::USR_REVEN: $var .= 'revendedora'; break;
	case self::USR_COORD: $var .= 'coordinadora'; break;
	case self::USR_EMPRE: $var .= 'empresaria'.$this->_userNegocio; break;
	default: return 0;
	}

	return $this->_global[$var];
}

// obtiene el total de muestrarios que un cliente tiene para una campaña
protected function _getTotMuestrario($cliId, $campania)
{
	$r = $this->_dbQuery('SELECT SUM(cantidad) c FROM web_pedidos_detalle a, web_pedidos b WHERE muestrario = 1 AND a.id_web_pedidos = b.id_web_pedidos AND id_cli_clientes = ? AND id_web_campanias = ? AND a.estado <> ? AND b.estado <> ? AND a.estado <> ? AND b.estado <> ?', array($cliId, $campania, self::OS_REM_EMPRE, self::OS_REM_EMPRE, self::OS_NSN_EMPRE, self::OS_NSN_EMPRE));

	return ($r[0]['c'] == null ? 0 : $r[0]['c']);
}

// actualiza datos del usuario logeado
protected function _updateMyData($data)
{
	return $this->_dbUpdate('web_usuarios', $data, array('id_web_usuarios' => $this->_userId));
}

// realiza el login del usuario
protected function _loginUser($cliId)
{
	$this->_saveSessionVar('userClient', $cliId);

	$r = $this->_dbGetOne('web_cache_clientes', array('id_cli_clientes' => $cliId));
	$this->_saveSessionVar('userZone', $r['id_cli_zonas']);
	$this->_saveSessionVar('negocio', $r['negocio']);

	if ($r['numero_cliente'] > 1)
	{
		if ($r['es_coordinador'])
		{
			$this->_saveSessionVar('userType', JBSYSWEBAPI::USR_COORD);
			$this->_saveSessionVar('userCoordinadorNro', $r['coordinador']);
		}
		else
		{
			$this->_saveSessionVar('userType', JBSYSWEBAPI::USR_REVEN);
			$this->_saveSessionVar('userCoordinadorNro', '');
		}

		$r = $this->_dbGetOne('web_cache_clientes', array('numero_cliente' => 1, 'id_cli_zonas' => $r['id_cli_zonas']));
		$this->_saveSessionVar('userParent', $cliId);
	}
	else
	{
		$this->_saveSessionVar('userType', JBSYSWEBAPI::USR_EMPRE);
		$this->_saveSessionVar('userParent', 0);
		$this->_saveSessionVar('userCoordinadorNro', '');
	}
}

// comprobar si la campaña tiene envío abierto o si se puede crear uno
// devuelve FALSE si no hay envío abierto ni se puede crear uno
// devuelve el ID del envío si hay envío abierto
// devuelve TRUE en otro caso
protected function _checkCampEnvio($campania)
{
	// primero buscamos si hay un envío abierto para la campaña, si lo hay devolvemos su ID
	$r = $this->_dbQuery('SELECT id_web_envios FROM web_envios WHERE id_web_campanias = ? AND id_cli_zonas = ? AND fecha_envio IS NULL', array($campania, $this->_userZone));
	if (! empty($r[0]['id_web_envios'])) return $r[0]['id_web_envios'];

	// verificamos si se podrá crear un nuevo envío
	$cierre = $this->_getCierre($campania);
	return ! $cierre['lastCierre'];
}

// crea un nuevo envío para una campaña
protected function _newEnvio($campania, $tipoPedido = null, $zona = null)
{
	$data = array('id_cli_zonas' => $zona == null ? $this->_userZone : $zona, 'id_web_usuarios' => $this->_userId, 'id_web_campanias' => $campania);
	if ($tipoPedido != null) $data['tipo_pedido'] = $tipoPedido;
	$this->_dbInsert('web_envios', $data);
	return $this->_dbInsertId;
}

// quita un item de un pedido
protected function _removeOrderItem($itemId)
{

/***************************************************************************************************************************************/


  $r = $this->_dbGetOne('web_pedidos_detalle', array('id_web_pedidos_detalle' => $itemId));
  $stock = $this->_dbGetOne('web_cache_articulos_stock', array('codigo11' => $r['cod11']));

  if($stock  && $stock['stock_ilimitado'] != 1){
      $reservaNueva = $stock['reserva'] - $r['cantidad'];
      $cantidadTotalNueva = $stock['cantidad_total'] + $r['cantidad'];
      $this->_dbQuery('UPDATE web_cache_articulos_stock SET reserva = ?, cantidad_total = ?  WHERE id_web_cache_articulos_stock = ?', array( $reservaNueva, $cantidadTotalNueva, $stock['id_web_cache_articulos_stock'] ) );
  }


/***************************************************************************************************************************************/



	// obtener estado del ítem
	//$r = $this->_dbGetOne('web_pedidos_detalle', array('id_web_pedidos_detalle' => $itemId), array('estado'));
	//if ($this->_logReq)
	//$this->_logReqData[] = 'Estado: '.$r['estado'];

	// restauramos el stock y las cabecera (salvo si el ítem fue rechazado)
	//if (! in_array($r['estado'], $this->_validRejectedStatus))
	$this->_restoreOrderQ($itemId);

	// eliminamos el item
	//$this->_dbDelete('web_pedidos_detalle', array('id_web_pedidos_detalle' => $itemId));
	$this->_dbUpdate('web_pedidos_detalle', array('estado' => self::OS_REM_EMPRE, 'ip' => $this->_ip), array('id_web_pedidos_detalle' => $itemId));






/***************************************************************************************************************************************/

	/*		para quitar la relación de los items con la promoción si uno de los items de la promo es eliminado	*/	

	if( $r['id_web_promocion_relacion'] != null ){
		$items = $this->_dbQuery('SELECT 		d.estado, d.cantidad, d.cantidad_preventa, d.muestrario, d.talle, d.code, d.color, d.tipo, 
											d.idArticulo, d.compradora, d.fecha_alta, d.es_feria AS isFeria, d.id_web_pedidos_detalle,
											d.ptos_unitarios AS ptos, d.precio AS precioDb, d.cuotas, d.id_web_promocion_relacion AS idWebPromocionRelacion , p.codigo_promocion AS codigoPromocion

								FROM 		web_pedidos_detalle d
								LEFT JOIN 	web_promocion_relacion r ON r.id_web_promocion_relacion = d.id_web_promocion_relacion
								LEFT JOIN 	web_cache_promocion p ON p.id_web_cache_promocion = r.id_web_cache_promocion
								WHERE 		d.id_web_pedidos = ? AND d.id_web_promocion_relacion = ?', array( $r['id_web_pedidos'] , $r['id_web_promocion_relacion'] ));


		foreach ($items as $k) {
			$this->_dbUpdate('web_pedidos_detalle', array('id_web_promocion_relacion' => null), array('id_web_pedidos_detalle' => $k['id_web_pedidos_detalle']));
		}

	}		


/***************************************************************************************************************************************/


}

// obtener el tipo de usuario
protected function _getUserType($cliId, $userId = null)
{
	if ($cliId == null)
	{
		$r = $this->_dbGetOne('web_usuarios', array('id_web_usuarios' => $userId));
		$cliId = $r['id_cli_clientes'];
	}

	$r = $this->_dbGetOne('web_cache_clientes', array('id_cli_clientes' => $cliId));

	if ($r['numero_cliente'] == 1) return JBSYSWEBAPI::USR_EMPRE;
	if ($r['es_coordinador']) return JBSYSWEBAPI::USR_COORD;
	return JBSYSWEBAPI::USR_REVEN;
}

// determinar si la campaña acepta cuotas
protected function _cuotasEnabled($campania)
{
	// si no se definió la campaña mínima, entonces se considera que NO acepta
	if (empty($this->_global['min_camp_cuotas'])) return false;

	// determinar si la campaña está dentro de las habilitadas
	$r = $this->_dbQuery('SELECT id_web_campanias FROM web_campanias WHERE id_web_campanias = ? AND orden_absoluto >= ?', array($campania, $this->_global['min_camp_cuotas']));
	return (isset($r[0]));
}

// obtener los ítems de un pedido
protected function _getOrderItems($id, $campania, $exclude = null, $res = null, $newCampania = null)
{
	if ($res == null)
/*		$res = $this->_dbGetAll('web_pedidos_detalle',
		array('id_web_pedidos' => $id),
		array(
		'estado',
		'cantidad',
		'cantidad_preventa',
		'muestrario',
		'talle',
		'code',
		'color',
		'tipo',
		'idArticulo',
		'compradora',
		'fecha_alta',
		'es_feria' => 'isFeria',
		'id_web_pedidos_detalle' => 'idItem',
		'ptos_unitarios' => 'ptos',
		'precio' => 'precioDb',
		'cuotas'
		));
*/



/**********************************************	VERSIÓN NUEVA ***********************************/

	$res = $this->_dbQuery('SELECT 		d.estado, d.cantidad, d.cantidad_preventa, d.muestrario, d.talle, d.code, d.color, d.tipo, 
											d.idArticulo, d.compradora, d.fecha_alta, d.es_feria AS isFeria, d.id_web_pedidos_detalle AS idItem,
											d.ptos_unitarios AS ptos, d.precio AS precioDb, d.cuotas, d.id_web_promocion_relacion AS idWebPromocionRelacion , p.codigo_promocion AS codigoPromocion

								FROM 		web_pedidos_detalle d
								LEFT JOIN 	web_promocion_relacion r ON r.id_web_promocion_relacion = d.id_web_promocion_relacion
								LEFT JOIN 	web_cache_promocion p ON p.id_web_cache_promocion = r.id_web_cache_promocion
								WHERE 		d.id_web_pedidos = ?', array($id));


/**********************************************	VERSIÓN NUEVA ***********************************/	



	//$r = $this->_dbGetOne('web_cache_preventa', array('id_web_campanias' => $campania));
	//$preventa = $r['fecha'];

	$colorStrings = array();
	$tipoStrings = array();
	$talleStrings = array();

	foreach($res as $k => $i)
	{
		if (! isset($colorStrings[$i['color']]))
		{
			$r = $this->_dbGetOne('web_cache_colores', array('color' => $i['color'], 'id_tab_campanias' => $campania));
			$colorStrings[$i['color']] = $r['descripcion'];
		}

		if (! isset($tipoStrings[$i['tipo']]))
		{
			$r = $this->_dbGetOne('web_cache_tipos', array('tipo' => $i['tipo'], 'id_tab_campanias' => $campania));
			$tipoStrings[$i['tipo']] = $r['descripcion'];
		}

		if (! isset($talleStrings[$i['talle']]))
		{
			$r = $this->_dbGetOne('web_cache_talles', array('talle' => $i['talle'], 'id_tab_campanias' => $campania));
			$talleStrings[$i['talle']] = sprintf('%03s', $r['descripcion']);
		}

		$r = $this->_dbGetOne('web_cache_articulos', array('id_web_cache_articulos' => $i['idArticulo']));
		$res[$k]['descripcion'] = $r['descripcion'];
		$res[$k]['precio'] = $r['precio'];
		$res[$k]['tipo_venta'] = $r['tipo_venta'];
		//$res[$k]['ptos'] = $r['puntos'];

		if (empty($r['cuotas']))
			$res[$k]['cuotas'] = 0;
		else
			$res[$k]['cuotas']++;

		$r = $this->_dbGetOne('web_cache_feria_articulos', array('cod8' => substr($r['cod11'], 0, 8)));
		if (isset($r['cod8']))
			$res[$k]['precioCompradora'] = $r['precio_compradora'];

		$res[$k]['code'] = sprintf('%04s', $i['code']);
		$res[$k]['talle'] = sprintf('%03s', $i['talle']);
		$res[$k]['tipo'] = sprintf('%02s', $i['tipo']);
		$res[$k]['color'] = sprintf('%02s', $i['color']);
		$res[$k]['colorString'] = $colorStrings[$i['color']];
		$res[$k]['tipoString'] = $tipoStrings[$i['tipo']];
		$res[$k]['talleString'] = $talleStrings[$i['talle']];
		$res[$k]['codeString'] = '';
		$res[$k]['descripcionColor'] = '';
		$res[$k]['descripcionTipo'] = '';
		//$res[$k]['preventa'] = ($preventa && $i['fecha_alta'] < $preventa);
		$res[$k]['preventa'] = $i['cantidad_preventa'];
		//$res[$k]['preventa'] = true;

		if ($newCampania != null)
		{
			$r = $this->_dbGetOne('web_cache_articulos', array('id_web_campanias' => $newCampania, 'Code' => $i['code'], 'Tipo' => $i['tipo'], 'Color' => $i['color'], 'Talle' => $i['talle']), array('precio'));
			$res[$k]['nuevaCampaniaPrecio'] = (empty($r['precio']) ? false : $r['precio']);
		}
	}

	if ($exclude == null) return $res;

	$res2 = array();
	foreach($res as $k => $i)
		if (! in_array($i['estado'], $exclude))
			$res2[] = $i;
	return $res2;
}

// obtener listado HTML de los ítems de una orden
protected function _getHTMLOrderItems($id, $campania, $exclude = null, $includeData = array(), $items = null)
{
	$html = '<ul>';
	foreach($this->_getOrderItems($id, $campania, $exclude, $items) as $i)
	{
		$html .= '<li>Código: '.$i['code'].', Tipo: '.$i['tipo'].'-'.$i['tipoString'].', Color: '.$i['color'].'-'.$i['colorString'].', Talle: '.$i['talleString'].', Cantidad: '.$i['cantidad'];
		foreach($includeData as $k => $v) $html .= ', '.$k.': '.$i[$v];
		$html .= '</li>';
	}
	return $html.'</ul>';
}

// actualiza la cantidad de pedidos abiertos
protected function _updateOpenedOrders($cliId)
{
	$r = $this->_dbQuery('SELECT COUNT(*) c FROM web_pedidos WHERE id_cli_clientes = ? AND estado NOT IN (?, ?, ?)', array($cliId, self::OS_SEN_EMPRE, self::OS_NSN_EMPRE, self::OS_REM_EMPRE));
	$this->_dbUpdate('web_usuarios', array('cantidad_abiertas' => (isset($r[0]) ? $r[0]['c'] : 0)), array('id_cli_clientes' => $cliId));
}

// actualiza stock
protected function _updateStock($id, $q, $isFeria = true)
{
	if ($isFeria)
	{
		$tbl = 'web_cache_feria_stock';
		$fld = 'stock';
		$idFld = 'cod11';
	}

	if ($this->_logReq)
		$this->_logReqData[] = 'Actualización de stock: tbl='.$tbl.', '.$idFld.'='.$id.', q='.$q;

	$this->_dbQuery('UPDATE '.$tbl.' SET '.$fld.' = '.$fld.' + ? WHERE '.$idFld.' = ?', array($q, $id));
}

// actualiza la cantidad, puntos y el monto de un pedido
protected function _updateOrderQ($id, $actualQ, $delta, $price, $ptos)
{
	$q = $actualQ + $delta;
	if ($this->_logReq)
		$this->_logReqData[] = 'Actual Q: '.$actualQ.', Delta:'.$delta.', New Q:'.$q.', price:'.$price;
	$this->_dbQuery('UPDATE web_pedidos SET unidades = ?, monto = monto + ?, puntos_total = puntos_total + ?, ip = ? WHERE id_web_pedidos = ?', array($q, $delta * $price, $delta * $ptos, $this->_ip, $id));
}

// calcular las unidades de preventa
protected function _calcUnidPrev($campania, &$data, $cantprev = null)
{
	// determinar si estoy en preventa
	$r = $this->_dbQuery('SELECT UNIX_TIMESTAMP(fecha) fecha FROM web_cache_preventa WHERE id_web_campanias = ?', array($campania));
	$preventa = (time() < $r[0]['fecha']);

	// calcular la cant. de unidades de preventa:
	$data['cantidad_preventa'] = ($preventa ? $data['cantidad'] : ($cantprev == null ? 0 : ($data['cantidad'] < $cantprev ? $data['cantidad'] : $cantprev)));

	// determino si se canceló la preventa
	//~return (! $preventa && $cantprev != null && $data['cantidad'] < $cantprev);
}

// actualizar stock y cabecera de un pedido
protected function _restoreOrderQ($itemId, $op = -1)
{
	$r = $this->_dbGetOne('web_pedidos_detalle', array('id_web_pedidos_detalle' => $itemId));
	$r2 = $this->_dbGetOne('web_pedidos', array('id_web_pedidos' => $r['id_web_pedidos']));
	$art = $this->_dbGetOne('web_cache_articulos', array('id_web_cache_articulos' => $r['idArticulo']));

	if ($art['feria']) $this->_updateStock($art['cod11'], $r['cantidad']);

	$this->_updateOrderQ($r['id_web_pedidos'], $r2['unidades'], $r['cantidad'] * $op, $r['precio'], $r['ptos_unitarios']);
}

// cambiar la campaña de una zona
protected function _changeCampaign($campania, $zone, $userid = null)
{
	// determinamos la cantidad de envíos que tiene la campaña
	$r = $this->_dbQuery('SELECT COUNT(*) c FROM web_envios WHERE fecha_envio IS NOT NULL AND id_web_campanias = ? AND id_cli_zonas = ?', array($campania, $zone));

	$cierres = $this->_getCantCierres($userid);

	// sólo la activamos si no superó la cantidad de envíos
	if ($r[0]['c'] < $cierres)
		return $this->_dbUpdate('web_campanias_zonas', array('id_web_campanias' => $campania), array('id_cli_zonas' => $zone));

	return array('msg' => 'Envíos: '.$r[0]['c'].', Cierres:'.$cierres);
}

// comprobar que la campaña tenga activo los premios
protected function _checkCampPrem($camp)
{
	$r = $this->_dbQuery('SELECT id_web_cache_campanias FROM web_cache_campanias a, web_campanias b WHERE a.orden_absoluto >= ? AND id_web_cache_campanias = ? AND id_web_cache_campanias = id_web_campanias AND habilitado = 1', array($this->_global['prem_min_campania'], $camp));
	return ! empty($r[0]['id_web_cache_campanias']);
}

// comprobar que la campaña y el cliente tenga incentivos
protected function _checkIncPrem($camp, $cliente)
{
	// comprobar que el cliente-campaña tenga incentivos
	if (($r = $this->_dbGetAll('web_cache_prem_incentivo', array('id_web_campanias' => $camp, 'id_cli_clientes' => $cliente))) === false)
		return false;

	// devolver el premio seleccionado y el ID de incentivo
	return $r;
}

// obtener listado de campañas
protected function _getCampanias($zone, $check_envio = false, $userid = null, $userType = null, $check_usertype = false)
{
	if ($userType == null) $userType = $this->_userType;

	//$r = $this->_dbGetOne('web_cache_preventa', array('id_web_campanias' => $campania));
	//$preventa = $r['fecha'];

	// si el usuario es administrador
	if ($this->_userType == JBSYSWEBAPI::USR_ADMIN)
	{
		$q = '
		SELECT * FROM
		(
		SELECT id_web_cache_campanias campania, IF(activo IS NULL, 0, activo) activa, IF(habilitado IS NULL, 0, habilitado) habilitado, minimo_monto_E, minimo_monto_D, minimo_unidades_E, minimo_unidades_D, relevamiento, IF(fecha IS NULL, 0, IF(NOW() < fecha, 1, 0)) preventa, a.sistema, a.orden_absoluto, fecha
		FROM web_cache_campanias a
		LEFT JOIN web_campanias b
			ON id_web_cache_campanias = b.id_web_campanias
		LEFT JOIN web_cache_preventa c
			ON id_web_cache_campanias = c.id_web_campanias
		UNION
		SELECT a.id_web_campanias campania, IF(activo IS NULL, 0, activo) activa, IF(habilitado IS NULL, 0, habilitado) habilitado, minimo_monto_E, minimo_monto_D, minimo_unidades_E, minimo_unidades_D, relevamiento, IF(fecha IS NULL, 0, IF(NOW() < fecha, 1, 0)) preventa, a.sistema, a.orden_absoluto, fecha
		FROM web_campanias a
		LEFT JOIN web_cache_campanias b
			ON a.id_web_campanias = id_web_cache_campanias
		LEFT JOIN web_cache_preventa c
			ON id_web_cache_campanias = c.id_web_campanias
		WHERE
			habilitado = 1 AND id_web_cache_campanias IS NULL
		) a
		ORDER BY a.orden_absoluto DESC';
		$params = array();
	}
	// si el usuario no es administrador
	else
	{
		//$r = $this->_dbGetOne('web_usuarios', array('id_web_usuarios' => $this->_userId), array('cantidad_cierres'));

		// buscamos las campañas cuya cantidad de envíos ya enviados en la zona
		// no superan la cant. de cierres; y de ellas tomamos la menor según
		// orden_absoluto



/**********************************************************************************************************************************************************************************/


        /*  VERSIÓN NUEVA, TOMA LA CANTIDAD DE CIERRES DE LA LÍDER */

    $cierres = $this->_dbQuery('
                  SELECT    u.cantidad_cierres 
                  FROM    web_usuarios u
                  INNER JOIN  web_cache_clientes c ON c.id_cli_clientes = u.id_cli_clientes
                  WHERE     id_cli_zonas =  ?  
                      AND numero_cliente = 1
    ', array($zone) );



    // buscamos las campañas cuya cantidad de envíos ya enviados en la zona
    // no superan la cant. de cierres; y de ellas tomamos la menor según
    // orden_absoluto
    $r = $this->_dbQuery('
    SELECT a.id_web_campanias, orden_absoluto
    FROM (
      SELECT id_web_campanias, COUNT(*) c
      FROM web_envios
      WHERE fecha_envio IS NOT NULL AND id_cli_zonas = ?
      GROUP BY id_web_campanias
    ) a, web_campanias b
    WHERE a.id_web_campanias = b.id_web_campanias AND c >= ?
    ORDER BY orden_absoluto DESC
    LIMIT 1
    ', array($zone, /*$this->_getCantCierres($userid) */ $cierres[0]['cantidad_cierres'] ));


/**********************************************************************************************************************************************************************************/



		/*	VERSIÓN ORIGINAL (tomaba en cuenta los cierres de cada usuario)	*/
/*

		$r = $this->_dbQuery('
		SELECT a.id_web_campanias, orden_absoluto
		FROM (
			SELECT id_web_campanias, COUNT(*) c
			FROM web_envios
			WHERE fecha_envio IS NOT NULL AND id_cli_zonas = ?
			GROUP BY id_web_campanias
		) a, web_campanias b
		WHERE a.id_web_campanias = b.id_web_campanias AND c >= ?
		ORDER BY orden_absoluto DESC
		LIMIT 1
		', array($zone, $this->_getCantCierres($userid)));

*/


		if ($this->_logReq)
			$this->_logReqData[] = print_r($r, true);

		// buscamos las campañas habilitadas en el sistema ordenadas por
		// orden_absoluto
		$q = 'SELECT DISTINCT a.id_web_campanias campania, IF(b.id_web_campanias IS NULL, 0, 1) activa, habilitado, '.(isset($r[0]) ? 'IF(a.orden_absoluto > ?, 1, 0)' : '1').' cierresOk, cantidad, relevamiento, IF(fecha IS NULL, 0, IF(NOW() < fecha, 1, 0)) preventa, orden_absoluto
		FROM web_campanias a
			LEFT JOIN web_campanias_zonas b
				ON a.id_web_campanias = b.id_web_campanias AND b.id_cli_zonas = ?
			LEFT JOIN web_catalogos c
				ON a.id_web_campanias = c.id_web_campanias AND c.id_cli_zonas = ?
			LEFT JOIN web_cache_preventa d
				ON a.id_web_campanias = d.id_web_campanias
		WHERE habilitado AND sistema = ?
		ORDER BY a.orden_absoluto DESC';

		if (isset($r[0]))
			$params = array($r[0]['orden_absoluto']);
		else
			$params = array();
		$params[] = $zone;
		$params[] = $zone;
		$params[] = $this->_userSistema;
	}

	// obtenemos las campañas
	$campanias = $this->_dbQuery($q, $params);
	$res = array('campanias' => $campanias);
	//$res['_debug'] = array($zone, $this->_userSistema);
	//$res['_debug'] = array($check_envio, $check_usertype, $this->_userCampaign);
	if ($this->_logReq)
		$this->_logReqData[] = print_r($campanias, true);

	// sin filtros, se devuelven todas las encontradas
	if (empty($check_envio) && empty($check_usertype)) return $res;

	if ($this->_logReq && ! empty($check_usertype))
		$this->_logReqData[] = 'Campaña seleccionada por empresaria: '.$this->_userCampaign;

	// descartamos campañas según los filtros
	$res['campanias'] = array();
	$valid = empty($check_usertype);
	$campanias = array_reverse($campanias);
	foreach($campanias as $k => $v)
	{
		if (! empty($check_envio) && $this->_checkCampEnvio($v['campania']) === false)
		{
			if ($this->_logReq)
				$this->_logReqData[] = $v['campania'].': descartada por envío.';
			continue;
		}
		if (! empty($check_usertype) && ! $valid)
		{
			if ($v['campania'] == $this->_userCampaign)
				$valid = true;
			if (! $valid)
			{
				if ($this->_logReq)
					$this->_logReqData[] = $v['campania'].': descartada por ser menor a la seleccionada por empresaria.';
				continue;
			}
		}
		$res['campanias'][] = $v;
	}
	$res['campanias'] = array_reverse($res['campanias']);

	return $res;
}

// verificar si la campaña es válida
protected function _validCamp($camp, $onlyOnCache = false, $checkUserType = false)
{
	// no verificar si el usuario es administrador
	if (empty($camp) || $this->_userType == JBSYSWEBAPI::USR_ADMIN) return true;

	// verificar que sea del mismo sistema del usuario
	if (($r = $this->_dbGetOne('web_cache_campanias', array('sistema' => $this->_userSistema, 'id_web_cache_campanias' => $camp), array('orden_absoluto'))) !== false)
		return true;
	if ($onlyOnCache) return false;
	if (($r = $this->_dbGetOne('web_campanias', array('sistema' => $this->_userSistema, 'id_web_campanias' => $camp), array('orden_absoluto'))) === false)
	{
		if ($this->_logReq)
			$this->_logReqData[] = 'La campaña no pertenece al sistema del usuario.';
		return false;
	}

	// verificar según el tipo de usuario (sólo para usuarios que no sean revendedoras ni coordinadoras)
	if (! $checkUserType || $this->_userType == JBSYSWEBAPI::USR_REVEN || $this->_userType == JBSYSWEBAPI::USR_COORD)
		return true;
	$r2 = $this->_dbGetOne('web_cache_campanias', array('id_web_cache_campanias' => $this->_userCampaign), array('orden_absoluto'));
	if ($r['orden_absoluto'] < $r2['orden_absoluto']) return false;

	return true;
}

// definir contraseña de usuario
protected function _setPassword($userId, $password, $oldPassword = null)
{
	$where = array('id_web_usuarios' => $userId);

	if ($oldPassword != null)
	{
		$r = $this->_dbGetOne('web_usuarios', $where);
		if (! $this->_validateHash($r['id_web_usuarios'].$oldPassword, $r['password'])) return false;
		//$where['password'] = $this->_hash($userId.$oldPassword);
	}

	return $this->_dbUpdate('web_usuarios', array(
	'password' => $this->_hash($userId.$password),
	), $where);
}

// guardar una variable global
protected function _saveGlobal($var, $val)
{
	$where = array('parametro' => $var);
	$r = $this->_dbGetOne('web_variables_globales', $where);
	$data = $where;
	$data['valor'] = $val;

	if (empty($r['parametro']))
		$this->_dbInsert('web_variables_globales', $data);
	else
		$this->_dbUpdate('web_variables_globales', $data, $where);
}

// guardar una estadística
protected function _saveStat($id, $value = 1, $title = null, $extra1 = '', $extra2 = '')
{
	if ($title == null)
	{
		if (! isset($this->_statsTitles[$id])) return false;
		$title = $this->_statsTitles[$id];
	}

	$where = array('id_web_estadisticas' => $id);
	$r = $this->_dbGetOne('web_estadisticas', $where);
	$data = array('extra1' => $extra1, 'extra2' => $extra2);
	if (empty($r['id_web_estadisticas']))
		$this->_dbInsert('web_estadisticas', array_merge(array('id_web_estadisticas' => $id, 'titulo' => $title, 'valor' => $value), $data));
	else
		$this->_dbUpdate('web_estadisticas', array_merge(array('valor' => $r['valor'] + $value), $data), $where);

	return true;
}

// guardar un log de auditoria
protected function _addAuditLog($type)
{
	$userId = (empty($this->_userId) ? $this->_getSessionVar('userId') : $this->_userId);
	if (empty($userId)) return false;
	return $this->_dbInsert('web_logs', array('id_web_usuarios' => $userId, 'id_tipo' => $type));
}

// corrige errores de encoding en datos
protected function fixEncoding($id)
{
	switch($id)
	{
	case 'pto-catalogo':
	{
		$tbl = 'web_cache_pto_catalogo';
		$key = 'id_web_cache_pto_catalogo';
		$fields = array('descripcion', 'detalle', 'imagen_url', 'thumb_url');
		break;
	}
	default: return false;
	}

	foreach($this->_dbQuery('SELECT '.implode(', ', array_merge(array($key), $fields)).' FROM '.$tbl) as $r)
	{
		$data = array();
		foreach($fields as $k)
		{
			if (json_encode(array('x' => $r[$k])) !== false) continue;
			$v = utf8_decode($r[$k]);
			if (json_encode(array('x' => $v)) === false)
				$v = utf8_encode($r[$k]);
			$data[$k] = $v;
		}
		if (count($data)) $this->_dbUpdate($tbl, $data, array($key => $r[$key]));
	}

	return true;
}

// procesa un resultado
protected function _returnResult($res)
{
	// guardar estadísticas
	if (! empty($this->_config['usageStats']))
	{
		$now = date('YmdH');
		if ($this->_dbh !== null)
			$this->_saveStat('req-'.$req.'-'.$now, 1, 'Request', $req, $now);
	}

	parent::_returnResult($res);
}

// determinar si el cliente pertenece a la zona o división del usuario
private function _checkClientRegDiv($clientId, $zoneId)
{
	if ($clientId == null)
		$w = array('id_cli_zonas' => $zoneId);
	else
		$w = array('id_cli_clientes' => $clientId);

	if ($this->_userType == JBSYSWEBAPI::USR_REGIO)
		$w['region'] = $this->_userRegion;
	else
		$w['division'] = $this->_userDivision;

	$r = $this->_dbGetOne('web_cache_clientes', $w);
	return (isset($r['id_cli_clientes']));
}



/***************************************************************************************************************************************/		


protected function _guardarSolicitudArticuloFaltante($idWebCampanias, $codigo11){

      $user = $this->_dbGetOne('web_usuarios', array('id_web_usuarios' => $this->_userId )); 

      $data = array(
          'id_web_campanias' => $idWebCampanias,
          'codigo11' => $codigo11,
          'fecha_solicitud' => date('Y-m-d H:i:s'),
          'mail' => $user['mail'],
          'id_web_usuarios' => $user['id_web_usuarios'],
          'id_cli_clientes' => $user['id_cli_clientes'],
          'enviado' => 0
      );

      $this->_dbInsert('web_solicitud_articulo_faltante', $data);
}



/***************************************************************************************************************************************/




}

class JBSYSWEBCRON extends JBSYSWEBAPI
{

protected $_cronId = 'cron';
protected $_isCron = true;
protected $_checkSession = false;

function __construct()
{
	if (! empty($_SERVER['REQUEST_METHOD'])) die;
	parent::__construct();
}

}
