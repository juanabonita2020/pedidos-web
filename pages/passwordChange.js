SISWEB.passwordChange =
{

conf: {
	validUserTypes: [ SISWEB.USRTYPE_EMPRE, SISWEB.USRTYPE_REVEN, SISWEB.USRTYPE_ADMIN, SISWEB.USRTYPE_COORD, SISWEB.USRTYPE_REGIO ]
},

change: function()
{
	var data = { /*password:$('#pass0').val() , */newPassword:$('#pass1').val() };

	/*if (! data.password)
	{
		SISWEB.alert('Debe ingresar su contraseña actual.');
		return;
	}*/

	if (! data.newPassword)
	{
		SISWEB.alert('Debe ingresar su contraseña nueva.');
		return;
	}

	if (! $('#pass2').val())
	{
		SISWEB.alert('Debe repetir su contraseña nueva.');
		return;
	}

	if ($('#pass2').val() != data.newPassword)
	{
		SISWEB.alert('Sus nueva contraseña y la repetición deben coincidir.');
		return;
	}

	$('#waitenv').show();

	SISWEB._api('change-password', function(data)
	{
		$('#waitenv').hide();

		if (data.ok)
		{
			SISWEB.alert('Su contraseña ha sido actualizada.');
			window.location.hash = '#home';
		}
		else
			SISWEB.alert('Su contraseña actual es incorrecta.');
	}, data);
},

init: function()
{
	$('#btnCont').click(function() { SISWEB.passwordChange.change(); });
}

};