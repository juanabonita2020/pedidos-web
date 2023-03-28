SISWEB.catalogoItem =
{

_code: null,
_campania: null,
_color: null,
_talle: null,
_tipo: null,
_tipoString: null,
_colorString: null,
_talleString: null,
_talles: new Array(),

init: function(parts)
{
	var p = parts.split(',');
	var code = p[0];
	var color = p[1];
	var colorid = p[2];
	SISWEB.catalogoItem._talle = null;

	SISWEB.catalogoItem._code = code;

	$('#title').html(code + ' ' + color);
	$('#det').html('Código: ' + code + ', color:' + color);
	$('#talls').html('');

	$('#waitenv').show();

	SISWEB._api('get-articulos-feria-con-stock', function(data)
	{
		$('#photo').attr('src', data.productos[0].img);
		$('#desc').html('<p>' + data.productos[0].descripcion + '</p><p>' + data.productos[0].descripcion_rango_talle + '</p>');

		return true;
	}, { code:code, color:colorid });

	SISWEB._api('get-campanias', function(data, params)
	{
		var campania = 0;

		for (var i = 0; i < data.campanias.length; i++)
			if (data.campanias[i].activa)
			{
				campania = data.campanias[i].campania;
				break;
			}

		if (! campania) return false;

		SISWEB.catalogoItem._campania = campania;

		SISWEB._api('get-articulo-feria-detalle', function(data)
		{
			var html = '';
			var values = new Array();
			SISWEB.catalogoItem._talles = new Array();

			for(var i = 0; i < data.talles.length; i++) if (data.talles[i].Color * 1 == colorid * 1)
			{
				values.push({ value:data.talles[i].Color + ',' + data.talles[i].Talle + ',' + data.talles[i].Tipo + ',' + data.talles[i].colorString + ',' + data.talles[i].talleString + ',' + data.talles[i].tipoString, label:data.talles[i].talleString });
				SISWEB.catalogoItem._talles.push(data.talles[i].stock);
			}
			
			SISWEB.loadDroplist('tall', values);
			
			$('#tall').change(function() { SISWEB.catalogoItem.setArt($(this).val()); });
			
			SISWEB.catalogoItem.setArt(values[0].value);

			//$('#talls').html(html);

			$('#waitenv').hide();

			return true;
		}, { code:code, campania:campania });
	}, null);

	$('#q').change(function()
	{
		if ($('#q').val() < 1) $('#q').val('1');
	});

	$('#add').click(function() { SISWEB.catalogoItem.addToCart(); });
},

setArt: function(p)
{
	var parts = p.split(',');
	SISWEB.catalogoItem._color = parts[0];
	SISWEB.catalogoItem._talle = parts[1];
	SISWEB.catalogoItem._tipo = parts[2];
	SISWEB.catalogoItem._colorString = parts[3];
	SISWEB.catalogoItem._talleString = parts[4];
	SISWEB.catalogoItem._tipoString = parts[5];
},

addToCart: function()
{
	//console.log(SISWEB.catalogoItem._talle);
	if (SISWEB.catalogoItem._talle == null)
	{
		SISWEB.alert('Debe seleccionar un talle.');
		return;
	}
	
	var i = $('#tall option:selected').index();	
	var max = SISWEB.catalogoItem._talles[i];
	
	if (max > 5)
	{
		max = 5;
		var msg = 'Sólo puede comprar hasta 5 unidades de un mismo producto y talle.';
	}
	else
		var msg = 'Stock insuficiente, Ud. puede elegir hasta ' + max + ' unidades.';
	//console.log(max);return;
	
	var q = $('#q').val() * 1;
	if (q < 1 || q > max)
	{
		SISWEB.alert(msg);
		return;
	}
	window.location.hash = '#pedidosCarga/cartFeria,' + SISWEB.catalogoItem._code + ',' + SISWEB.catalogoItem._tipo + ',' + SISWEB.catalogoItem._color + ',' + SISWEB.catalogoItem._talle + ',' + q + ',' + SISWEB.catalogoItem._campania + ',' + SISWEB.catalogoItem._tipoString + ',' + SISWEB.catalogoItem._colorString + ',' + SISWEB.catalogoItem._talleString;
}

};