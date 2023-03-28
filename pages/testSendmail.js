SISWEB.testSendmail =
{

conf: {
	validUserTypes: [ SISWEB.USRTYPE_ADMIN ]
},

test: function()
{
	var dest = $('#dest').val();
	if (! dest) return SISWEB.alert('Debe ingresar una dirección de e-mail.');
	
	$('#waitenv').show();
	
	SISWEB._api('test-sendmail', function(data)
	{
		$('#waitenv').hide();
		SISWEB.alert('<p>Resultado: <b>' + data.result + '</b></p><p>Cuenta de envío:<ul><li>Servidor: ' + data.host + '</li><li>Puerto: ' + data.port + '</li><li>Usuario: ' + data.username + '</li><li>Contraseña: ' + data.password + '</li></ul></p>', 'Resultado de la prueba');
	}, { dest:dest, account:$('#account').val() });
},

init: function()
{
	$('#testBtn').click(function(){ SISWEB.testSendmail.test(); })
}

}