SISWEB.regionalUsuarios =
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
	zona: $('#filterzone').val(),
	region: $('#filterregion').val(),
	cliente: $('#filtercliente').val(),
	nombre: $('#filternombre').val(),
	bloq: $('#filterbloq').val(),
	pg: pg
	};

	if (SISWEB.regionalUsuarios._orderBy != null)
		data.orderby = SISWEB.regionalUsuarios._orderBy;

	SISWEB._api('get-region-users', function(data)
	{
		if (SISWEB._actualPage != 'regionalUsuarios') return;

		var body = document.getElementById('ListaPedidosBody');
		body.innerHTML = '';

		for (var i = 0; i < data.usuarios.length; i++)
		{
			var tr = document.createElement('tr');
			var html = '<td>' + data.usuarios[i].TipoUsuario + '</td>';
			if (SISWEB._userType == SISWEB.USRTYPE_DIVIS)
				html += '<td>' + data.usuarios[i].region + '</td>';
			html += '<td>' + data.usuarios[i].zona + '</td><td>' + data.usuarios[i].numero_cliente + '</td><td>' + data.usuarios[i].nombre + '</td><td>' + data.usuarios[i].mail + '</td><td>' + data.usuarios[i].habilitada + '</td><td>' + data.usuarios[i].ultimo_ingreo + '</td><td>' + (data.usuarios[i].bloqueado == 0 ? 'No' : 'Sí') + '</td>';
			tr.innerHTML = html;
			body.appendChild(tr);
		}

		SISWEB.buildPager(body, SISWEB._userType == SISWEB.USRTYPE_DIVIS ? 9 : 8, data.pager, function(pg) { SISWEB.regionalUsuarios.showList(pg); });

		$('#waitenv').hide();
	}, data);
},

orderBy: function(o)
{
	SISWEB._orderByList('regionalUsuarios', 'ListaUsuarios', o, function()
	{
		SISWEB.regionalUsuarios.showList();
	});
},

init: function()
{
	SISWEB.regionalUsuarios._orderBy = null;
	
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

	SISWEB.regionalUsuarios.showList();

	$('#filterregion').change(function() { SISWEB.regionalUsuarios.showList() });
	$('#filterzone').change(function() { SISWEB.regionalUsuarios.showList() });
	$('#filtercliente').change(function() { SISWEB.regionalUsuarios.showList() });
	$('#filternombre').change(function() { SISWEB.regionalUsuarios.showList() });
	$('#filterbloq').change(function() { SISWEB.regionalUsuarios.showList() });
	
	$('#showall').click(function()
	{
		$('#filterregion').val('');
		$('#filterzone').val('');
		$('#filtercliente').val('');
		$('#filternombre').val('');
		$('#filterbloq').val('0');

		SISWEB.regionalUsuarios.showList();
	});
}

};