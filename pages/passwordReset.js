SISWEB.passwordReset =
{

conf: {
	validUserTypes: [ SISWEB.USRTYPE_ADMIN ]
},

send: function()
{
	var email = $('#mail1').val();

	if (! email)
	{
		SISWEB.alert('Debe ingresar un email.');
		return;
	}

	$('#waitenv').show();

	SISWEB._api('recover-user-account', function(data)
	{
		window.location.hash = '#home';
	}, { email:email });

	SISWEB.alert('Se le ha enviado intrucciones para recuperar la contraseña al email. Si no recibe el correo por favor chequee su casilla de spam.');
},

init: function()
{
	$('#sendBtn').click(function(){ SISWEB.passwordReset.send(); })
}

}