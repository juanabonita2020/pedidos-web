SISWEB.panelCoordinadora =
{

conf: {
	enabled: [ 'userHeader', 'showCampResume', 'showBanners' ],
	disabled: [ 'goBack' ],
	validUserTypes: [ SISWEB.USRTYPE_COORD ]
},

init: function()
{
	SISWEB.showHideComunidad();
	SISWEB._showOrdersFn = function() { SISWEB.showOrders(true, true); };
	SISWEB.showOrders(true, true);
	$('#loadOrder').click(function() { SISWEB.loadOrder(); });
}

};
