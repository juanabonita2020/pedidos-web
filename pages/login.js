SISWEB.login =
{

conf: {
	disabled: [ 'goBack' ]
},

login: function()
{
	var data = { email:$('#inputEmail').val() , password:$('#inputPassword').val() };

	if (! data.email)
	{
		//alert('Debe ingresar su email.');
		SISWEB.alert('Debe ingresar su email.');
		return;
	}

	if (! data.password)
	{
		//alert('Debe ingresar su contraseña.');
		SISWEB.alert('Debe ingresar su contraseña.');
		return;
	}

	$('#waitenv').show();

	SISWEB._api('login-user-account', function(data)
	{
		if (data.error)
		{
			$('#waitenv').hide();
			/*if (data.error == 2)
			{
				SISWEB.alert('Su contraseña es inválida.');
				window.location.hash = '#passwordRecover';
			}
			else*/
			$('#errorLogin').html(data.error == 1 ? 'Usuario no registrado' : (data.error == 2 ? 'Su contraseña es inválida.' : 'Su usuario se encuentra inhabilitado. Por favor contáctese a ayudapedidosweb@juanabonita.com indicando su número de Zona y Cliente, aclarando que su usuario esta inhabilitado.')).show();
		}
		else
		{
			if (data.global == null) SISWEB.alert('SISWEB.login.login - Datos globales inexistentes!!');
			SISWEB.siteURL = data.siteURL;
			SISWEB._global = data.global;
			SISWEB.loginUser(null, data);
		}
	}, data);
},

init: function(id)
{
	//console.log(id);
	
	SISWEB._api('get-status', function(data)
	{
		if (data.maintMode)
		{
			$('#maintmode').show();
			if (typeof id == 'undefined' || id != '1')
				$('#login_form').hide();
		}
	}, { });

	$('#login').click(function() { SISWEB.login.login(); });
}

};