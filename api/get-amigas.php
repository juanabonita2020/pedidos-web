<?php require 'lib/kernel.php';

// obtenemos el listado de amigas
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_EMPRE, JBSYSWEBAPI::USR_COORD, JBSYSWEBAPI::USR_REGIO, JBSYSWEBAPI::USR_DIVIS, JBSYSWEBAPI::USR_REVEN, JBSYSWEBAPI::USR_ADMIN);


protected $_input = array(
'zona' => array(
),
'cliente' => array(
),
'numerocliente' => array(
),
'pg' => array(
),
'thirdparty' => array(
)
);

//~ protected $_logReq = true;

protected function _process($input)
{
	$res = array(
	//'_debug' => $this->_userClient,
	'amigas' => array(),
	'estados' => array(),
	'pager' => array('count' => 0, 'next' => 0, 'page' => 1, 'pages' => 1, 'prev' => 0)
	);

	foreach($this->_dbGetAll('web_cache_pto_estado') as $e)
		$res['estados'][$e['id_web_cache_pto_estado']] = array($e['descripcion'], 0);

/*********************************************************************************************************************************************/
  $pos = count($res["estados"]);
  $res['estados'][$pos+1] = array("Disponible", 0);
/*********************************************************************************************************************************************/

	$conceptos = array();
	foreach($this->_dbGetAll('web_cache_pto_concepto') as $e)
		$conceptos[$e['id_web_cache_pto_concepto']] = $e['descripcion'];

        $where = '';
        if( $this->_userType == JBSYSWEBAPI::USR_REGIO ) {
              $where = ' AND wcc.region = ' . $this->_userRegion;
        }
        if( $this->_userType == JBSYSWEBAPI::USR_DIVIS ){
             $where = ' AND wcc.division = ' . $this->_userDivision;
        }
        
	$q = " 
        FROM
        (
              SELECT
              a.id_web_cache_pto_log id,
              id_web_cache_campanias campania,
              a.id_web_cache_campanias_entrega campania_entrega,
              case when a.id_web_cache_pto_concepto = 1
                then IFNULL(b2.clienta , ' // ')
                 else b1.clienta end  cliente,
              case when a.id_web_cache_pto_concepto = 1
                then IFNULL(b2.zona, concat('#',a.id_cli_clientes))
                else b1.zona end zona,
              case when a.id_web_cache_pto_concepto = 1
                 then IFNULL(b2.nombre, '(INACTIVA)' )
                 else b1.nombre end nombre,
              a.valor,
              a.id_web_cache_pto_estado estado,
              a.id_web_cache_pto_concepto concepto,
              cp.fecha_inicio fecha

              FROM web_cache_pto_log a
              LEFT JOIN (
                  SELECT id_web_cache_pto_log, CAST(d.valor as SIGNED) id_cli_cliente_pres
                  FROM web_cache_pto_detalle d
                  WHERE d.descripcion = 'id_cli_clientes'
                ) d ON d.id_web_cache_pto_log = a.id_web_cache_pto_log
              LEFT JOIN web_cache_pto_detalle d2 on d2.id_web_cache_pto_log = a.id_web_cache_pto_log and d2.descripcion = 'estado_presentada'
              LEFT JOIN web_cache_clientes_all b2 ON b2.id_web_cache_clientes_all = d.id_cli_cliente_pres
              LEFT JOIN web_cache_clientes_all b1 ON b1.id_web_cache_clientes_all = a.id_cli_clientes
              LEFT JOIN web_campanias cp on cp.id_web_campanias = a.id_web_cache_campanias
              
              LEFT JOIN web_cache_clientes wcc ON a.id_cli_clientes = wcc.id_cli_clientes

              WHERE a.id_cli_clientes = ? "  . $where .
              


            "   AND a.id_web_cache_pto_concepto <> 12

              UNION

              SELECT
              a.id_web_pto_canje id,
              '' campania,
              '' campania_entrega,
              '' cliente,
              '' zona,
              CONCAT_WS(' - ', a.cod11, IF(descripcion IS NULL, '(sin descripci&oacute;n)', descripcion)) nombre,
              a.valor valor,
              IFNULL(c.estado, 4) estado,
              'CANJE' concepto,
              fecha
              FROM
              web_pto_canje a
              LEFT JOIN web_cache_pto_catalogo b ON a.cod11 = b.cod11
              LEFT JOIN web_cache_pto_canjes_estado c ON c.id_web_pto_canje = a.id_web_pto_canje

              LEFT JOIN web_cache_clientes wcc ON a.id_cli_cliente = wcc.id_cli_clientes

              WHERE  a.id_cli_cliente = ? "   . $where .

       
        ") a
        ";

	$q2 = " 
        FROM
        (
            SELECT
              id_web_cache_pto_log id,
              valor,
              id_web_cache_pto_estado estado
            FROM
              web_cache_pto_log a

              LEFT JOIN web_cache_clientes wcc ON a.id_cli_clientes = wcc.id_cli_clientes

            WHERE a.id_cli_clientes = ? "  . $where .

              " AND id_web_cache_pto_concepto <> 12

            UNION

            SELECT
              id_web_pto_canje id,
              a.valor valor,
              4 estado
            FROM
              web_pto_canje a

              LEFT JOIN web_cache_clientes wcc ON a.id_cli_cliente = wcc.id_cli_clientes

            WHERE a.id_cli_cliente = ? " . $where .


        " ) a
        ";

	// tus resultados
	if (empty($input['thirdparty']))
	{
		$cliente = $this->_userClient;
		$valid = true;
	}
	else
	{
		$cliente = (empty($input['cliente']) ? '' : $input['cliente']);
		$valid = false;

		// filtrar por zona + numero cliente
		if (! empty($input['zona']) && ! empty($input['numerocliente']) && ($this->_isMyZone($input['zona']) || $this->_userType == JBSYSWEBAPI::USR_ADMIN))
		{
			$r = $this->_dbGetOne('web_cache_clientes', array('id_cli_zonas' => $input['zona'], 'numero_cliente' => $input['numerocliente']));
			if (isset($r['id_cli_clientes'])) $cliente = $r['id_cli_clientes'];
		}

		// filtrar por cliente si este pertenece a mi red
		if ($cliente && ($this->_isMyClient($cliente) || $this->_userType == JBSYSWEBAPI::USR_ADMIN))
			$valid = true;
	}

        $params = array($cliente, $cliente);

	// listado de terceros sin filtros (retornar vacío)
	if (! $valid) return $res;

	// estados
	foreach($this->_dbQuery('SELECT estado, SUM(valor) valor '.$q2.' GROUP BY estado', $params) as $e)
		$res['estados'][$e['estado']][1] += $e['valor'];


/***********************************************************************************************************************************************/

  /*  Nueva fila en la vista de puntos de amigas  */

  $arr = array($cliente);

  $r = $this->_dbQuery('SELECT SUM(valor) valor FROM web_cache_pto_log WHERE id_web_cache_pto_estado = 3 AND id_cli_clientes = ?', $arr);
  $puntos = array('pts' => $r[0]['valor']);

  $r2 = $this->_dbQuery('SELECT SUM(valor) valor FROM web_pto_canje WHERE id_cli_cliente = ?', $arr);
  $puntos['pts'] -= $r2[0]['valor'];

  $pos;
  for($k = 1; $k <= count($res["estados"]); $k ++) {
      if($res["estados"][$k][0] == "Disponible"){
        $pos = $k;  
      }
  }

  $res['estados'][$pos][1] = $puntos['pts'] ;

/***********************************************************************************************************************************************/


	// paginación
	$res['pager'] = $this->_dbPager($q2, $params, $input['pg'], 20, 'ORDER BY fecha DESC', null, $q);

	//~ $this->_logReqData[] = 'q: '.$q;
	//~ $this->_logReqData[] = 'q2: '.$q2;

	// amigas
	$res['amigas'] = $this->_dbQuery('SELECT * '.$q, $params);

	foreach($res['amigas'] as $k => $v)
	{
		$res['amigas'][$k]['_fecha'] = $this->_fromDate(substr($v['fecha'], 0, 10));
		$res['amigas'][$k]['nombre'] = utf8_encode($v['nombre']);
		//$res['amigas'][$k]['valor_amiga'] = $this->_toNumber($v['valor_amiga'], 1, true);
		$res['amigas'][$k]['_concepto'] = $v['concepto'];
		$res['amigas'][$k]['concepto'] = (isset($conceptos[$v['concepto']]) ? $conceptos[$v['concepto']] : $v['concepto']);

		if (isset($res['estados'][$v['estado']]))
		//{
			$res['amigas'][$k]['estado'] = $res['estados'][$v['estado']][0];
		//	$res['estados'][$v['estado']][1] += $v['valor'];
		//}
		else
			$res['amigas'][$k]['estado'] = $v['estado'];

		if ($v['estado'] == 4) $v['valor'] *= -1;
		$res['amigas'][$k]['valor'] = round($v['valor']);
	}

	return $res;
}

}

require 'lib/exe.php';
