<?php require 'lib/kernel.php';

// obtener el listado histórico de pedidos
class API extends JBSYSWEBAPI
{

protected $_validUserTypes = array(JBSYSWEBAPI::USR_EMPRE, JBSYSWEBAPI::USR_REVEN, JBSYSWEBAPI::USR_COORD, JBSYSWEBAPI::USR_DIVIS, JBSYSWEBAPI::USR_REGIO);

protected $_input = array(
'cliente' => array(
),

'pg' => array(
)
);

//protected $_logReq = true;

protected function _process($input)
{
/*
	$q = " 
	FROM(

		SELECT 			n.id_web_cache_mensaje_notificacion, n.titulo, n.mensaje, n.sistema, n.division, n.region, n.zona, n.numero_cliente
		FROM 			web_cache_mensaje_notificacion n
		INNER JOIN 		web_cache_clientes c ON c.id_cli_zonas = n.zona AND c.numero_cliente = n.numero_cliente 
		WHERE			n.fecha_inicio < NOW() AND ( n.fecha_limite > NOW() OR n.fecha_limite IS NULL )
		  				AND c.id_cli_clientes = ?	
				          
		UNION 

		SELECT 			n.id_web_cache_mensaje_notificacion, n.titulo, n.mensaje, n.sistema, n.division, n.region, n.zona, n.numero_cliente 
		FROM 			web_cache_mensaje_notificacion n
		INNER JOIN 		web_cache_clientes c ON c.id_cli_zonas = n.zona
		WHERE			n.fecha_inicio < NOW() AND ( n.fecha_limite > NOW() OR n.fecha_limite IS NULL )
		  				AND n.numero_cliente IS NULL 
		  				AND c.id_cli_clientes = ?	
				          
		UNION 

		SELECT 			n.id_web_cache_mensaje_notificacion, n.titulo, n.mensaje, n.sistema, n.division, n.region, n.zona, n.numero_cliente
		FROM 			web_cache_mensaje_notificacion n
		INNER JOIN 		web_cache_clientes c ON c.region = n.region
		WHERE			n.fecha_inicio < NOW() AND ( n.fecha_limite > NOW() OR n.fecha_limite IS NULL )
		  				AND n.numero_cliente IS NULL 
		  				AND n.zona IS NULL 
						AND c.id_cli_clientes = ?	

		UNION 

		SELECT 			n.id_web_cache_mensaje_notificacion, n.titulo, n.mensaje, n.sistema, n.division, n.region, n.zona, n.numero_cliente
		FROM 			web_cache_mensaje_notificacion n
		INNER JOIN 		web_cache_clientes c ON c.division = n.division
		WHERE			n.fecha_inicio < NOW() AND ( n.fecha_limite > NOW() OR n.fecha_limite IS NULL )
		  				AND n.numero_cliente IS NULL 
		  				AND n.zona IS NULL 
						AND c.id_cli_clientes = ?			     

		UNION

		SELECT 			n.id_web_cache_mensaje_notificacion, n.titulo, n.mensaje, n.sistema, n.division, n.region, n.zona, n.numero_cliente 
		FROM 			web_cache_mensaje_notificacion n
		INNER JOIN 		web_cache_clientes c ON c.sistema = n.sistema
		WHERE			n.fecha_inicio < NOW() AND ( n.fecha_limite > NOW() OR n.fecha_limite IS NULL )
		  				AND n.numero_cliente IS NULL 
		  				AND n.zona IS NULL 
						AND c.id_cli_clientes = ?	


	) x
	ORDER BY x.id_web_cache_mensaje_notificacion
    ";
*/

    $q;
    $params;
    if($this->_userType == JBSYSWEBAPI::USR_REGIO){
    	$q = " 	FROM(
					SELECT 		n.id_web_cache_mensaje_notificacion, n.titulo, n.mensaje, n.sistema, n.division, n.region, n.zona, n.numero_cliente 	
					FROM 		web_cache_mensaje_notificacion n
					WHERE		n.fecha_inicio < NOW() AND ( n.fecha_limite > NOW() OR n.fecha_limite IS NULL )
				  				AND n.numero_cliente IS NULL 
				  				AND n.zona IS NULL 
				  				AND n.division IS NULL 
				  				AND n.sistema IS NULL
				  				AND n.region = ? 

				  	UNION 

					SELECT 		n.id_web_cache_mensaje_notificacion, n.titulo, n.mensaje, n.sistema, n.division, n.region, n.zona, n.numero_cliente 
					FROM 			web_cache_mensaje_notificacion n
					WHERE			n.fecha_inicio < NOW() AND ( n.fecha_limite > NOW() OR n.fecha_limite IS NULL )
					  				AND n.numero_cliente IS NULL 
					  				AND n.zona IS NULL 
					  				AND n.division IS NOT NULL
					  				AND n.sistema IS NOT NULL
									AND n.region =	?		

				 )x ";

		$region = $this->_userRegion;
		$params = array($region, $region);
    }
    else if( $this->_userType == JBSYSWEBAPI::USR_DIVIS ){

    	$q = " 	FROM(
					SELECT 		n.id_web_cache_mensaje_notificacion, n.titulo, n.mensaje, n.sistema, n.division, n.region, n.zona, n.numero_cliente
	    			FROM 		web_cache_mensaje_notificacion n
					WHERE		n.fecha_inicio < NOW() AND ( n.fecha_limite > NOW() OR n.fecha_limite IS NULL )
				  				AND n.numero_cliente IS NULL 
				  				AND n.zona IS NULL 
				  				AND n.region IS NULL 
				  				AND n.sistema IS NULL
								AND n.division = ? 

					UNION 

					SELECT 		n.id_web_cache_mensaje_notificacion, n.titulo, n.mensaje, n.sistema, n.division, n.region, n.zona, n.numero_cliente 
					FROM 			web_cache_mensaje_notificacion n
					WHERE			n.fecha_inicio < NOW() AND ( n.fecha_limite > NOW() OR n.fecha_limite IS NULL )
					  				AND n.numero_cliente IS NULL 
					  				AND n.zona IS NULL 
					  				AND n.region IS NULL
					  				AND n.sistema IS NOT NULL
									AND n.division = ?			
				)x ";

		$division = $this->_userDivision;
		$params = array($division, $division);

    }
    else{
    	//query si es Líder, Revendedora o Coordinadora
		$q = " 
		FROM(

			SELECT 			n.id_web_cache_mensaje_notificacion, n.titulo, n.mensaje, n.sistema, n.division, n.region, n.zona, n.numero_cliente
			FROM 			web_cache_mensaje_notificacion n
			INNER JOIN 		web_cache_clientes c ON c.id_cli_zonas = n.zona AND c.numero_cliente = n.numero_cliente 
			WHERE			n.fecha_inicio < NOW() AND ( n.fecha_limite > NOW() OR n.fecha_limite IS NULL )
			  				AND c.id_cli_clientes = ?	
					          
			UNION 

			SELECT 			n.id_web_cache_mensaje_notificacion, n.titulo, n.mensaje, n.sistema, n.division, n.region, n.zona, n.numero_cliente 
			FROM 			web_cache_mensaje_notificacion n
			INNER JOIN 		web_cache_clientes c ON c.id_cli_zonas = n.zona
			WHERE			n.fecha_inicio < NOW() AND ( n.fecha_limite > NOW() OR n.fecha_limite IS NULL )
			  				AND n.numero_cliente IS NULL 
			  				AND c.id_cli_clientes = ?	
					          
			UNION 

			SELECT 			n.id_web_cache_mensaje_notificacion, n.titulo, n.mensaje, n.sistema, n.division, n.region, n.zona, n.numero_cliente
			FROM 			web_cache_mensaje_notificacion n
			INNER JOIN 		web_cache_clientes c ON c.region = n.region
			WHERE			n.fecha_inicio < NOW() AND ( n.fecha_limite > NOW() OR n.fecha_limite IS NULL )
			  				AND n.numero_cliente IS NULL 
			  				AND n.zona IS NULL 
			  				AND n.division IS NOT NULL
  							AND n.sistema IS NOT NULL
							AND c.id_cli_clientes = ?	

			UNION 

			SELECT 			n.id_web_cache_mensaje_notificacion, n.titulo, n.mensaje, n.sistema, n.division, n.region, n.zona, n.numero_cliente
			FROM 			web_cache_mensaje_notificacion n
			INNER JOIN 		web_cache_clientes c ON c.division = n.division
			WHERE			n.fecha_inicio < NOW() AND ( n.fecha_limite > NOW() OR n.fecha_limite IS NULL )
			  				AND n.numero_cliente IS NULL 
			  				AND n.zona IS NULL 
			  				AND n.region IS NULL
  							AND n.sistema IS NOT NULL
							AND c.id_cli_clientes = ?			     

			UNION

			SELECT 			n.id_web_cache_mensaje_notificacion, n.titulo, n.mensaje, n.sistema, n.division, n.region, n.zona, n.numero_cliente 
			FROM 			web_cache_mensaje_notificacion n
			INNER JOIN 		web_cache_clientes c ON c.sistema = n.sistema
			WHERE			n.fecha_inicio < NOW() AND ( n.fecha_limite > NOW() OR n.fecha_limite IS NULL )
			  				AND n.numero_cliente IS NULL 
			  				AND n.zona IS NULL 
			  				AND n.region IS NULL
  							AND n.division IS NULL
							AND c.id_cli_clientes = ?	


		) x
		ORDER BY x.id_web_cache_mensaje_notificacion
	    ";

	    $idCliente = $this->_userClient;
		$params = array($idCliente, $idCliente, $idCliente, $idCliente, $idCliente);
    }


	$pager = $this->_dbPager($q, $params, $input['pg'], 20, '', 'id_web_cache_mensaje_notificacion');

	$mensajes = $this->_dbQuery("SELECT x.id_web_cache_mensaje_notificacion, x.titulo, x.mensaje, x.sistema, x.division, x.region, x.zona, x.numero_cliente " . $q, $params);

	return array('mensajes' => $mensajes, 'pager' => $pager);
}

}

require 'lib/exe.php';
