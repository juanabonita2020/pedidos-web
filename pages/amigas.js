SISWEB.amigas =
{

conf: {
  enabled: [ 'userHeader' ],
  validUserTypes: [ SISWEB.USRTYPE_DIVIS, SISWEB.USRTYPE_REGIO, SISWEB.USRTYPE_COORD, SISWEB.USRTYPE_EMPRE, SISWEB.USRTYPE_REVEN, SISWEB.USRTYPE_ADMIN ]
},

_orderBy: null,
_tab: 0,
_searchBy: 0,

changeTab: function(tab)
{
	SISWEB.amigas._tab = tab;

	if (tab == 0)
	{
		$('#tab0').addClass('active');
		$('#tab1').removeClass('active');
		$('#tab0 a').addClass('colorjb');
		$('#tab1 a').removeClass('colorjb');
		$('#buscador').hide();
	}
	else
	{
		$('#tab0').removeClass('active');
		$('#tab1').addClass('active');
		$('#tab0 a').removeClass('colorjb');
		$('#tab1 a').addClass('colorjb');
		$('#buscador').show();
	}

	SISWEB.amigas.showList();
},

changeSearch: function(i)
{
	SISWEB.amigas._searchBy = i;

	if (i == 0)
	{
		$('#zona').attr('disabled', null);
		$('#cliente').attr('disabled', null);
		$('#nombre').attr('disabled', 'disabled');
	}
	else
	{
		$('#zona').attr('disabled', 'disabled');
		$('#cliente').attr('disabled', 'disabled');
		$('#nombre').attr('disabled', null);
	}

	SISWEB.amigas.showList();
},

details: function(id, concepto, fecha)
{
	//~ console.log(id, concepto);
	if (concepto == 'CANJE')
		SISWEB._modal('Detalle', 'Fecha de Canje: ' + fecha);
	else
		SISWEB._api('get-amiga-detalle', function(data)
		{
			var html = '<div class="panel panel-info">';

			if (data.det.length > 0)
			{
				html += '<table class="table"><tbody>';
				for(var i = 0; i < data.det.length; i++)
					html += '<tr><td>' + data.det[i][0] + '</td><td>' + data.det[i][1] + '</td></tr>';
				html += '</tbody></table>';
			}

			html += '<p style="margin:10px">Observacion respecto del estado de sus puntos: ' + data.obs + '</p>';

			if (data.det2.length > 0)
			{
				html += '<table class="table"><thead><tr><th></th><th colspan="2" style="text-align:center">Indicadora</th><th colspan="2" style="text-align:center">Indicada</th></tr><tr><th></th><th></th><th style="text-align:center">Solicitado</th><th style="text-align:center">Devuelto</th><th style="text-align:center">Solicitado</th><th style="text-align:center">Devuelto</th></tr><tbody>';
				for(var i = 0; i < data.det2.length; i++)
					html += '<tr><td>' + data.det2[i][0] + '</td><td>' + data.det2[i][5] + '</td><td style="text-align:center">' + data.det2[i][1] + '</td><td style="text-align:center">' + data.det2[i][2] + '</td><td style="text-align:center">' + data.det2[i][3] + '</td><td style="text-align:center">' + data.det2[i][4] + '</td></tr>';
				html += '</tbody></table>';
			}

			html += '</div>';

			SISWEB._modal('Detalle', html);
		}, { id:id });
},

showList: function(pg)
{
	$('#waitenv').show();

	$('#ListaResultadosBody').html('Cargando...espere...');
	$('#estados').html('');

	if (typeof pg == 'undefined') pg = 1;

	var data = {
	//estado: $('#filterestado').val(),
	pg: pg
	};

	/*if (SISWEB.amigas._orderBy != null)
		data.orderby = SISWEB.amigas._orderBy;*/

	if (SISWEB.amigas._tab)
	{
		data.thirdparty = true;

		if (SISWEB.amigas._searchBy)
			data.cliente = (SISWEB._suggests['nombre']['value'] == null ? '' : SISWEB._suggests['nombre']['value'].trim());
		else
		{
			data.zona = $('#zona').val();
			data.numerocliente = $('#cliente').val();
		}
	}

	SISWEB._api('get-amigas', function(data)
	{
		if (SISWEB._actualPage != 'amigas') return;

		var body = document.getElementById('ListaResultadosBody');
		body.innerHTML = '';

		for(var i = 0; i < data.amigas.length; i++)
		{
			var tr = document.createElement('tr');
			tr.innerHTML = '<td>' + data.amigas[i].campania + '</td><td>' + data.amigas[i].zona + '</td><td>' + data.amigas[i].cliente + '</td><td>' + data.amigas[i].nombre + '</td><td>' + data.amigas[i].concepto + '</td><td>' + data.amigas[i].valor + '</td><td>' + data.amigas[i].estado + '</td><td><button type="button" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Ver Detalle" ' + (data.amigas[i]._concepto != 'CANJE' && data.amigas[i]._concepto != 1 ? 'disabled="disabled"' : 'onclick="JavaScript:SISWEB.amigas.details(' + data.amigas[i].id + ', \'' + data.amigas[i]._concepto + '\', \'' + data.amigas[i]._fecha + '\')"') + '><span class="glyphicon glyphicon-search"></span></button></td>';
			body.appendChild(tr);
		}

		SISWEB.buildPager(body, 9, data.pager, function(pg) { SISWEB.amigas.showList(pg); });

		var html = '';
		for(i in data.estados)
			html += '<tr><td>Puntos ' + data.estados[i][0] + 's:</td><td class="text-right"><span id="total_pend">' + data.estados[i][1] + '</span></td></tr>';
		$('#estados').html(html);

		$('#waitenv').hide();
	}, data);
},

orderBy: function(o)
{
	SISWEB._orderByList('amigas', 'ListaResultados', o, function()
	{
		SISWEB.amigas.showList();
	});
},

init: function()
{
	//~ if (! SISWEB._global.comunidad) return SISWEB._homePage();

	SISWEB.amigas._searchBy = 0;
	SISWEB.amigas._tab = 0;
	//SISWEB.amigas._orderBy = null;

	if (SISWEB._userType == SISWEB.USRTYPE_REVEN || SISWEB._userType == SISWEB.USRTYPE_ADMIN)
		$('#amigastab').hide();
	else
		$('#amigastab').show();

	$('#zona').change(function() { SISWEB.amigas.showList(); });
	$('#cliente').change(function() { SISWEB.amigas.showList(); });

	SISWEB.initAutocomplete({
	id: 'nombre',
	url: 'get-revendedoras.php',
	minLength: 1,
	onSelect: function()
	{
		SISWEB.amigas.showList();
	}
	});

	if (SISWEB._userType == SISWEB.USRTYPE_ADMIN || SISWEB._userType == SISWEB.USRTYPE_REGIO || SISWEB._userType == SISWEB.USRTYPE_DIVIS)
            SISWEB.amigas.changeTab(1)
        else
            SISWEB.amigas.showList();
}

};
