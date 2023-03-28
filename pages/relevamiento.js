SISWEB.relevamiento =
{

conf: {
	validUserTypes: [ SISWEB.USRTYPE_EMPRE ]
},

init: function()
{
	SISWEB.relevamiento._listCampaigns();
},

changeCant: function(campania, i)
{
	SISWEB._api('modify-relevamiento', function(data)
	{
	}, { campania:campania, cantidad:$('#cantidad_' + i).val() });
},

_listCampaigns: function()
{
	$('#waitenv').show();

	SISWEB._api('get-campanias', function(data)
	{
		var lista = document.getElementById('ListaCampanias');
		lista.innerHTML = '';

		for (var i = 0; i < data.campanias.length; i++)
		if (data.campanias[i].relevamiento == 1)
		{
			var tr = document.createElement('tr');
			tr.innerHTML = '<td>' + data.campanias[i].campania + '</td><td><input type="number" size="4" maxlength="4" style="width:100px" id="cantidad_' + i + '" onchange="SISWEB.relevamiento.changeCant(\'' + data.campanias[i].campania + '\', \'' + i + '\')" ' + (data.campanias[i].cantidad == null ? '' : 'value="' + data.campanias[i].cantidad + '"') + ' /></td>';
			lista.appendChild(tr);
		}

		$('#waitenv').hide();
	});
}

}