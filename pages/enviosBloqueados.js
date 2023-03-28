SISWEB.enviosBloqueados =
{

conf: {
	validUserTypes: [ SISWEB.USRTYPE_ADMIN ]
},

init: function()
{
	SISWEB.enviosBloqueados._listBloqueados();
	
	$('#filterzona').change(function() { SISWEB.enviosBloqueados._listBloqueados(); });
	$('#filterregion').change(function() { SISWEB.enviosBloqueados._listBloqueados(); });
	
	$('#filtershowall').click(function()
	{
		$('#filterzona').val('');
		$('#filterregion').val('');
		SISWEB.enviosBloqueados._listBloqueados();
	});
},

process: function(action, id, extra)
{
	// desbloquear
	if (action == 1)
	{
		var q = '¿Está seguro de desbloquear a la empresaria de la zona?';
		var fn = function()
		{
			SISWEB._api('alta-usuario', function(data)
			{
				SISWEB.enviosBloqueados._listBloqueados();
			}, { id:id });
		};
	}
	// enviar
	else
	{
		var q = '¿Está seguro de enviar el pedido de la campaña?';
		var fn = function()
		{
			SISWEB.pedidosGestion.sendOrder(id, SISWEB.enviosBloqueados._listBloqueados, extra);
		};
	}
	
	SISWEB.confirm(q, fn);
},

_listBloqueados: function()
{
	$('#waitenv').show();

	SISWEB._api('get-envios-bloqueados', function(data)
	{
		var lista = document.getElementById('ListaEnviosBody');
		lista.innerHTML = '';

		for (var i = 0; i < data.envios.length; i++)
		{
			var o = { cliente:data.envios[i].cliente, campania:data.envios[i].campania, zona:data.envios[i].zona };

			var tr = document.createElement('tr');
			tr.innerHTML = '<td>' + data.envios[i].campania + '</td><td>' + data.envios[i].region + '</td><td>' + data.envios[i].zona + '</td><td>' + data.envios[i].intento + '</td><td>' + (data.envios[i].baja == 1 ? 'Sí' : 'No') + '</td><td>' + data.envios[i].aprobados + '</td><td>' + data.envios[i].en_proceso + '</td><td><button id="actbtn_' + i + 'a" type="button" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="left" title="Desbloquear"><span class="glyphicon glyphicon-ok"></span></button> <button id="actbtn_' + i + 'b" type="button" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="left" title="Enviar"' + (data.envios[i].en_proceso == 0 ? '' : ' disabled="disabled"') + '><span class="glyphicon glyphicon-send"></span></button></td>';
			
			lista.appendChild(tr);

			$('#actbtn_' + i + 'a').click(o, function(e) { SISWEB.enviosBloqueados.process(1, e.data.cliente); });
			if (data.envios[i].en_proceso == 0)
				$('#actbtn_' + i + 'b').click(o, function(e) { SISWEB.enviosBloqueados.process(2, e.data.campania, e.data.zona); });
		}

		$('#waitenv').hide();
	}, { 'zona': $('#filterzona').val(), 'region': $('#filterregion').val() });
}

}
