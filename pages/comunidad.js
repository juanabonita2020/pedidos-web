SISWEB.comunidad =
{

init: function()
{
	if (! SISWEB._global.comunidad) return SISWEB._goComunidad(1);
	
	if (SISWEB._global.catalogo)
		$('#comcatalogo').show();
	else
		$('#comcatalogo').hide();
}

}