SISWEB.panelRegional =
{

conf: {
	enabled: [ 'userHeader' ],
	disabled: [ 'goBack' ],
	validUserTypes: [ SISWEB.USRTYPE_REGIO, SISWEB.USRTYPE_DIVIS ]
},

init: function()
{
	if (SISWEB._userType == SISWEB.USRTYPE_REGIO)
	{
		var lbl1 = 'Regional';
//		SISWEB.showHideComunidad();
	}
	else
	{
		var lbl1 = 'Gerente Divisional';
//		$('#opciones_empresaria13').hide();
	}

	SISWEB.showHideComunidad();
	$('#lbl1').html(lbl1);
}

};
