SISWEB.claveMaestra =
{

conf: {
	validUserTypes: [ SISWEB.USRTYPE_ADMIN, SISWEB.USRTYPE_REGIO ]
},

save: function()
{
	var pass = $('#pass').val();
	SISWEB.alert(pass ? 'Se activó la contraseña maestra.' : 'Se desactivó la contraseña maestra');
	SISWEB._api('change-clave-maestra', function(data) { }, { pass:pass });
},

init: function()
{
	$('#saveBtn').click(function(){ SISWEB.claveMaestra.save(); })
}

}