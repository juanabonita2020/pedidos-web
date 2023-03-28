SISWEB.historial =
{

_pg: 1,
_client: null,

conf: {
	enabled: [ 'userHeader' ],
	validUserTypes: [ SISWEB.USRTYPE_EMPRE, SISWEB.USRTYPE_REVEN, SISWEB.USRTYPE_COORD ]
},

init: function(client)
{
	SISWEB.historial._pg = 1;
	SISWEB.historial._client = null;

	SISWEB.historial._listOrders(client);

	$('#historyshowall').click(function()
	{
		$('#filtervend').val('');
		$('#filtercamp').val('');
		SISWEB.historial._listOrders(null, 1);
	});

	$('#filtercamp').change(function() { SISWEB.historial._listOrders(null, 1); });

	if (typeof client == 'undefined')
	{
		SISWEB.initAutocomplete({
		id: 'filtervend',
		url: 'get-revendedoras.php',
		minLength: 1
		});

		$('#filtervend').blur(function()
		{
			if ($(this).val() && SISWEB._suggests.filtervend.value != null)
				SISWEB.historial._listOrders(SISWEB._suggests.filtervend.value, 1);
			else
				SISWEB.historial._listOrders(null, 1);
		});

		$('#filtervendenv').show();
	}
	else
		$('#filtervendenv').hide();
},

_listOrders: function(client, pg)
{
	if (typeof pg == 'undefined') pg = SISWEB.historial._pg;
	var params = { pg:pg };
	SISWEB.historial._pg = pg;

	if (typeof client != 'undefined' && client != null)
	{
		params.cliente = client;
		SISWEB.historial._client = client;
	}
	else if (SISWEB.historial._client != null) params.client = SISWEB.historial._client;

	if ($('#filtercamp').val()) params.campania = $('#filtercamp').val();

	$('#waitenv').show();

	SISWEB._api('get-history', function(data)
	{
		var lista = document.getElementById('ListaPedidos');
		lista.innerHTML = '';

		if (data.pedidos.length)
		{
			for (var i = 0; i < data.pedidos.length; i++)
			{
				var o = { id:data.pedidos[i].id };

				var tr = document.createElement('tr');
				if (data.pedidos[i].preventa == 1) tr.className = 'preventa';
				tr.innerHTML = '<td>' + data.pedidos[i].fecha_carga_f + '</td><td>' + data.pedidos[i].campania + '</td><td>' + data.pedidos[i].numero_cliente + '-' + data.pedidos[i].nombre + '</td><td>' + data.pedidos[i].unidades + '</td><td>' + Math.floor(data.pedidos[i].monto) + '</td><td>' + data.pedidos[i].accion + '</td><td><button type="button" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Ver Detalle del Pedido." id="view' + i + '"><span class="glyphicon glyphicon-search"></span></button></td>';
				lista.appendChild(tr);

				$('#view' + i).click(o, function(e) { SISWEB.viewOrder(e.data.id); });
			}

			SISWEB.buildPager(lista, 6, data.pager, function(pg) { SISWEB.historial._listOrders(client, pg); });

			$('#ListaPedidosBodyEnv').show();
			$('#ListaPedidosBodyEmpty').hide();
		}
		else
		{
			$('#ListaPedidosBodyEnv').hide();
			$('#ListaPedidosBodyEmpty').show();
		}

		$('#waitenv').hide();
	}, params);
}

}
