<?php

require 'lib/kernel.php';

// canjear puntos
class API extends JBSYSWEBAPI {

    protected $_input = array(
        'cod11' => array(
        ),
        'cliente' => array(
        )
    );

//protected $_logReq = true;

    protected function _process($input) {
        if (!empty($this->_global['desactivar_comunidad']) && $this->_userType != JBSYSWEBAPI::USR_ADMIN)
            return false;

        $cliente = ($this->_userType == JBSYSWEBAPI::USR_ADMIN ? $input['cliente'] : $this->_userClient);

        $params = array('id_cli_cliente' => $cliente, 'cod11' => $input['cod11']);

        // 1- determinar si el producto está disponible
        $r = $this->_dbQuery('SELECT stock, ts, cantidad_puntos pts, descripcion, id_web_cache_pto_catalogo FROM web_cache_pto_catalogo WHERE fecha_inicio <= NOW() AND fecha_fin >= NOW() /* AND stock */ AND cod11 = ?', array($input['cod11']));


        $r1 = $this->_dbQuery('SELECT pcs.id_web_cache_pto_catalogo, pcs.cantidad_stock_real, pcs.cantidad_canjes, pcs.id_web_cache_pto_catalogo_stock 
                                FROM web_cache_pto_catalogo_stock pcs 
                                WHERE pcs.id_web_cache_pto_catalogo = ?', array($r[0]['id_web_cache_pto_catalogo']));

        if (isset($r[0])) {
            // 2- determinamos si el producto tiene stock
            $r2 = $this->_dbQuery('SELECT COUNT(*) q FROM web_pto_canje WHERE cod11 = ? AND fecha >= ?', array($input['cod11'], $r[0]['ts']));

//		if ($r[0]['stock'] - $r2[0]['q'])

            if ($r1[0]['cantidad_stock_real'] > 0) {
                //Version 1.3.14
                // 3- determinar los puntos a descontar
                //~ $r2 = $this->_dbQuery('SELECT bonificado FROM web_cache_pto_electros_bonificados WHERE id_cli_clientes = ? AND codigo11 = ? AND ? BETWEEN fecha_desde AND fecha_hasta', array($cliente, $input['cod11'], date('Y-m-d')));
                //~ $valor = $r[0]['pts'] - floatval($r2[0]['bonificado']);
                $valor = $r[0]['pts'];

                // 4- determinamos cuantos puntos disponibles tiene el cliente
                $this->_dbh->beginTransaction();
                
                $params2 = array($cliente);
                $r2 = $this->_dbQuery('
			SELECT
				SUM(valor) valor
			FROM
				web_cache_pto_log
			WHERE
				id_web_cache_pto_estado = 3
				AND id_cli_clientes = ? for update
			', $params2);
                $pts = $r2[0]['valor'];
                $r2 = $this->_dbQuery('SELECT SUM(valor) valor FROM web_pto_canje WHERE id_cli_cliente = ? for update', $params2);
                $pts -= $r2[0]['valor'];

                // 5- determinamos si el cliente tiene suficientes puntos para canjear
                if ($pts >= $valor) {
                    
                    //agregamos un registro de control en web_pto_catalogo_stock_canje
                    $paramsReg = array('id_cli_cliente' => $cliente);
                    $paramsReg['id_web_cache_pto_catalogo'] = $r[0]['id_web_cache_pto_catalogo'];
                    $paramsReg['fecha_canje'] = date('Y-m-d H:i:s');
                    $paramsReg['stock_previo_canje'] = $r1[0]['cantidad_stock_real'];

                    $this->_dbInsert('web_pto_catalogo_stock_canje', $paramsReg);

                    // descontamos el stock 
                    $paramsStock = array('cantidad_stock_real' => ( $r1[0]['cantidad_stock_real'] - 1 ));
                    $paramsStock['cantidad_canjes'] = ( $r1[0]['cantidad_canjes'] + 1 );

                    $this->_dbUpdate('web_cache_pto_catalogo_stock', $paramsStock, array('id_web_cache_pto_catalogo_stock' => $r1[0]['id_web_cache_pto_catalogo_stock']));



                    $t = time();

                    // hacemos efectivo el canje
                    $params['fecha'] = date('Y-m-d H:i:s', $t);
                    $params['valor'] = $valor;
                    $params['puntos_totales'] = $pts;
                    //print_r($params);
                    $this->_dbInsert('web_pto_canje', $params);

                    // enviar email con detalles al usuario
                    $body = 'Hola.<br /><br />Tu canje se realizó de forma exitosa. <br /><br />Recibirás tu premio en las próximas campañas. <br /><br />Aquí el detalle de tu canje:<br />* Fecha: ' . date('d/m/Y', $t) . '<br />* Producto: ' . $r[0]['descripcion'] . '*<br /> Puntos disponibles antes del canje: ' . $pts . '<br />* Puntos utilizados: ' . $r[0]['pts'] . '<br />* Tus puntos restantes: ' . ($pts - $r[0]['pts']) . '<br /><br />Muchas gracias. <br /><br />Juana Bonita.';
                    $this->_sendmailToMe('Canjeaste un premio', $body, 2);
                    //$this->_sendmail('ayudapedidosweb@juanabonita.com', 'Canjeaste un premio', $body, 2, false, true);
                }
                
                $this->_dbh->commit();
            }
        }
    }

}

require 'lib/exe.php';
