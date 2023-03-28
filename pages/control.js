SISWEB.control =
{

conf: {
	//enabled: [ 'userHeader' ],
	validUserTypes: [ SISWEB.USRTYPE_ADMIN ]
},

changeTab: function(tab)
{
	$('#tab0').removeClass('active');
	$('#tab1').removeClass('active');
	$('#tab2').removeClass('active');

	$('#tabflds0').hide();
	$('#tabflds1').hide();
	$('#tabflds2').hide();
	
	$('#tabflds' + tab).show();
	$('#tab' + tab).addClass('active');
	
	switch(tab)
	{
	case 0:
		SISWEB.control.showTableData(1);
		break;
	case 1:
	{
		SISWEB.control.showTableData(2);
		SISWEB.control.showTableData(3);
		SISWEB.control.showTableData(4);
		break;
	}
	case 2:
	{
		SISWEB.control.showTableData(5);
		SISWEB.control.showTableData(6);
		SISWEB.control.showTableData(7);
		SISWEB.control.showTableData(8);
		break;
	}
	}
},

getClientData: function(type)
{
	var data = { action:(type == 1 ? 12 : 13), data1:$("#zona2").val(), data2:$("#cliente2").val() };
	
	if (type == 1)
	{
		data.data1 = $("#zona2").val();
		data.data2 = $("#cliente2").val();
	}
	else
	{
		data.data1 = $("#zona4").val();
		data.data2 = $("#cliente4").val();
	}
	
	if (! data.data1 || ! data.data2) return;

	SISWEB._api('admin-control', function(pdata)
	{
		if (typeof pdata.msg == 'undefined')
		{
			if (type == 1)
			{
				$("#dniactual").val(pdata[0].dni);
				$("#emailactual").val(pdata[0].mail);
				$("#bajaactual").val(pdata[0].baja);
				$("#dninuevo").val(pdata[0].dni);
				$("#emailnuevo").val(pdata[0].mail);
				$("#bajanuevo").val(pdata[0].baja);
			}
			else
			{
				$("#emailactual2").val(pdata[0].mail);
				$("#emailnuevo2").val(pdata[0].mail);
			}
			
			return;
		}
		
		if (type == 1)
		{
			$("#dniactual").val('');
			$("#emailactual").val('');
			$("#bajaactual").val('');
			$("#dninuevo").val('');
			$("#emailnuevo").val('');
			$("#bajanuevo").val('');
		}
		else
		{
			$("#emailactual2").val('');
			$("#emailnuevo2").val('');
		}
		
		SISWEB.alert(pdata.msg);
	}, data);
},

showTableData: function(tbl)
{
	var data = { action:tbl };
	var url = 'admin-control';

	switch(tbl)
	{
	case 2: case 3: case 4:
		data.data1 = $('#email').val();
		break;
	case 5: case 6: case 7: case 8:
	{
		data.data1 = $('#zona3').val();
		data.data2 = $('#cliente3').val();
		
		if (tbl == 8)
		{
			if (! data.data1 || ! data.data2) return;
			url = 'http://apis-pedidosweb.juanabonita.com/get-debug-zonacliente.php';
			data = { zona:data.data1, clienta:data.data2 * 1 };
		}
		
		break;
	}
	}
	
	SISWEB._dataTable('tbl' + tbl, url, data, '');
},

clientAction: function(action)
{
	var data = { action:action };
	
	if (action == 10)
	{
		if ($('input[name=action10o]')[0].checked)
		{
			data.data1 = $('#zona1').val();
			data.data2 = $('#cliente1').val();
		}
		else
		{
			data.data1 = $('#email2').val();
			data.data2 = '';
		}
		data.data3 = $('#pass1').val();
		data.data4 = $('#pass2').val();
	}
	else if (action == 14)
	{
		data.data1 = $('#zona4').val();
		data.data2 = $('#cliente4').val();
		data.data3 = $('#emailnuevo2').val();
	}
	else
	{
		data.data1 = $('#zona2').val();
		data.data2 = $('#cliente2').val();
		data.data3 = $('#dninuevo').val();
		data.data4 = $('#emailnuevo').val();
		data.data5 = $('#bajanuevo').val();		
	}
	
	SISWEB._api('admin-control', function(pdata)
	{
		SISWEB.alert(pdata.msg);
	}, data);
},

init: function()
{
	SISWEB.control.changeTab(0);
	
	$('#zona2').change(function() { SISWEB.control.getClientData(1); });
	$('#cliente2').change(function() { SISWEB.control.getClientData(1); });
	
	$('#zona4').change(function() { SISWEB.control.getClientData(2); });
	$('#cliente4').change(function() { SISWEB.control.getClientData(2); });
	
	$('#email').change(function() { SISWEB.control.changeTab(1); });
	$('#zona3').change(function() { SISWEB.control.changeTab(2); });
	$('#cliente3').change(function() { SISWEB.control.changeTab(2); });
	
	$('#action10').click(function() { SISWEB.control.clientAction(10); });
	$('#action11').click(function() { SISWEB.control.clientAction(11); });
	$('#action12').click(function() { SISWEB.control.clientAction(14); });
}

};