SISWEB.flyers =
{

conf: {
	validUserTypes: [ SISWEB.USRTYPE_ADMIN ]
},

init: function()
{
	SISWEB.flyers._listFlyers();
},

_sistemas: [],

_dest: function()
{
	$('#flyerusuario').hide();
	$('#flyertipo').hide();

	switch($('#flyerdest').val())
	{
	case '2': $('#flyertipo').show(); break;
	case '3': $('#flyerusuario').show(); break;
	}
},

_save: function(id)
{
	var data =
	{
	titulo: $('#flyertitulo').val(),
	contenido: $('#flyercontenido').val(),
	pagina: $('#flyerpagina').val(),
	fecha_desde: $('#flyerdesde').val(),
	fecha_hasta: $('#flyerhasta').val(),
	auto_borrar: $('#flyerborrar').val(),
	sistema: $('#flyersistema').val(),
	dest_usuario: $('#flyerdest').val() == 3 ? $('#flyerusuario').val() : '',
	dest_tipousuario: $('#flyerdest').val() == 2 ? $('#flyertipo').val() : ''
	};

	if (typeof id != 'undefined') data.flyer = id;

	//console.log(data);

	SISWEB._api('modify-flyer', function(data)
	{
		SISWEB.flyers._listFlyers();
	}, data);
},

_modify: function(id)
{
	var sistemas = '';
	for (var i in SISWEB.flyers._sistemas)
		for (var j = 0; j < SISWEB.flyers._sistemas[i][1].length; j++)
			sistemas += '<option value="' + SISWEB.flyers._sistemas[i][1][j] + '">Sistema ' + SISWEB.flyers._sistemas[i][1][j] + ' (' + SISWEB.flyers._sistemas[i][0] + ')</option>';

	var msg = '<div class="row"><div class="col-md-8"><div class="form-group"><label>Página:</label><select class="form-control" id="flyerpagina"><option value="panel">Panel del usuario</option><option value="carga">Carga/edición del pedido</option><option value="personal">Datos personales</option><option value="clientes">Gestión de clientes</option><option value="regionales">Gestión de regionales</option><option value="gestion">Gestión de pedidos</option><option value="usuarios">Listado de usuarios</option><option value="historial">Historial de pedidos</option><option value="canje">Canje</option><option value="comunidad">Comunidad inicio</option><option value="accion-enviar pedido">Acción: enviar pedido</option><option value="accion-cerrar pedido">Acción: cerrar pedido</option><option value="accion-cerrar pedido propio">Acción: cerrar pedido propio</option></select></div></div><div class="col-md-4"><div class="form-group"><label>Auto-borrar:</label><select class="form-control" id="flyerborrar"><option value="0">No</option><option value="1">Sí</option></select></div></div></div><div class="row"><div class="col-md-6"><div class="form-group"><label>Título:</label><input class="form-control" id="flyertitulo" /></div></div><div class="col-md-3"><div class="form-group"><label>Desde:</label><input class="form-control" id="flyerdesde" maxlength="10" /></div></div><div class="col-md-3"><div class="form-group"><label>Hasta:</label><input class="form-control" id="flyerhasta" maxlength="10" /></div></div></div><div class="form-group"><label>Contenido:</label><textarea class="form-control" id="flyercontenido"></textarea><br />Variables: [USERID]</div><div class="form-group"><label>Destinatario:</label><div class="row"><div class="col-md-6"><select class="form-control" id="flyerdest" onchange="SISWEB.flyers._dest()"><option value="1">Todos</option><option value="2">Tipo de usuario</option><option value="3">Usuario</option></select></div><div class="col-md-6"><select class="form-control" id="flyersistema">' + sistemas + '</select><input class="form-control" id="flyerusuario" style="display:none" /><br /><select class="form-control" id="flyertipo" style="display:none"><option value="1">Empresaria</option><option value="2">Revendedora</option><option value="3">Administrador</option><option value="4">Coordinadora</option><option value="5">Consumidora</option><option value="6">Regional</option></select></div></div></div>';

	if (typeof id == 'undefined')
		SISWEB.alert(msg, 'Crear flyer', function() { SISWEB.flyers._save(); });
	else
		SISWEB._api('get-flyers', function(data)
		{
			SISWEB.alert(msg, 'Editar flyer', function() { SISWEB.flyers._save(id); }, null, function()
			{
				$('#flyerpagina').val(data.pagina_o);
				$('#flyerborrar').val(data.auto_borrar_o);
				$('#flyertitulo').val(data.titulo);
				$('#flyersistema').val(data.sistema);
				$('#flyerdesde').val(data.fecha_desde == '--' ? '' : data.fecha_desde);
				$('#flyerhasta').val(data.fecha_hasta == '--' ? '' : data.fecha_hasta);
				$('#flyercontenido').val(data.contenido);
				if (data.dest_tipousuario != null)
				{
					$('#flyerdest').val('2');
					$('#flyertipo').val(data.dest_tipousuario);
				}
				else if (data.dest_usuario != null)
				{
					$('#flyerdest').val('3');
					$('#flyerusuario').val(data.dest_usuario);
				}
				else
					$('#flyerdest').val('1');
			});
		}, { flyer:id });
},

_remove: function(id)
{
	SISWEB.confirm('¿Está seguro de eliminar el flyer?', function()
	{
		SISWEB._api('remove-flyer', function(data)
		{
			SISWEB.flyers._listFlyers();
		}, { flyer:id });
	}, function() { });
},

_listFlyers: function()
{
	$('#waitenv').show();

	SISWEB._api('get-flyers', function(data)
	{
		var lista = document.getElementById('ListaFlyersBody');
		lista.innerHTML = '';

		for (var i = 0; i < data.flyers.length; i++)
		{
			var tr = document.createElement('tr');
			tr.innerHTML = '<td>' + data.flyers[i].titulo + '</td><td>' + data.flyers[i].sistema + '</td><td>' + data.flyers[i].pais + '</td><td>' + data.flyers[i].pagina + '</td><td>' + data.flyers[i].fecha_desde + '</td><td>' + data.flyers[i].fecha_hasta + '</td><td>' + data.flyers[i].dest + '</td><td>' + data.flyers[i].auto_borrar + '</td><td><button type="button" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Editar" onclick="SISWEB.flyers._modify(' + data.flyers[i].id + ')"><span class="glyphicon glyphicon-pencil"></span></button> <button type="button" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="SISWEB.flyers._remove(' + data.flyers[i].id + ')"><span class="glyphicon glyphicon-remove"></span></button></td>';
			lista.appendChild(tr);
		}

		SISWEB.flyers._sistemas = data.sistemas;

		$('#waitenv').hide();
	});
}

}
