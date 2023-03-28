SISWEB.restorePedido =
{

conf: {
	validUserTypes: [ SISWEB.USRTYPE_ADMIN ]
},

init: function()
{
	$('#restoreBtn').click(function()
	{
		var data = { envio:$('#id').val(), stage:1 };

		if (! data.envio)
			return SISWEB.alert('Debe ingresar el ID del envío.');

		SISWEB._api('restore-pedido', function(res)
		{
			if (! res.zona)
				SISWEB.alert('No existe envío con el ID ingresado.');
			else if (res.q == 0)
				SISWEB.alert('El envío ingresado no contiene pedidos recuperables.');
			else
				SISWEB.confirm('Se encontraron <b>' + res.q + '</b> pedidos para recuperar de la zona <b>' + res.zona + '</b>. ¿Desea recuperarlos?', function()
				{
					data.stage = 2;
					SISWEB._api('restore-pedido', function(res)
					{
						SISWEB.alert('Los pedidos no enviados del envío <b>' + data.envio + '</b> fueron recuperados.');
					}, data);
				}, function() {});
		}, data);
	});
}

}