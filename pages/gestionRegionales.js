SISWEB.gestionRegionales =
{

conf: {
	validUserTypes: [ SISWEB.USRTYPE_ADMIN ]
},

_type: 0,
_label: '',
_id: 'region',

init: function(t)
{
	if (typeof t == 'undefined') 
	{
		SISWEB.gestionRegionales._type = 1;
		SISWEB.gestionRegionales._label = 'regional';
		SISWEB.gestionRegionales._id = 'region';
		$('#ListaRegionales_l').html('regionales');
		$('#ListaRegionales_l2').html('Región');
		$('#ListaRegionales_n1').show();
		$('#ListaRegionales_n2').show();
	}
	else
	{
		SISWEB.gestionRegionales._type = t;
		SISWEB.gestionRegionales._label = 'divisional';
		SISWEB.gestionRegionales._id = 'division';
		$('#ListaRegionales_l').html('divisionales');
		$('#ListaRegionales_l2').html('División');
		$('#ListaRegionales_n1').hide();
		$('#ListaRegionales_n2').hide();
	}
	
	SISWEB.gestionRegionales._listRegionales();

	$('#addBtn').click(function()
	{
		var data = { email:$('#email').val(), pass:$('#pass').val(), passm:$('#passm').val(), nombre:$('#nombre').val(), apellido:$('#apellido').val(), negocio:$('#negocio').val() };

		if (SISWEB.gestionRegionales._type == 1)
		{
			data.region = $('#region').val();
			data.division = '';
			var c = (! data.region);
		}
		else
		{
			data.region = '';
			data.division = $('#region').val();
			var c = (! data.division);
		}
		
		if (! data.email || ! data.pass || ! data.nombre || ! data.apellido || c)
		{
			SISWEB.alert('Debe ingresar todos los datos.');
			return;
		}

		$('#email').val('');
		$('#pass').val('');
		$('#passm').val('');
		$('#nombre').val('');
		$('#apellido').val('');
		$('#negocio').val('');
		
		if (SISWEB.gestionRegionales._type == 1)
			$('#region').val('');
		else
			$('#division').val('');

		SISWEB._api('add-regional', function(data)
		{
			if (typeof data.msg == 'undefined')
				SISWEB.gestionRegionales._listRegionales();
			else
				SISWEB.alert(data.msg);
		}, data);
	});
},

remove: function(id)
{
	SISWEB.confirm('¿Está seguro de eliminar el usuario ' + SISWEB.gestionRegionales._label + '?', function()
	{
		SISWEB._api('delete-regional', function(data)
		{
			SISWEB.gestionRegionales._listRegionales();
		}, { id:id });
	}, function() { });
},

update: function(id, data, value)
{
	SISWEB._api('update-regional', function(data)
	{
	}, { id:id, data:data, value:value });
},

_listRegionales: function()
{
	$('#waitenv').show();

	SISWEB._api('get-regionales', function(data)
	{
		var lista = document.getElementById('ListaRegionales');
		lista.innerHTML = '';

		for (var i = 0; i < data.regionales.length; i++)
		{
			var o = { id:data.regionales[i].id };

			var tr = document.createElement('tr');
			
			var html = '<td><input id="email' + i + '" value="' + data.regionales[i].mail + '" onchange="SISWEB.gestionRegionales.update(\'' + data.regionales[i].id + '\', \'email\', this.value)" size="15" /></td><td><input id="pass' + i + '" onchange="SISWEB.gestionRegionales.update(\'' + data.regionales[i].id + '\', \'pass\', this.value)" size="15" /></td><td><input id="passm' + i + '" onchange="SISWEB.gestionRegionales.update(\'' + data.regionales[i].id + '\', \'passm\', this.value)" size="15" /></td><td><input id="' + SISWEB.gestionRegionales._id + i + '" value="' + (SISWEB.gestionRegionales._type == 1 ? data.regionales[i].region : data.regionales[i].division) + '" onchange="SISWEB.gestionRegionales.update(\'' + data.regionales[i].id + '\', \'' + SISWEB.gestionRegionales._id + '\', this.value)" size="2" /></td><td><input id="nombre' + i + '" value="' + data.regionales[i].nombre + '" onchange="SISWEB.gestionRegionales.update(\'' + data.regionales[i].id + '\', \'nombre\', this.value)" size="15" /></td><td><input id="apellido' + i + '" value="' + data.regionales[i].apellido + '" onchange="SISWEB.gestionRegionales.update(\'' + data.regionales[i].id + '\', \'apellido\', this.value)" size="15" /></td>';
		
			if (SISWEB.gestionRegionales._type == 1)
				html += '<td><select id="negocio' + i + '" onchange="SISWEB.gestionRegionales.update(\'' + data.regionales[i].id + '\', \'negocio\', this.value)"><option value=""></option><option value="D"' + (data.regionales[i].negocio == 'D' ? ' selected="selected"' : '') + '>Deli</option><option value="E"' + (data.regionales[i].negocio == 'E' ? ' selected="selected"' : '') + '>Empresaria</option></select></td>';
			
			html += '<td><button id="actbtn_' + i + '" type="button" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="left"  title="Eliminar"><span class="glyphicon glyphicon-remove"></span></button></td>';
			
			tr.innerHTML = html;
			
			lista.appendChild(tr);

			$('#actbtn_' + i).click(o, function(e) { SISWEB.gestionRegionales.remove(e.data.id); });
		}

		$('#waitenv').hide();
	}, { type:SISWEB.gestionRegionales._type });
}

}
