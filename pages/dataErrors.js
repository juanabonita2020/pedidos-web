SISWEB.dataErrors =
{

conf: {
	//enabled: [ 'userHeader' ],
	validUserTypes: [ SISWEB.USRTYPE_ADMIN ]
},

showList: function(pg)
{
	$('#waitenv').show();
	
	$('#ListaResultadosBody').html('Cargando...espere...');
	
	if (typeof pg == 'undefined') pg = 1;

	var data = {
	pg: pg,
	action: 8,
	data1: $('#zona').val(),
	data2: $('#cliente').val()
	};

	document.getElementById('ListaResultadosBody').innerHTML = '';

	if (! data.data1 && ! data.data2)
		$('#waitenv').hide();
	else
	SISWEB._api('admin-control', function(data)
	{
		var body = document.getElementById('ListaResultadosBody');
		
		for(var i = 0; i < data.length; i++)
		{
			var tr = document.createElement('tr');
			tr.innerHTML = '<td>' + data[i].id + '</td><td>' + data[i].nro + '</td><td>' + data[i].nombre + '</td><td>' + data[i].pedido + '</td><td>' + data[i].fpedido + '</td><td>' + data[i].envio + '</td><td>' + data[i].fenvio + '</td><td>' + data[i].clizona + '</td><td>' + data[i].envzona + '</td>';
			body.appendChild(tr);
		}

		//SISWEB.buildPager(body, 9, data.pager, function(pg) { SISWEB.dataErrors.showList(pg); });
		
		$('#waitenv').hide();
	}, data);
},

init: function()
{
	$('#zona').change(function() { SISWEB.dataErrors.showList(); });
	$('#cliente').change(function() { SISWEB.dataErrors.showList(); });
}

};