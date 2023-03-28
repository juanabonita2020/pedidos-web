SISWEB.signupManual =
{

conf: {
	disabled: [ 'goBack' ]
},

_error: 0,

init: function(error)
{
	switch(error)
	{
	case '3': case '4': case '8':
		var msg = 'No pudimos validar los datos que está intentando utilizar con los que tenemos registrados en nuestro sistema. Por su seguridad, por favor complete sus datos, y la ayudaremos a validar esta información. Le enviaremos por correo electrónico los pasos a seguir para que pueda registrarse en nuestro sistema.'; break;
	default:
		var msg = 'El ' + (error == 1 ? 'DNI' : 'Correo Electrónico') + ' que está intentando utilizar no coincide con el que tenemos registrado en nuestro sistema. Por favor complete sus datos, y la ayudaremos a validar esta información. Le enviaremos por correo electrónico los pasos a seguir para que pueda registrarse en nuestro sistema.';
	}

	$('#errorMsg').html(msg);

	SISWEB.signupManual._error = error;

	if (SISWEB.signup._dataEmail != null) $('#inputEmail').val(SISWEB.signup._dataEmail);
	if (SISWEB.signup._dataZone != null) $('#inputZona').val(SISWEB.signup._dataZone);
	if (SISWEB.signup._dataClient != null) $('#inputCliente').val(SISWEB.signup._dataClient);
	if (SISWEB.signup._dataDNI != null) $('#inputDNI').val(SISWEB.signup._dataDNI);

	$('#signupBtn').click(function() { SISWEB.signupManual.signup() });
},

signup: function()
{
	var data =
	{
	zona: $('#inputZona').val(),
	clienta: $('#inputCliente').val(),
	dni: $('#inputDNI').val(),
	email: $('#inputEmail').val(),
	nombre: $('#inputName').val(),
	apellido: $('#inputLastName').val(),
	tel: $('#inputTel1').val(),
	error: SISWEB.signupManual._error
	};

	if (! data.zona || ! data.clienta || ! data.dni || ! data.email || ! data.nombre || ! data.apellido || ! data.tel)
	{
		SISWEB.alert('Debe ingresar todos los datos.');
		return;
	}

	$('#signupBtn').attr('disabled', 'disabled');

	SISWEB._api('register-contacto', function(data)
	{
		SISWEB.alert(data.ok ? 'Hemos recibido su reclamo. Recuerde, usted todavía no tiene un usuario del sistema ya que alguno de nuestros datos no coinciden con los que ud. nos ha suministrado. En breve nos comunicaremos con Ud. para solucionar su problema.' : 'Su reclamo ya ha sido tomado con anterioridad, debe esperar 24 horas hábiles y un operador se contactara con ud para solucionar su problema.');
		window.location.hash = '#login';
	}, data);
}

}