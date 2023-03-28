SISWEB.capacitacionCat =
{

_ids: 0,

conf: {
	validUserTypes: [ SISWEB.USRTYPE_ADMIN ]
},

_listCats: function(pg)
{
	$('#waitenv').show();

	if (typeof pg == 'undefined') pg = 1;

	SISWEB._api('get-capacitacion-cats', function(data)
	{
		var lista = document.getElementById('ListaCats');
		lista.innerHTML = '';

		SISWEB.capacitacionCat._ids = new Array();

		for (var i = 0; i < data.cats.length; i++)
		{
			SISWEB.capacitacionCat._ids.push(data.cats[i].id_web_capacitacion_cat);
			var tr = document.createElement('tr');
			tr.innerHTML = '<td><input name="id[]" value="' + data.cats[i].id_web_capacitacion_cat + '" type="hidden" /><input name="titulo[]" value="' + data.cats[i].titulo + '" /></td><td><img style="max-width:200px;max-height:200px" src="images/capacitcats/' + data.cats[i].imagen + '" /><br /><input name="imagen[]" type="file" /></td><td><input name="orden[]" value="' + data.cats[i].orden + '" maxlength="3" size="3" /></td><td><button class="btn" name="action" value="remove_' + data.cats[i].id_web_capacitacion_cat + '">Eliminar</button></td>';
			lista.appendChild(tr);
		}

		SISWEB.buildPager(lista, 4, data.pager, function(pg) { SISWEB.capacitacionCat._listCats(pg); });

		var tr = document.createElement('tr');
		tr.innerHTML = '<td><input name="titulo[]" /></td><td><input name="imagen[]" type="file" /></td><td><input name="orden[]" maxlength="3" size="3" /></td><td></td>';
		lista.appendChild(tr);

		$('#waitenv').hide();
	}, { pg:pg });
},

init: function()
{
	SISWEB.capacitacionCat._listCats();
}

};