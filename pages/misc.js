SISWEB.misc =
{
		
conf: {
	validUserTypes: [ SISWEB.USRTYPE_ADMIN ]
},

fixEncoding: function(tbl)
{
	SISWEB._api('admin-control', function(data)
	{
		SISWEB.alert(data.msg);
	}, { action:20, data1:tbl });
},

ctrlCom: function(t)
{
	SISWEB._api('admin-control', function(data)
	{
		var val = (data.val == 0);
		if (t == 1)
			SISWEB._global.catalogo = val;
		else
			SISWEB._global.comunidad = val;
		SISWEB.misc.init();
	}, { action:t == 1 ? 21 : 22 });
},

init: function()
{
	if (SISWEB._global.catalogo)
	{
		$('#hidecatalog').show();
		$('#showcatalog').hide();
	}
	else
	{
		$('#hidecatalog').hide();
		$('#showcatalog').show();
	}
	
	if (SISWEB._global.comunidad)
	{
		$('#disacom').show();
		$('#enacom').hide();
	}
	else
	{
		$('#disacom').hide();
		$('#enacom').show();
	}
}

}