SISWEB.userRecover =
{

_stage: 1,
_askTel: false,
_data: {},

conf: {
	disabled: [ 'goBack' ]
},

process: function()
{
	switch(SISWEB.userRecover._stage)
	{
	case 1:
		if ($('#zona').val())
			SISWEB.userRecover._data.zona = $('#zona').val();
		else
		{
			SISWEB.alert('Debe ingresar su zona.');
			return;
		}
		break;
	case 2:
		if ($('#cliente').val())
		{
			SISWEB.userRecover._data.cliente = $('#cliente').val();
			SISWEB.userRecover._data.stage = 1;
			
			SISWEB._api('recover-user', function(data)
			{
				for(var i = 0; i < data.dni.length; i++)
					$('#q1' + (i + 1)).html(data.dni[i]);

				if (typeof data.tel == 'undefined')
					SISWEB.userRecover._askTel = false;
				else
				{
					SISWEB.userRecover._askTel = true;
					for(var i = 0; i < data.tel.length; i++)
						$('#q2' + (i + 1)).html(data.tel[i]);
				}
				
				for(var i = 0; i < data.loc.length; i++)
					$('#q3' + (i + 1)).html(data.loc[i]);
					
				for(var i = 0; i < data.nac.length; i++)
					$('#q4' + (i + 1)).html(data.nac[i]);
				
				$('#s2').hide();
				SISWEB.userRecover._stage = 3;
				$('#s3').show();
			}, SISWEB.userRecover._data);
		}
		else
			SISWEB.alert('Debe ingresar su cliente.');
		return;
	default:
	{
		var i = SISWEB.userRecover._stage - 2;
		var val = '';
		for (var j = 1; j <= 3; j++)
		{
			var k = 'q' + i + j;
			if (document.getElementById(k + 'r').checked)
			{
				var val = $('#' + k).html();
				break;
			}
		}
		
		switch(SISWEB.userRecover._stage)
		{
		case 3:
		{
			var lbl = 'DNI';
			var name = 'dni';
			break;
		}
		case 4:
		{
			var lbl = 'teléfono';
			var name = 'telefono';
			break;
		}
		case 5:
		{
			var lbl = 'localidad';
			var name = 'localidad';
			break;
		}
		default:
		{
			var lbl = 'fecha de nacimiento';
			var name = 'nacimiento';
		}
		}
		
		if (val)
			SISWEB.userRecover._data[name] = val;
		else
		{
			SISWEB.alert('Debe ingresar su ' + lbl + '.');
			return;
		}
		
		if (SISWEB.userRecover._stage == 4 || SISWEB.userRecover._stage == 6)
		{
			SISWEB.userRecover._data.stage = 2;
			
			SISWEB._api('recover-user', function(data)
			{
				SISWEB.alert(data.username == null ? 'No pudimos determinar su usuario, inténtelo de nuevo.' : 'Su nombre de usuario es: <b>' + data.username + '</b>');
			}, SISWEB.userRecover._data);
			
			SISWEB.userRecover.reset();
			return;
		}
	}
	}
	
	$('#s' + SISWEB.userRecover._stage).hide();
	
	SISWEB.userRecover._stage++;
	
	if (SISWEB.userRecover._stage == 4 && ! SISWEB.userRecover._askTel)
		SISWEB.userRecover._stage = 5;
	
	$('#s' + SISWEB.userRecover._stage).show();
},

reset: function()
{
	SISWEB.userRecover._stage = 1;
	SISWEB.userRecover._askTel = false;
	SISWEB.userRecover._data = {};
	$('#s1').show();
	$('#s2').hide();
	$('#s3').hide();
	$('#s4').hide();
	$('#s5').hide();
	$('#s6').hide();
	$('#zona').val('');
	$('#cliente').val('');
	for(var i = 1; i <= 3; i++)
		for(var j = 1; j <= 3; j++)
			document.getElementById('q' + i + j + 'r').checked = '';
},

init: function()
{
	SISWEB.userRecover.reset();
	$('#btn').click(function(){ SISWEB.userRecover.process(); })
}

}