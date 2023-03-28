<?php require 'lib/kernel.php';

// obtener datos de la página
class API extends JBSYSWEBAPI
{

protected $_input = array(
'pg' => array(
	'required' => true
)
);

protected $_logReq = true;
protected $_checkSession = false;

protected function _process($input)
{
        $comunidad = false;
        if(  ($this->_userType == JBSYSWEBAPI::USR_REGIO || $this->_userType == JBSYSWEBAPI::USR_DIVIS) && empty($this->_global['desactivar_comunidad'])  ){
            $comunidad = true;
        }
        else{
            $comunidad = $this->_getComunidad();
        }
    
	$res = array('_version' => JBSYSWEBVERSION, 'flyers' => array(), 'actionFlyers' => array(), 'notice' => '', 'comunidad' => $comunidad, 'sistemas' => $this->_dbQuery('SELECT sistema, b.pais FROM web_sistemas a, web_paises b WHERE a.pais = id_web_paises ORDER BY sistema'));

	switch($input['pg'])
	{
	case 'panelAdministrador':
	case 'panelCoordinadora':
	case 'panelEmpresaria':
	case 'panelRegional':
	case 'panelRevendedora':
		$page = 'panel'; break;
	case 'pedidosCarga':
		$page = 'carga'; break;
	case 'personalInfo':
	case 'passwordChange':
		$page = 'personal'; break;
	case 'historial':
		$page = 'historial'; break;
	case 'clientesGestion':
		$page = 'clientes'; break;
	case 'gestionRegionales':
		$page = 'regionales'; break;
	case 'pedidosGestion':
		$page = 'gestion'; break;
	case 'regionalUsuarios':
		$page = 'usuarios'; break;
        case 'comunidad':
                $page = 'comunidad'; break;
        case 'canje':
                $page = 'canje'; break;
	default:
		$page = '';
	}

	if ($this->_logReq) $this->_logReqData[] = 'Page: '.$page;

	if ($page && $this->_userId)
	{
		$this->_dbQuery('DELETE FROM web_flyers WHERE auto_borrar AND fecha_hasta IS NOT NULL AND fecha_hasta < NOW()');

		foreach($this->_dbQuery('
		SELECT
			a.id_web_flyers id, titulo, contenido, IF(pagina IN ("Accion-enviar pedido", "Accion-cerrar pedido", "Accion-cerrar pedido propio"), 1, 0) actionFlyer, IF(pagina = "Accion-enviar pedido", 1, IF(pagina = "Accion-cerrar pedido", 2, IF(pagina = "Accion-cerrar pedido propio", 3, 0))) action
		FROM
			web_flyers a
			LEFT JOIN web_flyers_ocultar b
				ON a.id_web_flyers = b.id_web_flyers AND id_web_usuarios = ?
		WHERE
			pagina IN (?, "Accion-enviar pedido", "Accion-cerrar pedido", "Accion-cerrar pedido propio")
			AND id_web_usuarios IS NULL
			AND (fecha_desde IS NULL OR fecha_desde <= NOW())
			AND (fecha_hasta IS NULL OR fecha_hasta >= NOW())
			AND (dest_usuario IS NULL OR dest_usuario = ?)
			AND (dest_tipousuario IS NULL OR dest_tipousuario = ?)
			AND sistema = ?
		', array($this->_userId, $page, $this->_userId, $this->_userType, $this->_userSistema)) as $v)
		{
			$v['contenido'] = str_replace('[USERID]', $this->_userId, $v['contenido']);
			if ($v['actionFlyer'])
				$res['actionFlyers'][] = $v;
			else
				$res['flyers'][] = $v;
		}

		$res['notice'] = (empty($this->_config['notice']) ? '' : $this->_config['notice']);
	}

	return $res;
}

}

require 'lib/exe.php';
