SISWEB.avancesZonas =
{

conf: {
	enabled: [ 'userHeader' ],
	validUserTypes: [ SISWEB.USRTYPE_REGIO, SISWEB.USRTYPE_DIVIS ]
},

_orderBy: null,

showList: function(pg)
{
	$('#waitenv').show();

	document.getElementById('ListaPedidosBody').innerHTML = 'Cargando...espere...';

	if (typeof pg == 'undefined') pg = 1;

	var data = {
	estado: $('#filterestado').val(),
	accion: $('#filteraccion').val(),
	campania: $('#filtercamp').val(),
	zona: $('#filterzone').val(),
	region: $('#filterregion').val(),
	empresaria: $('#filteremp').val(),
	pg: pg
	};

	if (SISWEB.avancesZonas._orderBy != null)
		data.orderby = SISWEB.avancesZonas._orderBy;

	SISWEB._api('get-region-stats', function(data)
	{
		if (SISWEB._actualPage != 'avancesZonas') return;

		var body = document.getElementById('ListaPedidosBody');
		body.innerHTML = '';

		for (var i = 0; i < data.pedidos.length; i++)
		{
			var tr = document.createElement('tr');
			var html = '<td>' + data.pedidos[i].estado + '</td><td>' + data.pedidos[i].campania + '</td>';
			if (SISWEB._userType == SISWEB.USRTYPE_DIVIS)
				html += '<td>' + data.pedidos[i].region + '</td>';
			html += '<td>' + data.pedidos[i].zona + '</td><td>' + data.pedidos[i].empresaria + '</td><td>' + data.pedidos[i].accion + '</td><td>' + data.pedidos[i].fecha + '</td><td>' + data.pedidos[i].pedidos + '</td><td>' + data.pedidos[i].unidades + '</td><td align="right">' + data.pedidos[i].monto + '</td>';
			tr.innerHTML = html;
			body.appendChild(tr);
		}

		SISWEB.buildPager(body, SISWEB._userType == SISWEB.USRTYPE_DIVIS ? 10 : 9, data.pager, function(pg) { SISWEB.avancesZonas.showList(pg); });

		$('#waitenv').hide();
	}, data);
},

orderBy: function(o)
{
	SISWEB._orderByList('avancesZonas', 'ListaZonas', o, function()
	{
		SISWEB.avancesZonas.showList();
	});
},

init: function()
{
	SISWEB.avancesZonas._orderBy = null;

	if (SISWEB._userType == SISWEB.USRTYPE_REGIO)
	{
		$('#filterregion').hide();
		$('#o6').hide();
	}
	else
	{
		$('#filterregion').show();
		$('#o6').show();
	}

	SISWEB.avancesZonas.showList();

	$('#filterestado').change(function() { SISWEB.avancesZonas.showList() });
	$('#filteraccion').change(function() { SISWEB.avancesZonas.showList() });
	$('#filtercamp').change(function() { SISWEB.avancesZonas.showList() });
	$('#filterregion').change(function() { SISWEB.avancesZonas.showList() });
	$('#filterzone').change(function() { SISWEB.avancesZonas.showList() });
	$('#filteremp').change(function() { SISWEB.avancesZonas.showList() });

	$('#showall').click(function()
	{
		 $('#filterestado').val('');
		$('#filteraccion').val('');
		$('#filtercamp').val('');
		$('#filterregion').val('');
		$('#filterzone').val('');
		$('#filteremp').val('');

		SISWEB.avancesZonas.showList();
	});
}

};
