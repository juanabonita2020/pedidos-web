SISWEB.capacitacion =
{

editId: null,

init: function(param)
{
	//
	SISWEB.capacitacion.editId = null;

	if (SISWEB._userType == SISWEB.USRTYPE_ADMIN)
	{
		$('#ayudaTipo').change(function() { SISWEB.capacitacion.showList() });
		$('#sistemaTipo').change(function() { SISWEB.capacitacion.showList() });
		$('#faqAddBtn').click(function() { SISWEB.capacitacion.showForm() });
		$('#addBtn2').click(function() { SISWEB.capacitacion.save() });
		$('#catsBtn').click(function() { window.location.hash = '#capacitacionCat' });
		$('#ayudaTipo').show();
		$('#sistemaTipo').show();
		$('#faqAddBtn').show();
		$('#catsBtn').show();
	}
	else
	{
		$('#faqAddBtn').hide();
		$('#ayudaTipo').hide();
		$('#sistemaTipo').hide();
		$('#catsBtn').hide();
	}

	var html1 = '';
	var html2 = '<option value="">Todos los sistemas</option>';
	for(var i = 0; i < SISWEB._sistemas.length; i++)
	{
		var l = SISWEB._sistemas[i].sistema + ' (' + SISWEB._sistemas[i].pais + ')';
		html1 += '<input type="checkbox" id="sistema' + SISWEB._sistemas[i].sistema + '" /> ' + l + '<br />';
		html2 += '<option value="' + SISWEB._sistemas[i].sistema + '">' + l + '</option>';
	}
	$('#sistemas').html(html1);
	$('#sistemaTipo').html(html2);

	if (typeof param != 'undefined')
	{
		var p = param.split(',');
		//console.log(p);
		SISWEB.capacitacion.showList(p[0], p[1]);
	}
	else
		SISWEB.capacitacion.showList();
},

save: function(submit)
{
	$('#waitenv').show();

	var data = { orden:$('#orden').val(), titulo:$('#titulo').val(), contenido:$('#contenido').val(), t_empresaria:document.getElementById('t_empresaria').checked ? 1 : 0, t_revendedora:document.getElementById('t_revendedora').checked ? 1 : 0, t_revdeli:document.getElementById('t_revdeli').checked ? 1 : 0, t_coordinadora:document.getElementById('t_coordinadora').checked ? 1 : 0, t_consumidora:document.getElementById('t_consumidora').checked ? 1 : 0, t_regional:document.getElementById('t_regional').checked ? 1 : 0, t_regional_e:document.getElementById('t_regional_e').checked ? 1 : 0, t_anonimo:document.getElementById('t_anonimo').checked ? 1 : 0, t_division:document.getElementById('t_division').checked ? 1 : 0, t_lider:document.getElementById('t_lider').checked ? 1 : 0, categoria:$('#cat').val(), sistemas:"" };

	var sistemas = $('#sistemas input');
	for(var i = 0; i < sistemas.length; i++)
		if (sistemas[i].checked)
			data.sistemas += (data.sistemas == '' ? '' : ',') + sistemas[i].id.substr(7)

	if (SISWEB.capacitacion.editId == null)
		var action = 'add';
	else
	{
		var action = 'modify';
		data.id = SISWEB.capacitacion.editId;
		SISWEB.capacitacion.editId = null;
	}

	SISWEB._api(action + '-capacitacion', function(data)
	{
		SISWEB.capacitacion._closeForm();
		SISWEB.capacitacion.showList();
		$('#waitenv').hide();
	}, data, { method:'POST' });
},

delete: function(id)
{
	SISWEB.confirm('¿Está seguro de eliminar la capacitación?', function()
	{
		$('#waitenv').show();
		SISWEB._api('delete-capacitacion', function(data)
		{
			SISWEB.capacitacion.showList();
		}, { id:id });
	}, function() { });
},

showForm: function(orden, titulo, contenido, t_empresaria, t_revendedora, t_coordinadora, t_consumidora, t_regional, t_regional_e, t_anonimo, t_division, t_lider, t_revdeli, categoria, sistemas)
{
	$('#addEnv').show();
	$('#faqAddBtn').hide();
	$('#orden').val(typeof orden == 'undefined' ? '' : orden);
	$('#titulo').html(typeof titulo == 'undefined' ? '' : titulo);
	$('#contenido').html(typeof contenido == 'undefined' ? '' : contenido);
	if (categoria) $('#cat').val(categoria);
	SISWEB._goBackFn = SISWEB.capacitacion._closeForm;

	document.getElementById('t_empresaria').checked = (typeof t_empresaria == 'undefined' || t_empresaria == 0 ? '' : 'checked');
	document.getElementById('t_revendedora').checked = (typeof t_revendedora == 'undefined' || t_revendedora == 0 ? '' : 'checked');
	document.getElementById('t_revdeli').checked = (typeof t_revdeli == 'undefined' || t_revdeli == 0 ? '' : 'checked');
	document.getElementById('t_coordinadora').checked = (typeof t_coordinadora == 'undefined' || t_coordinadora == 0 ? '' : 'checked');
	document.getElementById('t_consumidora').checked = (typeof t_consumidora == 'undefined' || t_consumidora == 0 ? '' : 'checked');
	document.getElementById('t_regional').checked = (typeof t_regional == 'undefined' || t_regional == 0 ? '' : 'checked');
	document.getElementById('t_regional_e').checked = (typeof t_regional_e == 'undefined' || t_regional_e == 0 ? '' : 'checked');
	document.getElementById('t_division').checked = (typeof t_division == 'undefined' || t_division == 0 ? '' : 'checked');
	document.getElementById('t_anonimo').checked = (typeof t_anonimo == 'undefined' || t_anonimo == 0 ? '' : 'checked');
	document.getElementById('t_lider').checked = (typeof t_lider == 'undefined' || t_lider == 0 ? '' : 'checked');

	var btnicon = document.getElementById('addBtn2Icon');

	if (typeof orden == 'undefined')
		btnicon.className = 'glyphicon glyphicon-plus';
	else
		btnicon.className = 'glyphicon glyphicon-edit';

	var sistemasc = $('#sistemas input');
	for(var i = 0; i < sistemasc.length; i++)
		$(sistemasc[i]).attr("checked", null);

	if (typeof sistemas != 'undefined')
		for(var i = 0; i < sistemas.length; i++)
			$('#sistema' + sistemas[i]).prop("checked", "checked");

	$(document).scrollTop(0);
},

edit: function(id)
{
	$('#waitenv').show();

	SISWEB._api('get-capacitacion', function(data)
	{
		SISWEB.capacitacion.editId = id;
		SISWEB.capacitacion.showForm(data.capacitaciones[0].orden, data.capacitaciones[0].titulo, data.capacitaciones[0].contenido, data.capacitaciones[0].t_empresaria, data.capacitaciones[0].t_revendedora, data.capacitaciones[0].t_coordinadora, data.capacitaciones[0].t_consumidora, data.capacitaciones[0].t_regional, data.capacitaciones[0].t_regional_e, data.capacitaciones[0].t_anonimo, data.capacitaciones[0].t_division, data.capacitaciones[0].t_lider, data.capacitaciones[0].t_revdeli, data.capacitaciones[0].categoria, data.capacitaciones[0].sistemas);
		$('#waitenv').hide();
	}, { id:id, tipo:$('#ayudaTipo').val(), sistema:$('#sistemaTipo').val() });
},

showList: function(anchor, category)
{
	if (SISWEB._userType == SISWEB.USRTYPE_ADMIN)
		var data = { tipo:$('#ayudaTipo').val(), sistema:$('#sistemaTipo').val() };
	else
		var data = {};

	if (typeof category != 'undefined') data.category = category;

	$('#waitenv').show();

	SISWEB._api('get-capacitacion', function(data)
	{
		var listado = document.getElementById('ayudaListado');
		listado.innerHTML = '';

		for(var i = 0; i < data.capacitaciones.length; i++)
		{
			var div = document.createElement('div');
			div.innerHTML = '<h2>' + data.capacitaciones[i].titulo + (SISWEB._userType == SISWEB.USRTYPE_ADMIN ? ' - orden:' + data.capacitaciones[i].orden : '') + '</h2><h4>' + data.capacitaciones[i].titulocat + '</h4>' + (SISWEB._userType == SISWEB.USRTYPE_ADMIN ? '<button onclick="SISWEB.capacitacion.edit(' + data.capacitaciones[i].id + ')" type="button" class="btn btn-success" data-toggle="tooltip" data-placement="left" title="Editar"><span class="glyphicon glyphicon-edit"></span></button> <button onclick="SISWEB.capacitacion.preview(' + data.capacitaciones[i].id + ')" type="button" class="btn btn-success" data-toggle="tooltip" data-placement="left" title="Visualizar">Visualizar</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button onclick="SISWEB.capacitacion.delete(' + data.capacitaciones[i].id + ')" type="button" class="btn btn-success" data-toggle="tooltip" data-placement="left" title="Eliminar"><span class="glyphicon glyphicon-remove"></span></button><hr />' : data.capacitaciones[i].contenido);
			listado.appendChild(div);
			//data.capacitaciones[i].contenido
		}

		SISWEB._initAnchors();

		if (typeof anchor != 'undefined' && anchor != null && anchor != '') SISWEB._anchor(anchor);

		html = '';
		for(var i = 0; i < data.cats.length; i++)
			html += '<option value="' + data.cats[i].id + '">' + data.cats[i].titulo + '</option>';
		$('#cat').html(html);

		$('#waitenv').hide();
	}, data);
},

preview: function(id)
{
	SISWEB._modal('Visualizar', '<iframe src="api/get-capacitacion.php?id=' + id + '&preview=1" width="720" height="300" border="0" style="border:none"></iframe>', 750);
},

_closeForm: function()
{
	$('#addEnv').hide();
	$('#faqAddBtn').show();
	SISWEB._goBackFn = null;
}

}
