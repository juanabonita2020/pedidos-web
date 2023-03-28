SISWEB.signup =
{

_dataEmail: null,
_dataZone: null,
_dataClient: null,
_dataDNI: null,

conf: {
	disabled: [ 'goBack' ]
},

init: function()
{
	$('#signupBtn').click(function() { SISWEB.signup.signup() });
},

signup: function()
{
	var data =
	{
	zona: $('#inputZona').val(),
	clienta: $('#inputCliente').val(),
	dni: $('#inputDNI').val(),
	email: $('#inputEmail').val(),
	password: $('#inputPassword').val()
	};

	var password2 = $('#inputConfirmPassword').val();

	if (! data.zona || ! data.clienta || ! data.dni || ! data.email || ! data.password || ! password2)
	{
		SISWEB.alert('Debe ingresar todos los datos.');
		return;
	}

	if (! SISWEB.checkValidEmail(data.email))
	{
		SISWEB.alert('Debe ingresar un email válido.');
		return;
	}

	if (data.password != password2)
	{
		SISWEB.alert('Las contraseñas deben coincidir.');
		return;
	}

	if (data.password.length < 6)
	{
		SISWEB.alert('La contraseña debe tener al menos 6 caracteres.');
		return;
	}

	if (data.dni.length < 6)
	{
		SISWEB.alert('El DNI debe tener entre 6 y 8 cifras.');
		return;
	}

	SISWEB.signup._dataEmail = data.email;
	SISWEB.signup._dataZone = data.zona;
	SISWEB.signup._dataClient = data.clienta;
	SISWEB.signup._dataDNI = data.dni;

	SISWEB._api('register-user-account', function(data)
	{
		switch(data.error)
		{
		case 1: case 2: case 3: case 4: case 8:
			window.location.hash = '#signupManual/' + data.error;
			break;
		case 5:
			SISWEB.alert('Ya se encuentra registrado.');
			break;
		case 6:
			SISWEB.alert('E-mail se encuentra en uso.');
			break;
		case 7:
			SISWEB.alert('En este momento no se puede completar su registracion. Contacte a su ' + data.msg + ' antes de registrarse.');
			break;
		default:
			SISWEB._api('login-user-account', function(data2)
			{
				/*if (data2.ok)
				{*/
					SISWEB.personalInfo.showGoBack = false;
					SISWEB.loginUser('personalInfo');
				/*}
				else
				{
					SISWEB.alert(typeof data.msg == 'undefined' ? 'Gracias por registrarse en nuestro sitio. Ahora puede iniciar sesión.' : data.msg);
					window.location.hash = '#login';
				}*/
			}, { email:$('#inputEmail').val(), password:$('#inputPassword').val() });
		}
	}, data);
}

}