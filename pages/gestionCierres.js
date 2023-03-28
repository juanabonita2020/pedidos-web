SISWEB.gestionCierres =
{

conf: {
	validUserTypes: [ SISWEB.USRTYPE_ADMIN ]
},

init: function()
{
	SISWEB.gestionCierres._listCierres();
},

changeCierres: function(usuario, cierres)
{
	SISWEB._api('change-cierres', function(data)
	{
	}, { usuario:usuario, cierres:cierres });
},

_listCierres: function()
{
	$('#waitenv').show();

	SISWEB._api('get-empresarias', function(data)
	{
		var lista = document.getElementById('ListaCierres');
		lista.innerHTML = '';

		for (var i = 0; i < data.empresarias.length; i++)
		{
			var o = { usuario:data.empresarias[i].usuario, i:i };

			var tr = document.createElement('tr');
			tr.innerHTML = '<td>' + data.empresarias[i].nombre + '</td><td>' + data.empresarias[i].zona + '</td><td><input size="4" id="cierre_' + i + '" value="' + data.empresarias[i].cierres + '" /></td>';
			lista.appendChild(tr);

			$('#cierre_' + i).change(o, function(e) { SISWEB.gestionCierres.changeCierres(e.data.usuario, $('#cierre_' + e.data.i).val()); });
		}

		$('#waitenv').hide();
	});
}

}