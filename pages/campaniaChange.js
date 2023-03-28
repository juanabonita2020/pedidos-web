SISWEB.campaniaChange =
{

conf: {
	enabled: [ 'userHeader' ],
	validUserTypes: [ SISWEB.USRTYPE_EMPRE ]
},

change: function()
{
	$('#waitenv').show();

	SISWEB._api('change-campania', function(data)
	{
		$('#waitenv').hide();
		SISWEB._homePage();
		return true;
	}, { campania:$('#campania').val() });
},

init: function()
{
	$('#btnChange').click(function() { SISWEB.campaniaChange.change(); });

	$('#waitenv').show();

	SISWEB._api('get-campanias', function(data)
	{
		var activa = 0;
		
		var campanias = new Array();

		for (var i = 0; i < data.campanias.length; i++)
		if (data.campanias[i].cierresOk == 1)
		{
			campanias.push({ value: data.campanias[i].campania, label: data.campanias[i].campania });
			if (data.campanias[i].activa == "1") activa = data.campanias[i].campania;
		}
		
		if (campanias.length)
		{
			SISWEB.loadDroplist('campania', campanias);
			console.log(activa);
			if (activa) $('#campania').val(activa);
		}
		else
		{
			return SISWEB.noCampaigns();
			//$('#campania').attr({ 'disabled':'disabled' });
			//$('#btnChange').attr({ 'disabled':'disabled' });
		}

		$('#waitenv').hide();

		return true;
	});
}

};