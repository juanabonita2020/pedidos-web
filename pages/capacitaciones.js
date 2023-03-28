SISWEB.capacitaciones =
{

init: function(param)
{
	SISWEB._api('get-capacitacion-cats', function(data)
	{
		var listado = document.getElementById('ayudaListado');
		listado.innerHTML = '';

		for(var i = 0; i < data.cats.length; i++)
		{
			var a = document.createElement('a');
			a.href = "#capacitacion/," + data.cats[i].id_web_capacitacion_cat;
			a.style.backgroundImage = 'url("images/capacitcats/' + data.cats[i].imagen + '")';
			a.innerHTML = data.cats[i].titulo;
			listado.appendChild(a);
		}
	}, { all:true });
}

}