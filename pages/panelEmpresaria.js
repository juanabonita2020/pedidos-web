SISWEB.panelEmpresaria =
{

conf: {
	enabled: [ 'userHeader', 'showCampResume', 'showBanners' ],
	disabled: [ 'goBack' ],
	validUserTypes: [ SISWEB.USRTYPE_EMPRE ]
},

init: function()
{
	SISWEB.showHideComunidad();

/*	if (SISWEB._userNegocio == 'D')
	{
*/		if ($('#loginLideresU').val())
			$('#opciones_empresaria_lider').show();
		else
			SISWEB._api('login-lideres', function(data)
			{
				$('#loginLideresFrm').attr('action', data.url);
				$('#loginLideresU').val(data.username);
				$('#loginLideresP').val(data.password);
				$('#opciones_empresaria_lider').show();
			});
/*	}
	else
		$('#opciones_empresaria_lider').hide();
*/
	SISWEB.showOrders(true, true);

	SISWEB._showOrdersFn = function() { SISWEB.showOrders(true, true); };
},

loginLideres: function()
{
/*	if (SISWEB._userNegocio == 'D') */ $('#loginLideresFrm').submit();
}

};
