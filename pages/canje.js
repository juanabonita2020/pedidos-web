SISWEB.canje =
{

_id: null,
_clientId: null,
_clientName: '',
_clientZone: '',
_clientNro: '',

showInstr: function()
{
	SISWEB.alert('<b>Instrucciones</b><p>En esta sección encontrarás todos los premios que se encuentran disponibles en los catálogos de canje de premios.</p><p>Verificá cuantos puntos tenés <b>Disponibles</b> para canjear hasta este momento.</p><p>Clickeá en el producto que deseás para verlo en Detalle.</p><p>Presioná en el botón <b>“Canjear”</b> para realizar tu canje.</p><p>Esperá a recibir tu premio con la entrega de tu próximo pedido (1).</p><p>Si tenés dudas sobre la cantidad de puntos que tenés disponibles, podes clickear en “Ver Detalle” y visualizar el estado de cada participación en los distintos programas.(2)</p>(1)    Recibirás tu canje con tu próxima campaña en caso que lo hayas realizado 15 días antes de tu cierre. Si no, lo recibirás en la campaña siguiente.</br>(2)    Recordá que tus pedidos como los de tu amiga indicada deben cumplir ciertos requisitos para transformarse en puntos disponibles.</br>');
},

save: function()
{
	SISWEB.confirm('¿Esta seguro que desea canjear el producto seleccionado?', function()
	{
		var data = { cod11:$('#talle').val() };
		if (SISWEB._userType == SISWEB.USRTYPE_ADMIN)
			data.cliente = SISWEB.canje._clientId

		$('#waitenv').show();

		SISWEB._api('canje-ptos', function(data)
		{
			SISWEB.alert('Tu canje fue realizado con éxito. EL PRODUCTO CANJEADO LLEGARÁ JUNTO A TU PEDIDO DE LA PRÓXIMA CAMPAÑA.');
			window.location.hash = '#canje';
		}, data);
	}, function() { });
},

getClientId: function()
{
	var data = { zona:$('#canjezona').val(), cliente:$('#canjecliente').val() };

	$('#canjeclientenombre').hide();

	if (data.zona && data.cliente)
	{
		SISWEB.canje._clientZone = data.zona;
		SISWEB.canje._clientNro = data.cliente;

		$('#waitenv').show();
		SISWEB._api('get-cliente', function(data)
		{
			$('#waitenv').hide();
			if (typeof data.id_cli_clientes != 'undefined')
			{
				SISWEB.canje._clientId = data.id_cli_clientes;
				SISWEB.canje._clientName = data.nombre;
				$('#canjeclientenombre').html('Cliente seleccionado: ' + data.nombre).show();
			}
			else
				SISWEB.canje._clientId = null;
			SISWEB.canje.show();
		}, data);
	}
	else
	{
		SISWEB.canje._clientId = null;
		SISWEB.canje.show();
	}
},

show: function()
{
	if (SISWEB._userType == SISWEB.USRTYPE_ADMIN)
	{
		if (SISWEB.canje._clientId == null)
		{
			$('#catalogpts_env').hide();
			$('#cataloglst').hide();
			$('#cataloglst2_env').hide();
			return;
		}
		else
		{
			$('#catalogptsp').hide();
			$('#catalogpts_env').show();
			$('#cataloglst').show();
			$('#cataloglst2_env').hide();
		}
	}

	$('#waitenv').show();

	if (SISWEB.canje._id == null)
		var data = {};
	else
		var data = { detalle:SISWEB.canje._id };

	if (SISWEB._userType == SISWEB.USRTYPE_ADMIN) data.cliente = SISWEB.canje._clientId;
	data.pg = -1;

	SISWEB._api('get-pto-catalogo', function(data)
	{
		$('#catalogpts').html(data.pts);

		if (data.ptsp > 0)
		{
			$('#catalogptsp').html('Pendientes: ' + data.ptsp);
			$('#catalogptsp').show();
		}
		else
			$('#catalogptsp').hide();

		var body = document.getElementById('cataloglst');
		body.innerHTML = '';
		var body2 = document.getElementById('cataloglst2');
		body2.innerHTML = '';
		$('#cataloglst2_env').hide();

		if (SISWEB.canje._id == null)
		{
			$('#canjetop').show();

			for (var i = 0; i < data.premios.length; i++)
			{
				var div1 = document.createElement('div');
				div1.className = 'col-md-3';
				var a = document.createElement('a');
				a.href = '#canje/' + data.premios[i].cod11;
				var div2 = document.createElement('div');
				if (data.premios[i].canjeable == 1) div2.className = 'sel';
				//div2.style.backgroundImage = 'url("' + data.premios[i].imagen + '")';
				var html = '<img src="' +  data.premios[i].imagen + '" /><span>' + data.premios[i].ptsb + '<br />punto' + (data.premios[i].pts == 1 ? '' : 's') + '</span><p>' + data.premios[i].descripcion + '</p><b>' + (data.premios[i].canjeable == 1 ? '¡canjealo ahora!' : 'te faltan ' + data.premios[i].ptsF + ' puntos') + '</b>' + (data.premios[i].stock <= 0 ? '<i>agotado</i>' : (data.premios[i].comingSoon == 1 ? '<i>próximamente</i>' : ''));
				div2.innerHTML = html;
				a.appendChild(div2);
				div1.appendChild(a);

				if (data.premios[i].canjeable == 1)
					body.appendChild(div1);
				else
				{
					body2.appendChild(div1);
					$('#cataloglst2_env').show();
				}
			}
		}
		else
		{
			if (SISWEB._userType == SISWEB.USRTYPE_ADMIN)
			{
				$('#canjebanner').hide();
				$('#catalogpts_env').hide();
			}
			else
				$('#canjetop').hide();

			var div1 = document.createElement('div');
			div1.className = 'col-md-5 canje' + (data.canjeable == 1 ? ' sel' : '');
			div1.style.backgroundImage = 'url("' + data.imagen + '")';
			div1.style.backgroundSize = 'contain';
			div1.style.backgroundRepeat = 'no-repeat';
			div1.style.height = '500px';
			div1.innerHTML = '<span>' + data.cantidad_puntos + '<br />punto' + (data.cantidad_puntos == 1 ? '' : 's') + '</span><b>' + (data.canjeable == 1 ? '¡canjealo ahora!' : 'te faltan ' + data.ptsF + ' puntos') + '</b>' + (data.stock <= 0 ? '<i>agotado</i>' : '');
			body.appendChild(div1);

			div1 = document.createElement('div');
			div1.className = 'col-md-7';
			var html = '<h3>' + data.descripcion + '</h3><div class="row"><div class="col-md-7">' + data.detalle;

			if (data.canjeable && data.stock >= 0)
			{
				if (data.talles != 0)
				{
					html += '<p>Seleccione el talle: <select id="talle">';
					for(var i = 0; i < data.talles.length; i++)
						html += '<option value="' + data.talles[i].cod11 + '">' + data.talles[i].talle + '</option>';
					html += '</select></p>';
				}
				else
					html += '<input type="hidden" id="talle" value="' + SISWEB.canje._id + '" />';
                                
                                if(data.stock > 0){
                                    html += '<br /><p><button class="btn btn-success btn-lg" onclick="SISWEB.canje.save()">Canjear</button></p>';
                                }    
			}

			html += '</div><div class="col-md-5"><div id="catalogpts_env">Mis puntos<br /><br />'  + data.pts + '<br /><br /><a href="#amigas">Ver Detalle</a></div></div></div>' + '<br /><p><span class="label label-primary" style="font-size:1.05em' + ';display:block;height:50px'  + '">' +  'EL PRODUCTO CANJEADO LLEGARÁ JUNTO A TU PEDIDO DE LA <br> PRÓXIMA CAMPAÑA.' + '</span></p><p>La imagen es a modo ilustrativo: podrás recibir uno igual u otro de similares características.</p><p>Artículos sujetos a disponibilidad de stock.</p>';

			//Version 1.3.14
			//if (data.bonificado != 0) html += '<p><span style="font-size:1em" class="label label-success">Se te han bonificado ' + data.bonificado + ' puntos en este producto.</span></p>';

			div1.innerHTML = html;
			body.appendChild(div1);
		}

		$('#waitenv').hide();
	}, data);
},

init: function(id)
{
	if (! SISWEB._global.comunidad) return SISWEB._goComunidad(1);

	SISWEB.canje._id = (typeof id == 'undefined' ? null : id);

	if (SISWEB._userType == SISWEB.USRTYPE_ADMIN)
	{
		$('#canjebanner').hide();
		$('#canjeselector').show();

		$('#canjezona').change(function() { SISWEB.canje.getClientId(); });
		$('#canjecliente').change(function() { SISWEB.canje.getClientId(); });

		if (SISWEB.canje._id == null)
			SISWEB.canje._clientId = null;
		else if (SISWEB.canje._clientId == null)
		{
			window.location.hash = '#canje';
			return;
		}

		$('#canjezona').val(SISWEB.canje._clientZone);
		$('#canjecliente').val(SISWEB.canje._clientNro);

		if (SISWEB.canje._clientZone && SISWEB.canje._clientNro)
			SISWEB.canje.getClientId();
	}
	else
	{
		$('#canjebanner').show();
		$('#canjeselector').hide();

		SISWEB.canje._clientId = null;

		SISWEB._api('get-publicidad', function(data, params)
		{
			if (typeof data[4] != 'undefined')
				$('#canjebanner').css('backgroundImage', 'url("images/ads/' + data[4][0][0] + '")');
		});
	}

	SISWEB.canje.show();
}

};
