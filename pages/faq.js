SISWEB.faq =
{

editId: null,

init: function(code)
{
	//
	SISWEB.faq.editId = null;

	if (SISWEB._userType == SISWEB.USRTYPE_ADMIN)
	{
		$('#ayudaTipo').change(function() { SISWEB.faq.showList() });
		$('#faqAddBtn').click(function() { SISWEB.faq.showForm() });
		$('#addBtn2').click(function() { SISWEB.faq.save() });
		$('#ayudaTipo').show();
		$('#faqAddBtn').show();
	}
	else
	{
		$('#faqAddBtn').hide();
		$('#ayudaTipo').hide();
	}
	
	SISWEB.faq.showList();
},

save: function(submit)
{	
	$('#waitenv').show();
	
	var data = { orden:$('#orden').val(), pregunta:$('#pregunta').val(), respuesta:$('#respuesta').val(), t_empresaria:document.getElementById('t_empresaria').checked ? 1 : 0, t_revendedora:document.getElementById('t_revendedora').checked ? 1 : 0, t_coordinadora:document.getElementById('t_coordinadora').checked ? 1 : 0, t_consumidora:document.getElementById('t_consumidora').checked ? 1 : 0, t_regional:document.getElementById('t_regional').checked ? 1 : 0, t_anonimo:document.getElementById('t_anonimo').checked ? 1 : 0 };
	
	if (SISWEB.faq.editId == null)
		var action = 'add';
	else
	{
		var action = 'modify';
		data.id = SISWEB.faq.editId;
		SISWEB.faq.editId = null;
	}
	
	SISWEB._api(action + '-faq', function(data)
	{
		SISWEB.faq._closeForm();
		SISWEB.faq.showList();
		$('#waitenv').hide();
	}, data, { method:'POST' });
},

delete: function(id)
{
	SISWEB.confirm('¿Está seguro de eliminar la pregunta?', function()
	{
		$('#waitenv').show();
		SISWEB._api('delete-faq', function(data)
		{
			SISWEB.faq.showList();
		}, { id:id });
	}, function() { });
},

showForm: function(orden, pregunta, respuesta, t_empresaria, t_revendedora, t_coordinadora, t_consumidora, t_regional, t_anonimo)
{
	$('#addEnv').show();
	$('#faqAddBtn').hide();
	$('#orden').val(typeof orden == 'undefined' ? '' : orden);
	$('#pregunta').html(typeof pregunta == 'undefined' ? '' : pregunta);
	$('#respuesta').html(typeof respuesta == 'undefined' ? '' : respuesta);
	SISWEB._goBackFn = SISWEB.faq._closeForm;
	
	document.getElementById('t_empresaria').checked = (typeof t_empresaria == 'undefined' || t_empresaria == 0 ? '' : 'checked');
	document.getElementById('t_revendedora').checked = (typeof t_revendedora == 'undefined' || t_revendedora == 0 ? '' : 'checked');
	document.getElementById('t_coordinadora').checked = (typeof t_coordinadora == 'undefined' || t_coordinadora == 0 ? '' : 'checked');
	document.getElementById('t_consumidora').checked = (typeof t_consumidora == 'undefined' || t_consumidora == 0 ? '' : 'checked');
	document.getElementById('t_regional').checked = (typeof t_regional == 'undefined' || t_regional == 0 ? '' : 'checked');
	document.getElementById('t_anonimo').checked = (typeof t_anonimo == 'undefined' || t_anonimo == 0 ? '' : 'checked');
		
	var btnicon = document.getElementById('addBtn2Icon');
	
	if (typeof orden == 'undefined')
		btnicon.className = 'glyphicon glyphicon-plus';
	else
		btnicon.className = 'glyphicon glyphicon-edit';
},

edit: function(id)
{
	$('#waitenv').show();
	
	SISWEB._api('get-faq', function(data)
	{
		SISWEB.faq.editId = id;
		SISWEB.faq.showForm(data.preguntas[0].orden, data.preguntas[0].pregunta, data.preguntas[0].respuesta, data.preguntas[0].t_empresaria, data.preguntas[0].t_revendedora, data.preguntas[0].t_coordinadora, data.preguntas[0].t_consumidora, data.preguntas[0].t_regional, data.preguntas[0].t_anonimo);
		$('#waitenv').hide();
	}, { id:id, tipo:$('#ayudaTipo').val() });
},

showList: function()
{
	if (SISWEB._userType == SISWEB.USRTYPE_ADMIN)
		var data = { tipo:$('#ayudaTipo').val() };
	else
		var data = {};
		
	$('#waitenv').show();

	SISWEB._api('get-faq', function(data)
	{
		var listado = document.getElementById('ayudaListado');
		listado.innerHTML = '';
		
		for(var i = 0; i < data.preguntas.length; i++)
		{
			var div = document.createElement('div');
			div.innerHTML = '<h2>' + data.preguntas[i].pregunta + (SISWEB._userType == SISWEB.USRTYPE_ADMIN ? ' - orden:' + data.preguntas[i].orden : '') + '</h2><div>' + data.preguntas[i].respuesta + '</div>' + (SISWEB._userType == SISWEB.USRTYPE_ADMIN ? '<button onclick="SISWEB.faq.edit(' + data.preguntas[i].id + ')" type="button" class="btn btn-success" data-toggle="tooltip" data-placement="left" title="Editar"><span class="glyphicon glyphicon-edit"></span></button> <button onclick="SISWEB.faq.delete(' + data.preguntas[i].id + ')" type="button" class="btn btn-success" data-toggle="tooltip" data-placement="left" title="Eliminar"><span class="glyphicon glyphicon-remove"></span></button><hr />' : '');
			listado.appendChild(div);
		}
		
		$('#waitenv').hide();
	}, data);
},

_closeForm: function()
{
	$('#addEnv').hide();
	$('#faqAddBtn').show();
	SISWEB._goBackFn = null;
}

}