SISWEB.avanceMigracion =
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
	//faltaMigrar: (document.getElementById('filter1').checked ? 1 : 0),
	estado: $('#filterestado').val(),
	region: $('#filterregion').val(),
	//~nombre: $('#filternombre').val(),
	pg: pg
	};

	if (SISWEB.avanceMigracion._orderBy != null)
		data.orderby = SISWEB.avanceMigracion._orderBy;

	SISWEB._api('get-region-migracion', function(data)
	{
		if (SISWEB._actualPage != 'avanceMigracion') return;

		var body = document.getElementById('ListaPedidosBody');
		body.innerHTML = '';

		for (var i = 0; i < data.estados.length; i++)
		{
			var tr = document.createElement('tr');
			var html = '';
			if (SISWEB._userType == SISWEB.USRTYPE_DIVIS)
				html += '<td>' + data.estados[i].region + '</td>';
			html += '<td>' + data.estados[i].zona + '</td><td>' + data.estados[i].nombre + '</td><td>' + data.estados[i].mail + '</td><td>' + data.estados[i].dni + '</td><td>' + data.estados[i].estado_migracion + '</td><td>' + data.estados[i].ultima_campania_cargada + '</td><td>' + data.estados[i].cantidad_pedidos_cargado_por_lider + '</td><td>' + data.estados[i].cantidad_pedidos_cargado_por_vendedoras + '</td>';
			tr.innerHTML = html;
			body.appendChild(tr);
		}

		SISWEB.buildPager(body, 9, data.pager, function(pg) { SISWEB.avanceMigracion.showList(pg); });

		$('#waitenv').hide();
	}, data);
},

orderBy: function(o)
{
	SISWEB._orderByList('avanceMigracion', 'ListaEstados', o, function()
	{
		SISWEB.avanceMigracion.showList();
	});
},

init: function()
{
	SISWEB.avanceMigracion._orderBy = null;
	
	if (SISWEB._userType == SISWEB.USRTYPE_REGIO)
	{
		$('#filterregion').hide();
		$('#colreg').hide();
	}
	else
	{
		$('#filterregion').show();
		$('#colreg').show();
	}
	
	$('#filterregion').change(function() { SISWEB.avanceMigracion.showList() });
	$('#filterestado').change(function() { SISWEB.avanceMigracion.showList() });

	$('#showall').click(function()
	{
		//document.getElementById('filter1').checked = false;
		$('#filterregion').val('');
		$('#filterestado').val('');
		
		SISWEB.avanceMigracion.showList();
	});
	
	SISWEB.avanceMigracion.showList();
}

};