SISWEB.pedidosCarga =
{

_orderItems: new Array(),
_cartItems: new Array(),
_campanias: {},
_editingOrder: false,
_editingOrderData: null,
_orderCustomer: null,
_isCart: false,
_isCartFeria: false,
_campaign: null,
_goShopping: 'catalogo',
_headerCreated: false,
_awardsWarning: true,
_muestrarioPend: 0,
_conMail: null,
_conNombre: null,
_conDNI: null,
_conTel: null,
_conItemsQ: 0,
_conItems: null,
_showCuotas: false,
_popupUnidadesPremio: new Array(),
_popupUnidadesPremioC: new Array(),
_premiosMul: new Array(),
_premiosMulMax: 0,

//usado para el control de stock
_flagStockInsuficiente: false,

_flagCargaPromocion: false,
_flagBuscoPromocion: false,
_idPromocion: null,
_cantidadArticulos: null,
_codigoPromocion: null,
_idPromPromocion: null,
/*********************************************************************************************************************/
//para ubicar correctamente el foco en la carga de pedidos y el llamado a los servicios
_seAutocompleta: false,
_solicitudTipo: 0,
_solicitudColor: 0,
_solicitudTalle: 0,

/*********************************************************************************************************************/

conf: {
	enabled: [ 'userHeader' ]
},

addItem: function(noCheckQ)
{
	if (typeof noCheckQ == 'undefined') noCheckQ = false;

	var q = $('#addQ').val() * 1;

	if (typeof SISWEB._global.max_unid_item != 'undefined' && SISWEB._global.max_unid_item * 1 > 0 && q > SISWEB._global.max_unid_item * 1)
	{
		SISWEB.alert('Esta tratando de cargar más de ' + SISWEB._global.max_unid_item + ' unidades un producto. Por favor contáctese con atencionalcliente@juanabonita.com comunicando este problema para recibir instrucciones.');
		return;
	}

	if (! noCheckQ && q > 10)
	{
		SISWEB.confirm('¿Confirma agregar ' + q + ' unidades del producto?', function()
		{
			SISWEB.pedidosCarga.addItem(true);
		}, function() { });
		return;
	}

	var suggests = [['Type', 'tipo'], ['Color', 'color'], ['Tall', 'talle']];

	var check = false;
	var sugvals = new Array();

	for(var i = 0; i < suggests.length; i++)
	{
		var f = 'add' + suggests[i][0];
		var value = (i == 2 ? SISWEB._suggests[f].label : SISWEB._suggests[f].value);
		SISWEB._log(f);
		SISWEB._log(value);

		if (! value)
		{
			check = true;
			value = $('#' + f).val();
			SISWEB._log(value);
		}

		sugvals[i] = value;
	}

	if (check)
	{
		SISWEB._api('get-articulo', function(data, params)
		{
			$('#waitenv').hide();

			if (typeof data.msg == 'undefined')
			{
				for(var i = 0; i < suggests.length; i++)
				{
					var f = 'add' + suggests[i][0];
					var j = suggests[i][1];
					var k = j + 'String';
					var j = 'id' + j.substr(0, 1).toUpperCase() + j.substr(1);

					SISWEB._suggests[f].value = data[j];
					SISWEB._suggests[f].label = data[k];
					SISWEB._suggests[f].autoValue = data[j];
					SISWEB._suggests[f].autoLabel = data[k];
				}

				SISWEB.pedidosCarga.addItemOk();
			}
			else
				SISWEB.alert(data.msg);
		}, { campania:$('#campania').val(), code:$('#addCode').val(), tipo:sugvals[0], color:sugvals[1], talleString:sugvals[2], q:1 });

		return;
	}

	SISWEB.pedidosCarga.addItemOk();
},

addItemOk: function()
{
	if (SISWEB.pedidosCarga._addItem(
	$('#addCode').val(),
	SISWEB._suggests['addType'].value,
	SISWEB._suggests['addColor'].value,
	SISWEB._suggests['addTall'].value,
	$('#addQ').val(),
	$('#campania').val()
	))
	{
		SISWEB.pedidosCarga.ctrlOrderChg(false, false);
		SISWEB.pedidosCarga.clear();
	}
},

updateItemInServer: function(item, fn)
{
	SISWEB.pedidosCarga._updateItemInServer(item, 'modify', { cuotas:SISWEB.pedidosCarga._orderItems[item].cuotas == 2 ? 1 : 0, muestrario:SISWEB.pedidosCarga._orderItems[item].muestrario }, function(data)
	{
		fn(data);
	});
},

_checkCuotasMues: function(item, k)
{
	var checkbox = $('#item' + item + '_' + k)[0];
	if (typeof checkbox == 'undefined') return true;
	if (checkbox.checked)
	{
		SISWEB.alert('Un articulo puede ser solicitado como muestario o cuotas, pero no ambos.');
		return false;
	}
	return true;
},

checkCuotasItem: function(item)
{
	if (SISWEB.pedidosCarga._orderItems[item].cuotas == 1)
	{
		if (! SISWEB.pedidosCarga._checkCuotasMues(item, 'm'))
		{
			$('#item' + item + '_c')[0].checked = false;
			return;
		}
		SISWEB.pedidosCarga._orderItems[item].cuotas = 2;
	}
	else if (SISWEB.pedidosCarga._orderItems[item].cuotas == 2)
		SISWEB.pedidosCarga._orderItems[item].cuotas = 1;
	else
		return true;

//	SISWEB.pedidosCarga.updateItemInServer(item, function(data) { });

		/********************  VERSIÓN NUEVA  **********************/
	SISWEB.pedidosCarga.updateItemInServer(item, function(data) { 

		if (data != null)
		{
			$('#item' + item + '_c')[0].checked = false;
			SISWEB.alert(data.msg);

		}

	});
		/********************  VERSIÓN NUEVA  **********************/

	return true;
},

checkMuesItem: function(item)
{
	if (SISWEB.pedidosCarga._orderItems[item].muestrario)
		SISWEB.pedidosCarga._muestrarioPend += parseInt(SISWEB.pedidosCarga._orderItems[item].q, 10);
	else
	{
		if (! SISWEB.pedidosCarga._checkCuotasMues(item, 'c'))
		{
			$('#item' + item + '_m')[0].checked = false;
			return;
		}
		if (SISWEB.pedidosCarga._muestrarioPend == 0 || ( SISWEB.pedidosCarga._muestrarioPend - SISWEB.pedidosCarga._orderItems[item].q ) < 0 )
		{
			document.getElementById('item' + item + '_m').checked = '';
			SISWEB.alert('No puede activar el muestrario porque estaría superando el máximo permitido de unidades.');
			return;
		}

		SISWEB.pedidosCarga._muestrarioPend -= SISWEB.pedidosCarga._orderItems[item].q;
	}

	console.log("Este es el muestrario Pendiente:  " + SISWEB.pedidosCarga._muestrarioPend);

	if(  SISWEB.pedidosCarga._muestrarioPend  >= 0 ){

		SISWEB.pedidosCarga._orderItems[item].muestrario = ! SISWEB.pedidosCarga._orderItems[item].muestrario;

		if (SISWEB.pedidosCarga._isCart)
			SISWEB.pedidosCarga._cartItems = SISWEB.pedidosCarga._orderItems;

		SISWEB.pedidosCarga.updateItemInServer(item, function(data)
		{
			if (data != null)
			{
				SISWEB.pedidosCarga._muestrarioPend -= SISWEB.pedidosCarga._orderItems[item].q;
				document.getElementById('item' + item + '_m').checked = '';
				SISWEB.pedidosCarga._orderItems[item].muestrario = ! SISWEB.pedidosCarga._orderItems[item].muestrario;
				SISWEB.alert(data.msg);
			}
		});
	}
	else{
			SISWEB.alert('No puede activar el muestrario porque estaría superando el máximo permitido de unidades. Le quedan disponibles ' + SISWEB.pedidosCarga._muestrarioPend + ' unidades');
	}	

	/*SISWEB.pedidosCarga._updateItemInServer(item, 'modify', { muestrario:SISWEB.pedidosCarga._orderItems[item].muestrario }, function(data)
	{
		if (data != null)
		{
			SISWEB.pedidosCarga._muestrarioPend -= SISWEB.pedidosCarga._orderItems[item].q;
			document.getElementById('item' + item + '_m').checked = '';
			SISWEB.pedidosCarga._orderItems[item].muestrario = ! SISWEB.pedidosCarga._orderItems[item].muestrario;
			SISWEB.alert(data.msg);
		}
	});*/

	return true;
},

removeItem: function(item)
{
	SISWEB.confirm('¿Desea eliminar el item del pedido?', function()
	{
		SISWEB.pedidosCarga._updateItemInServer(item, 'remove');


	/********************  VERSIÓN VIEJA  **********************/

/*
		var items = new Array();
		var j = 0;

		for (var i = 0; i < SISWEB.pedidosCarga._orderItems.length; i++)
			if (i == item)
			{
				if (SISWEB.pedidosCarga._orderItems[i].muestrario)
					SISWEB.pedidosCarga._muestrarioPend += SISWEB.pedidosCarga._orderItems[i].q * 1;
			}
			else
				items.push(SISWEB.pedidosCarga._orderItems[i]);

		SISWEB.pedidosCarga._orderItems = items;

		SISWEB.pedidosCarga.loadItems();
		
		SISWEB.pedidosCarga.popupUnidadesPremio();
*/

	/********************  VERSIÓN VIEJA  **********************/


	/********************  VERSIÓN NUEVA  **********************/

	SISWEB.pedidosCarga.loadOrder();

    setTimeout( function(){ 
		SISWEB.pedidosCarga.loadItems();		
		SISWEB.pedidosCarga.popupUnidadesPremio();
    }, 600);

	/********************  VERSIÓN NUEVA  **********************/



	}, function() { });
},

/********************  VERSIÓN VIEJA  **********************/

/*

loadItems: function()
{
	if (SISWEB.pedidosCarga._isCart)
		SISWEB.pedidosCarga._cartItems = SISWEB.pedidosCarga._orderItems;
	else if (SISWEB.pedidosCarga._headerCreated && SISWEB._userType != SISWEB.USRTYPE_EMPRE)
	{
		SISWEB.pedidosCarga.ctrlOrderChg(false, false);
	}

	var body = document.getElementById('DetallePedidoBody');
	if (body == null) return;
	body.innerHTML = '';

	if (SISWEB.pedidosCarga._orderItems.length)
	{
		var totQ = 0;
		var tot = 0;
		var totPtos = 0;

		for (var i = 0; i < SISWEB.pedidosCarga._orderItems.length; i++)
		{
			var prefix = 'item' + i + '_';

			var tr = document.createElement('tr');
			if (typeof SISWEB.pedidosCarga._orderItems[i].preventa != 'undefined' && SISWEB.pedidosCarga._orderItems[i].preventa != 0)
			{
				tr.className = 'preventa';
				tr.title = 'Item considerado en la preventa';
			}

			var html = '<td>' + SISWEB.pedidosCarga._orderItems[i].code + '</td><td>' + SISWEB.pedidosCarga._orderItems[i].type + '-' + SISWEB.pedidosCarga._orderItems[i].typeLabel + '</td><td>' + SISWEB.pedidosCarga._orderItems[i].color + '-' + SISWEB.pedidosCarga._orderItems[i].colorLabel + '</td><td>' + SISWEB.pedidosCarga._orderItems[i].tallLabel + '</td><td>' + SISWEB.pedidosCarga._orderItems[i].descripcion + '</td><td>' + SISWEB.pedidosCarga._orderItems[i].q + (SISWEB.pedidosCarga._orderItems[i].preventa > 0 && SISWEB.pedidosCarga._orderItems[i].preventa < SISWEB.pedidosCarga._orderItems[i].q ? ' <span title="Cantidad solicitada en preventa">(' + SISWEB.pedidosCarga._orderItems[i].preventa + ')</span>' : '') + '</td><td class="cuotas">' + (SISWEB.pedidosCarga._orderItems[i].cuotas > 0 ? '<input type="checkbox" id="' + prefix + 'c" ' + (SISWEB.pedidosCarga._orderItems[i].cuotas == 2 ? 'checked="checked"' : '') + ' />' : '') + '</td>';

			if (! SISWEB.pedidosCarga._isCart)
			{
				var ptos = (SISWEB.pedidosCarga._orderItems[i].ptos == null ? 0 : SISWEB.pedidosCarga._orderItems[i].ptos * SISWEB.pedidosCarga._orderItems[i].q);
				html += '<td class="DetallePedido_x">' + ptos + '</td>';
				totPtos += ptos;
			}

			tr.innerHTML = html;

			if (SISWEB._userType != SISWEB.USRTYPE_REVEN && ! SISWEB.pedidosCarga._isCartFeria)
			{
				var td = document.createElement('td');
				var input = document.createElement('input');
				input.type = 'checkbox';
				input.id = prefix + 'm';
				if (SISWEB.pedidosCarga._orderItems[i].muestrario)
					input.checked = 'checked';
				td.appendChild(input);
				tr.appendChild(td);
			}

			td = document.createElement('td');
			var button = document.createElement('button');
			button.innerHTML = '<span class="glyphicon glyphicon-trash"></span>';
			button.id = prefix + 'd';
			td.appendChild(button);
			tr.appendChild(td);
			body.appendChild(tr);

			var o = { i:i };

			if (SISWEB._userType != SISWEB.USRTYPE_REVEN)
				$('#' + prefix + 'm').click(o, function(e) { SISWEB.pedidosCarga.checkMuesItem(e.data.i); });

			$('#' + prefix + 'd').click(o, function(e) { SISWEB.pedidosCarga.removeItem(e.data.i); });

			if (SISWEB.pedidosCarga._orderItems[i].cuotas > 0)
				$('#' + prefix + 'c').click(o, function(e) { SISWEB.pedidosCarga.checkCuotasItem(e.data.i); });

			if (SISWEB.pedidosCarga._showCuotas)
				$('.cuotas').show();
			else
				$('.cuotas').hide();
				
			totQ += SISWEB.pedidosCarga._orderItems[i].q * 1;
		}

		$('#DetallePedido').show();
		$('#DetallePedidoEmpty').hide();

		SISWEB.pedidosCarga.popupUnidadesPremio();
	}
	else
	{
		$('#DetallePedido').hide();
		$('#DetallePedidoEmpty').show();
	}
},

*/

	/********************  VERSIÓN VIEJA  **********************/


	/********************  VERSIÓN NUEVA  **********************/

loadItems: function()
{
	if (SISWEB.pedidosCarga._isCart)
		SISWEB.pedidosCarga._cartItems = SISWEB.pedidosCarga._orderItems;
	else if (SISWEB.pedidosCarga._headerCreated && SISWEB._userType != SISWEB.USRTYPE_EMPRE)
	{
		SISWEB.pedidosCarga.ctrlOrderChg(false, false);
	}

	var body = document.getElementById('DetallePedidoBody');
	if (body == null) return;
	body.innerHTML = '';

	if (SISWEB.pedidosCarga._orderItems.length)
	{
		var totQ = 0;
		var tot = 0;
		var totPtos = 0;

		for (var i = 0; i < SISWEB.pedidosCarga._orderItems.length; i++)
		{
			var prefix = 'item' + i + '_';

			var tr = document.createElement('tr');
			if (typeof SISWEB.pedidosCarga._orderItems[i].preventa != 'undefined' && SISWEB.pedidosCarga._orderItems[i].preventa != 0)
			{
				tr.className = 'preventa';
				tr.title = 'Item considerado en la preventa';
			}

			var tipoString = '';
			var colorString = '';
			if(typeof SISWEB.pedidosCarga._orderItems[i].type == 'undefined'){
				tipoString = SISWEB.pedidosCarga._orderItems[i].typeLabel.substring(0, 2);
			}
			else{
				tipoString = SISWEB.pedidosCarga._orderItems[i].type;
			}
			if(typeof SISWEB.pedidosCarga._orderItems[i].color == 'undefined'){
				colorString = SISWEB.pedidosCarga._orderItems[i].colorLabel.substring(0, 2);
			}
			else{
				colorString = SISWEB.pedidosCarga._orderItems[i].color;
			}


			var html = '<td>' + SISWEB.pedidosCarga._orderItems[i].code + '</td><td>' + /* SISWEB.pedidosCarga._orderItems[i].type */ tipoString + '-' + SISWEB.pedidosCarga._orderItems[i].typeLabel + '</td><td>' + /* SISWEB.pedidosCarga._orderItems[i].color */ colorString + '-' + SISWEB.pedidosCarga._orderItems[i].colorLabel + '</td><td>' + SISWEB.pedidosCarga._orderItems[i].tallLabel + '</td><td>' + SISWEB.pedidosCarga._orderItems[i].descripcion + '</td><td>' + SISWEB.pedidosCarga._orderItems[i].q + (SISWEB.pedidosCarga._orderItems[i].preventa > 0 && SISWEB.pedidosCarga._orderItems[i].preventa < SISWEB.pedidosCarga._orderItems[i].q ? ' <span title="Cantidad solicitada en preventa">(' + SISWEB.pedidosCarga._orderItems[i].preventa + ')</span>' : '') + '</td><td class="cuotas">' + (SISWEB.pedidosCarga._orderItems[i].cuotas > 0 ? '<input type="checkbox" id="' + prefix + 'c" ' + (SISWEB.pedidosCarga._orderItems[i].cuotas == 2 ? 'checked="checked"' : '') + ' />' : '') + '</td><td>' + (SISWEB.pedidosCarga._orderItems[i].codigoPromocion != null ? SISWEB.pedidosCarga._orderItems[i].codigoPromocion : '' )+ '</td>';

			if (! SISWEB.pedidosCarga._isCart)
			{
				var ptos = (SISWEB.pedidosCarga._orderItems[i].ptos == null ? 0 : SISWEB.pedidosCarga._orderItems[i].ptos * SISWEB.pedidosCarga._orderItems[i].q);
				html += '<td class="DetallePedido_x">' + ptos + '</td>';
				totPtos += ptos;
			}

			tr.innerHTML = html;

			console.log(SISWEB.pedidosCarga._orderItems[i].idWebPromocionRelacion);

			if( SISWEB.pedidosCarga._orderItems[i].idWebPromocionRelacion != null  ||  
				( typeof SISWEB.pedidosCarga._orderItems[i].esPromo != 'undefined' && SISWEB.pedidosCarga._orderItems[i].esPromo ) ){
				tr.setAttribute("style", "background-color:lightsalmon;");
			}
/*			if( typeof SISWEB.pedidosCarga._orderItems[i].esPromo != 'undefined' && SISWEB.pedidosCarga._orderItems[i].esPromo == true){
				tr.setAttribute("style", "background-color:lightsalmon;");	
			}
*/

			if (SISWEB._userType != SISWEB.USRTYPE_REVEN && ! SISWEB.pedidosCarga._isCartFeria)
			{
				var td = document.createElement('td');
				var input = document.createElement('input');
				input.type = 'checkbox';
				input.id = prefix + 'm';
				if (SISWEB.pedidosCarga._orderItems[i].muestrario)
					input.checked = 'checked';
				td.appendChild(input);
				tr.appendChild(td);
			}

			td = document.createElement('td');
			var button = document.createElement('button');
			button.innerHTML = '<span class="glyphicon glyphicon-trash"></span>';
			button.id = prefix + 'd';
			td.appendChild(button);
			tr.appendChild(td);
			body.appendChild(tr);

			var o = { i:i };

			if (SISWEB._userType != SISWEB.USRTYPE_REVEN)
				$('#' + prefix + 'm').click(o, function(e) { SISWEB.pedidosCarga.checkMuesItem(e.data.i); });

			$('#' + prefix + 'd').click(o, function(e) { SISWEB.pedidosCarga.removeItem(e.data.i); });

			if (SISWEB.pedidosCarga._orderItems[i].cuotas > 0)
				$('#' + prefix + 'c').click(o, function(e) { SISWEB.pedidosCarga.checkCuotasItem(e.data.i); });

			if (SISWEB.pedidosCarga._showCuotas)
				$('.cuotas').show();
			else
				$('.cuotas').hide();
				
			totQ += SISWEB.pedidosCarga._orderItems[i].q * 1;
		}

		$('#DetallePedido').show();
		$('#DetallePedidoEmpty').hide();

		SISWEB.pedidosCarga.popupUnidadesPremio();
	}
	else
	{
		$('#DetallePedido').hide();
		$('#DetallePedidoEmpty').show();
	}
},

/********************  VERSIÓN NUEVA  **********************/

popupUnidadesPremio: function()
{
	var k = SISWEB.pedidosCarga._popupUnidadesPremio.length;
	
	if (k)
	{		
		var totQ = 0;
		for (var i = 0; i < SISWEB.pedidosCarga._orderItems.length; i++)
			if (SISWEB.pedidosCarga._orderItems[i].tipoVenta == '1')
				totQ += SISWEB.pedidosCarga._orderItems[i].q * 1;
		
		var lbl = 'Felicidades alcanzaste todos los niveles de premios!.';
		
		if (totQ >= SISWEB.pedidosCarga._popupUnidadesPremio[k - 1])
			SISWEB.pedidosCarga._premiosMulMax = SISWEB.pedidosCarga._popupUnidadesPremioC[k - 1];
		else
		{
			SISWEB.pedidosCarga._premiosMulMax = 0;
			
			for(var i = 0; i < k; i++)
				if (SISWEB.pedidosCarga._popupUnidadesPremio[i] > totQ)
				{
					var j = SISWEB.pedidosCarga._popupUnidadesPremio[i] - totQ;
					if (j > 1) j = j + ' unidades'; else j = j + ' unidad';
					lbl = (i > 0 ? 'Ya te ganaste el premio por ' + SISWEB.pedidosCarga._popupUnidadesPremio[i-1] + ' unidades. ' : '') + 'Suma ' + j + ' más y gana el premio del siguiente nivel!.';
					if (i > 0)
						SISWEB.pedidosCarga._premiosMulMax = SISWEB.pedidosCarga._popupUnidadesPremioC[i - 1];
					break;
				}
		}
		
		$('#ABM_limites_lbl').html(lbl);
		$('#ABM_limites').show();
		
		SISWEB.pedidosCarga.showPremiosMul();
	}	
},


/********************  VERSIÓN VIEJA  **********************/

/*
loadOrder: function()
{
	$('#waitenv').show();

	//~ console.log(SISWEB.pedidosCarga._editingOrderData);

	SISWEB._api('get-pedido', function(data)
	{
		if (SISWEB._userType == SISWEB.USRTYPE_REVEN && (data.estado == 130 || data.estado == 90))
		{
			SISWEB.alert('Tu ' + (SISWEB._userNegocio == 'D' ? 'líder' : 'empresaria') + ' está cargando un pedido a tu nombre. Debes esperar a que ella lo de por cerrado para cargar un pedido propio.');
			window.location.hash = '#home';
			return;
		}

		$('#nombrevendedora').val(data.numeroCliente + '- ' + data.nombreCliente);
		$('#soutien').val(data.soutien);
		$('#bombacha').val(data.bombacha);
		$('#inferior').val(data.inferior);
		$('#superior').val(data.superior);
		$('#jean').val(data.jean);
		$('#campania').val(data.campania);
		SISWEB.pedidosCarga._muestrarioPend = data.muestrarioPend;

		// SISWEB.pedidosCarga._orderItems = new Array();

		SISWEB.pedidosCarga._orderCustomer = data.cliente;

		if (data.items != null)
			for (var i = 0; i < data.items.length; i++)
				if (data.items[i].isFeria == '0' && data.items[i].estado != '160')
				SISWEB.pedidosCarga._orderItems.push(
				{
				code: data.items[i].code,
				type: data.items[i].tipo,
				color: data.items[i].color,
				tall: data.items[i].talle,
				q: data.items[i].cantidad,
				idArt: data.items[i].idArticulo,
				muestrario: data.items[i].muestrario == 'true' || data.items[i].muestrario == true,
				typeLabel: data.items[i].tipoString,
				colorLabel: data.items[i].colorString,
				tallLabel: data.items[i].talleString,
				idItem: data.items[i].idItem,
				descripcion: data.items[i].descripcion,
				ptos: data.items[i].ptos,
				preventa: data.items[i].preventa,
				cuotas: data.items[i].cuotas,
				tipoVenta: data.items[i].tipo_venta
				});

		SISWEB.pedidosCarga.loadItems();
		SISWEB.pedidosCarga._showCuotas = data.cuotas;
		if (data.cuotas) $('.cuotas').show();
		$('#waitenv').hide();
		SISWEB.pedidosCarga.showPremios(SISWEB.pedidosCarga._editingOrderData.cliente);
	}, { cliente:SISWEB.pedidosCarga._editingOrderData.cliente, campania:SISWEB.pedidosCarga._editingOrderData.campania });
},

*/

/********************  VERSIÓN VIEJA  **********************/

/********************  VERSIÓN NUEVA  **********************/

loadOrder: function()
{
	$('#waitenv').show();

	//~ console.log(SISWEB.pedidosCarga._editingOrderData);

	if( /*SISWEB.pedidosCarga._editingOrderData == null */ SISWEB.pedidosCarga._editingOrder == false ){
		if (SISWEB._userType == SISWEB.USRTYPE_REVEN)
			var cliente = SISWEB._userId;
		else if (typeof SISWEB._suggests['nombrevendedora'] != 'undefined')
		{
			$('#nombrevendedora').val(SISWEB._suggests['nombrevendedora'].label);
			if (SISWEB._suggests['nombrevendedora'].value != null)
				var cliente = SISWEB._suggests['nombrevendedora'].value.trim();
			else
				var cliente = null;
		}

		var campania = $('#campania').val();

		SISWEB.pedidosCarga._editingOrderData = { cliente:cliente, campania:campania };
		console.log( SISWEB.pedidosCarga._editingOrderData );
	}

	SISWEB._api('get-pedido', function(data)
	{
		if (SISWEB._userType == SISWEB.USRTYPE_REVEN && (data.estado == 130 || data.estado == 90))
		{
			SISWEB.alert('Tu ' + (SISWEB._userNegocio == 'D' ? 'líder' : 'empresaria') + ' está cargando un pedido a tu nombre. Debes esperar a que ella lo de por cerrado para cargar un pedido propio.');
			window.location.hash = '#home';
			return;
		}

		$('#nombrevendedora').val(data.numeroCliente + '- ' + data.nombreCliente);
		$('#soutien').val(data.soutien);
		$('#bombacha').val(data.bombacha);
		$('#inferior').val(data.inferior);
		$('#superior').val(data.superior);
		$('#jean').val(data.jean);
		$('#campania').val(data.campania);
		SISWEB.pedidosCarga._muestrarioPend = data.muestrarioPend;

		// SISWEB.pedidosCarga._orderItems = new Array();

		SISWEB.pedidosCarga._orderCustomer = data.cliente;

		if (data.items != null)

			//agregado para que no duplique los items`
			SISWEB.pedidosCarga._orderItems = new Array();

			for (var i = 0; i < data.items.length; i++)
				if (data.items[i].isFeria == '0' && data.items[i].estado != '160')
				SISWEB.pedidosCarga._orderItems.push(
				{
				code: data.items[i].code,
				type: data.items[i].tipo,
				color: data.items[i].color,
				tall: data.items[i].talle,
				q: data.items[i].cantidad,
				idArt: data.items[i].idArticulo,
				muestrario: data.items[i].muestrario == 'true' || data.items[i].muestrario == true,
				typeLabel: data.items[i].tipoString,
				colorLabel: data.items[i].colorString,
				tallLabel: data.items[i].talleString,
				idItem: data.items[i].idItem,
				descripcion: data.items[i].descripcion,
				ptos: data.items[i].ptos,
				preventa: data.items[i].preventa,
				cuotas: data.items[i].cuotas,
				tipoVenta: data.items[i].tipo_venta

				/*		agergado para ver el código y no el ID	*/
				,codigoColor: data.items[i].codigoColor
				,codigoTipo: data.items[i].codigoTipo

				,idWebPromocionRelacion: data.items[i].idWebPromocionRelacion
				,codigoPromocion: data.items[i].codigoPromocion


				});

		SISWEB.pedidosCarga.loadItems();
		SISWEB.pedidosCarga._showCuotas = data.cuotas;
		if (data.cuotas) $('.cuotas').show();
		$('#waitenv').hide();
		SISWEB.pedidosCarga.showPremios(SISWEB.pedidosCarga._editingOrderData.cliente);
	}, { cliente:SISWEB.pedidosCarga._editingOrderData.cliente, campania:SISWEB.pedidosCarga._editingOrderData.campania });
},


/********************  VERSIÓN NUEVA  **********************/


createOrder: function()
{
	if (! SISWEB.pedidosCarga._editingOrder)
	{
		if (SISWEB.pedidosCarga._isCart)
		{
			if (SISWEB._userType == SISWEB.USRTYPE_CONSU)
			{
				var email = $('#conMail').val();

				if (SISWEB._userType == SISWEB.USRTYPE_CONSU && (! email || ! $('#conNombre').val() || ! $('#conDNI').val() || ! $('#conTel').val()))
				{
					SISWEB.alert('Debe ingresar todos sus datos personales.');
					return;
				}

				if (! SISWEB.checkValidEmail(email))
				{
					SISWEB.alert('Debe ingresar un email válido.');
					return;
				}
			}

			$('#waitenv').show();

			SISWEB.pedidosCarga._orderCustomer = (SISWEB._userType == SISWEB.USRTYPE_EMPRE || SISWEB._userType == SISWEB.USRTYPE_COORD ? SISWEB._suggests['nombrevendedora'].value : SISWEB._userId);

			var campania = (SISWEB.pedidosCarga._isCart ? SISWEB.pedidosCarga._campaign : $('#campania').val());

			SISWEB.pedidosCarga._conItemsQ = SISWEB.pedidosCarga._orderItems.length;
			SISWEB.pedidosCarga._conItems = new Array();

			for (var i = 0; i < SISWEB.pedidosCarga._orderItems.length; i++)
				if (! SISWEB.pedidosCarga._addPedidoItem(campania, SISWEB.pedidosCarga._orderItems[i]))
				{
					$('#waitenv').hide();
					return;
				}

			SISWEB.pedidosCarga._cartItems = new Array();

			$('#waitenv').hide();

			window.location.hash = '#catalogo/liquidaciononline';
		}

		SISWEB.pedidosCarga._thanksAlert();

		if (SISWEB._userType == SISWEB.USRTYPE_EMPRE || SISWEB._userType == SISWEB.USRTYPE_COORD)
		{
			SISWEB._hashOld = null;
			return;
		}
	}
	else
		SISWEB.pedidosCarga._thanksAlert();

	window.location.hash = '#home';
},

ctrlOrderChg: function(vendedora, campania)
{
	//console.log(vendedora, campania);
	if (vendedora)
	{
		$('#nombrevendedora').attr('disabled', null);
		$('#nombrevendedora_help').show();
	}
	else
	{
		$('#nombrevendedora').attr('disabled', 'disabled');
		$('#nombrevendedora_help').hide();
	}

	if (campania)
	{
		$('#campania').attr('disabled', null);
		$('#campania_help').show();
	}
	else
	{
		$('#campania').attr('disabled', 'disabled');
		$('#campania_help').hide();
	}

	if (! vendedora && ! campania)
		$('#chgOrderBtn').show();
	else
		$('#chgOrderBtn').hide();
},

init: function(orderId)
{

	$("#btnCerrarModal").on("click", function(e){
	  	e.preventDefault();
	  	$('#modal').hide();
		$('#addBtn').attr({ 'disabled':null });
	});
	$("#btnAceptarModal").on("click", function(e){
	  	 e.preventDefault();
	  	 SISWEB.pedidosCarga._cargarCamposPromocion();
	});
	$("#btnCerrarModalPromocion").on("click", function(e){
	  	 e.preventDefault();
		$('#modalPromocion').hide();

	});
	$("#btnAceptarModalPromocion").on("click", function(e){
	  	e.preventDefault();
	  	for(var i = 0; i < SISWEB.pedidosCarga._cantidadArticulos; i++){
			if( $("#articulo_"+i).val() == -1 ){
				SISWEB.pedidosCarga._mostrarAlerta("<label><strong>Debe seleccionar todos los artículos para poder cargar la promoción</strong></label>");
				return false;
			}
		}	
		SISWEB.pedidosCarga._addItemsPromocion();
	});
	$("#btnAceptarModalAlerta").on("click", function(e){
	  	 e.preventDefault();
	  	 $('#modalAlerta').hide();
	});


	$("#modal").draggable({
	    handle: ".modal-header"
	}); 
	$("#modalPromocion").draggable({
	    handle: ".modal-header"
	}); 
	$("#modalAlerta").draggable({
	    handle: ".modal-header"
	}); 

	SISWEB.pedidosCarga._muestrarioPend = 0;
	SISWEB.pedidosCarga._orderCustomer = null;
	SISWEB.pedidosCarga._awardsWarning = true;
	SISWEB.pedidosCarga._isCartFeria = 0;
	SISWEB.pedidosCarga._headerCreated = false;
	SISWEB.pedidosCarga._premiosMul = new Array();
	SISWEB.pedidosCarga._premiosMulMax = 0;

	SISWEB._goBackFn = function() { SISWEB._homePage(); };

	$('.cuotas').hide();
	$('#DetallePedido').hide();
	$('#ABM_conData').hide();
	$('#DetallePedidoEmpty').show();
	$('#ABM_limites').hide();
	SISWEB.pedidosCarga.ctrlOrderChg(true, true);
	//$('#nombrevendedora').attr('disabled', null);
	//$('#campania').attr('disabled', null);

	$('#chgOrderBtn').click(function()
	{
		SISWEB.pedidosCarga.init();
		window.location.hash = '#pedidosCarga';
	});

	if (SISWEB._userType == SISWEB.USRTYPE_CONSU)
		$('#userHeader').hide();
	else
		$('#nombrevendedora_env').show();

	if (typeof orderId == 'undefined')
	{
		SISWEB.pedidosCarga._editingOrder = false;
		SISWEB.pedidosCarga._isCart = false;
		$('#ABM_premios').hide();
		$('#ABM_premios_new').hide();
		$('#ABM_premios_multiple').hide();
		$('#ABM_premios_inc').hide();
		$('#ABM_item').hide();
		$('#PanelDetallePedido').hide();
		$('#createBtn').hide();
	}
	else
	{
		var params = orderId.split(',');

		if (params[0] == 'cartFeria' || params[0] == 'cart')
		{
			$('#ABM_conData').show();
			SISWEB.pedidosCarga._isCart = true;

			if (SISWEB.pedidosCarga._isCartFeria = (params[0] == 'cartFeria'))
			{
				$('#nombrevendedora_env').hide();
				$('#ABM_item').hide();
				$('#ABM_premios').hide();
				$('#ABM_premios_new').hide();
				$('#ABM_premios_multiple').hide();
				$('#ABM_premios_inc').hide();
				$('#DetallePedido_x').hide();
				$('#createBtn').html('Terminar pedido');

				if (SISWEB._userType != SISWEB.USRTYPE_CONSU)
					$('#ABM_conData').hide();
			}

			if (SISWEB.pedidosCarga._conMail != null)
				$('#conMail').val(SISWEB.pedidosCarga._conMail);

			if (SISWEB.pedidosCarga._conNombre != null)
				$('#conNombre').val(SISWEB.pedidosCarga._conNombre);

			if (SISWEB.pedidosCarga._conDNI != null)
				$('#conDNI').val(SISWEB.pedidosCarga._conDNI);

			if (SISWEB.pedidosCarga._conTel != null)
				$('#conTel').val(SISWEB.pedidosCarga._conTel);

			$('#conMail').change(function() { SISWEB.pedidosCarga._conMail = $(this).val() });
			$('#conNombre').change(function() { SISWEB.pedidosCarga._conNombre = $(this).val() });
			$('#conDNI').change(function() { SISWEB.pedidosCarga._conDNI = $(this).val() });
			$('#conTel').change(function() { SISWEB.pedidosCarga._conTel = $(this).val() });
		}
		else
		{
			SISWEB.pedidosCarga._editingOrder = true;
			SISWEB.pedidosCarga._isCart = false;
			SISWEB.pedidosCarga._editingOrderData = { cliente:params[0], campania:params[1] };
		}
	}

	if (SISWEB.pedidosCarga._isCart)
	{
		SISWEB.pedidosCarga._editingOrder = false;
		SISWEB.pedidosCarga._orderItems = SISWEB.pedidosCarga._cartItems;
		$('#PanelDetallePedidoTitle').html('Carrito:');

		if (typeof params[1] == 'undefined')
			SISWEB.pedidosCarga.loadItems();
		else
			SISWEB.pedidosCarga._addItem(params[1], params[2], params[3], params[4], params[5], params[6], params[7], params[8], null, params[9]);

		$('#ABM_item').hide();
		$('#campania_env').hide();
		//$('#DetallePedido_p').show();
		//$('#DetallePedido_s').show();
		//$('#DetallePedidoFoot').show();
		$('#goShop_env').show();

		if (SISWEB._userType != SISWEB.USRTYPE_EMPRE)
		{
			$('#nombrevendedora_env').hide();
		}

		$('#goShop_env').click(function() { window.location.hash = SISWEB.pedidosCarga._goShopping; });

		if (SISWEB._userType == SISWEB.USRTYPE_REVEN)
			$('#DetallePedidoTotM').hide();
		else
			$('#DetallePedidoTotM').show();

		if (params[0] == 'cartFeria' && SISWEB.pedidosCarga._goShopping.substr(-17) != 'liquidaciononline')
			SISWEB.pedidosCarga._goShopping += '/liquidaciononline';
	}
	else
	{
		if (SISWEB._userType == SISWEB.USRTYPE_CONSU)
		{
			window.location.hash = '#home';
			return;
		}

		SISWEB.pedidosCarga._orderItems = new Array();
		$('#PanelDetallePedidoTitle').html('Tu Pedido:');
		//~$('#ABM_item').show();
		$('#campania_env').show();
		/*$('#DetallePedido_p').hide();
		$('#DetallePedido_s').hide();
		$('#DetallePedidoFoot').hide();*/
		$('#goShop_env').hide();

/*********************************************  VERSIÓN VIEJA *********************************/
//		$('#addBtn').click(function() { SISWEB.pedidosCarga.addItem(); });
/*********************************************  VERSIÓN VIEJA *********************************/

/*********************************************  VERSIÓN NUEVA *********************************/

		$('#addBtn').click(function() { 

			if( SISWEB.pedidosCarga._flagCargaPromocion == false &&  SISWEB.pedidosCarga._flagBuscoPromocion == false ){ 
				SISWEB.pedidosCarga._buscarPromocion();	
			}	
			else{
				SISWEB.pedidosCarga.addItem();
			}
		
		//	SISWEB.pedidosCarga.addItem(); 

		});

/*********************************************  VERSIÓN NUEVA *********************************/


		$('#clearBtn').click(function() { SISWEB.pedidosCarga.clear(); });

		$('#boton_ayuda').click(function()
		{
			$('#AyudaCargaItem').show();
			$('#boton_ayuda').hide();
		});

		$('#boton_ayuda_close').click(function()
		{
			$('#AyudaCargaItem').hide();
			$('#boton_ayuda').show();
		});

		SISWEB._api('get-campanias', function(data)
		{
			var activa = 0;
			var campanias = new Array();
			SISWEB.pedidosCarga._campanias = {};

			for (var i = 0; i < data.campanias.length; i++)
			{
				if (data.campanias[i].cierresOk != 0)
				{
					SISWEB.pedidosCarga._campanias[data.campanias[i].campania] = { preventa:data.campanias[i].preventa == 1 };
					campanias.push({ value:data.campanias[i].campania, label:data.campanias[i].campania });
				}
				if (data.campanias[i].activa == "1") activa = data.campanias[i].campania;
			}
			console.log('pedidosCArga');
			if (! campanias.length) return SISWEB.noCampaigns();

			SISWEB.loadDroplist('campania', campanias, 'Elija la campaña');

			if (SISWEB._userType != SISWEB.USRTYPE_EMPRE && SISWEB._userType != SISWEB.USRTYPE_REVEN)
				$('#campania').change(function(e)
				{
					//console.log('B');
					SISWEB.pedidosCarga.searchOrder();
					e.stopPropagation();
				});
			else if (SISWEB._userType == SISWEB.USRTYPE_EMPRE && activa)
				$('#campania').val(activa);

			if (SISWEB.pedidosCarga._editingOrder)
				return SISWEB.pedidosCarga.loadOrder();

			return true;
		}, { check_usertype:(SISWEB._userType != SISWEB.USRTYPE_EMPRE && SISWEB._userType != SISWEB.USRTYPE_COORD) });

		$('#addCode').change(function()
		{
			switch($(this).val().length)
			{
			case 3: var prefix = '0'; break;
			case 2: var prefix = '00'; break;
			case 1: var prefix = '000'; break;
			default: var prefix = '';
			}
			$(this).val(prefix + $(this).val());
			return SISWEB.pedidosCarga._addChangeVal($(this), 1);
		}).unbind('keydown').keydown(function(e)
		{
			var code = e.keyCode || e.which;
			//console.log(code);
			if (code == 13 || code == 9)
			{
				switch($(this).val().length)
				{
				case 3: var prefix = '0'; break;
				case 2: var prefix = '00'; break;
				case 1: var prefix = '000'; break;
				default: var prefix = '';
				}
				$(this).val(prefix + $(this).val());
				return SISWEB.pedidosCarga._addChangeVal($(this), 1);
			}
		}).focus(function()
		{
			if (SISWEB.pedidosCarga._orderCustomer == null && (typeof SISWEB._suggests.nombrevendedora == 'undefined' || SISWEB._suggests.nombrevendedora.value == null) && $('#nombrevendedora').attr('disabled') != 'disabled')
			{
				$('#nombrevendedora').focus();
				SISWEB.alert('Debe seleccionar una vendedora antes de cargar un pedido.');
			}
		});

		$('#addQ').change(function()
		{
			//~ console.log('x');
			return SISWEB.pedidosCarga._addChangeVal($(this), 5);
		}).unbind('keydown').keydown(function(e)
		{
			var code = e.keyCode || e.which;
			//console.log(code);
			if (code == 13 || code == 9)
			{
				SISWEB.pedidosCarga._addChangeVal($(this), 5);
				return false;
			}
		});

		$('#addTall').unbind('keydown').keydown(function(e)
		{
			var code = e.keyCode || e.which;
			//console.log('****');
			//console.log(code);
			if (code == 13 || code == 9)
			{
				$('#addQ').focus();
				return false;
			}
		});
	}

	if (typeof orderId != 'undefined' || SISWEB.pedidosCarga._isCart || SISWEB._userType == SISWEB.USRTYPE_REVEN)
	{
		/*if (SISWEB.pedidosCarga._isCart)
			$('#nombrevendedora').attr('disabled', null);
		else
			$('#nombrevendedora').attr('disabled', 'disabled');
		$('#campania').attr('disabled', 'disabled');*/

		var showVend = SISWEB.pedidosCarga._isCart;
		//var showCamp = true;

		if (SISWEB._userType == SISWEB.USRTYPE_REVEN)
			$('#nombrevendedora').val(SISWEB._userData.fullName);
	}
	else
	{
		var showVend = true;
		//var showCamp = true;
		//var showCamp = (SISWEB._userType != SISWEB.USRTYPE_COORD);
		/*$('#nombrevendedora').attr('disabled', null);
		if (SISWEB._userType == SISWEB.USRTYPE_COORD)
			$('#campania').attr('disabled', 'disabled');*/
	}

	SISWEB.pedidosCarga.ctrlOrderChg(showVend, typeof orderId == 'undefined');

	if (typeof orderId != 'undefined' && ! SISWEB.pedidosCarga._isCart)
	{
		SISWEB.pedidosCarga._editingOrder = true;
		//$('#createBtn').attr({ 'disabled':null });
		//~ $('#createBtn').hide();
	}
	else
	{
		if (SISWEB._userType == SISWEB.USRTYPE_EMPRE || SISWEB._userType == SISWEB.USRTYPE_COORD || SISWEB._userType == SISWEB.USRTYPE_REVEN)
		{
			if (SISWEB._userType == SISWEB.USRTYPE_EMPRE || SISWEB._userType == SISWEB.USRTYPE_COORD)
			{
				SISWEB.initAutocomplete({
				id: 'nombrevendedora',
				url: 'get-revendedoras.php',
				minLength: 1,
				onSelect: function()
				{
					//console.log('C');
					SISWEB.pedidosCarga.searchOrder();
				}
				});

				$('#nombrevendedora').keydown(function(e)
				{
					var code = e.keyCode || e.which;
					//console.log(code);
					if (code == 13 || code == 9) $('#addCode').focus();
				});
			}

			if (SISWEB._userType == SISWEB.USRTYPE_EMPRE || SISWEB._userType == SISWEB.USRTYPE_REVEN)
				$('#campania').change(function(e)
				{
					//console.log('A');
					SISWEB.pedidosCarga.searchOrder();
					e.stopPropagation();
				});
		}

		//~ $('#createBtn').show();
	}

	$('#createBtn').click(function()
	{
		SISWEB.pedidosCarga.createOrder();
		
		if (SISWEB._userType == SISWEB.USRTYPE_REVEN)
			var cliente = SISWEB._userId;
		else if (typeof SISWEB._suggests['nombrevendedora'] != 'undefined')
		{
			if (SISWEB._suggests['nombrevendedora'].value != null)
				var cliente = SISWEB._suggests['nombrevendedora'].value.trim();
			else
				return;
		}
		
		var premmul = '';		
		for(var i = 0; i < SISWEB.pedidosCarga._premiosMul.length; i++)
			for(var j = 0; j < SISWEB.pedidosCarga._premiosMul[i].arts.length; j++)
				premmul += SISWEB.pedidosCarga._premiosMul[i].cod + '|' + SISWEB.pedidosCarga._premiosMul[i].arts[j].cod11 + '|' + SISWEB.pedidosCarga._premiosMul[i].arts[j].sel + ',';
		//~ console.log(premmul);
		
		SISWEB._api('save-premiosmul', function(data, params)
		{
			$('#waitenv').hide();
		}, { campania:$('#campania').val(), cliente:cliente, sel:premmul });
	});

	if (SISWEB._userType == SISWEB.USRTYPE_REVEN || SISWEB.pedidosCarga._isCartFeria)
		$('#DetallePedido_m').hide();
	else
		$('#DetallePedido_m').show();

	var prem = ['soutien', 'bombacha', 'inferior', 'superior', 'jean'];

	for(var i in prem)
		$('#' + prem[i]).change({ i:prem[i] }, function(e)
		{
			var val = $(this).val() * 1;
			var min = SISWEB._global[e.data.i + '_minimo'] * 1;
			var max = SISWEB._global[e.data.i + '_maximo'] * 1;
			var pro = SISWEB._global[e.data.i + '_progresion'] * 1;
			var s = (val - min) % pro;
			//console.log(e.data.i, val, s, min, max, pro);
			if (val < min || val > max || s != 0)
			{
				SISWEB.alert('El talle de ' + e.data.i + ' debe estar entre ' + min + ' y ' + max + ' y con progresión de ' + pro + '.', null, function(a)
				{
					$('#' + e.data.i).val('').focus();
				});
			}
		}).unbind('keydown').keydown({ i:i * 1 + 1, id:prem[i * 1 + 1] }, function(e)
		{
			var code = e.keyCode || e.which;
			//console.log(code, e.data);
			if (code == 13)
			{
				if (e.data.i < 5)
					$('#' + e.data.id).focus();
				else
					$('#addCode').focus();
			}
		});

	$(function () { $("[data-toggle='tooltip']").tooltip(); });

	if (typeof orderId != 'undefined') $('#soutien').focus();
},

searchOrder: function()
{
	//console.log('1');
	SISWEB.pedidosCarga._orderItems = new Array();
	SISWEB.pedidosCarga.loadItems();
	$('#addCode').val('');
	$('#addQ').val('');
	// SISWEB.pedidosCarga._addInitSuggest(1);
	// SISWEB.pedidosCarga._addInitSuggest(2);
	// SISWEB.pedidosCarga._addInitSuggest(3);
	$('#addType').attr({ 'disabled':'disabled' });
	$('#addColor').attr({ 'disabled':'disabled' });
	$('#addTall').attr({ 'disabled':'disabled' });

	if (SISWEB._userType == SISWEB.USRTYPE_REVEN)
		var cliente = SISWEB._userId;
	else if (typeof SISWEB._suggests['nombrevendedora'] != 'undefined')
	{
		$('#nombrevendedora').val(SISWEB._suggests['nombrevendedora'].label);
		if (SISWEB._suggests['nombrevendedora'].value != null)
			var cliente = SISWEB._suggests['nombrevendedora'].value.trim();
		else
			var cliente = null;
	}

	var campania = $('#campania').val();

	if (cliente != null && campania)
	{
		$('#waitenv').show();

		SISWEB._api('get-pedido', function(data)
		{
			$('#waitenv').hide();
			if (data.zona_a == 1)
			{
				$('#campania').val('');
				SISWEB.alert('Esta vendedora pertenecía a otra Zona y tiene todavía un pedido abierto en la misma. Por favor contáctese con atencionalcliente@juanabonita.com indicando esta situacion y el número de zona y vendedora.');
			}
			else
			{
				if (data != null && typeof data.warning != 'undefined' && data.warning == 1)
					SISWEB.confirm('Usted ya envió un pedido para la campaña, ¿está segura que quiere cargar otro más?<br /><br /><a href="#historial">ver mis pedidos</a>', function()
					{
						SISWEB.pedidosCarga.processRevend(typeof data.cliente != 'undefined', cliente, $('#campania').val());
					}, function() { });
				else
					SISWEB.pedidosCarga.processRevend(typeof data.cliente != 'undefined', cliente, $('#campania').val());
			}
		}, { cliente:cliente, warning:1, campania:campania });
	}
	else
	{
		$('#ABM_premios').hide();
		$('#ABM_premios_new').hide();
		$('#ABM_premios_multiple').hide();
		$('#ABM_premios_inc').hide();
		$('#ABM_item').hide();
		$('#PanelDetallePedido').hide();
	}

	SISWEB.pedidosCarga._enableAddBtn();
},

showPremios: function(cliente)
{
	SISWEB._api('get-campania', function(data)
	{
		if (data.premios.length && data.premios[0].arts.length)
		{
			var html = '<div class="row">';
			var mod = 0;
			for(var i = 0; i < data.premios.length; i++)
			{
				mod = data.premios[i].mod;
				html += '<div class="col-md-6"><label>' + data.premios[i].desc + '</label><br /><select class="form-control" ' + (data.premios[i].mod == 1 ? '' : 'disabled="disabled"') + '><option value="">Elige tu opción</option>';
				for(var j = 0; j < data.premios[i].arts.length; j++)
					html += '<option ' + (data.premios[i].arts[j].sel == 1 ? 'selected="selected" ' : '') + 'value="' + data.premios[i].arts[j].id + '">' + data.premios[i].arts[j].cod11 + ' - ' + data.premios[i].arts[j].desc + '</option>';
				html += '</select></div>';
			}
			html += '</div>';
			if (mod == 0) html += '<p>El premio no puede ser modificado ya que Ud. ya envio un pedido con esta informacion anteriormente.</p>';
			$('#ABM_premios_new_bdy').html(html);
			$('#ABM_premios_new').show();
			$('#ABM_premios').hide();
		}
		else if ($('#campania').val() * 1 < SISWEB._global.prem_min_campania * 1)
		{
			$('#ABM_premios').show();
			$('#ABM_premios_new_bdy').html('');
			$('#ABM_premios_new').hide();
		}
		else
		{
			$('#ABM_premios').hide();
			$('#ABM_premios_new_bdy').html('');
			$('#ABM_premios_new').hide();
		}
		
		if (data.incentivo_premios.length)
		{
			var html = '<div class="row">';
			var mod = 0;
			for(var i = 0; i < data.incentivo_premios.length; i++)
			{
				html += '<div class="col-md-6"><label>' + data.incentivo_premios[i].desc + '</label><br /><select class="form-control" ' + (data.incentivo_premios[i].mod == 1 ? '' : 'disabled="disabled"') + '><option value="">Elige tu opción</option>';
				for(var j = 0; j < data.incentivo_premios[i].arts.length; j++)
					html += '<option ' + (data.incentivo_premios[i].arts[j].sel == 1 ? 'selected="selected" ' : '') + 'value="' + data.incentivo_premios[i].arts[j].cod11 + '">' + data.incentivo_premios[i].arts[j].cod11 + ' - ' + data.incentivo_premios[i].arts[j].desc + '</option>';
				html += '</select></div>';
			}
			html += '</div>';
			$('#ABM_premios_inc_bdy').html(html);
			$('#ABM_premios_inc').show();
		}
		else
		{
			$('#ABM_premios_inc_bdy').html('');
			$('#ABM_premios_inc').hide();
		}
		
		if (data.popup_unidades_premio)
		{
			SISWEB.pedidosCarga._popupUnidadesPremio = data.popup_unidades_premio.split(',');
			SISWEB.pedidosCarga._popupUnidadesPremioC = data.popup_unidades_premio_c.split(',');
			SISWEB.pedidosCarga.popupUnidadesPremio();
		}
		else
		{
			SISWEB.pedidosCarga._popupUnidadesPremio = [];
			SISWEB.pedidosCarga._popupUnidadesPremioC = [];
			$('#ABM_limites').hide();
		}

		SISWEB.pedidosCarga._premiosMul = data.premios_m;
		//~ SISWEB.pedidosCarga._premiosMul = data.premios;
		SISWEB.pedidosCarga.showPremiosMul();
		
	}, { cliente:cliente, campania:$('#campania').val() });
},

showPremiosMul: function()
{
	if (SISWEB.pedidosCarga._premiosMul.length && SISWEB.pedidosCarga._premiosMul[0].arts.length && SISWEB.pedidosCarga._premiosMulMax)
	{
		var html = '<div class="row">';
		var mod = 0;
		for(var i = 0; i < SISWEB.pedidosCarga._premiosMul.length; i++)
		{
			//~ mod = data.premios[i].mod;
			html += '<div class="col-md-12"><label>' + SISWEB.pedidosCarga._premiosMul[i].desc + '</label><div class="row">';
			var md = Math.floor(12 / SISWEB.pedidosCarga._premiosMul[i].arts.length);
			if (md == 0) md = 1;
			for(var j = 0; j < SISWEB.pedidosCarga._premiosMul[i].arts.length; j++)
			{
				if (typeof SISWEB.pedidosCarga._premiosMul[i].arts[j].sel == 'undefined' || SISWEB.pedidosCarga._premiosMul[i].arts[j].sel > SISWEB.pedidosCarga._premiosMulMax)
					SISWEB.pedidosCarga._premiosMul[i].arts[j].sel = 0;
				//~ html += '<div class="col-md-2"><div class="premiomul" style="background-image:url(contenidos/prem_fotos/619_00769001001.jpg)">PREMIO ' + String.fromCharCode(65 + j) + '<p><small>Artículo: ' + SISWEB.pedidosCarga._premiosMul[i].arts[j].cod11 + '</small>' + SISWEB.pedidosCarga._premiosMul[i].arts[j].desc + '<br /><select onchange="SISWEB.pedidosCarga.premiosMulSel(' + i + ', ' + j + ', this)">';
				html += '<div class="col-md-' + md + '"><div class="premiomul" style="background-image:url(contenidos/prem_fotos/' + $('#campania').val() + '_' + SISWEB.pedidosCarga._premiosMul[i].arts[j].cod11 + '.jpg)"><p><select onchange="SISWEB.pedidosCarga.premiosMulSel(' + i + ', ' + j + ', this)">';
				for(var k = 0; k <= SISWEB.pedidosCarga._premiosMulMax; k++)
					html += '<option value="' + k + '"' + (SISWEB.pedidosCarga._premiosMul[i].arts[j].sel == k ? ' selected="selected"' : '') + '>' + k + '</option>';
				html += '</select></p></div></div>';
			}
			html += '</div></div>';
		}
		html += '</div>';
		$('#ABM_premios_multiple_bdy').html(html);
		$('#ABM_premios_multiple').show();
	}
	else
		$('#ABM_premios_multiple').hide();
},

premiosMulSel: function(i, j, el)
{
	var v = $(el).val();
	SISWEB.pedidosCarga._premiosMul[i].arts[j].sel = v;	
	var t = 0;
	for(var j2 = 0; j2 < SISWEB.pedidosCarga._premiosMul[i].arts.length; j2++)
		t += SISWEB.pedidosCarga._premiosMul[i].arts[j2].sel * 1;
	//~ console.log(i, j, v, t);
	if (t > SISWEB.pedidosCarga._premiosMulMax)
	{
		SISWEB.alert('La suma de los premios seleccionados no puede superar ' + SISWEB.pedidosCarga._premiosMulMax + '.');
		SISWEB.pedidosCarga._premiosMul[i].arts[j].sel = 0;
		$(el).val('0');
	}
},

processRevend: function(loadOrder, cliente, campania)
{
	$('#ABM_premios').hide();
	$('#ABM_premios_new').hide();
	$('#ABM_premios_multiple').hide();
	$('#ABM_premios_inc').hide();
	$('#ABM_item').hide();
	$('#PanelDetallePedido').hide();
	$('#createBtn').hide();

	if (loadOrder)
	{
		SISWEB.confirm('Ya existe un pedido para la vendedora y campaña seleccionada. ¿Desea editarlo?', function()
		{
			window.location.hash = '#pedidosCarga/' + cliente + ',' + campania;
		}, function()
		{
			$('#campania').val('');
		});
	}
	else
	{
		SISWEB._api('get-pedido', function(data)
		{
			if (data.length)
			{
				var html = 'Recuerde que la vendedora seleccionada tiene otros pedidos abiertos en estas campañas: ';
				for(var i = 0; i < data.length; i++)
					html += '<p><button class="btn btn-success" onclick="SISWEB.editOrder(' + cliente + ',' + data[i] + ')">' + data[i] + ' - Continuar cargando</button></p>';
				html += '<p><button class="btn btn-success" onclick="SISWEB.closeDialog()">Comenzar un pedido nuevo en la campaña ' + $('#campania').val() + '</button></p>';
				SISWEB.alert(html);
			}
		}, { cliente:cliente, search:true });

		SISWEB.pedidosCarga.showPremios(cliente);

		$('#ABM_item').show();
		$('#PanelDetallePedido').show();
		$('#createBtn').show();
		//$('#waitenv').show();
		$('#soutien').focus();
		$('.cuotas').hide();

		SISWEB._api('get-muestrario-pend', function(data)
		{
			//$('#waitenv').hide();
			SISWEB.pedidosCarga._muestrarioPend = data.cantidad;
			SISWEB.pedidosCarga._showCuotas = data.cuotas;
			if (data.cuotas) $('.cuotas').show();
		}, { cliente:cliente, campania:$('#campania').val() });
	}
},

clear: function()
{
	$('#addCode').val('');
	$('#addQ').val('');
	var fields = ['Type', 'Color', 'Tall'];

	for(var i in fields)
	{
		var f = 'add' + fields[i];
		$('#' + f).val('').attr({ 'disabled':'disabled' });
		if (typeof SISWEB._suggests[f] != 'undefined')
		{
			SISWEB._suggests[f].value = '';
			SISWEB._suggests[f].label = '';
		}		
	}
/******************************		NUEVO		*******************************/
	SISWEB.pedidosCarga._flagCargaPromocion = false;
	SISWEB.pedidosCarga._flagBuscoPromocion = false;
	SISWEB.pedidosCarga._idPromocion = null;
	SISWEB.pedidosCarga._cantidadArticulos = null;
/******************************		NUEVO		*******************************/

	$('#addCode').focus();
},

_addItemToMemory: function(data)
{
	SISWEB.pedidosCarga._orderItems.push(data);
	SISWEB.pedidosCarga.loadItems();
},

_addPedidoItem: function(campania, data, isNew)
{
	var prems = $('#ABM_premios_new_bdy select');

	var warnBody = 'Recuerde ingresar los talles de las prendas del Programa de Premios. Estos talles seran los enviados en caso que gane un premio.';
	var warnTitle = 'Atención';

	if (prems.length)
	{
		for(var i = 0; i < prems.length; i++)
			if ($(prems[i]).val() == '')
			{
				SISWEB.alert(warnBody, warnTitle, function() { });
				break;
			}
	}
	else if (SISWEB.pedidosCarga._awardsWarning && ! SISWEB.pedidosCarga._isCartFeria && (! $('#soutien').val() || ! $('#bombacha').val() || ! $('#inferior').val() || ! $('#superior').val() || ! $('#jean').val()) && $('#campania').val() * 1 < SISWEB._global.prem_min_campania * 1)
	{
		SISWEB.pedidosCarga._awardsWarning = false;
		SISWEB.alert(warnBody, warnTitle, function() { $('#addCode').focus(); });
	}

	SISWEB.pedidosCarga._addPedidoItemConfirmed(campania, data, isNew);

	return true;
},

_addPedidoItemConfirmed: function(campania, data, isNew)
{
	if (typeof isNew == 'undefined') isNew = false;

	$('#waitenv').show();

	if (SISWEB.pedidosCarga._orderCustomer == null)
		SISWEB.pedidosCarga._orderCustomer = (typeof SISWEB._suggests.nombrevendedora != 'undefined' ? SISWEB._suggests.nombrevendedora.value : SISWEB._userId);

	var prems = $('#ABM_premios_new_bdy select');
	var premios = '';
	for(var i = 0; i < prems.length; i++) premios += $(prems[i]).val() + ',';
		
	prems = $('#ABM_premios_inc_bdy select');
	var premiosInc = '';
	for(var i = 0; i < prems.length; i++) premiosInc += $(prems[i]).val() + ',';
		
	SISWEB._api('add-pedido-item', function(apiData)
	{

/***************************************************************************************************************************************/

			//Mensaje cuando el pedido tendrá un cargo extra
        if(undefined != typeof apiData.msgError && null != apiData.msgError){ 
            console.log(apiData.msgError);
            SISWEB.alert(apiData.msgError, "Mensaje");
            SISWEB.pedidosCarga._flagStockInsuficiente = true;
	    }


       	else{

       		if (isNew) SISWEB.pedidosCarga._addItemToMemory(data);
       		 SISWEB.pedidosCarga._flagStockInsuficiente = false;

		}


/***************************************************************************************************************************************/

		$('#waitenv').hide();

		//COMENTADA POR EL CONTROL DE STOCK, SE HACE EN EL "ELSE" DE ARRIBA SI HAY STOCK DISPONIBLE
//		if (isNew) SISWEB.pedidosCarga._addItemToMemory(data);

		if (SISWEB.pedidosCarga._orderItems.length)
		{
			var i = SISWEB.pedidosCarga._orderItems.length - 1;
			SISWEB.pedidosCarga._orderItems[i].idItem = apiData.idItem;
		}

		if (SISWEB.pedidosCarga._conItems != null)
		{
			SISWEB.pedidosCarga._conItems.push(apiData.idItem);
			SISWEB.pedidosCarga._conItemsQ--;

			if (! SISWEB.pedidosCarga._conItemsQ)
				SISWEB._api('notify-pedido-feria', function(apiData) {}, { items:SISWEB.pedidosCarga._conItems, conMail:SISWEB.pedidosCarga._conMail, nombre:SISWEB.pedidosCarga._conNombre, campania:campania });
		}
	}, { idCliente:SISWEB.pedidosCarga._orderCustomer, campania:campania, idArticulo:data.idArt, cantidad:data.q, soutien:$('#soutien').val(), bombacha:$('#bombacha').val(), inferior:$('#inferior').val(), superior:$('#superior').val(), jean:$('#jean').val(), mail:$('#conMail').val(), nombre:$('#conNombre').val(), dni:$('#conDNI').val(), tel:$('#conTel').val(), premios:premios, premioInc:premiosInc/*, dev_orderid:SISWEB.pedidosCarga._orderId*/ });

/**********************		 NUEVO	 	****************************/

	SISWEB.pedidosCarga._flagCargaPromocion = false;
	SISWEB.pedidosCarga._flagBuscoPromocion = false;
	SISWEB.pedidosCarga._idPromocion = null;
	SISWEB.pedidosCarga._cantidadArticulos = null;
	SISWEB.pedidosCarga._codigoPromocion = null;
	SISWEB.pedidosCarga._idPromPromocion = null;
	$('#addBtn').attr({ 'disabled':"disabled" });

/**********************		 NUEVO	 	****************************/

},

_addNewItem: function(data, campania)
{
    
/*******************************************************************************************************************************************************/	

    SISWEB._api('get-stock-articulo', function(resData)
    {
      	var tieneStock = true;

      	if(resData.stock != false){
            console.log('---------------------------------');
            console.log('Datos del stock disponible actual: ' +  (  resData.stock['cantidad_total'] *1  ) );
            console.log('Cantidad solicitada:  ' + (data.q) );
            console.log('Stock resultante si se realiza la transacción: ' + (  (resData.stock['cantidad_total'] * 1)  - data.q ) );
            console.log(data);
            console.log('---------------------------------');

            var resultado = (resData.stock['cantidad_total'] *1 ) - data.q;

            if( resultado < 0 ){

                SISWEB._api('save-solicitud-articulo', function(result)
                {
                    console.log(result);
                }	, {  codigo11: resData.codigo11, campania: campania} );			    		

                SISWEB.alert('En estos momentos el artículo '+ resData.codigo11 +' no cuenta con suficiente stock. Te invitamos a que ingreses nuevamente en los próximos días para concretar tu compra');
                tieneStock = false;
                return false;
            }
       	}
		
        if(tieneStock){
            if (SISWEB.pedidosCarga._isCartFeria){
              console.log("Solo se guarda en memoria");
              SISWEB.pedidosCarga._addItemToMemory(data);
            }
            else{
              console.log("Se envió a guardar el item");
              return SISWEB.pedidosCarga._addPedidoItem(campania, data, true);
            }
        } 
        return false;

    }, {  idArticulo:data.idArt} );


/*******************************************************************************************************************************************************/
    
    
        /* ORIGINAL  */
/*    
	// update in server
	//if (SISWEB.pedidosCarga._editingOrder)
	if (SISWEB.pedidosCarga._isCartFeria)
		SISWEB.pedidosCarga._addItemToMemory(data);
	else
		return SISWEB.pedidosCarga._addPedidoItem(campania, data, true);

	return false;
*/  
    
},

_addItem: function(code, type, color, tall, q, campania, typeLabel, colorLabel, func, tallLabel)
{
	SISWEB.pedidosCarga._headerCreated = true;

	if (SISWEB.pedidosCarga._campaign == null && typeof campania != 'undefined' && campania != null)
		SISWEB.pedidosCarga._campaign = campania;

	var idx = null;

	// add a new product or update a existent one ?
	for (var i = 0; i < SISWEB.pedidosCarga._orderItems.length; i++)
		if (SISWEB.pedidosCarga._orderItems[i].code.toUpperCase() == code.toUpperCase() && SISWEB.pedidosCarga._orderItems[i].type == type && SISWEB.pedidosCarga._orderItems[i].color == color && SISWEB.pedidosCarga._orderItems[i].tall == tall)
		{
			idx = i;
			break;
		}

	if (idx == null) // add new product
	{
		$('#waitenv').show();

		var data = { campania:campania, code:code, tipo:type, color:color, talle:tall, q:q };
		var params = { code:code, tipo:type, color:color, talle:tall, q:q, campania:campania, func:func };

		if (typeof typeLabel == 'undefined')
			params.typeLabel = SISWEB._suggests['addType'].autoLabel;
		else
			params.typeLabel = typeLabel;

		if (typeof colorLabel == 'undefined')
			params.colorLabel = SISWEB._suggests['addColor'].autoLabel;
		else
			params.colorLabel = colorLabel;

		if (typeof tallLabel == 'undefined')
			params.tallLabel = SISWEB._suggests['addTall'].autoLabel;
		else
			params.tallLabel = tallLabel;

		// get idArt and price
		SISWEB._api('get-articulo', function(data, params)
		{
			$('#waitenv').hide();

			if (typeof data != 'undefined' && data != null && typeof data.idArticulo != 'undefined' && data.idArticulo != null)
			{
				var d = { code:params.code, type:params.tipo, color:params.color, tall:params.talle, q:params.q, idArt:data.idArticulo, muestrario:false, cuotas:data.cuotas, typeLabel:params.typeLabel, tallLabel:params.tallLabel, colorLabel:params.colorLabel, precio:data.precio, descripcion:data.descripcion, ptos:data.ptos, tipoVenta:data.tipoVenta };

				if (typeof $('#addTall').autocomplete('instance') != 'undefined')
					$('#addTall').autocomplete('option', 'source', []);

				if (typeof $('#addType').autocomplete('instance') != 'undefined')
					$('#addType').autocomplete('option', 'source', []);

				if (typeof $('#addColor').autocomplete('instance') != 'undefined')
					$('#addColor').autocomplete('option', 'source', []);

				if (SISWEB.pedidosCarga._addNewItem(d, params.campania))
					if (typeof params.func == 'function') params.func();
			}
		}, data, params);
	}
	else if (SISWEB.pedidosCarga._orderItems[idx].q != q)  // update a product
	{
		/*if (! SISWEB.pedidosCarga._campanias[$('#campania').val()].preventa && q < SISWEB.pedidosCarga._orderItems[idx].preventa * 1)
			SISWEB.confirm('Está modificando articulos que participan de la preventa. De continuar, todos sus productos dejaran de participar en la preventa. ¿Está seguro que desea continuar?', function()
			{
				for(var i = 0; i < SISWEB.pedidosCarga._orderItems.length; i++)
					SISWEB.pedidosCarga._orderItems[i].preventa = '0';
				SISWEB.pedidosCarga._updateItem(campania, idx, q);
			}, function() { });
		else*/
        
/*******************************************************************************************************************************************************/


    SISWEB._api('get-stock-articulo', function(resData)
    {
        var tieneStock = true;

        if(resData.stock != false){
            var cantDisponible = (resData.stock['cantidad_total'] *1 ) +  ( SISWEB.pedidosCarga._orderItems[idx].q * 1) ;

            console.log('---------------------------------');
            console.log('Datos del stock disponible actual: ' + ( resData.stock['cantidad_total']  *1  )  );
            console.log('Cantidad anterior del item en el pedido  ' + (SISWEB.pedidosCarga._orderItems[idx].q * 1 )  );
            console.log('Cantidad nueva del item en el pedido  ' + (q)  );
            console.log('Stock resultante si se realiza la transacción: ' + (cantDisponible - q ) );
            console.log('---------------------------------');

            if( (cantDisponible - q) < 0 ){  

                SISWEB._api('save-solicitud-articulo', function(result)
                {
                        console.log(result);
                }	, {  codigo11: resData.codigo11, campania: campania} );

                SISWEB.alert('En estos momentos el artículo '+ resData.codigo11 +' no cuenta con suficiente stock. Te invitamos a que ingreses nuevamente en los próximos días para concretar tu compra');
                tieneStock = false;
                return false;
            }
      	}
	   
        if(tieneStock){
            SISWEB.pedidosCarga._updateItem(campania, idx, q);
        } 
        return false;

    }, {  idArticulo:SISWEB.pedidosCarga._orderItems[idx].idArt} );


/*******************************************************************************************************************************************************/    
        
        
        /*    ORIGINAL  */
/*        
		SISWEB.pedidosCarga._updateItem(campania, idx, q);
*/
    
        }

	$('#addCode').focus();

	return true;
},

_updateItem: function(campania, idx, q)
{

/***************************************************************************************************************************************/

	var item = Object.assign( {}, SISWEB.pedidosCarga._orderItems[idx] );
	var cantAnterior = item.q;
	item.q = q;


	if (! SISWEB.pedidosCarga._isCart){
		SISWEB.pedidosCarga._addPedidoItem(campania, item);
	}


	setTimeout(function () {
		if(SISWEB.pedidosCarga._flagStockInsuficiente){	
			SISWEB.pedidosCarga._flagStockInsuficiente = false;
		}	
		else{
//			SISWEB.alert('Ha cambiado la cantidad del producto de ' + SISWEB.pedidosCarga._orderItems[idx].q + ' a ' + q);
			SISWEB.alert('Ha cambiado la cantidad del producto de ' + cantAnterior + ' a ' + q);

			SISWEB.pedidosCarga._orderItems[idx].q = q;
			SISWEB.pedidosCarga.loadItems();
		}

	}, 5000);


/***************************************************************************************************************************************/


//Comentado porque ya se tiene en cuenta arriba

/*	
	SISWEB.alert('Ha cambiado la cantidad del producto de ' + SISWEB.pedidosCarga._orderItems[idx].q + ' a ' + q);

	SISWEB.pedidosCarga._orderItems[idx].q = q;
	SISWEB.pedidosCarga.loadItems();
*/
	// update in server
//	if (/*SISWEB.pedidosCarga._editingOrder && */! SISWEB.pedidosCarga._isCart)
		//~ SISWEB.pedidosCarga._updateItemInServer(idx, 'modify', { cantidad:q - SISWEB.pedidosCarga._orderItems[idx].q });
//		SISWEB.pedidosCarga._addPedidoItem(campania, SISWEB.pedidosCarga._orderItems[idx]);
},

_updateItemInServer: function(item, action, data, onFunc)
{
	//if (! SISWEB.pedidosCarga._editingOrder) return false;

	if (typeof data == 'undefined') data = {};
	data.idItem = SISWEB.pedidosCarga._orderItems[item].idItem;

	//~console.log(data);
	$('#waitenv').show();
	return SISWEB._api(action + '-pedido-item', function(data)
	{
		$('#waitenv').hide();
		if (typeof onFunc == 'function') onFunc(data);
	}, data);
},

_addChangeVal: function(obj, lvl, noAutocomplete)
{
	if (typeof noAutocomplete == 'undefined') noAutocomplete = false;

	if (lvl < 5)
	{
		//$('#addQ').attr({ 'disabled':'disabled' });

		if (lvl < 4)
		{
			// SISWEB.pedidosCarga._addInitSuggest(3);
			$('#addTall').attr({ 'disabled':'disabled' });

			if (lvl < 3)
			{
				// SISWEB.pedidosCarga._addInitSuggest(2);
				$('#addColor').attr({ 'disabled':'disabled' });

				if (lvl < 2)
				{
					// SISWEB.pedidosCarga._addInitSuggest(1);
					$('#addType').attr({ 'disabled':'disabled' });

/*********************************************************************************************************************/
					/*		AGREGADO PARA UBICAR EL FOCO DURANTE LA CARGA	*/
					SISWEB.pedidosCarga._seAutocompleta = false;
					SISWEB.pedidosCarga._solicitudTipo = 0;
					SISWEB.pedidosCarga._solicitudColor = 0;
					SISWEB.pedidosCarga._solicitudTalle = 0;

/*********************************************************************************************************************/

				}
			}
		}
	}

	if (obj.val())
	{
		var suggData = { campania:$('#campania').val(), code:$('#addCode').val() };
		if (! noAutocomplete) suggData.autocomplete = true;

		if (lvl == 1)
		{
			var nextField = 'addType';
			var suggURL = 'get-tipos';
			var suggObj = 'tipos';
			var suggVal = 'tipo';
				var suggCod = 'codigo_tipo';
			var suggLbl = 'tipoString';
			var suggHideIdInResults = false;
			var searchOnlyInLbl = false;

/*******************************************************************************************************************************************************************************************************/
			/*		AGREGADO PARA EVITAR LAMMAR A LOS SERVICIOS REITERADAMENTE	*/
			SISWEB.pedidosCarga._solicitudTipo ++;
/*******************************************************************************************************************************************************************************************************/

		}
		else if (lvl == 2 || lvl == 3)
		{
			suggData.tipo = SISWEB._suggests['addType'].value;

			if (lvl == 2)
			{
				var nextField = 'addColor';
				var suggURL = 'get-colores';
				var suggObj = 'colores';
				var suggVal = 'color';
					var suggCod = 'codigo_color';
				var suggLbl = 'colorString';
				var suggHideIdInResults = false;
				var searchOnlyInLbl = false;

/*******************************************************************************************************************************************************************************************************/
				/*		AGREGADO PARA EVITAR LAMMAR A LOS SERVICIOS REITERADAMENTE	*/
				SISWEB.pedidosCarga._solicitudColor ++;
/*******************************************************************************************************************************************************************************************************/

			}
			else
			{
				var nextField = 'addTall';
				var suggURL = 'get-talles';
				var suggObj = 'talles';
				var suggVal = 'talle';
					var suggCod = false;
				var suggLbl = 'descripcion';
				var suggHideIdInResults = true;
				var searchOnlyInLbl = true;
				suggData.color = SISWEB._suggests['addColor'].value;

/*******************************************************************************************************************************************************************************************************/
				/*		AGREGADO PARA EVITAR LAMMAR A LOS SERVICIOS REITERADAMENTE	*/
				SISWEB.pedidosCarga._solicitudTalle ++;
/*******************************************************************************************************************************************************************************************************/

			}
		}
		else if (lvl == 4) var nextField = 'addQ';
		else if (lvl == 5) var nextField = 'addBtn';

		//if (nextField != 'addQ' && nextField != 'addBtn') $('#' + nextField).keydown(function(e) { return false; });


		/*		CONDICIÓN ORIGINAL		*/
//		if (typeof suggURL != 'undefined'){


/*******************************************************************************************************************************************************************************************************/
		/*		CONDICIÓN NUEVA		*/ /*		AGREGADO PARA EVITAR LAMMAR A LOS SERVICIOS REITERADAMENTE	*/
		if (typeof suggURL != 'undefined' &&   (  	(suggURL == 'get-tipos' &&	SISWEB.pedidosCarga._solicitudTipo < 2)  ||  (suggURL == 'get-colores' &&	SISWEB.pedidosCarga._solicitudColor < 3) 
		|| (suggURL == 'get-talles' &&	SISWEB.pedidosCarga._solicitudTalle < 2)   )  )
		{

/*******************************************************************************************************************************************************************************************************/		

			$('#waitenv').show();

			if (typeof SISWEB._suggests[nextField] != 'undefined' && typeof SISWEB._suggests[nextField].autoLabel != 'undefined')
			{
				SISWEB._suggests[nextField].autoLabel = null;
				SISWEB._suggests[nextField].autoValue = null;
			}

			//~ console.log(suggData);

			SISWEB._api(suggURL, function(data, params)
			{
				if (lvl == 1 && data[params.obj].length == 0)
				{
					SISWEB.alert('Código no válido.');
					$('#addCode').val('');
				}

				var values = new Array();
				for (var i = 0; i < data[params.obj].length; i++)
				{

			/*		
					var val = data[params.obj][i][params.val];
					var lbl = data[params.obj][i][params.lbl];
					//~ values.push({ value:val, label:lbl });
					if (params.lbl != params.val) val += '-' + lbl;
					values.push(val);
			*/
/*
					if(params.cod != false){
						var codigo = data[params.obj][i][params.cod];
					}
					var val = data[params.obj][i][params.val];
					var lbl = data[params.obj][i][params.lbl];
					//~ values.push({ value:val, label:lbl });
					
					if(params.cod != false){
					//	codigo += '-' + lbl;
						val += '-' + codigo+ ' ' +  lbl;
						values.push(val);
					}
					else{
						if (params.lbl != params.val) val += '-' + lbl;
						values.push(val);
					}	
*/

/***************************************************************************************************************************************************/


					if(params.cod != false){
						var codigo = data[params.obj][i][params.cod];
					}
					var val = data[params.obj][i][params.val];
					var lbl = data[params.obj][i][params.lbl];
					//~ values.push({ value:val, label:lbl });
					
					if(params.cod != false){
/*						codigo += '-' + lbl;
						values.push(codigo);
*/					
						val += '-' + codigo+ '-' +  lbl;
						values.push(val);
					}
					else{
						if (params.lbl != params.val) val += '-' + lbl;
						values.push(val);
					}	


/***************************************************************************************************************************************************/


				}

				//~ console.log(values);

				var acOpts = {id: params.nextField, minLength: 1, source:values, hideIdInResults:suggHideIdInResults, searchOnlyInLbl:searchOnlyInLbl, onSelect: function()
				{
					//console.log('**');
					return SISWEB.pedidosCarga._addChangeVal($('#' + params.nextField), params.nextLvl);
				}};

				if (noAutocomplete)
					acOpts.noReset = true;

//				SISWEB.initAutocomplete(acOpts);
				SISWEB.iniciarAutocompletado(acOpts);

				if (typeof data.autocomplete != 'undefined')
				{
					var autocompletes = new Array();
					//~ console.log(data.autocomplete);

					//~ console.log("*4");

					if (typeof SISWEB._suggests['addType'] == 'undefined')
						SISWEB._suggests['addType'] = { value:'', label:'' };

					if (typeof SISWEB._suggests['addColor'] == 'undefined')
						SISWEB._suggests['addColor'] = { value:'', label:'' };

					if (typeof SISWEB._suggests['addTall'] == 'undefined')
						SISWEB._suggests['addTall'] = { value:'', label:'' };

/*
					if (typeof data.autocomplete.type != 'undefined')
						autocompletes.push(['Type', data.autocomplete.type[0], data.autocomplete.type[0] + '-' + data.autocomplete.type[1], 2, data.autocomplete.type[1]]);

					if (typeof data.autocomplete.color != 'undefined')
						autocompletes.push(['Color', data.autocomplete.color[0], data.autocomplete.color[0] + '-' + data.autocomplete.color[1], 3, data.autocomplete.color[1]]);

					if (typeof data.autocomplete.tall != 'undefined')
						autocompletes.push(['Tall', data.autocomplete.tall[0], data.autocomplete.tall[0] + '-' + data.autocomplete.tall[1], 4, data.autocomplete.tall[1]]);
*/


/***************************************************************************************************************************************************/					
					
					if (typeof data.autocomplete.type != 'undefined')
						autocompletes.push(['Type', data.autocomplete.type[0], data.autocomplete.type[1] + '-' + data.autocomplete.type[2], 2, data.autocomplete.type[2]]);

					if (typeof data.autocomplete.color != 'undefined')
						autocompletes.push(['Color', data.autocomplete.color[0], data.autocomplete.color[1] + '-' + data.autocomplete.color[2], 3, data.autocomplete.color[2]]);

					if (typeof data.autocomplete.tall != 'undefined')
						autocompletes.push(['Tall', data.autocomplete.tall[0], data.autocomplete.tall[0] + '-' + data.autocomplete.tall[1], 4, data.autocomplete.tall[1]]);

/***************************************************************************************************************************************************/


					if (autocompletes.length)
						for(var i = 0; i < autocompletes.length; i++)
						{
							var field = 'add' + autocompletes[i][0];
							//console.log(autocompletes[i]);

/*********************************************************************************************************************/

							SISWEB.pedidosCarga._seAutocompleta = true;	

/*********************************************************************************************************************/							

							SISWEB.setAutocompleteVal(field, autocompletes[i][1], suggHideIdInResults ? autocompletes[i][4] : autocompletes[i][2], autocompletes[i][1], autocompletes[i][4]);
							SISWEB.pedidosCarga._addChangeVal($('#' + field), autocompletes[i][3], true);
						}
				}

				$('#waitenv').hide();
			}, suggData, { obj:suggObj, val:suggVal, lbl:suggLbl, cod:suggCod, nextField:nextField, nextLvl:lvl + 1 });
		}

/*********************************************************************************************************************/

		/*			NUEVA VERSIÓN PARA DEJAR EL FOCO EN EL CAMPO CORREPONDIENTE		*/

		if(SISWEB.pedidosCarga._seAutocompleta){
			if( $('#addCode').val() == '' ){
		        $('#addCode').attr({ 'disabled':null }).focus();
		        console.log("Code");
		    }
		    else if( $('#addType').val() == '' ){
		        SISWEB.pedidosCarga._pasoTipo = true; 
		        $('#addType').attr({ 'disabled':null }).focus();
		        console.log('Tipo');
		    }
		    else if( $('#addColor').val() == '' ){
		        SISWEB.pedidosCarga._pasoColor = true;
		        $('#addColor').attr({ 'disabled':null }).focus();
		        console.log('Color');
		    }
		    else if( $('#addTall').val() == '' ){
		        SISWEB.pedidosCarga._pasoTalle = true;
		        $('#addTall').attr({ 'disabled':null }).focus();
		        console.log('Talle');
		    }
		    else if( $('#addQ').val() == ''){
		        $('#addQ').attr({ 'disabled':null }).focus();
		        console.log('Cantidad');      
		    }
		    else{
		        $('#addBtn').attr({ 'disabled':null }).focus();
		    }
		}		
		else{
			$('#' + nextField).attr({ 'disabled':null }).focus();
		}	


/*********************************************************************************************************************/

//		ORIGINAL
//		$('#' + nextField).attr({ 'disabled':null }).focus();
	}

	SISWEB.pedidosCarga._enableAddBtn();

	return true;
},

_enableAddBtn: function()
{
	if (
	$('#addQ').val() > 0
	&& $('#addQ').attr('disabled') == null
	&& (SISWEB.pedidosCarga._editingOrder || SISWEB._userType != SISWEB.USRTYPE_EMPRE || (SISWEB._userType == SISWEB.USRTYPE_EMPRE && typeof SISWEB._suggests['nombrevendedora'] != 'undefined' && SISWEB._suggests['nombrevendedora'].value != null))
	)
		$('#addBtn').attr({ 'disabled':null });
	else
		$('#addBtn').attr({ 'disabled':'disabled' });
},

_thanksAlert: function()
{
	if (SISWEB._userType == SISWEB.USRTYPE_CONSU)
		SISWEB.alert('Contáctate con <strong>' + SISWEB._userRefererName + '</strong> para coordinar la entrega y pago de tu pedido.', 'Su pedido ha sido guardado');
	else
		SISWEB.alert('Su pedido ha sido guardado.');
}


/***********************************************	NUEVO	********************************************/

,_buscarPromocion: function(){
  $('#addBtn').attr({ 'disabled':'disabled' });
  var data = { campania:$('#campania').val(), code:$('#addCode').val(), tipo:SISWEB._suggests['addType'].value, color:SISWEB._suggests['addColor'].value, talle:SISWEB._suggests['addTall'].value, q:$('#addQ').val() };

  SISWEB._api('get-articulo', function(data, params)
  {
    $('#waitenv').hide();

 	if (typeof data.msg != 'undefined')	{
		SISWEB.alert(data.msg);
		return;
	}

    if (typeof data != 'undefined' && data != null && typeof data.idArticulo != 'undefined' && data.idArticulo != null){
      	//if(data.tipoVenta == 50){
      	if(data.tiene_promo == 1){
      			SISWEB._api('get-promociones', function(data) {
		  			SISWEB.pedidosCarga._flagCargaPromocion = false;
					SISWEB.pedidosCarga._flagBuscoPromocion = false;

					if(typeof data != 'undefined' && data != null && data.promociones.length ){
		  				$("#promocion").html("");
		  				var promos = '<option value="-1" data-cant_articulos="-1"><strong>Seleccione una promoción</strong></option>'; 
		  				for(var i = 0; i < data.promociones.length; i++){
		  					//promos += '<option value="'+ data.promociones[i].id_web_cache_promocion +'" data-cant_articulos="' + data.promociones[i].cantidad_articulos  +'" , data-codigo_promocion="'+ data.promociones[i].codigo_promocion +'">' +  data.promociones[i].codigo_promocion  + ' - ' + data.promociones[i].descripcion + '</option>'
		  					promos += '<option value="'+ data.promociones[i].id_web_cache_promocion +'" data-cant_articulos="' + data.promociones[i].cantidad_articulos  +'" , data-codigo_promocion="'+ data.promociones[i].codigo_promocion +'" , data-id_prom_promocion="'+ data.promociones[i].id_prom_promocion +'">' +  data.promociones[i].codigo_promocion  + ' - ' + data.promociones[i].descripcion + '</option>'
		  				}
						$("#promocion").append(promos);
						SISWEB.pedidosCarga._flagBuscoPromocion = true;
						$("#modal").show();
					}
					else{
						if( SISWEB.pedidosCarga._flagCargaPromocion == false){ 
							SISWEB.pedidosCarga.addItem();
						}
					}

		  	 	}, {campania: $('#campania').val(), codigo11: data.cod11 } );
      	}
      	else{
  			if( SISWEB.pedidosCarga._flagCargaPromocion == false){ 
				SISWEB.pedidosCarga.addItem();
			}
      	}
    }
  }, data);

},

_cargarCamposPromocion: function(){
	if( $("#promocion").val() == -1 ){
		SISWEB.pedidosCarga._mostrarAlerta("<label><strong>Debe seleccionar una promoción para continuar</strong></label>");
		return false;
	}
	else{
		SISWEB.pedidosCarga._idPromocion = $('#promocion').val();
		SISWEB.pedidosCarga._cantidadArticulos = $('#promocion  option:selected').data("cant_articulos");	
		SISWEB.pedidosCarga._codigoPromocion = $('#promocion  option:selected').data("codigo_promocion");	
		SISWEB.pedidosCarga._idPromPromocion = $('#promocion  option:selected').data("id_prom_promocion");
		SISWEB.pedidosCarga._construirModalPromocion();
	}
},

_construirModalPromocion: function(){
		SISWEB._api('get-articulos-promocion', function(data)
		{
			$('#waitenv').hide();
			if (typeof data != 'undefined' && data != null) {
				var idArticulo;
				$("#frmPromocion").html("");
  				var promos = '<option value="-1"><strong>Seleccione un Artículo</strong></option>'; 
  				for(var k = 0; k < data.articulos.length; k++){
  					promos += '<option value="'+ data.articulos[k].id_web_cache_articulos +'"  data-codigo11="' + data.articulos[k].cod11  +'", data-descripcion="' + data.articulos[k].descripcion + '" ,data-tipo="'+ data.articulos[k].Tipo +'"  ,data-color="'+ data.articulos[k].Color +'"  ,data-talle="'+ data.articulos[k].Talle +'">'
  					 /*+  data.articulos[k].cod11  + ' - ' + data.articulos[k].descripcion  */ + data.articulos[k].Code + ' - ' + data.articulos[k].tipo_str + ' - ' + data.articulos[k].color_str + ' - ' + data.articulos[k].talle_str + '</option>'

  					var codeUpp = $('#addCode').val().toUpperCase(); 
  					if( /* $('#addCode').val() */ codeUpp == data.articulos[k].Code && $('#addType').val().substring(0,2) == data.articulos[k].codigo_tipo && $('#addColor').val().substring(0,2) == data.articulos[k].codigo_color && $('#addTall').val() == data.articulos[k].talle_str  ){
  						idArticulo = data.articulos[k].id_web_cache_articulos;
  					}
  				}

  				var combos = "";
				for(var i = 0; i < SISWEB.pedidosCarga._cantidadArticulos; i++){
					combos += 	'<div class="form-group">'+
			                        '<label>Artículo ' + (i + 1) + '</label>' +
			                        '<select id="articulo_' + i + '" class="form-control" style="width: 100%; display:block; height:30px; text-align: center;"> ' + promos + ' </select>' +
			                    '</div>';

				}
				$("#frmPromocion").append(combos);
				$("#articulo_0").val(idArticulo);
				$('#articulo_0').attr('disabled',true);
			}
		}, { campania:$('#campania').val(), idPromPromocion: SISWEB.pedidosCarga._idPromPromocion  } );
		$("#modalPromocion").show();
},

_mostrarAlerta: function(mensaje){
	$("#panelMensaje").html("");
	$("#panelMensaje").append(mensaje);
	$("#modalAlerta").show();
},

_addItemsPromocion: function(){

	var articulos = new Array();
	for(var i = 0; i < SISWEB.pedidosCarga._cantidadArticulos; i++){	
		articulos.push($("#articulo_"+i).val() );
	}

	console.log(articulos);
	var articulos1 = articulos.toString();

	if( /*SISWEB.pedidosCarga._editingOrderData == null */ SISWEB.pedidosCarga._editingOrder == false ){
		if (SISWEB._userType == SISWEB.USRTYPE_REVEN)
			var cliente = SISWEB._userId;
		else if (typeof SISWEB._suggests['nombrevendedora'] != 'undefined')
		{
			$('#nombrevendedora').val(SISWEB._suggests['nombrevendedora'].label);
			if (SISWEB._suggests['nombrevendedora'].value != null)
				var cliente = SISWEB._suggests['nombrevendedora'].value.trim();
			else
				var cliente = null;
		}

		var campania = $('#campania').val();
		SISWEB.pedidosCarga._editingOrderData = { cliente:cliente, campania:campania };
	}	

	SISWEB._api('add-pedido-item-promocion', function(apiData)	{

	        if(undefined != typeof apiData.msgError && null != apiData.msgError){ 
	            console.log(apiData.msgError);
	            SISWEB.alert(apiData.msgError, "Mensaje");
	            SISWEB.pedidosCarga._flagStockInsuficiente = true;
		    }

		 	SISWEB.pedidosCarga._flagCargaPromocion = false;
			SISWEB.pedidosCarga._flagBuscoPromocion = false;
   		 	SISWEB.pedidosCarga._flagStockInsuficiente = false;
   		 	SISWEB.pedidosCarga._idPromocion = null;
			SISWEB.pedidosCarga._cantidadArticulos = null;
   		 	SISWEB.pedidosCarga._codigoPromocion = null;
   		 	SISWEB.pedidosCarga._idPromPromocion = null;
 			$('#modal').hide();
			$('#modalPromocion').hide();	
			SISWEB.pedidosCarga.clear();

			$('#waitenv').hide();
		 	SISWEB.pedidosCarga.loadOrder();

	        setTimeout( function(){ 
	           
				if (SISWEB.pedidosCarga._orderItems.length)
				{
					var i = SISWEB.pedidosCarga._orderItems.length - 1;
					SISWEB.pedidosCarga._orderItems[i].idItem = apiData.idItem;
				}
	
				if (SISWEB.pedidosCarga._conItems != null)
				{
					SISWEB.pedidosCarga._conItems.push(apiData.idItem);
					SISWEB.pedidosCarga._conItemsQ--;

					if (! SISWEB.pedidosCarga._conItemsQ)
						SISWEB._api('notify-pedido-feria', function(apiData) {}, { items:SISWEB.pedidosCarga._conItems, conMail:SISWEB.pedidosCarga._conMail, nombre:SISWEB.pedidosCarga._conNombre, campania:campania });
				}
	        }, 600);

	}, { idCliente:SISWEB.pedidosCarga._editingOrderData.cliente, campania:$('#campania').val(), articulos:articulos1, idPromocion: SISWEB.pedidosCarga._idPromocion, soutien:$('#soutien').val(), bombacha:$('#bombacha').val(), inferior:$('#inferior').val(), superior:$('#superior').val(), jean:$('#jean').val(), mail:$('#conMail').val(), nombre:$('#conNombre').val(), dni:$('#conDNI').val(), tel:$('#conTel').val() });


}

/***********************************************	NUEVO	********************************************/


};
