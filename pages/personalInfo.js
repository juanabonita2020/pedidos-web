SISWEB.personalInfo =
{

showGoBack: true,
_loaderQ: 2,

conf: {
	validUserTypes: [ SISWEB.USRTYPE_EMPRE, SISWEB.USRTYPE_REVEN, SISWEB.USRTYPE_COORD ],
	disabled: [ 'goBack' ]
},

save: function()
{
	if ((! $('#inputTel1a').val() || ! $('#inputTel1p').val() || ! $('#inputTel1s').val()) && (! $('#inputTel2a').val() || ! $('#inputTel2p').val() || ! $('#inputTel2s').val()) && (! $('#inputCela').val() || ! $('#inputCelp').val() || ! $('#inputCels').val()))
	{
		SISWEB.alert('Debe ingresar al menos un teléfono.');
		return false;
	}

	if (! $('#inputCodigoPostal').val())
	{
		SISWEB.alert('Debe ingresar el código postal.');
		return false;
	}

	if (! $('#inputFechaNacimiento').val())
	{
		SISWEB.alert('Debe ingresar la fecha de nacimiento.');
		return false;
	}

	if ($('#inputInstagram').val() == 'SI' && $('#inputUsuarioInstagram').val() == '')
	{
		SISWEB.alert('Debe ingresar su usuario de Instagram.');
		return false;
	} else if ($('#inputInstagram').val() == 'NO')
		$('#inputUsuarioInstagram').val('');

	if ($('#inputFacebook').val() == 'SI' && $('#inputUsuarioFacebook').val() == '')
	{
		SISWEB.alert('Debe ingresar su usuario de Facebook.');
		return false;
	} else if ($('#inputFacebook').val() == 'NO')
		$('#inputUsuarioFacebook').val('');

	//~ console.log('OK!!');return false;

	$('#waitenv').show();

	SISWEB._userData.fullName = $('#inputName').val() + ' ' + $('#inputLastName').val();
	$('#username').html(SISWEB._userData.fullName);

	SISWEB._api('save-datos-personales', function(data)
	{
		window.location.hash = '#home';
	},
	{
	nombre: $('#inputName').val(),
	apellido: $('#inputLastName').val(),
	telefonoArea1: $('#inputTel1a').val(),
	telefonoPrefijo1: $('#inputTel1p').val(),
	telefonoSufijo1: $('#inputTel1s').val(),
	telefonoArea2: $('#inputTel2a').val(),
	telefonoPrefijo2: $('#inputTel2p').val(),
	telefonoSufijo2: $('#inputTel2s').val(),
	celularArea: $('#inputCela').val(),
	celularPrefijo: $('#inputCelp').val(),
	celularSufijo: $('#inputCels').val(),
	direccion: $('#inputAddress').val(),
	codigoPostal: $('#inputCodigoPostal').val(),
	localidad: $('#inputLocalidad').val(),
	fechaNacimiento: $('#inputFechaNacimiento').val(),
	provincia: $('#inputProvincia').val(),
	altura: $('#inputAltura').val(),
	piso: $('#inputPiso').val(),
	departamento: $('#inputDepto').val(),
	barrio: $('#inputBarrio').val(),
	id_web_paises: $('#inputPais').val(),
	tiene_instagram: $('#inputInstagram').val(),
	usuario_instagram: $('#inputUsuarioInstagram').val(),
	tiene_facebook: $('#inputFacebook').val(),
	usuario_facebook: $('#inputUsuarioFacebook').val(),
	id_sistema_operativo: $('#inputSOMovil').val(),
	alertaCerrar: document.getElementById('inputCloseAlert').checked ? 1 : 0
	});

	return false;
},

validateInput: function(type, el)
{
	switch(type)
	{
	case 1:
	{
		if (el.val().substr(0, 1) != '0')
			el.val('0' + el.val());
		break;
	}
	}
},

loadProvincias: function(provincia)
{
	SISWEB._api('get-provincias', function(data)
	{
		var select = document.getElementById('inputProvincia');
		select.innerHTML = '';
		for (var i = 0; i < data.provincias.length; i++)
		{
			var option = document.createElement('option');
			option.value = data.provincias[i].idProvincias;
			option.innerHTML = data.provincias[i].descripcion;
			select.appendChild(option);
		}
		$('#provinciaLabel').html(data.leyenda);
		if (typeof provincia != null)
			$('#inputProvincia').val(provincia);
		$('#waitenv').hide();
	}, { pais:$('#inputPais').val() });
},

loadDatos: function()
{
	if (SISWEB.personalInfo._loaderQ > 0)
	{
		setTimeout(SISWEB.personalInfo.loadDatos, 1000);
		return;
	}

	SISWEB._api('get-datos-personales', function(data)
	{
		//console.log(data);
		$('#inputName').val(data.nombre);
		$('#inputLastName').val(data.apellido);
		$('#inputTel1a').val(data.telefonoArea1);
		$('#inputTel1p').val(data.telefonoPrefijo1);
		$('#inputTel1s').val(data.telefonoSufijo1);
		$('#inputTel2a').val(data.telefonoArea2);
		$('#inputTel2p').val(data.telefonoPrefijo2);
		$('#inputTel2s').val(data.telefonoSufijo2);
		$('#inputCela').val(data.celularArea);
		$('#inputCelp').val(data.celularPrefijo);
		$('#inputCels').val(data.celularSufijo);
		$('#inputAddress').val(data.direccion);
		$('#inputCodigoPostal').val(data.codigoPostal);
		$('#inputLocalidad').val(data.localidad);
		$('#inputFechaNacimiento').val(data.fechaNacimiento);
		$('#inputAltura').val(data.altura);
		$('#inputPiso').val(data.piso);
		$('#inputDepto').val(data.departamento);
		$('#inputBarrio').val(data.barrio);
		$('#inputPais').val(data.id_web_paises);
		$('#inputInstagram').val(data.tiene_instagram);
		$('#inputUsuarioInstagram').val(data.usuario_instagram);
		$('#inputFacebook').val(data.tiene_facebook);
		$('#inputUsuarioFacebook').val(data.usuario_facebook);
		$('#inputSOMovil').val(data.id_sistema_operativo);

		if (data.closeAlert == "1")
			document.getElementById('inputCloseAlert').checked = 'checked';
		else
			document.getElementById('inputCloseAlert').checked = '';

		SISWEB.personalInfo.loadProvincias(data.provincia);

		SISWEB.personalInfo.autocompleteLocal();
	});
},

autocompleteLocal: function()
{
	$('#inputLocalidad').autocomplete({
	source: SISWEB.apiURL + 'get-localidad.php?pais=' + $('#inputPais').val(),
	minLength: 2
	});
},

init: function()
{
	SISWEB.personalInfo._loaderQ = 2;

	$('#waitenv').show();

	if (SISWEB.personalInfo.showGoBack)
	{
		$('#successReg').hide();
		$('#goBack').show();
	}
	else
	{
		$('#successReg').show();
		SISWEB.personalInfo.showGoBack = true;
	}

	//~ $('#inputName').inputmask('*', { greedy:false, repeat:40 });
	//~ $('#inputLastName').inputmask('*', { greedy:false, repeat:40 });
	$('#inputTel1a').inputmask('9', { greedy:false, repeat:5 }).change(function() { SISWEB.personalInfo.validateInput(1, $(this)); });
	$('#inputTel1p').inputmask('9', { greedy:false, repeat:4 });
	$('#inputTel1s').inputmask('9', { greedy:false, repeat:4 });
	$('#inputTel2a').inputmask('9', { greedy:false, repeat:5 }).change(function() { SISWEB.personalInfo.validateInput(1, $(this)); });
	$('#inputTel2p').inputmask('9', { greedy:false, repeat:4 });
	$('#inputTel2s').inputmask('9', { greedy:false, repeat:4 });
	$('#inputCela').inputmask('9', { greedy:false, repeat:5 }).change(function() { SISWEB.personalInfo.validateInput(1, $(this)); });
	$('#inputCelp').inputmask('9', { greedy:false, repeat:4 });
	$('#inputCels').inputmask('9', { greedy:false, repeat:4 });
	$('#inputCodigoPostal').inputmask('9', { greedy:false, repeat:4 });
	$('#inputFechaNacimiento').inputmask('d/m/y', { 'placeholder': 'dd/mm/aaaa' });

	$('#inputPais').change(function()
	{
		SISWEB.personalInfo.loadProvincias();
		SISWEB.personalInfo.autocompleteLocal();
	});

	SISWEB._api('get-pais', function(data)
	{
		var select = document.getElementById('inputPais');
		select.innerHTML = '';
		for (var i = 0; i < data.pais.length; i++)
		{
			var option = document.createElement('option');
			option.value = data.pais[i].id_web_paises;
			option.innerHTML = data.pais[i].pais;
			select.appendChild(option);
		}
		SISWEB.personalInfo._loaderQ--;
	});

	SISWEB._api('get-SOMovil', function(data)
	{
		var select = document.getElementById('inputSOMovil');
		select.innerHTML = '';
		for (var i = 0; i < data.SOMovil.length; i++)
		{
			var option = document.createElement('option');
			option.value = data.SOMovil[i].id_sistema_operativo;
			option.innerHTML = data.SOMovil[i].sistema_operativo;
			select.appendChild(option);
		}
		SISWEB.personalInfo._loaderQ--;
	});

	setTimeout(SISWEB.personalInfo.loadDatos, 1000);

	//~ $('#saveBtn').click(function() { SISWEB.personalInfo.save(); });
}

}
