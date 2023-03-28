SISWEB.encuesta =
{

_pg: 1,
_client: null,

conf: {
	enabled: [ 'userHeader' ],
	validUserTypes: [ SISWEB.USRTYPE_EMPRE, SISWEB.USRTYPE_REVEN, SISWEB.USRTYPE_COORD ]
},

init: function(client)
{
	SISWEB.encuesta._pg = 1;
	SISWEB.encuesta._client = null;

	SISWEB.encuesta._listEncuestas(client);
},

_listEncuestas: function(client, pg)
{
	if (typeof pg == 'undefined') pg = SISWEB.encuesta._pg;
	var params = { pg:pg };
	SISWEB.encuesta._pg = pg;

	if (typeof client != 'undefined' && client != null)
	{
		params.cliente = client;
		SISWEB.encuesta._client = client;
	}
	else if (SISWEB.encuesta._client != null) params.client = SISWEB.encuesta._client;

	$('#waitenv').show();

	SISWEB._api('get-encuestas', function(data)
	{
		var lista = document.getElementById('ListaEncuestas');
		lista.innerHTML = '';

		if (data.encuestas.length)
		{
			for (var i = 0; i < data.encuestas.length; i++)
			{
				var str = "Ir a la encuesta";
				var tr = document.createElement('tr');				
//				tr.innerHTML = '<td>' + data.encuestas[i].campania + '</td><td>' + str.link(data.encuestas[i].url) + '</td><td>'  + data.encuestas[i].fecha_vigencia + '</td>'; //<td><a href="' + data.encuestas[i].url + '"></a></td>';
				tr.innerHTML = '<td>' + data.encuestas[i].campania + '</td><td> <a href="'+data.encuestas[i].url +'" target="_blank">'+str+'</a></td><td>'  + data.encuestas[i].fecha_vigencia + '</td>'; //<td><a href="' + data.encuestas[i].url + '"></a></td>';


				lista.appendChild(tr);
			}

			SISWEB.buildPager(lista, 6, data.pager, function(pg) { SISWEB.encuesta._listOrders(client, pg); });

			$('#ListaEncuestasBodyEnv').show();
			$('#ListaEncuestasBodyEmpty').hide();
		}
		else
		{
			$('#ListaEncuestasBodyEnv').hide();
			$('#ListaEncuestasBodyEmpty').show();
		}

		$('#waitenv').hide();
	}, params);
}

}
