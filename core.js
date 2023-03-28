var SISWEB =
{

USRTYPE_EMPRE: 1,
USRTYPE_REVEN: 2,
USRTYPE_ADMIN: 3,
USRTYPE_COORD: 4,
USRTYPE_CONSU: 5,
USRTYPE_REGIO: 6,
USRTYPE_DIVIS: 7,

apiURL: 'api/',
siteURL: null,

_version: 0,
_hashTimer: null,
_hashOld: null,
_userId: null,
_userType: null,
_userData: null,
_userRefId: null,
_userReferer: null,
_userRefererName: null,
_userNegocio: null,
_userAlert: null,
_goBackFn: null,
_suggests: {},
_url: null,
_showOrdersFn: null,
_global: null,
_bulkOrders: new Array(),
_bulkOrdersQ: 0,
_debugging: false,
_actualPage: null,
_flyers: new Array(),
_actionFlyers: new Array(),
_comunidad: false,
_sistemas: new Array(),

init: function(ver)
{
	SISWEB._version = ver;

	if (SISWEB._userId == null)
	{
		$('#waitenv').show();

		SISWEB._api('get-user-session', function(data)
		{
			if (data.loggedInUsername == null || ! data.verifiedUserAccount)
			{
				SISWEB._initFinished();
				var hashSubStr = window.location.hash.substr(0, 8);
				var hashSubStr2 = window.location.hash.substr(0, 16);
				if (hashSubStr != '#newPass' && hashSubStr != '#referer' && hashSubStr2 != '#passwordRecover')
					SISWEB.goToLoginPage();
			}
			else
			{
				SISWEB.siteURL = data.siteURL;
				if (data.global == null) SISWEB.alert('SISWEB.init - Datos globales inexistentes!!');
				SISWEB._global = data.global;
				//~ SISWEB._comunidad = data.comunidad;
				SISWEB.loginUser(null, data);
			}
		}, {}, null, false);
	}
	else
		SISWEB._initFinished();
},

showHideComunidad: function()
{
	if (SISWEB._comunidad)
		$('#opciones_empresaria13').show();
	else
		$('#opciones_empresaria13').hide();
},

goToLoginPage: function()
{
	if (window.location.hash.substr(0, 6) != '#login')
		window.location.hash = '#login';
},

loginUser: function(goTo, userdata)
{
	$('#waitenv').show();

	if (typeof userdata != 'undefined' && userdata.alert != null) SISWEB._userAlert = userdata.alert;

	SISWEB._api('get-datos-personales', function(data)
	{
		SISWEB._userId = data.id;
		SISWEB._userType = data.type;
		SISWEB._userRefId = data.refId;
		SISWEB._userRefererName = data.refererClientName
		SISWEB._userNegocio = data.negocio;

		SISWEB._userData = { fullName:data.nombre + ' ' + data.apellido, zone:data.zona, clientId:data.idF, nroCliente:data.nroCliente };

		$('#username').html(SISWEB._userData.fullName);
		$('#nrozona').html(SISWEB._userData.zone);
		//~ $('#nroclienta').html(SISWEB._userData.clientId);
		$('#nroclienta').html(SISWEB._userData.nroCliente);

		$('#cerrarSesion_env').show();

		if (data.forzarDatosPersonales == '1')
			goTo = 'personalInfo';

		if (typeof goTo == 'undefined' || goTo == null)
		{
			if (! data.nombre || ! data.apellido)
				window.location.hash = '#personalInfo';
			else if (window.location.hash == '#login')
				window.location.hash = '#home';
		}
		else
			window.location.hash = '#' + goTo;

		SISWEB._initFinished();
	});
},

initAutocomplete: function(setts)
{
	if (typeof setts.noReset == 'undefined' || ! setts.noReset)
		SISWEB.resetAutocomplete(setts.id);

	if (typeof setts.minLength == 'undefined')
		var minLength = 2;
	else
		var minLength = setts.minLength;

	var opts = {
	minLength: minLength,
	select: function(event, ui)
	{
		var vals = ui.item.value.split('-');
		//~ console.log(ui.item.label, ui.item.value, vals);
		SISWEB._suggests[setts.id].autoValue = vals[0];
		SISWEB._suggests[setts.id].autoLabel = vals[1];
		SISWEB._suggests[setts.id].value = vals[0];
		SISWEB._suggests[setts.id].selected = true;
		SISWEB._suggests[setts.id].label = (setts.hideIdInResults ? '' : (typeof vals[2] == 'undefined' ? vals[0] : vals[2]) + '-') + vals[1];
		$('#' + setts.id).val(vals[1]);
		//console.log("*1", vals[1]);
		if (typeof setts.onSelect != 'undefined') setts.onSelect();
		event.stopPropagation();
		return false;
	},
	response: function(event, ui)
	{
		//console.log(ui.content);

		if (typeof ui.content[0] == 'undefined')
		{
			var val = null;
			var lbl = null;
		}
		else
		{
			var vals = ui.content[0].value.split('-');
			var val = vals[0];
			var lbl = vals[1];
			//console.log(val);
		}

		if (! setts.searchOnlyInLbl)
		{
			SISWEB._suggests[setts.id].autoValue = val;
			SISWEB._suggests[setts.id].autoLabel = lbl;
		}
	},
	focus: function(event, ui) { return false; },
	search: function(event, ui)
	{
		if (setts.searchOnlyInLbl)
		{
			var term = $('#' + setts.id).val();
			var found = false;
			for(var i = 0; i < setts.source.length; i++)
			{
				var p = setts.source[i].split('-');
				if (p[1].indexOf(term) >= 0)
				{
					SISWEB._suggests[setts.id].autoValue = p[0];
					SISWEB._suggests[setts.id].autoLabel = p[1];
					found = true;
					break;
				}
			}
			return found;
		}
	}
	};

	if (typeof setts.url == 'undefined')
		opts.source = setts.source;
	else
		opts.source = SISWEB.apiURL + setts.url;

	if (typeof setts.hideIdInResults == 'undefined')
		setts.hideIdInResults = false;

	$('#' + setts.id).autocomplete(opts)
	.blur(function() {
		if (typeof SISWEB._suggests[setts.id].noSetLabel == 'undefined' || ! SISWEB._suggests[setts.id].noSetLabel)
		{
			var prevlbl = $(this).val();
			var newlbl = SISWEB._suggests[setts.id].label;
			//console.log("*5", newlbl, prevlbl);
			if (newlbl && ! prevlbl)
			{
				//console.log('changed!');
				$(this).val(newlbl);
			}
			SISWEB._suggests[setts.id].noSetLabel = true;
		}
	})
	.autocomplete('instance')._renderItem = function(ul, item)
	{
		var vals = item.label.split('-');
		var li = $('<li>').append('<a>' + (setts.hideIdInResults ? '' : (typeof vals[2] == 'undefined' ? vals[0] : vals[2]) + '-') + vals[1] + '</a>');

		if (setts.searchOnlyInLbl)
		{
			var term = $('#' + setts.id).val();
			if (vals[1].indexOf(term) < 0) return li;
		}

		var o = li.appendTo(ul);
		//~ console.log(o);
		return o;
    };

	var o = { setts:setts };

    $('#' + setts.id).keydown(o, function(e)
    {
		if (typeof SISWEB._suggests[setts.id].selected != "undefined" && SISWEB._suggests[setts.id].selected)
		{
			SISWEB._suggests[setts.id].selected = false;
			return true;
		}
		var code = e.keyCode || e.which;
		//console.log(code, SISWEB._suggests[e.data.setts.id].autoValue);
		if (code == 13 || code == 9)
		{
			if (typeof SISWEB._suggests[e.data.setts.id].autoValue != 'undefined' && SISWEB._suggests[e.data.setts.id].autoValue != null)
			{
				//console.log("*2", e.data.setts);
				//console.log(SISWEB._suggests[e.data.setts.id].autoValue);
				//console.log(SISWEB._suggests[e.data.setts.id].autoLabel);
				SISWEB._suggests[e.data.setts.id].value = SISWEB._suggests[e.data.setts.id].autoValue;
				SISWEB._suggests[e.data.setts.id].label = SISWEB._suggests[e.data.setts.id].autoLabel;
				SISWEB._suggests[e.data.setts.id].noSetLabel = true;
				var inputLbl = (e.data.setts.hideIdInResults ? '' : SISWEB._suggests[e.data.setts.id].autoValue + '-') + SISWEB._suggests[e.data.setts.id].autoLabel;
				//console.log("*2", inputLbl);
				$('#' + e.data.setts.id).val(inputLbl);
			}
			/*else
				$('#' + e.data.setts.id).val('');*/

			//console.log(e.data.setts.onSelect);

			if (typeof e.data.setts.onSelect != 'undefined') e.data.setts.onSelect();
			return false;
		}
	});
},

resetAutocomplete: function(id)
{
	return SISWEB.setAutocompleteVal(id, null, '');
},

setAutocompleteVal: function(id, value, label, autoValue, autoLabel)
{
	if (typeof autoValue == 'undefined') autoValue = value;
	if (typeof autoLabel == 'undefined') autoLabel = label;

	//~ console.log(value, label, autoValue, autoLabel);

	SISWEB._suggests[id] = { value:value, label:label, autoValue:autoValue, autoLabel:autoLabel };
	//var actlbl = $('#' + id).val();
	if (label) $('#' + id).val(label);
	//console.log("*3", id, label);
	return true;
},

loadDroplist: function(id, values, emptyOption)
{
	var select = document.getElementById(id);
	if (select == null) return false;

	if (typeof emptyOption != 'undefined')
		select.innerHTML = '<option value="">' + emptyOption + '</option>';
	else
		select.innerHTML = '';

	for (var i = 0; i < values.length; i++)
	{
		var option = document.createElement('option');
		option.value = values[i].value;
		option.innerHTML = values[i].label;
		select.appendChild(option);
	}

	return true;
},

showCampResume: function()
{
	SISWEB._api('get-campanias', function(data)
	{
		var campania = 0;

		for (var i = 0; i < data.campanias.length; i++)
			if (data.campanias[i].activa == "1")
			{
				campania = data.campanias[i].campania;
				break;
			}

		if (! campania) return false;

		$('#cp_activa_vendedoras').html(campania);

		SISWEB._api('get-estado-cierre', function(data)
		{
			$('#dias_restantes').html(data.cantidadDiasRestantes + ' días');
		}, { campania:campania });

		return true;
	});
},

loadOrder: function()
{
	if (SISWEB._userType != SISWEB.USRTYPE_REVEN)
	{
		window.location.hash = '#pedidosCarga';
		return;
	}

	$('#waitenv').show();

	SISWEB._api('get-pedido', function(data)
	{
		//~ console.log(data);

		if (data == null)
			window.location.hash = '#pedidosCarga';
		else
			window.location.hash = '#pedidosCarga/' + SISWEB._userId;

		$('#waitenv').hide();
	}, { cliente:SISWEB._userId });
},
/*********************************************  VERSIÓN VIEJA *********************************/
/*
viewOrder: function(id, compradora, esFeria)
{
	$('#waitenv').show();

	SISWEB._api('get-pedido', function(data)
	{
		var html = '<table class="table table-bordered table-hover"><tr><th>Código</th><th>Tipo</th><th>Color</th><th>Talle</th><th>Cantidad</th><th class="DetallePedido_x">Puntos</th><th>Muestrario</th><th>Cuotas</th></tr>';

		for (var i = 0; i < data.items.length; i++)
		if (data.items[i].estado != 160)
		{
			SISWEB._log('Compradora=' + compradora + ', esFeria=' + esFeria + ', item-compradora=' + data.items[i].compradora + ', item-isferia=' + data.items[i].isFeria);
			if (typeof compradora != 'undefined' && typeof esFeria != 'undefined' && compradora != '*' && esFeria != '*' && (compradora != data.items[i].compradora || esFeria != data.items[i].isFeria)) continue;

			html += '<tr' + (data.items[i].preventa != 0 ? ' class="preventa" data-toggle="tooltip" data-placement="top" title="Item considerado en la preventa"' : '') + '><td>' + data.items[i].code + '</td><td>' + data.items[i].tipoString + '</td><td>' + data.items[i].colorString + '</td><td>' + data.items[i].talleString + '</td><td>' + data.items[i].cantidad + '</td><td class="DetallePedido_x">' + (data.items[i].ptos == null ? 0 : data.items[i].ptos * data.items[i].cantidad) + '</td><td>' + (data.items[i].muestrario == '1' ? 'Sí' : 'No') + '</td><td>' + (data.items[i].cuotas == '2' ? 'Sí' : 'No') + '</td></tr>';
		}

		html += '</table><p><b>Talles del premio</b></p><table class="table table-bordered table-hover"><tr><th>Soutien</th><th>Bombacha</th><th>Inferior</th><th>Superior</th></tr><tr><td>' + data.soutien_f + '</td><td>' + data.bombacha_f + '</td><td>' + data.inferior_f + '</td><td>' + data.superior_f + '</td></tr></table>';

		$('#waitenv').hide();
		return SISWEB._modal('Detalle del pedido', html, 750);
	}, { numeroPedido:id });
},
*/

/*********************************************  VERSIÓN VIEJA *********************************/

/*********************************************  VERSIÓN NUEVA *********************************/

viewOrder: function(id, compradora, esFeria)
{
	$('#waitenv').show();

	SISWEB._api('get-pedido', function(data)
	{
		var html = '<table class="table table-bordered table-hover text-center"><tr><th class="text-center">Código</th><th class="text-center">Tipo</th><th class="text-center">Color</th><th class="text-center">Talle</th><th class="text-center">Cantidad</th><th class="DetallePedido_x text-center">Puntos</th><th class="text-center">Muestrario</th><th class="text-center">Cuotas</th><th class="text-center">Promoción</th></tr>';

		for (var i = 0; i < data.items.length; i++)
		if (data.items[i].estado != 160)
		{
			SISWEB._log('Compradora=' + compradora + ', esFeria=' + esFeria + ', item-compradora=' + data.items[i].compradora + ', item-isferia=' + data.items[i].isFeria);
			if (typeof compradora != 'undefined' && typeof esFeria != 'undefined' && compradora != '*' && esFeria != '*' && (compradora != data.items[i].compradora || esFeria != data.items[i].isFeria)) continue;

			console.log(data.items[i].idWebPromocionRelacion);			

			html += '<tr' + ( data.items[i].preventa != 0 ? ' class="preventa" data-toggle="tooltip" data-placement="top" title="Item considerado en la preventa"' :  data.items[i].idWebPromocionRelacion != null ? ' style="background-color: lightsalmon;" data-toggle="tooltip" data-placement="top" title="Item incluído en la Promoción"' : '' ) + '><td>' + data.items[i].code + '</td><td>' + data.items[i].tipoString + '</td><td>' + data.items[i].colorString + '</td><td>' + data.items[i].talleString + '</td><td>' + data.items[i].cantidad + '</td><td class="DetallePedido_x">' + (data.items[i].ptos == null ? 0 : data.items[i].ptos * data.items[i].cantidad) + '</td><td>' + (data.items[i].muestrario == '1' ? 'Sí' : 'No') + '</td><td>' + (data.items[i].cuotas == '2' ? 'Sí' : 'No') + '</td><td>' +  (data.items[i].codigoPromocion != null ? data.items[i].codigoPromocion : '' ) + '</td></tr>';
		}

		html += '</table><p><b>Talles del premio</b></p><table class="table table-bordered table-hover"><tr><th>Soutien</th><th>Bombacha</th><th>Inferior</th><th>Superior</th></tr><tr><td>' + data.soutien_f + '</td><td>' + data.bombacha_f + '</td><td>' + data.inferior_f + '</td><td>' + data.superior_f + '</td></tr></table>';

		$('#waitenv').hide();
		return SISWEB._modal('Detalle del pedido', html, 750);
	}, { numeroPedido:id });
},

/*********************************************  VERSIÓN NUEVA *********************************/


editOrder: function(cliente, campania)
{
	window.location.hash = '#pedidosCarga/' + cliente + ',' + campania;
},

deleteOrder: function(id, isFeria, compradora)
{

/***************************************************************************************************************************************/

	//para evitar que al eliminar un pedido propio lo tome como 1 solo item
	isFeria = null;

/***************************************************************************************************************************************/


	SISWEB.confirm('¿Está seguro de eliminar la orden?', function()
	{
		SISWEB._progressInit('Eliminando la orden');

		var params = { numeroPedido:id };
		if (typeof isFeria != 'undefined' && isFeria != null) params.isFeria = isFeria;
		if (typeof compradora != 'undefined' && compradora != null) params.compradora = compradora;

		SISWEB._api('delete-pedido', function(data)
		{
			if (SISWEB._showOrdersFn == null) return;
			SISWEB._progressCancel();
			return SISWEB._showOrdersFn();
		}, params);
	}, function() { });
	//~ if (! confirm('¿Está seguro de eliminar la orden?')) return false;
},

closeOrder: function(id, msg, checkRejected)
{
	if (typeof msg == 'undefined' || msg == null) msg = '¿Está seguro de cerrar la orden?';
	return SISWEB._orderAction('close-pedido', id, msg, null, null, null, false, checkRejected, null, function(data)
	{
		if (typeof data.msg != 'undefined')
			SISWEB.alert(data.msg, 'Cerrar orden');
		//~ console.log(data);
		SISWEB._showActionFlyer(data.myOwnOrder ? 3 : 2);
		SISWEB._progressCancel();
	}, 'Cerrando la orden');
},

acceptOrder: function(id, isFeria, compradora)
{
	return SISWEB._orderAction('accept-pedido', id, null, null, isFeria, compradora, false, null, null, null, 'Aceptando la orden');
},

rejectOrder: function(id, isFeria, compradora)
{
	return SISWEB._orderAction('reject-pedido', id, '¿Está seguro de editar la orden? \n\n Si confirma, la orden se podrá editar en la lista de los pedidos cargados', null, isFeria, compradora, false, null, null, null, 'Rechazando la orden');
},

getOrderStatus: function(code)
{
	code = code * 1;

	switch(code)
	{
	case 40: case 90: case 130: return 0; //cargando
	case 10: case 50: case 100: case 140: return 1; //cerrado
	case 20: case 60: case 110: return 2; //autorizado
	case 30: case 70: case 120: return 4; //rechazado
	case 80: return 6; //borrado
	case 160: return 5; //no enviado
	}
	return 3; //enviado
},

getOrderStatusLbl: function(code)
{
	var lbls = ['Cargando', 'Cerrado', 'Autorizado', 'Enviado', 'Rechazado', 'No enviado', 'Borrado'];
	var i = SISWEB.getOrderStatus(code);
	return lbls[i];
},

logout: function()
{
	$('#waitenv').show();
	SISWEB._userAlert = null;

	SISWEB._api('close-user-session', function(data)
	{
		SISWEB._userId = null;
		SISWEB._userType = null;
		$('#cerrarSesion_env').hide();
		SISWEB.pedidosCarga._cartItems = new Array();
		window.location.hash = '#home';
	});
},

referer: function(id)
{
	if (SISWEB._userId == null)
	{
		$('#waitenv').show();

		SISWEB._api('referer', function(data)
		{
			if (data.ok)
			{
				SISWEB._userId = 0;
				SISWEB._userType = SISWEB.USRTYPE_CONSU;
				SISWEB._userReferer = id;
				SISWEB._userRefererName = data.clientName;
				$('#cerrarSesion_env').show();
			}

			window.location.hash = '#home';
		}, { id:id });
	}
	else
	{
		SISWEB.alert('Este enlace es para enviar a sus referidos.');
		window.location.hash = '#home';
	}
},

alert: function(msg, title, okFn, okFnData, onShowFn, size, cancelFn, disableHotKey)
{
	if (typeof title == 'undefined' || title == null) title = 'Información';
	if (typeof size == 'undefined') size = null;
	if (typeof disableHotKey == 'undefined') disableHotKey = false;
	SISWEB._dialog(title, msg, 1, okFn, cancelFn, okFnData, null, null, onShowFn, size, disableHotKey);
},

confirm: function(msg, okFn, cancelFn, cssClass)
{
	SISWEB._dialog('Consulta', msg, 2, okFn, cancelFn, null, null, cssClass);
},

closeDialog: function()
{
	BootstrapDialog.closeAll();
},

noCampaigns: function()
{
	window.location.hash = '#home';
	SISWEB.alert('Se alcanzo el maximo de cierres posibles para las campañas habilitadas.');
	return true;
},

checkValidEmail: function(email)
{
	var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
    return pattern.test(email);
},

showOrders: function(stats, onlyMyOrders)
{
	if (typeof stats == 'undefined') stats = false;
	if (typeof onlyMyOrders == 'undefined') onlyMyOrders = false;

	SISWEB._log('Stats=' + stats + ', onlyMyOrders=' + onlyMyOrders);

	$(window).scrollTop(0);
	$('#waitenv').show();

	var data = {};

	//if (onlyMyOrders) data.cliente = SISWEB._userId;

	SISWEB._api('get-pedidos', function(data)
	{
		var os0 = 0;
		var os1 = 0;
		var os2 = 0;

		var body = document.getElementById('ListaPedidosBody');

		if (body != null)
		{
			body.innerHTML = '';
			found = false;

			for (var i = 0; i < data.pedidos.length; i++)
			{
				var estado1 = data.pedidos[i].estado;
				var estado = SISWEB.getOrderStatus(estado1);
				SISWEB._log('Estado: pedido=' + estado1 + ', item=' + estado + ', cliente=' + data.pedidos[i].client);

				switch(estado)
				{
				case 0: case 4: os0++; break; //cargando
				case 1: os1++; break; //cerrado
				case 2: os2++; break; //autorizado
				}

				if (onlyMyOrders && ((estado1 != 40 && estado1 != 130 && estado1 != 90 && estado1 != 10 && estado1 != 70 && estado1 != 30 && estado1 != 120) || data.pedidos[i].client* 1 != SISWEB._userId * 1)) continue;

				found = true;

				SISWEB._log('passed');

				var empty = true;

				for(var j = 0; j < data.pedidos[i].sub.length; j++)
				{
					empty = false;

					var prefix = 'order_' + i + '_' + j + '_';

					var tr = document.createElement('tr');
					var html = '<td>' + data.pedidos[i].campania +'</td><td>' + data.pedidos[i].fechaCarga +'</td><td>';

					//html += '[' + data.pedidos[i].numeroPedido + '/' + j + '] ';

					//console.log(data.pedidos[i].sub[j]);

					var opts = { noClose:true, isFeria:data.pedidos[i].sub[j].esFeria, compradora:data.pedidos[i].sub[j].compradora, subEstado:data.pedidos[i].sub[j].estado };
					SISWEB._log(opts);

					if (data.pedidos[i].usuarioAlta == SISWEB._userRefId)
					{
						if (data.pedidos[i].sub[j].compradora == null)
							html += 'Propio';
						else
							html += data.pedidos[i].sub[j].nombre;

						if (data.pedidos[i].sub[j].esFeria == 1)
						{
							opts.noEdit = true;
							html += ' <span class="glyphicon glyphicon-shopping-cart" title="Estos son pedidos de Liquidación Online"></span>';
						}
					}
					else
					{
						if (data.pedidos[i].sub[j].isFeria && data.pedidos[i].sub[j].compradora != null)
							html += data.pedidos[i].sub[j].nombre;
						else
						{
							html += (data.pedidos[i].usuarioAltaTipo == SISWEB.USRTYPE_COORD ? 'Coordinadora' : (data.pedidos[i].negocio == 'D' ? 'Líder' : 'Empresaria'));
							opts.noDel = true;
						}

						opts.noEdit = true;
					}

					if (SISWEB.getOrderStatus(data.pedidos[i].sub[j].estado) == 4) html += ' (rechazado)';

					html += '</td><td class="text-center">' + data.pedidos[i].sub[j].unidades + '</td><td style="white-space:nowrap">' + SISWEB._getOrderBtns(i, data.pedidos[i], prefix, opts) + '</td>';

					if (! onlyMyOrders) html += '<td></td>';

					tr.innerHTML = html;

					body.appendChild(tr);

					SISWEB._setOrderBtnsEvents(data.pedidos[i], prefix, opts);
				}

				if (! empty)
				{
					var tr = document.createElement('tr');
					tr.innerHTML = '<td colspan="5" align="center"><button type="button" class="btn btn-success" id="sendBtn' + i + '" onclick="SISWEB.closeAndSend(\'' + data.pedidos[i].numeroPedido + '\', \'' + data.pedidos[i].campania + '\')">Cerrar Pedido y Enviar a ' + (SISWEB._userNegocio == 'D' ? 'Líder' : 'Empresaria') + '</button></td>';
					body.appendChild(tr);
				}
			}

			if (found)
			{
				$('#ListaPedidosBodyEmpty').hide();
				$('#ListaPedidosBodyEnv').show();
			}
			else
			{
				$('#ListaPedidosBodyEmpty').show();
				$('#ListaPedidosBodyEnv').hide();
			}

			$(function () { $("[data-toggle='tooltip']").tooltip(); });

			if (stats)
			{
				$('#pedidos_en_proceso_de_carga').html(os0);
				$('#pedidos_en_entregados').html(os1);
				$('#pedidos_autorizados').html(os2);
				$('#total_pedidos').html(os0 + os1 + os2);
			}
		}

		$('#waitenv').hide();

		return true;
	}, data);
},

closeAndSend: function(id, campania)
{
	SISWEB.closeOrder(id, '¿Está seguro de cerrar y enviar los pedidos?', true);
},

changeOrderCamp: function(id, act, reloadFn)
{
	if (typeof act == 'undefined' || act == null)
		SISWEB._api('get-campanias', function(data)
		{
			var msg = '<div class="form-group"><label>Nueva campaña:</label><select onchange="SISWEB.changeOrderCamp(' + id + ', 1)" class="form-control" id="newcamp"><option value="">seleccione</option>';
			for (var i = 0; i < data.campanias.length; i++)
				if (data.campanias[i].habilitado == "1" && data.campanias[i].cierresOk == "1")
					msg += '<option value="' + data.campanias[i].campania + '">' + data.campanias[i].campania + '</option>';
			msg += '</select></div><div id="chgcampdet">Seleccione una campaña.</div>';

			SISWEB._dialog('Cambiar campaña del pedido', msg, 2, function()
			{
				var camp = $('#newcamp').val();

				if (! camp)
				{
					SISWEB.alert('Debe seleccionar una campaña.', 'Cambiar campaña del pedido');
					return false;
				}

				SISWEB._api('change-pedido-campania', function(data)
				{
					if (typeof reloadFn != 'undefined' && reloadFn != null) reloadFn();
					SISWEB.alert('Se ha cambiado la campaña del pedido.', 'Cambiar campaña del pedido');
				}, { numeroPedido:id, campania:camp });
				return true;
			}, function() { });
		}, { check_envio:true });
	else if (act == 1)
	{
		var camp = $('#newcamp').val();
		$('#chgcampdet').html('Seleccione una campaña.');

		if (camp)
			SISWEB._api('get-pedido', function(data)
			{
				if (data.campania == camp)
				{
					$('#newcamp').val('');
					SISWEB.alert('Debe seleccionar una campaña distinta a la del pedido.', 'Cambiar campaña del pedido');
				}
				else
				{
					var html = '<div class="form-group"><label>Items del pedido:</label><table class="table table-bordered table-hover"><tr><th>Código</th><th>Tipo</th><th>Color</th><th>Talle</th><th>Precio actual</th><th>Precio nuevo</th></tr>';
					for(var i = 0; i < data.items.length; i++)
						if (data.items[i].estado != '80' && (data.items[i].estado != '160' || data.items[i].nuevaCampaniaPrecio * 1))
							html += '<tr><td>' + data.items[i].code + '</td><td>' + data.items[i].tipoString + '</td><td>' + data.items[i].colorString + '</td><td>' + data.items[i].talleString + '</td><td>' + data.items[i].precioDb + '</td><td>' + (data.items[i].nuevaCampaniaPrecio * 1 ? data.items[i].nuevaCampaniaPrecio : 'Se eliminará') + '</td></tr>';
					html += '</div>';
					$('#chgcampdet').html(html);
				}
			}, { numeroPedido:id, nuevaCampania:camp });
	}
},

_dialog: function(title, msg, mod, okFn, cancelFn, okFnData, cancelFnData, cssClass, onShowFn, size, disableHotKey)
{
	if (typeof mod == 'undefined') mod = 1;
	if (typeof okFnData == 'undefined' || okFnData == null) okFnData = {};
	if (typeof cancelFnData == 'undefined' || cancelFnData == null) cancelFnData = {};
	if (typeof disableHotKey == 'undefined') disableHotKey = false;

	if (mod == 1)
	{
		var lbl = 'Continuar';
		var hotkey = 13;
	}
	else
	{
		var lbl = 'Cancelar';
		var hotkey = 27;
	}

	if (disableHotKey)
	{
		var hotkey = null;
		var okHotkey = null;
	}
	else
		var okHotkey = 13;

	var buttons = [{
		label: lbl,
		hotkey: hotkey,
		action: function(dialogItself)
		{
			dialogItself.close();
			if (mod == 1 && typeof okFn != 'undefined')
				okFn(okFnData);
			else if (mod == 2 && typeof cancelFn != 'undefined' && cancelFn != null)
				cancelFn(cancelFnData);
		}
	}];

	if (mod == 2)
		buttons.push({
			label: 'Aceptar',
			hotkey: okHotkey,
			action: function(dialogItself)
			{
				dialogItself.close();
				if (typeof okFn != 'undefined') okFn(okFnData);
			}
		});

	var opts = {
	title: title,
	message: msg,
	buttons: buttons
	};

	if (typeof cssClass != 'undefined' && cssClass != null) opts.cssClass = cssClass;
	if (typeof onShowFn != 'undefined' && onShowFn != null) opts.onshown = onShowFn;
	if (typeof size != 'undefined' && size != null) opts.size = size;
	if (typeof cancelFn != 'undefined' && cancelFn != null) opts.onhide = cancelFn;

	BootstrapDialog.show(opts);
},

buildPager: function(cnt, colspan, pager, fn)
{
	var tr = document.createElement('tr');
	var td = document.createElement('td');
	td.colSpan = colspan;
	td.style.textAlign = 'right';

	if (pager.count > 0)
	{
		if (pager.prev)
		{
			var a = document.createElement('a');
			a.href = 'Javascript:;';
			a.innerHTML = '<span class="glyphicon glyphicon-circle-arrow-left"></span> Pág. anterior';
			var o = { pg:pager.prev };
			$(a).click(o, function(e) { fn(e.data.pg); })
			td.appendChild(a);
		}

		var span = document.createElement('span');
		span.innerHTML = 'Página ' + pager.page + ' de ' + pager.pages;
		span.style.marginLeft = '20px';
		span.style.marginRight = '20px';
		td.appendChild(span);

		if (pager.next)
		{
			var a = document.createElement('a');
			a.href = 'Javascript:;';
			a.innerHTML = 'Pág. siguiente <span class="glyphicon glyphicon-circle-arrow-right"></span> ';
			var o = { pg:pager.next };
			$(a).click(o, function(e) { fn(e.data.pg); })
			td.appendChild(a);
		}
	}
	else
		td.innerHTML = 'Sin registros.';

	tr.appendChild(td);
	cnt.appendChild(tr);
},

_getOrderBtns: function(i, orderdata, prefix, opts)
{
	if (typeof opts != 'undefined' && typeof opts.subEstado != 'undefined' && opts.subEstado != null && opts.subEstado != 0 && opts.subEstado != '')
		var status = SISWEB.getOrderStatus(opts.subEstado);
	else
		var status = SISWEB.getOrderStatus(orderdata.estado);
	//console.log(opts, status);

	SISWEB._log('Order button, i=' + i + ', status:' + status);
	SISWEB._log(opts);

	if (typeof opts == 'undefined') opts = {};

	if (typeof opts.noEdit == 'undefined') opts.noEdit = false;
	if (typeof opts.noDel == 'undefined') opts.noDel = false;
	if (typeof opts.noClose == 'undefined') opts.noClose = false;
	if (typeof opts.changeCamp == 'undefined' || (opts.changeCamp && status != 0 && status != 4)) opts.changeCamp = false;

	var showMainBtn = true;

	switch(status)
	{
	case 0: //cargando
	case 4: //rechazado
	{
		var btn_label = 'Cerrar el pedido';
		var btn_icon = 'upload';
		var btn_class = 'default';
		var btn_label2 = '';
		var btn_style = '';
		var accepted = false;

		if (opts.noClose && status == 4)
		{
			var btn_label = 'Aprobar el pedido';
			var btn_icon = 'ok';
			var btn_label2 = 'Aprobar';
			var btn_style = 'width:100px';
		}
		else
			showMainBtn = ! opts.noClose;

		break;
	}
	case 1: //cerrado
	{
		var btn_label = 'Aprobar el pedido';
		var btn_icon = 'ok';
		var btn_class = 'default';
		var btn_label2 = 'Aprobar';
		var btn_style = 'width:100px';

		var btn_label_b = 'Permitir Edición';
		var btn_icon_b = 'remove';
		var btn_class_b = 'default';
		var btn_label2_b = 'Permitir Edición';
		var btn_style_b = 'width:100px';

		var accepted = false;
		break;
	}
	default:
	{
		var btn_label = 'Permitir Edición';
		var btn_icon = 'remove';
		var btn_class = 'default';
		var btn_label2 = 'Permitir Edición';
		var btn_style = 'width:100px';
		var accepted = true;
	}
	}

	var html = '';

	if (! opts.noEdit && (status == 0 || status == 4))
		html += '<button type="button" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="left" title="Editar el pedido." id="' + prefix + 'e"><span class="glyphicon glyphicon-pencil"></span></button> ';

	html += '<button type="button" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Ver Detalle del Pedido." id="' + prefix + 'v"><span class="glyphicon glyphicon-search"></span></button> ';

	if (! opts.noDel)
		html += '<button type="button" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="bottom" title="Eliminar el pedido." id="' + prefix + 'd"><span class="glyphicon glyphicon-trash"></span></button> ';

	html += (accepted ? '<button type="button" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="right" title="Pedido aprobado"><span class="glyphicon glyphicon-ok"></span> Aprobado</button> ' : '');

	if (showMainBtn)
		html += '<button type="button" class="btn btn-' + btn_class + ' btn-sm" data-toggle="tooltip" data-placement="right" title="' + btn_label + '" id="' + prefix + 'a" style="' + btn_style + '"><span class="glyphicon glyphicon-' + btn_icon + '"></span> ' + btn_label2 + '</button>'

	return html + (typeof btn_label_b == 'undefined' ? '' : ' <button type="button" class="btn btn-' + btn_class_b + ' btn-sm" data-toggle="tooltip" data-placement="right" title="' + btn_label_b + '" id="' + prefix + 'b" style="' + btn_style_b + '"><span class="glyphicon glyphicon-' + btn_icon_b + '"></span> ' + btn_label2_b + '</button>') + (opts.changeCamp ? ' <button type="button" class="btn btn-default btn-sm" title="Cambiar la campaña" data-toggle="tooltip" data-placement="right" id="' + prefix + 'cc"><span class="glyphicon glyphicon-new-window"></span></button>' : '');
},

_orderWithoutClientWarning: function()
{
	SISWEB.alert('No se puede completar la acción porque no se encuentra el nombre de la vendedora, por favor contactar a "ayudapedidosweb@juanabonita.com.ar" indicando el problema.');
},

_setOrderBtnsEvents: function(orderdata, prefix, opts)
{
	var o = { id:orderdata.client };
	var o2 = { id:orderdata.numeroPedido, subitem:0, isFeria:null, compradora:null, reloadFn:null };
	//console.log(orderdata);
	if (typeof opts != 'undefined')
	{
		if (typeof opts.subitem != 'undefined') o2.subitem = opts.subitem;
		if (typeof opts.reloadFn != 'undefined') o2.reloadFn = opts.reloadFn;
	}

	if (typeof opts != 'undefined' && typeof opts.isFeria != 'undefined') o2.isFeria = opts.isFeria;
	if (typeof opts != 'undefined' && typeof opts.compradora != 'undefined') o2.compradora = opts.compradora;
	if (typeof opts.noClose == 'undefined') opts.noClose = false;

	if (orderdata.nroCliente * 1)
		$('#' + prefix + 'v').click(o2, function(e) { SISWEB.viewOrder(e.data.id, e.data.compradora, e.data.isFeria); });
	else
		$('#' + prefix + 'v').click(o2, function(e) { SISWEB._orderWithoutClientWarning(); });

	if (typeof opts != 'undefined' && typeof opts.subEstado != 'undefined' && opts.subEstado != null && opts.subEstado != 0 && opts.subEstado != '')
		var status = SISWEB.getOrderStatus(opts.subEstado);
	else
		var status = SISWEB.getOrderStatus(orderdata.estado);

	if ((typeof opts == 'undefined' || typeof opts.noEdit == 'undefined' || ! opts.noEdit) && (status == 0 || status == 4))
	{
		if (orderdata.nroCliente * 1)
			$('#' + prefix + 'e').click(o, function(e) { SISWEB.editOrder(orderdata.client, orderdata.campania); });
		else
			$('#' + prefix + 'e').click(o, function(e) { SISWEB._orderWithoutClientWarning(); });
	}

	if (typeof opts == 'undefined' || typeof opts.noDel == 'undefined' || ! opts.noDel)
	{
		if (orderdata.nroCliente * 1)
			$('#' + prefix + 'd').click(o2, function(e) { SISWEB.deleteOrder(e.data.id, e.data.isFeria, e.data.compradora); });
		else
			$('#' + prefix + 'd').click(o2, function(e) { SISWEB._orderWithoutClientWarning(); });
	}

	if (status == 0 || (status == 4 && ! opts.noClose))
	{
		if (orderdata.nroCliente * 1)
			$('#' + prefix + 'a').click(o2, function(e) { SISWEB.closeOrder(e.data.id, null, true); });
		else
			$('#' + prefix + 'a').click(o2, function(e) { SISWEB._orderWithoutClientWarning(); });
	}
	else if (status == 1 || (status == 4 && opts.noClose))
	{
		if (orderdata.nroCliente * 1)
			$('#' + prefix + 'a').click(o2, function(e) { SISWEB.acceptOrder(e.data.id, e.data.isFeria, e.data.compradora); });
		else
			$('#' + prefix + 'a').click(o2, function(e) { SISWEB._orderWithoutClientWarning(); });

		if (status == 1)
		{
			if (orderdata.nroCliente * 1)
				$('#' + prefix + 'b').click(o2, function(e) { SISWEB.rejectOrder(e.data.id, e.data.isFeria, e.data.compradora); });
			else
				$('#' + prefix + 'b').click(o2, function(e) { SISWEB._orderWithoutClientWarning(); });
		}
	}
	else
	{
		if (orderdata.nroCliente * 1)
			$('#' + prefix + 'a').click(o2, function(e) { SISWEB.rejectOrder(e.data.id, e.data.isFeria, e.data.compradora); });
		else
			$('#' + prefix + 'a').click(o2, function(e) { SISWEB._orderWithoutClientWarning(); });
	}

	if (orderdata.nroCliente * 1)
		$('#' + prefix + 'cc').click(o2, function(e) { SISWEB.changeOrderCamp(e.data.id, null, e.data.reloadFn); });
	else
		$('#' + prefix + 'cc').click(o2, function(e) { SISWEB._orderWithoutClientWarning(); });
},

_orderAction: function(action, id, question, campania, isFeria, compradora, confirm, usr1, cssClass, processFn, progressMsg)
{
	if (typeof processFn == 'undefined') processFn = null;

	if (typeof question != 'undefined' && question != null)
		SISWEB.confirm(question, function()
		{
			if (typeof progressMsg != 'undefined') SISWEB._progressInit(progressMsg);
			SISWEB._doOrderAction(action, id, campania, isFeria, compradora, processFn, confirm, usr1);
		}, function() { }, cssClass);
	else
	{
		if (typeof progressMsg != 'undefined') SISWEB._progressInit(progressMsg);
		SISWEB._doOrderAction(action, id, campania, isFeria, compradora, processFn, confirm, usr1);
	}
},

_dataTable: function(table, apiAction, data, title)
{
	var tbl = $('#' + table);
	tbl.html('<tr><td>...</td></tr>');

	if (apiAction.substr(0, 5) == 'http:')
		$.ajax({
		url: apiAction,
		dataType: 'json',
		type: 'GET',
		data: data,
		async: false,
		success: function(resdata)
		{
			SISWEB._dataTableLoad(tbl, resdata);
		}
		});
	else
		SISWEB._api(apiAction, function(pdata)
		{
			SISWEB._dataTableLoad(tbl, pdata);
		}, data);
},

_dataTableLoad: function(tbl, pdata)
{
	tbl.html('');
	if (! pdata.length) return;

	for(var i = 0; i < pdata.length; i++)
	{
		var html = '';

		if (i == 0)
		{
			for(j in pdata[i]) html += '<th>' + j + '</th>';
			var tr = document.createElement('tr');
			tr.innerHTML = html;
			tbl.append(tr);
			html = '';
		}

		for(j in pdata[i]) html += '<td>' + pdata[i][j] + '</td>';

		var tr = document.createElement('tr');
		tr.innerHTML = html;
		tbl.append(tr);
	}
},

_anchor: function(id)
{
	var anchor = document.getElementById(id);
	if (anchor != null) anchor.scrollIntoView(true);
},

_initAnchors: function()
{
	var anchors = $('a[anchor]');
	for(var i = 0; i < anchors.length; i++)
		anchors[i].href = 'Javascript:SISWEB._anchor("' + $(anchors[i]).attr('anchor') + '")';
},

_progress: function(v, max)
{
	$('#progressbarval').html(v + ' de ' + max + ' (' + Math.round(v / max * 100) + '%)');
	$('#progressbar').progressbar('value', v);
	$('#progenv').show();
},

_progressInit: function(msg, max)
{
	$('#progmsg').html(msg);

	if (typeof max == 'undefined')
	{
		$('#progressbar').hide();
		$('#progcancelenv').hide();
		$('#progenv').show();
	}
	else
	{
		$('#progressbar').progressbar({ max:max }).show();
		$('#progcancelenv').show();
	}
},

_progressCancel: function()
{
	SISWEB._bulkOrders = new Array();
	$('#progenv').hide();
	$('#waitenv').hide();
},

_doBulkOrderAction: function(action, okFn, msg)
{
	if (SISWEB._bulkOrders.length)
	{
		$('#waitenv').show();

		if (SISWEB._bulkOrdersQ == SISWEB._bulkOrders.length)
			SISWEB._progressInit(msg, SISWEB._bulkOrdersQ);

		var order = SISWEB._bulkOrders.pop();
		if (typeof order.campania == 'undefined') order.campania = null;
		if (typeof order.isFeria == 'undefined') order.isFeria = null;
		if (typeof order.compradora == 'undefined') order.compradora = null;

		SISWEB._doOrderAction(action, order.id, order.campania, order.isFeria, order.compradora, function(data)
		{
			if (typeof data.msg != 'undefined')
				SISWEB.alert(data.msg, msg, function() { SISWEB._doBulkOrderProRes(action, okFn); });
			else if (typeof okFn != 'undefined' && SISWEB._bulkOrders.length == 0)
			{
				$('#progenv').hide();
				$('#waitenv').hide();
				okFn();
			}
			else
				SISWEB._doBulkOrderProRes(action, okFn);

			/*var q = SISWEB._bulkOrdersQ - SISWEB._bulkOrders.length;
			//console.log(q + '/' + SISWEB._bulkOrdersQ);
			SISWEB._progress(q, SISWEB._bulkOrdersQ);
			SISWEB._doBulkOrderAction(action, okFn);*/
		});
	}
	else
	{
		$('#progenv').hide();
		$('#waitenv').hide();
	}
},

_doBulkOrderProRes: function(action, okFn)
{
	SISWEB._progress(SISWEB._bulkOrdersQ - SISWEB._bulkOrders.length, SISWEB._bulkOrdersQ);
	SISWEB._doBulkOrderAction(action, okFn);
},

_doOrderAction: function(action, id, campania, isFeria, compradora, processFn, confirm, usr1)
{
	$('#waitenv').show();

	if (id != null)
		var data = { numeroPedido:id };
	else
		var data = null;

	var params = { numeroPedido:id };
	if (typeof campania != 'undefined' && campania != null) params.campania = campania;
	if (typeof isFeria != 'undefined' && isFeria != null) params.isFeria = isFeria;
	if (typeof compradora != 'undefined' && compradora != null) params.compradora = compradora;
	if (typeof confirm != 'undefined' && confirm) params.confirm = true;
	if (typeof usr1 != 'undefined' && usr1 != null) params.usr1 = usr1;

	return SISWEB._api(action, function(data)
	{
		//~ if (data != null) data._compradora = compradora;
		$('#waitenv').hide();
		if (typeof processFn == 'undefined' || processFn == null)
		{
			if (data != null && typeof data.msg != 'undefined')
			{
				if (typeof data.confirm != null && data.confirm)
					return SISWEB._orderAction(action, id, data.msg, campania, isFeria, compradora, true, usr1);
				SISWEB.alert(data.msg);
			}
			if (SISWEB._showOrdersFn == null) return;
			SISWEB._progressCancel();
			return SISWEB._showOrdersFn();
		}
		if (SISWEB._showOrdersFn != null && SISWEB._bulkOrders.length == 0) SISWEB._showOrdersFn();
		return processFn(data);
	}, params);
},

_loadDropList: function(campania, code, id, service, data, lbl, obj)
{
	$('#waitenv').show();

	data.campania = campania;
	data.code = code;

	SISWEB._api(service, function(data, params)
	{
		var droplist = document.getElementById(params.id);
		droplist.innerHTML = '<option value="">' + params.lbl + '</option>';
		var lbl = params.id + 'String';

		for (var i = 0; i < data[params.obj].length; i++)
		{
			var option = document.createElement('option');
			option.value = data[params.obj][i][params.id];
			if (typeof data[params.obj][i][lbl] == 'undefined')
				option.innerHTML = option.value;
			else
				option.innerHTML = data[params.obj][i][lbl];
			droplist.appendChild(option);
		}

		$('#waitenv').hide();
	}, data, { id:id, lbl:lbl, obj:obj });
},

_orderByList: function(i, el, o ,fn)
{
	if (SISWEB[i]._orderBy == o)
		SISWEB[i]._orderBy = -o;
	else
		SISWEB[i]._orderBy = o;

	//console.log(SISWEB[i]._orderBy);

	var o2 = Math.abs(o);

	$('#' + el + ' .glyphicon').hide();
	$('#' + el + ' #o' + o2 +' .glyphicon-chevron-' + (SISWEB[i]._orderBy < 0 ? 'down' : 'up')).show();

	fn();
},

_modal: function(title, body, width)
{
	if (typeof width == 'undefined') width = '600';
	$('#modal .modal-dialog').css('width', width + 'px');
	$('#modalTitle').html(title);
	$('#modalBody').html(body);
	$('#modal').modal({ keyboard: true });
	$('#modal').modal('show');
},

_modalClose: function()
{
	$('.modal').hide();
	$('.modal-backdrop').hide();
},

_initFinished: function()
{
	SISWEB._hashTimer = setTimeout(SISWEB._hashHandler, 100);

	$('#goBack').click(function() { SISWEB._goBack(); });

	$('#progcancel').click(function() { SISWEB._progressCancel(); });

	$('#body').show();

	return true;
},

_goBack: function()
{
	if (SISWEB._goBackFn == null)
		history.go(-1);
	else
		SISWEB._goBackFn();
},

_loadPage: function(page, url)
{
	if (! page) return false;

	SISWEB._modalClose();

	SISWEB._url = url;
	SISWEB._goBackFn = null;

	var pageObj = window['SISWEB'][page];

	if (typeof pageObj == 'undefined')
	{
		SISWEB._log('Página inválida.');
		return SISWEB._homePage();
	}

	if (typeof pageObj.conf != 'undefined' && typeof pageObj.conf.validUserTypes != 'undefined' && $.inArray(SISWEB._userType, pageObj.conf.validUserTypes) == -1)
	{
		SISWEB._log('Acceso no permitido.');
		return SISWEB._homePage();
	}

	$('#waitenv').show();

	//var s = Math.floor(Date.now()/600000);

	return $.get('pages/' + page + '.html?v=' + SISWEB._version, function(content)
	{
		$('#maincnt').html(content);

		if (SISWEB._userAlert == null)
			$('#alert').hide();
		else
			$('#alert').html(SISWEB._userAlert).show();

		$(window).scrollTop(0);

		var pageObj = window['SISWEB'][page];

		if (typeof pageObj == 'undefined' || typeof pageObj.conf == 'undefined')
			var pageConf = {};
		else
			var pageConf = pageObj.conf;

		if (typeof pageConf.enabled == 'undefined')
			pageConf.enabled = new Array();

		if (typeof pageConf.disabled == 'undefined')
			pageConf.disabled = new Array();

		if ($.inArray('userHeader', pageConf.enabled) == -1)
			$('#userHeader').hide();
		else
			$('#userHeader').show();

		if ($.inArray('showCampResume', pageConf.enabled) != -1)
			SISWEB.showCampResume();

		if ($.inArray('cleanBody', pageConf.enabled) == -1)
			$('body').removeClass('clean');
		else
			$('body').addClass('clean');

		$('#banner1').hide();
		$('#banner2').hide();
		$('#banner3').hide();

		if ($.inArray('showBanners', pageConf.enabled) != -1)
			SISWEB._api('get-publicidad', function(data, params)
			{
				for(var i = 1; i <= 3; i++)
					if (typeof data[i] != 'undefined')
					{
						var id = 'banner' + i;

						var cnt = document.getElementById(id);
						cnt.innerHTML = '';

						for(var j = 0; j < data[i].length; j++)
						{
							var div = document.createElement('div');
							div.className = 'item';
							div.innerHTML = (data[i][j][1] ? '<a href="' + data[i][j][1] + '" target="_blank">' : '') + '<img src="images/ads/' + data[i][j][0] + '" class="img-thumbnail img-responsive" />' + (data[i][j][1] ? '</a>' : '');
							cnt.appendChild(div);
						}

						$('#' + id).show();

						$("#" + id).owlCarousel({
						//navigation : true, // Show next and prev buttons
						autoPlay: true,
						stopOnHover: true,
						slideSpeed : 300,
						paginationSpeed : 400,
						singleItem:true
						});
					}
			});

		if ($.inArray('goBack', pageConf.disabled) == -1)
			$('#goBack').show();
			//$('#goBack2').show();
		else
			$('#goBack').hide();
			//$('#goBack2').hide();

		SISWEB._suggests = {};

		SISWEB._actualPage = page;

		if (typeof pageObj != 'undefined' && typeof pageObj.init != 'undefined') pageObj.init(SISWEB._url);

		$('#waitenv').hide();

		SISWEB._api('get-page', function(data, params)
		{
			if (data._version != SISWEB._version)
				SISWEB.alert("Se detectó una nueva versión. Presione <b>Continuar</b> para actualizar.", "Nueva versión", function()
				{
					window.location = '.';
				});
			else
			{
				SISWEB._flyers = data.flyers;
				SISWEB._actionFlyers = data.actionFlyers;
				SISWEB._comunidad = data.comunidad;
				if (SISWEB._global != null)
					SISWEB._global.comunidad = data.comunidad;
				SISWEB._sistemas = data.sistemas;
				SISWEB.showHideComunidad();
				SISWEB._showFlyer();
				if (data.notice == '')
					$('#userHeaderNotice').hide();
				else
				{
					$('#userHeaderNotice div').html(data.notice);
					$('#userHeaderNotice').show();
				}
			}
		}, { pg:page });

	}).fail(function() { window.location.hash = '#errorPageNotFound'; } );
},

_showFlyer: function()
{
	if (typeof SISWEB._flyers == 'undefined' || ! SISWEB._flyers.length) return;
	var flyer = SISWEB._flyers.pop();
	//console.log(flyer);
	SISWEB._alertFlyer(flyer.contenido, flyer.titulo, flyer.id, SISWEB._showFlyer);
},

_showActionFlyer: function(action)
{
	for(var i = 0; i < SISWEB._actionFlyers.length; i++)
		if (SISWEB._actionFlyers[i].action == action)
			SISWEB._alertFlyer(SISWEB._actionFlyers[i].contenido, SISWEB._actionFlyers[i].titulo, SISWEB._actionFlyers[i].id);
},

_alertFlyer: function(contenido, titulo, id, fn)
{
	SISWEB.alert(contenido, titulo, function()
	{
		SISWEB._closeFlyer(id, fn);
	}, {}, function()
	{
		$('.bootstrap-dialog-footer-buttons').append('<p style="position:absolute;bottom:15px"><input type="checkbox" id="flyerhide" /> no volver a mostrar</p>');
	}, BootstrapDialog.SIZE_WIDE, function()
	{
		//console.log('*2');
		SISWEB._closeFlyer(id, fn);
	});
},

_closeFlyer: function(id, fn)
{
	if (document.getElementById('flyerhide').checked)
		SISWEB._api('ocultar-flyer', function(data, params) {}, { flyer:id });
	if (typeof fn != 'undefined') fn();
},

_goComunidad: function(goHome)
{
	if (SISWEB._global.comunidad)
		window.location.hash = '#comunidad';
	else
		SISWEB.alert('En mantenimiento.', 'Comunidad', function()
		{
			if (goHome) window.location.hash = '#home';
		});
},

_homePage: function()
{
	switch(SISWEB._userType)
	{
	case SISWEB.USRTYPE_EMPRE:
		window.location.hash = '#panelEmpresaria';
		return true;
	case SISWEB.USRTYPE_REVEN:
		window.location.hash = '#panelRevendedora';
		return true;
	case SISWEB.USRTYPE_ADMIN:
		window.location.hash = '#panelAdministrador';
		return true;
	case SISWEB.USRTYPE_COORD:
		window.location.hash = '#panelCoordinadora';
		return true;
	case SISWEB.USRTYPE_REGIO:
	case SISWEB.USRTYPE_DIVIS:
		window.location.hash = '#panelRegional';
		return true;
	case SISWEB.USRTYPE_CONSU:
		window.location.hash = '#catalogo/liquidaciononline';
		//window.location.hash = '#catalogo/feria';
		return true;
	}

	SISWEB.goToLoginPage();
	return true;
},

_hashHandler: function()
{
	var hash = window.location.hash;
	hash = hash.substr(1, hash.length - 1);

	if (hash != SISWEB._hashOld)
	{
		// console.log(SISWEB._hashOld + ' => ' + hash);

		if (! hash || hash == 'home')
			SISWEB._homePage();
		else if (hash == 'logout')
			SISWEB.logout();
		else
		{
			var parts = hash.split('/', 2);

			if (parts[0] == 'referer')
				SISWEB.referer(parts[1]);
			else
				SISWEB._loadPage(parts[0], parts[1]);
		}

		SISWEB._hashOld = hash;
	}

	SISWEB._hashTimer = setTimeout(SISWEB._hashHandler, 100);
},

_api: function(apiFn, resFn, data, params, redirectOnError)
{
	if (typeof redirectOnError == 'undefined') redirectOnError = true;

	if (typeof data == 'undefined')
		var method = 'GET';
	else
		var method = 'POST';

	return $.ajax({
	url: SISWEB.apiURL + apiFn + '.php',
	dataType: 'json',
	type: method,
	data: data,
	async: false,
	success: function(resdata)
	{
		if (resdata == null || typeof resdata.error == 'undefined' || typeof resdata.code == 'undefined' || ! resdata.code || ! redirectOnError)
			return resFn(resdata, params);
		if (resdata != null && typeof resdata.code != 'undefined' && resdata.code == 2) return;
		if (SISWEB._userId != null) SISWEB.alert(resdata.error);
		SISWEB._userId = null;
		SISWEB.goToLoginPage();
	},
	error: function()
	{
		SISWEB.alert('Error de conexión y/o servidor.');$('#waitenv').hide();
	}
	});
},

_log: function(msg)
{
	if (SISWEB._debugging) console.log(msg);
}


/***************************************************************************************************************************************************/

,iniciarAutocompletado: function(setts)
{
	if (typeof setts.noReset == 'undefined' || ! setts.noReset)
		SISWEB.resetAutocomplete(setts.id);

	if (typeof setts.minLength == 'undefined')
		var minLength = 2;
	else
		var minLength = setts.minLength;

	var opts = {
	minLength: minLength,
	select: function(event, ui)
	{
		var vals = ui.item.value.split('-');
		//~ console.log(ui.item.label, ui.item.value, vals);
		SISWEB._suggests[setts.id].autoValue = vals[0];
		SISWEB._suggests[setts.id].autoLabel = vals[1] + '-' + vals[2];
		SISWEB._suggests[setts.id].value = vals[0];
		SISWEB._suggests[setts.id].selected = true;
		SISWEB._suggests[setts.id].label = (setts.hideIdInResults ? '' : (typeof vals[2] == 'undefined' ? vals[0] : vals[2]) + '-') + vals[1];
		$('#' + setts.id).val(vals[1]);
		//console.log("*1", vals[1]);
		if (typeof setts.onSelect != 'undefined') setts.onSelect();
		event.stopPropagation();
		return false;
	},
	response: function(event, ui)
	{
		//console.log(ui.content);

		if (typeof ui.content[0] == 'undefined')
		{
			var val = null;
			var lbl = null;
		}
		else
		{
			var vals = ui.content[0].value.split('-');
			var val = vals[0];
			var lbl = vals[1];
			//console.log(val);
		}

		if (! setts.searchOnlyInLbl)
		{
			SISWEB._suggests[setts.id].autoValue = val;
			SISWEB._suggests[setts.id].autoLabel = lbl;
		}
	},
	focus: function(event, ui) { return false; },
	search: function(event, ui)
	{
		if (setts.searchOnlyInLbl)
		{
			var term = $('#' + setts.id).val();
			var found = false;
			for(var i = 0; i < setts.source.length; i++)
			{
				var p = setts.source[i].split('-');
				if (p[1].indexOf(term) >= 0)
				{
					SISWEB._suggests[setts.id].autoValue = p[0];
					SISWEB._suggests[setts.id].autoLabel = p[1];
					found = true;
					break;
				}
			}
			return found;
		}
	}
	};

	if (typeof setts.url == 'undefined')
		opts.source = setts.source;
	else
		opts.source = SISWEB.apiURL + setts.url;

	if (typeof setts.hideIdInResults == 'undefined')
		setts.hideIdInResults = false;

	$('#' + setts.id).autocomplete(opts)
	.blur(function() {
		if (typeof SISWEB._suggests[setts.id].noSetLabel == 'undefined' || ! SISWEB._suggests[setts.id].noSetLabel)
		{
			var prevlbl = $(this).val();
			var newlbl = SISWEB._suggests[setts.id].label;
			//console.log("*5", newlbl, prevlbl);
			if (newlbl && ! prevlbl)
			{
				//console.log('changed!');
				$(this).val(newlbl);
			}
			SISWEB._suggests[setts.id].noSetLabel = true;
		}
	})
	.autocomplete('instance')._renderItem = function(ul, item)
	{
		var vals = item.label.split('-');
		var li = $('<li>').append('<a>' + (setts.hideIdInResults ? '' : (typeof vals[2] == 'undefined' ? vals[0] : vals[2]) + '-') + vals[1] + '</a>');

		if (setts.searchOnlyInLbl)
		{
			var term = $('#' + setts.id).val();
			if (vals[1].indexOf(term) < 0) return li;
		}

		var o = li.appendTo(ul);
		//~ console.log(o);
		return o;
    };

	var o = { setts:setts };

    $('#' + setts.id).keydown(o, function(e)
    {
		if (typeof SISWEB._suggests[setts.id].selected != "undefined" && SISWEB._suggests[setts.id].selected)
		{
			SISWEB._suggests[setts.id].selected = false;
			return true;
		}
		var code = e.keyCode || e.which;
		//console.log(code, SISWEB._suggests[e.data.setts.id].autoValue);
		if (code == 13 || code == 9)
		{
			if (typeof SISWEB._suggests[e.data.setts.id].autoValue != 'undefined' && SISWEB._suggests[e.data.setts.id].autoValue != null)
			{
				//console.log("*2", e.data.setts);
				//console.log(SISWEB._suggests[e.data.setts.id].autoValue);
				//console.log(SISWEB._suggests[e.data.setts.id].autoLabel);
				SISWEB._suggests[e.data.setts.id].value = SISWEB._suggests[e.data.setts.id].autoValue;
				SISWEB._suggests[e.data.setts.id].label = SISWEB._suggests[e.data.setts.id].autoLabel;
				SISWEB._suggests[e.data.setts.id].noSetLabel = true;
				var inputLbl = /*(e.data.setts.hideIdInResults ? '' : SISWEB._suggests[e.data.setts.id].autoValue + '-') +*/ SISWEB._suggests[e.data.setts.id].autoLabel;
				//console.log("*2", inputLbl);
				$('#' + e.data.setts.id).val(inputLbl);
			}
			/*else
				$('#' + e.data.setts.id).val('');*/

			//console.log(e.data.setts.onSelect);

			if (typeof e.data.setts.onSelect != 'undefined') e.data.setts.onSelect();
			return false;
		}
	});
}

/***************************************************************************************************************************************************/

};
