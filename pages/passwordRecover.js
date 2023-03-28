SISWEB.passwordRecover =
{

conf: {
	disabled: [ 'goBack' ]
},

init: function()
{
	$('#sendBtn').click(function(){ SISWEB.passwordReset.send(); })
	$('#sendBtn2').click(function(){ SISWEB.goToLoginPage(); })
}

}