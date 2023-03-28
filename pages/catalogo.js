SISWEB.catalogo =
{

init: function(type)
{
	if (type == 'liquidaciononline')
	{
		var url = 'get-articulos-feria-con-stock';
		var title = 'Liquidación Online';
	}
	else
		return;

	$('#title').html(title);

	if (SISWEB._userType == SISWEB.USRTYPE_CONSU)
	{
		$('#reference2').html('<strong>' + SISWEB._userRefererName + '</strong> te envía las siguientes ofertas.');
		$('#reference').hide();
		$('#reference2').show();
	}
	else
	{
		$('#reference').show();
		$('#reference2').hide();
		$('#refurl').val(SISWEB.siteURL + '#referer/' + SISWEB._userRefId).click(function () { $(this).select(); });
		$('#refurl').keypress(function() { return false; });
	}

	if (SISWEB.pedidosCarga._cartItems.length)
	{
		//$('#carritoInfo').show();

		var tot = 0;
		var totQ = 0;
		for (var i = 0; i < SISWEB.pedidosCarga._cartItems.length; i++)
		{
			totQ += SISWEB.pedidosCarga._cartItems[i].q * 1;
			tot += SISWEB.pedidosCarga._cartItems[i].q * SISWEB.pedidosCarga._cartItems[i].precio;
		}

		//$('#carritoTots').html(totQ + ' items, $ ' + tot);

		$('#showCart').click(function() { window.location.hash = '#pedidosCarga/cartFeria'; });
		var showCartLbl = totQ + ' items';
	}
	else
	{
		$('#showCart').attr('disabled', 'disabled');
		var showCartLbl = 'Vacío';
	}
	
	$('#showCart').html('<span class="glyphicon glyphicon-shopping-cart"></span> ' + showCartLbl).show();

	$('#waitenv').show();

	SISWEB._api('get-campanias', function(data)
	{
		var campania = 0;

		for (var i = 0; i < data.campanias.length; i++)
			if (data.campanias[i].activa)
			{
				campania = data.campanias[i].campania;
				break;
			}

		if (! campania) return false;

		SISWEB._api(url, function(data)
		{
			var body = document.getElementById('cataloglst');
			body.innerHTML = '';

			for (var i = 0; i < data.productos.length; i++)
			{
				data.productos[i].precioOriginal = Math.round(data.productos[i].precioOriginal);
				data.productos[i].precio = Math.round(data.productos[i].precio);
				
				var div1 = document.createElement('div');
				div1.className = 'col-md-3';
				var a = document.createElement('a');
				a.href = '#catalogoItem/' + data.productos[i].code + ',' + data.productos[i].color + ',' + data.productos[i].idcolor;
				var div2 = document.createElement('div');
				div2.style.backgroundImage = 'url("' + data.productos[i].thumb + '")';
				var html = '<p>';
				html += data.productos[i].code + '<br />' + data.productos[i].color + '<br />' + data.productos[i].descripcion_rango_talle + '<span class="price"><i>$ ' + data.productos[i].precioOriginal + '</i><br />$ ' + data.productos[i].precio + '</span>';
				
				if (data.productos[i].ultimas) html += '<b>Ultimas unidades</b>';
				html += '</p>';
				if (data.productos[i].nuevo) html += '<span>Nuevo !</span>';
				
				div2.innerHTML = html;
				a.appendChild(div2);
				div1.appendChild(a);
				body.appendChild(div1);
			}

			// $(function () { $("[data-toggle='tooltip']").tooltip(); });

			$('#waitenv').hide();

			return true;
		}, { });

		return true;
	});
}

}