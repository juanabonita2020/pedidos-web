SISWEB.stats =
{

conf: {
	validUserTypes: [ SISWEB.USRTYPE_ADMIN ]
},

init: function()
{
	SISWEB.stats._listStats();
},

_listStats: function()
{
	$('#waitenv').show();

	SISWEB._api('get-stats', function(data)
	{
		var lista = document.getElementById('ListaStats');
		lista.innerHTML = '';

		for (var i = 0; i < data.stats.length; i++)
		{
			var tr = document.createElement('tr');
			tr.innerHTML = '<td>' + data.stats[i].titulo + '</td><td>' + data.stats[i].valor + '</td></td>';
			lista.appendChild(tr);
		}

		$('#waitenv').hide();
	});
}

}