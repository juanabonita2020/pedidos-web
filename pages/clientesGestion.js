SISWEB.clientesGestion =
{

_pg: 1,
_client: null,
_orderBy: null,

conf: {
	enabled: [ 'userHeader' ],
	validUserTypes: [ SISWEB.USRTYPE_EMPRE ]
},

init: function(client)
{
	SISWEB.clientesGestion._pg = 1;
	SISWEB.clientesGestion._client = null;

	$('#listshowall').click(function() { $('#filtervend').val('');SISWEB.clientesGestion.showUsers(null, 1); });

	SISWEB.initAutocomplete({
	id: 'filtervend',
	url: 'get-revendedoras.php',
	minLength: 1
	});

	$('#filtervend').blur(function()
	{
		if ($(this).val() && SISWEB._suggests.filtervend.value != null)
			SISWEB.clientesGestion.showUsers(SISWEB._suggests.filtervend.value, 1);
		else
			SISWEB.clientesGestion.showUsers(null, 1);
	});

	SISWEB.clientesGestion.showUsers();
},

orderBy: function(o)
{
	SISWEB._orderByList('clientesGestion', 'ListaPedidosCerrados', o, function()
	{
		SISWEB.clientesGestion.showUsers(SISWEB._suggests.filtervend.value, 1);
	});
},

changeStatus: function(id, status)
{
	if (status)
		SISWEB.clientesGestion.changeStatusConfirm(id, status);
	else
		SISWEB.confirm('Si usted desautoriza a este usuario, el mismo no podra ingresar nunca mas al sistema. ¿Esta seguro?', function()
		{
			SISWEB.clientesGestion.changeStatusConfirm(id, status);
		}, function() { });
},

changeStatusConfirm: function(id, status)
{
	$('#waitenv').show();
	SISWEB._api('change-user-state', function(data)
	{
		SISWEB.clientesGestion.showUsers();
	}, { idUsuario:id, habilitado:status });
},

showUsers: function(client, pg)
{
	$('#waitenv').show();

	if (typeof pg == 'undefined') pg = SISWEB.clientesGestion._pg;
	var params = { pg:pg };
	SISWEB.clientesGestion._pg = pg;

	if (typeof client != 'undefined' && client != null)
	{
		params.cliente = client;
		SISWEB.clientesGestion._client = client;
	}
	else if (SISWEB.clientesGestion._client != null) params.client = SISWEB.clientesGestion._client;

	if (SISWEB.clientesGestion._orderBy != null) params.orderBy = SISWEB.clientesGestion._orderBy;

	SISWEB._api('get-users', function(data)
	{
		if (data.usuarios.length)
		{
			var list = document.getElementById('ListaUsuariosBody');
			list.innerHTML = '';

			for (var i = 0; i < data.usuarios.length; i++)
			{
				var o = { id:data.usuarios[i].idCliente };

				if (data.usuarios[i].tipo == 1)
				{
					if (data.usuarios[i].habilitado)
					{
						var btnlbl = 'Desautorizar';
						var btnicon = 'ban';
						var btnclass = 'default';
						var status = 'Autorizada';
						o.status = false;
					}
					else
					{
						var btnlbl = 'Autorizar';
						var btnicon = 'ok';
						var btnclass = 'success';
						var status = 'Desautorizada';
						o.status = true;
					}
				}
				else
					var status = 'Sin Registrar';

				var tr = document.createElement('tr');
				tr.innerHTML = '<tr><td>' + data.usuarios[i].fechaCarga + '</td><td>' + data.usuarios[i].numeroClienta + '</td><td>' + data.usuarios[i].nombre + '</td><td>' + data.usuarios[i].mail + '</td><td>' + status + '</td><td>' + (data.usuarios[i].tipo == 1 ? '<button id="authbtn_' + i + '" type="button" class="btn btn-' + btnclass + ' btn-sm" data-toggle="tooltip" data-placement="left"  title="' + btnlbl + ' Vendedora"><span class="glyphicon glyphicon-' + btnicon + '-circle "> ' + btnlbl + '</span></button>' : '') + '</td><td><button id="ordersbtn_' + i + '" type="button" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="left" title="Ordenes"><span class="glyphicon glyphicon-search"> Ordenes</span></button></td></tr>';
				list.appendChild(tr);

				$('#authbtn_' + i).click(o, function(e) { SISWEB.clientesGestion.changeStatus(e.data.id, e.data.status); });
				$('#ordersbtn_' + i).click(o, function(e) { window.location.hash = '#historial/' + e.data.id; });
			}

			SISWEB.buildPager(list, 5, data.pager, function(pg) { SISWEB.clientesGestion.showUsers(client, pg); });

			$('#ListaPedidosCerrados').show();
			$('#ListaPedidosBodyEmpty').hide();
		}
		else
		{
			$('#ListaPedidosCerrados').hide();
			$('#ListaPedidosBodyEmpty').show();
		}

		$('#waitenv').hide();
	}, params);
}

};