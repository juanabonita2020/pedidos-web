SISWEB.newPass =
{

init: function(code)
{
	//SISWEB.alert('Se le ha enviado su nueva contraseña al email.');

	SISWEB._api('recover-user-account', function(data)
	{
		//window.location.hash = '#home';
	}, { code:code });
}

}