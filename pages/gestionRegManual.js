SISWEB.gestionRegManual =
{

conf: {
	validUserTypes: [ SISWEB.USRTYPE_ADMIN ]
},

_orderBy: null,

details: function(id)
{
	SISWEB._api('get-reg-manual', function(data)
	{
		var to = data.clienta_email;
		//if (data.mail) to += (to ? ', ' : '') + data.mail;
		//if (data.region_correo) to += (to ? ', ' : '') + data.region_correo;
		//if (data.empresaria_correo) to += (to ? ', ' : '') + data.empresaria_correo;

		var msg =
		'<h4>Datos en la base de datos</h4>' +
		'<b>Cliente</b>: ' + data.clienta + (data.clienta_email == '' ? '' : ' (' + data.clienta_email + ')') +
		'<br /><b>Zona</b>: ' + data.zona +
		'<br /><b>Negocio</b>: ' + data.negocio +
		(data.region == 0 ? '' : '<br /><b>Región</b>: ' + data.region + ' (Gerente: ' + data.region_gerente + '/' + data.region_correo + ')') +
		'<br /><b>Causa</b>: ' + data.causa +
		'<h4>Datos ingresados</h4>' +
		'<b>Nombre</b>: ' + data.nombre +
		'<br /><b>Apellido</b>: ' + data.apellido +
		'<br /><b>E-mail</b>: ' + data.mail +
		'<br /><b>DNI</b>: ' + data.dni +
		'<br /><b>Teléfono</b>: ' + data.telefono +
		/*(data.detalles == null ? '' : '<h4>Detalles</h4>' + data.detalles) +*/
		'<h4>Enviar e-mail</h4>' +
		'<div class="form-group"><label>A:</label><input class="form-control" id="regmanto" value="' + to + '" /></div><div class="form-group"><input class="form-control" placeholder="Asunto" id="regmansubject" value="( Ref.: ' + id + ' )" /></div><div class="form-group"><textarea class="form-control" id="regmanmsg" placeholder="Mensaje"></textarea></div><div class="row"><div class="col-md-6"><button class="btn btn-default btn-success" onclick="SISWEB.gestionRegManual.send(' + id + ')">Enviar</button></div><div class="col-md-6" style="text-align:right"><button class="btn btn-default" onclick="SISWEB.gestionRegManual.view(' + id + ')">Ver mensajes</button></div></div>';
		SISWEB.alert(msg, 'Detalles del registro manual', function(){}, null, function(){}, null, function(){}, true);
	}, { id: id });
},

send: function(id)
{
	SISWEB.closeDialog();
	SISWEB._api('send-reg-manual', function(data)
	{
		SISWEB.alert('Mensaje enviado.', 'Detalles del registro manual');
		SISWEB.gestionRegManual.showList();
	}, { id:id, to:$('#regmanto').val(), subject:$('#regmansubject').val(), msg:$('#regmanmsg').val() }, { method:'POST' });
},

view: function(id)
{
	SISWEB._api('get-reg-manual-msgs', function(data)
	{
		var msg = '';
		for(var i = 0; i < data.msgs.length; i++)
			msg += (i ? '<hr />' : '') + '<b>Fecha</b>: ' + data.msgs[i].timestamp + '<br /><b>A</b>: ' + data.msgs[i].to + '<br /><b>Asunto</b>: ' + data.msgs[i].subject + '<br /><b>Mensaje</b>: ' + data.msgs[i].body;
		SISWEB.alert(msg, 'Mensajes enviados');
	}, { id:id });
},

showList: function(pg)
{
	$('#waitenv').show();

	document.getElementById('ListaPedidosBody').innerHTML = 'Cargando...espere...';

	if (typeof pg == 'undefined') pg = 1;

	var data = {
	estado: $('#filterestado').val(),
	zona: $('#filterzone').val(),
	cliente: $('#filterclient').val(),
	fltid: $('#filterid').val(),
	pg: pg
	};

	if (SISWEB.gestionRegManual._orderBy != null)
		data.orderby = SISWEB.gestionRegManual._orderBy;

	SISWEB._api('get-reg-manual', function(data)
	{
		if (SISWEB._actualPage != 'gestionRegManual') return;

		var body = document.getElementById('ListaPedidosBody');
		body.innerHTML = '';

		for (var i = 0; i < data.registros.length; i++)
		{
			var tr = document.createElement('tr');
			tr.innerHTML = '<td>' + data.registros[i].id + '</td><td>' + data.registros[i].fecha + '</td><td>' + data.registros[i].negocio + '</td><td>' + data.registros[i].zona + '</td><td>' + data.registros[i].clienta + '</td><td>' + data.registros[i].causa + '</td><td>' + (data.registros[i].leido == '1' ? 'Sí' : 'No') + '</td><td>' + data.registros[i].respondido_por + '</td><td><button onclick="SISWEB.gestionRegManual.details(' + data.registros[i].id + ')" type="button" class="btn btn-default btn-sm">Detalles</button></td>';
			body.appendChild(tr);
		}

		SISWEB.buildPager(body, 9, data.pager, function(pg) { SISWEB.gestionRegManual.showList(pg); });

		$('#waitenv').hide();
	}, data);
},

orderBy: function(o)
{
	SISWEB._orderByList('gestionRegManual', 'ListaRegistros', o, function()
	{
		SISWEB.gestionRegManual.showList();
	});
},

init: function()
{
	SISWEB.gestionRegManual._orderBy = null;

	SISWEB.gestionRegManual.showList();

	$('#filterestado').change(function() { SISWEB.gestionRegManual.showList() });
	$('#filterzone').change(function() { SISWEB.gestionRegManual.showList() });
	$('#filterclient').change(function() { SISWEB.gestionRegManual.showList() });
	$('#filterid').change(function() { SISWEB.gestionRegManual.showList() });

	$('#showall').click(function()
	{
		$('#filterestado').val('');
		$('#filterzone').val('');

		SISWEB.gestionRegManual.showList();
	});
}

};