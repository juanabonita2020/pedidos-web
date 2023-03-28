SISWEB.panelRevendedora =
{

conf: {
	enabled: [ 'userHeader', 'showCampResume', 'showBanners' ],
	disabled: [ 'goBack' ],
	validUserTypes: [ SISWEB.USRTYPE_REVEN ]
},

init: function()
{
	SISWEB.showHideComunidad();
	SISWEB._showOrdersFn = SISWEB.showOrders;
	SISWEB.showOrders(false, true);
	$('#loadOrder').click(function() { SISWEB.loadOrder(); });
}

};
