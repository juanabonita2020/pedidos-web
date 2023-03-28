SISWEB.campaigns =
{

conf: {
	validUserTypes: [ SISWEB.USRTYPE_ADMIN ]
},

changeStatus: function(campania, status)
{
	var fn = (status == true ? 'disable' : 'enable');

	$('#waitenv').show();
	SISWEB._api(fn + '-campania', function(data)
	{
		$('#waitenv').hide();
		SISWEB.campaigns._listCampaigns();
	}, { campania:campania });
},

changeMin: function(campania, tipo, negocio, id)
{
	SISWEB._api('modify-campania', function(data)
	{
	}, { campania:campania, tipo:tipo, negocio:negocio, val:$('#' + id).val() });
},

changeRelevamiento: function(campania, i)
{
	SISWEB._api('modify-campania', function(data)
	{
	}, { campania:campania, tipo:'rel', val:document.getElementById('relev_' + i).checked });
},

init: function()
{
	SISWEB.campaigns._listCampaigns();
},

_listCampaigns: function()
{
	$('#waitenv').show();

	SISWEB._api('get-campanias', function(data)
	{
		var lista = document.getElementById('ListaCampanias');
		lista.innerHTML = '';

		for (var i = 0; i < data.campanias.length; i++)
		{
			var o = { campania:data.campanias[i].campania };

			if (data.campanias[i].habilitado == 1)
			{
				var btnlbl = 'Desactivar';
				var btnicon = 'ban';
				var btnclass = 'default';
				var status = 'Activo';
				o.habilitado = true;
			}
			else
			{
				var btnlbl = 'Activar';
				var btnicon = 'ok';
				var btnclass = 'success';
				var status = 'Desactivado';
				o.habilitado = false;
			}

			var tr = document.createElement('tr');
			tr.innerHTML = '<td>' + data.campanias[i].campania + '</td><td>' + data.campanias[i].sistema + '</td><td>' + status + '</td><td>' + (o.habilitado ? 'E: <input id="minmontoe_' + i + '" value="' + data.campanias[i].minimo_monto_E + '" size="10" onchange="SISWEB.campaigns.changeMin(\'' + data.campanias[i].campania + '\', \'monto\', \'e\', \'minmontoe_' + i + '\')" /><br />D: <input id="minmontod_' + i + '" value="' + data.campanias[i].minimo_monto_D + '" size="10" onchange="SISWEB.campaigns.changeMin(\'' + data.campanias[i].campania + '\', \'monto\', \'d\', \'minmontod_' + i + '\')" />' : '') + '</td><td>' + (o.habilitado ? 'E: <input id="minunide_' + i + '" value="' + data.campanias[i].minimo_unidades_E + '" size="5" onchange="SISWEB.campaigns.changeMin(\'' + data.campanias[i].campania + '\', \'unid\', \'e\', \'minunide_' + i + '\')" /><br />D: <input id="minunidd_' + i + '" value="' + data.campanias[i].minimo_unidades_D + '" size="5" onchange="SISWEB.campaigns.changeMin(\'' + data.campanias[i].campania + '\', \'unid\', \'d\', \'minunidd_' + i + '\')" />' : '') + '</td><td>' + (o.habilitado ? '<input type="checkbox" id="relev_' + i + '"' + (data.campanias[i].relevamiento == 1 ? ' checked="checked"' : '') + ' onclick="SISWEB.campaigns.changeRelevamiento(\'' + data.campanias[i].campania + '\', \'' + i + '\')" />' : '') + '</td><td>' + (data.campanias[i].preventa == 1 ? '<b>' : '') + data.campanias[i].fecha + (data.campanias[i].preventa == 1 ? '</b>' : '') + '</td><td><button id="actbtn_' + i + '" type="button" class="btn btn-' + btnclass + ' btn-sm" data-toggle="tooltip" data-placement="left"  title="' + btnlbl + ' campaña"><span class="glyphicon glyphicon-' + btnicon + '-circle "> ' + btnlbl + '</span></button></td>';
			lista.appendChild(tr);

			$('#actbtn_' + i).click(o, function(e) { SISWEB.campaigns.changeStatus(e.data.campania, e.data.habilitado); });
		}

		$('#waitenv').hide();
	});
}

}
