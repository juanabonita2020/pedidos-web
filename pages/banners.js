SISWEB.banners =
{

_ids: 0,

conf: {
	validUserTypes: [ SISWEB.USRTYPE_ADMIN ]
},

_listBanners: function(pg)
{
	$('#waitenv').show();

	if (typeof pg == 'undefined') pg = 1;

	SISWEB._api('get-publicidad', function(data)
	{
		var lista = document.getElementById('ListaBanners');
		lista.innerHTML = '';

		SISWEB.banners._ids = new Array();

		for (var i = 0; i < data.pubs.length; i++)
		{
			SISWEB.banners._ids.push(data.pubs[i].id_web_publicidad);
			var tr = document.createElement('tr');
			tr.innerHTML = '<td><input name="id[]" value="' + data.pubs[i].id_web_publicidad + '" type="hidden" /><input name="espacio[]" value="' + data.pubs[i].espacio + '" maxlength="5" size="5" /></td><td><input name="campania[]" value="' + data.pubs[i].id_web_campanias + '" maxlength="4" size="4" /></td><td><input name="desde[]" value="' + data.pubs[i].desde + '" maxlength="10" size="10" /><br /><input name="hasta[]" value="' + data.pubs[i].hasta + '" maxlength="10" size="10" /></td><td><img style="max-width:200px;max-height:200px" src="images/ads/' + data.pubs[i].imagen + '" /><br /><input name="imagen[]" type="file" /></td><td><input name="link[]" value="' + data.pubs[i].link + '" /></td><td><button class="btn" name="action" value="remove_' + data.pubs[i].id_web_publicidad + '">Eliminar</button></td>';
			lista.appendChild(tr);
		}

		SISWEB.buildPager(lista, 6, data.pager, function(pg) { SISWEB.banners._listBanners(pg); });

		var tr = document.createElement('tr');
		tr.innerHTML = '<td><input name="espacio[]" maxlength="5" size="5" /></td><td><input name="campania[]" maxlength="5" size="5" /></td><td><input name="desde[]" maxlength="10" size="10" /><br /><input name="hasta[]" maxlength="10" size="10" /></td><td><input name="imagen[]" type="file" /></td><td><input name="link[]" /></td><td></td>';
		lista.appendChild(tr);

		$('#waitenv').hide();
	}, { all:1, pg:pg });
},

init: function()
{
	SISWEB.banners._listBanners();
}

};