SISWEB.mensajes =
{

_pg: 1,
_client: null,

conf: {
	enabled: [ 'userHeader' ],
	validUserTypes: [ SISWEB.USRTYPE_EMPRE, SISWEB.USRTYPE_REVEN, SISWEB.USRTYPE_COORD, SISWEB.USRTYPE_REGIO, SISWEB.USRTYPE_DIVIS ]
},

init: function(client) {
	SISWEB.mensajes._pg = 1;
	SISWEB.mensajes._client = null;
	SISWEB.mensajes._listMensajes(client);	
},


_listMensajes: function(client, pg) {
	if (typeof pg == 'undefined') pg = SISWEB.mensajes._pg;
	var params = { pg:pg };
	SISWEB.mensajes._pg = pg;

	if (typeof client != 'undefined' && client != null)
	{
		params.cliente = client;
		SISWEB.mensajes._client = client;
	}
	else if (SISWEB.mensajes._client != null) params.client = SISWEB.mensajes._client;

	if ($('#filtercamp').val()){
		params.titulo = $('#filterTitulo').val();	
	} 

	$('#waitenv').show();

	SISWEB._api('get-mensajes', function(data) {
		var lista = document.getElementById('ListaMensajes');
		lista.innerHTML = '';

		if( data.mensajes.length ){

			for (var i = 0; i < data.mensajes.length; i++){

				var o = { id:data.mensajes[i].id_web_cache_mensaje_notificacion, mensaje:data.mensajes[i].mensaje };

				var tr = document.createElement('tr');
				tr.innerHTML = '<td>' + data.mensajes[i].titulo + '</td><td><button type="button" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Ver Mensaje." id="view' + i + '"><span class="glyphicon glyphicon-envelope"></span></button></td>';
				lista.appendChild(tr);

				var plantilla = "<strong>JUANA BONITA TE INFORMA: \n\n</strong>";
				$('#view' + i).click(o, function(e) { SISWEB.alert(plantilla + e.data.mensaje);  });

			}	
			SISWEB.buildPager(lista, 6, data.pager, function(pg) { SISWEB.mensajes._listMensajes(client, pg); });

			$('#ListaMensajesBodyEnv').show();
			$('#ListaMensajesBodyEmpty').hide();
		}
		else {
			$('#ListaMensajesBodyEnv').hide();
			$('#ListaMensajesBodyEmpty').show();
		}

		$('#waitenv').hide();
	}, params);
}

}
