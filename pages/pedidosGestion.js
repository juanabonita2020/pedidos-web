SISWEB.pedidosGestion =
{

_loaded: false,

conf: {
	enabled: [ 'userHeader' ],
	validUserTypes: [ SISWEB.USRTYPE_EMPRE, SISWEB.USRTYPE_COORD ]
},

sendOrder: function(campania, processFn, zona)
{
	if (typeof processFn == 'undefined') processFn = null;
	$('#waitenv').show();
	SISWEB._api('get-cierre', function(data)
	{
		$('#waitenv').hide();
		if (data.notToSendOrders > 0)
			var msg = (data.lastCierre == 1 ? '¡Atención! Los pedidos no aprobados se perderán ya que este es su último envió disponible para esta campaña. ¿Está seguro?' : 'Tiene algunos pedidos aún sin autorizar, ¿está seguro que desea enviar sólo los pedidos autorizados?');
		else
			var msg = '¿Está seguro de enviar las ordenes aceptadas?';
		return SISWEB._orderAction('send-cierre', null, msg, campania, null, null, null, zona, 'danger', function(data)
		{
			if (data != null && typeof data.msg != 'undefined')
				SISWEB.alert(data.msg, 'Enviar a cierre');
			SISWEB._showActionFlyer(1);
			SISWEB._progressCancel();
			if (processFn != null) processFn(data);
		});
	}, { campania:campania, zona:zona });
},

closeAll: function(orders)
{
	//console.log(orders);

	SISWEB.confirm('¿Está seguro de cerrar todos los pedidos?', function()
	{
		$('#waitenv').show();
		SISWEB._api('get-pedidos', function(data)
		{
			SISWEB._bulkOrders = new Array();
			for (var i = 0; i < data.pedidos.length; i++)
			{
				var status = SISWEB.getOrderStatus(data.pedidos[i].estado);
				if (status == 0 || status == 4) //cargando o rechazado
					SISWEB._bulkOrders.push({ id:data.pedidos[i].numeroPedido });
			}
			$('#waitenv').hide();
			//console.log(SISWEB._bulkOrders);
			SISWEB._bulkOrdersQ = SISWEB._bulkOrders.length;
			SISWEB._doBulkOrderAction('close-pedido', SISWEB.pedidosGestion.showOrders, 'Cerrando los pedidos');
		});
	}, function() { });
},

appAll: function(orders)
{
	//console.log(orders);

	SISWEB.confirm('¿Está seguro de aprobar todos los pedidos?', function()
	{
		SISWEB._bulkOrders = new Array();
		for (var i = 0; i < orders.length; i++)
			SISWEB._bulkOrders.push(orders[i]);
		//console.log(SISWEB._bulkOrders);
		SISWEB._bulkOrdersQ = SISWEB._bulkOrders.length;
		SISWEB._doBulkOrderAction('accept-pedido', SISWEB.pedidosGestion.showOrders, 'Aprobando los pedidos');
	}, function() { });
},

showOrders: function()
{
	$(window).scrollTop(0);
	$('#waitenv').show();

	SISWEB._api('get-pedidos', function(data)
	{
		var openedBody = document.getElementById('ListaPedidosAbiertosBody');

		if (openedBody != null) openedBody.innerHTML = '';

		var closedOrders = {};
		var openedQ = 0;
		var openedTotal = 0;
		var orders = new Array();
		var opts = { isFeria:'*', compradora:'*', reloadFn:SISWEB.pedidosGestion.showOrders  };
		var found = false;

		for (var i = 0; i < data.pedidos.length; i++)
		{
			var prefix = 'order_' + i + '_';
			//~ console.log(prefix);

			var status = SISWEB.getOrderStatus(data.pedidos[i].estado);

			var html = '<td>' + data.pedidos[i].nroCliente + (status == 4 ? ' (rechazado)' : '') + '</td>';
			var html1 = '<td><span' + (data.pedidos[i].baja == '1' ? ' title="Usuario Bloqueado"' : '') + '>' + data.pedidos[i].nombreCliente + '</span></td><td class="text-center">' + data.pedidos[i].unidades + '</td><td class="text-center">$ '+ Math.floor(data.pedidos[i].monto) + '</td><td>' + SISWEB._getOrderBtns(i, data.pedidos[i], prefix, { changeCamp:true }) + '</td>';

			if (status == 0 || status == 4) //cargando o rechazado
			{
				openedQ += data.pedidos[i].unidades * 1;
				openedTotal += data.pedidos[i].monto * 1;
				found = true;

				if (openedBody != null)
				{
					var tr = document.createElement('tr');
					if (data.pedidos[i].baja == '1') tr.className = 'baja';
					tr.innerHTML = html + '<td>' + data.pedidos[i].campania + '</td>' + html1;
					openedBody.appendChild(tr);
					SISWEB._setOrderBtnsEvents(data.pedidos[i], prefix, opts);
				}

				orders.push({ id:data.pedidos[i].numeroPedido });
			}
			else
			{
				if (typeof closedOrders[data.pedidos[i].campania] == 'undefined')
					closedOrders[data.pedidos[i].campania] = {
						tr: new Array(),
						trClass: new Array(),
						prefix: new Array(),
						items: new Array(),
						q: 0,
						total: 0
					}

				//~ console.log(prefix);

				closedOrders[data.pedidos[i].campania].tr.push(html + html1);
				closedOrders[data.pedidos[i].campania].trClass.push(data.pedidos[i].baja == '1' ? 'baja' : '');
				closedOrders[data.pedidos[i].campania].prefix.push(prefix);
				closedOrders[data.pedidos[i].campania].items.push(data.pedidos[i]);
				closedOrders[data.pedidos[i].campania].q += data.pedidos[i].unidades * 1;
				closedOrders[data.pedidos[i].campania].total += data.pedidos[i].monto * 1;
			}
		}

		if (found)
		{
			$('#ListaPedidosAbiertos').show();
			$('#ListaPedidosAbiertosEmpty').hide();
		}
		else
		{
			$('#ListaPedidosAbiertos').hide();
			$('#ListaPedidosAbiertosEmpty').show();
		}

		var closedBody = document.getElementById('ListaPedidosCerradosBody');

		if (closedBody != null)
		{
			closedBody.innerHTML = '';
			found = false;

			for(i in closedOrders)
			{
				found = true;
				var tr = document.createElement('tr');
				tr.innerHTML = '<td colspan="5" align="center"><strong>Campaña ' + i + '</strong></td>';
				closedBody.appendChild(tr);

				var tr = document.createElement('tr');
				tr.innerHTML = '<th>Nro. Cliente</th><th>Nombre</th><th class="text-center">Unidades</th><th class="text-center">Valor</th><th>Acciones</th>';
				closedBody.appendChild(tr);

				var orders2 = new Array();

				for(var j = 0; j < closedOrders[i].tr.length; j++)
				{
					var tr = document.createElement('tr');
					tr.innerHTML = closedOrders[i].tr[j];
					tr.className = closedOrders[i].trClass[j];
					closedBody.appendChild(tr);
					SISWEB._setOrderBtnsEvents(closedOrders[i].items[j], closedOrders[i].prefix[j], opts);
					orders2.push({ id:closedOrders[i].items[j].numeroPedido, campania:i });
				}


				/*Versión Nueva*/
				/*no les da la posibilidad de enviar pedidos si están en estado de baja*/

				var idCliCliente = SISWEB._userId;
				SISWEB._api('get-data-cliente', function(data, params)
				{
					var esDisabled = '';
					if(data != null && typeof data.baja != 'undefined' && data.baja == 1){
						esDisabled = 'disabled';
					}

					var tr = document.createElement('tr');
					tr.innerHTML = '<td></td><td class="text-center"><strong>Total:</strong> </td><td class="text-center"><strong>' + closedOrders[i].q + '</strong></td><td class="text-center"><strong>$ ' + closedOrders[i].total + '</strong></td><td><button type="button" class="btn btn-success" id="appBtn' + i + '">Aprobar todos</button> <button type="button" class="btn btn-success" id="sendBtn' + i + '"'  + esDisabled + '>Enviar Pedidos a la Empresa</button></td>';
					closedBody.appendChild(tr);

				}, 	{id_cli_clientes: idCliCliente}, { i: i, closedBody: closedBody, closedOrders: closedOrders } );

				/*Versión Original*/

/*
				var tr = document.createElement('tr');
				tr.innerHTML = '<td></td><td class="text-center"><strong>Total:</strong> </td><td class="text-center"><strong>' + closedOrders[i].q + '</strong></td><td class="text-center"><strong>$ ' + closedOrders[i].total + '</strong></td><td><button type="button" class="btn btn-success" id="appBtn' + i + '">Aprobar todos</button> <button type="button" class="btn btn-success" id="sendBtn' + i + '">Enviar Pedidos a la Empresa</button></td>';
				closedBody.appendChild(tr);
*/
				$('#sendBtn' + i).click({ i:i }, function(e) { return SISWEB.pedidosGestion.sendOrder(e.data.i); });
				$('#appBtn' + i).click({ orders:orders2 }, function(e) { return SISWEB.pedidosGestion.appAll(e.data.orders); });
			}

			if (found)
			{
				$('#ListaPedidosCerrados').show();
				$('#ListaPedidosCerradosEmpty').hide();
			}
			else
			{
				$('#ListaPedidosCerrados').hide();
				$('#ListaPedidosCerradosEmpty').show();
			}
		}

		$('#ListaPedidosAbiertosQ').html(openedQ);
		$('#ListaPedidosAbiertosTotal').html('$ ' + openedTotal);

		$(function () { $("[data-toggle='tooltip']").tooltip(); });

		if (orders.length)
		{
			$('#closeAll').unbind('click').click(function() { return SISWEB.pedidosGestion.closeAll(); });
			$('#closeAll').show();
		}
		else
			$('#closeAll').hide();

		$('#waitenv').hide();
		SISWEB._progressCancel();

		return true;
	});
},

init: function()
{
	//SISWEB._showOrdersFn = SISWEB.pedidosGestion.showOrders;
	SISWEB._showOrdersFn = function() { SISWEB._hashOld = null; };

	$(function () { $("[data-toggle='tooltip']").tooltip(); });

	//~ $('#sendBtn').click(function() { return SISWEB.pedidosGestion.sendOrder(); });

	SISWEB.pedidosGestion._loaded = false;

	SISWEB._api('get-campanias', function(data)
	{
		var campania = 0;

		for (var i = 0; i < data.campanias.length; i++)
			if (data.campanias[i].activa)
			{
				campania = data.campanias[i].campania;
				break;
			}

		if (! campania) return false;

		//~ $('#campania').html(campania);

		return true;
	});

	if (SISWEB._userType == SISWEB.USRTYPE_COORD)
	{
		$('#ListaPedidosCerradosEnv').hide();
		$('#ListaVendedorasQueNoEmpezaronEnv').hide();
		$('#ListaVendedorasQueNoEmpezaronEnv2').hide();
		$('#ListaVendedorasQueNoEmpezaronBodyEmpty').hide();
		$('#ListaVendedorasQueNoEmpezaronBodyEmpty2').hide();
		SISWEB.pedidosGestion.showOrders();
	}
	else
	{
		setTimeout(function() { if (! SISWEB.pedidosGestion._loaded) $('#waitenv').show(); }, 1000);

		SISWEB._api('get-revendedoras-sin-pedido', function(data)
		{
			var empty1 = true;
			var empty2 = true;

			if (data.revendedoras.length)
			{
				var lista1 = document.getElementById('ListaVendedorasQueNoEmpezaronBody');
				lista1.innerHTML = '';

				var lista2 = document.getElementById('ListaVendedorasQueNoEmpezaronBody2');
				lista2.innerHTML = '';

				for (var i = 0; i < data.revendedoras.length; i++)
				{
					var tr = document.createElement('tr');
					tr.innerHTML = '<td>' + data.revendedoras[i].campania + '</td><td>' + data.revendedoras[i].numeroClienta + '</td><td>' + data.revendedoras[i].nombre + '</td>';//<td>--</td>

					if (data.revendedoras[i].tipo == 1)
					{
						lista1.appendChild(tr);
						empty1 = false;
					}
					else
					{
						lista2.appendChild(tr);
						empty2 = false;
					}
				}
			}

			if (empty1)
			{
				$('#ListaVendedorasQueNoEmpezaron').hide();
				$('#ListaVendedorasQueNoEmpezaronBodyEmpty').show();
			}
			else
			{
				$('#ListaVendedorasQueNoEmpezaron').show();
				$('#ListaVendedorasQueNoEmpezaronBodyEmpty').hide();
			}

			if (empty2)
			{
				$('#ListaVendedorasQueNoEmpezaron2').hide();
				$('#ListaVendedorasQueNoEmpezaronBodyEmpty2').show();
			}
			else
			{
				$('#ListaVendedorasQueNoEmpezaron2').show();
				$('#ListaVendedorasQueNoEmpezaronBodyEmpty2').hide();
			}

			$('#waitenv').hide();

			SISWEB.pedidosGestion._loaded = true;

			SISWEB.pedidosGestion.showOrders();
		}
		);
	}
}

}
