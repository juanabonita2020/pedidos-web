SISWEB.ficheros =
{

_root: null,
_path: '',
_prevPaths: null,
_title: '',

init: function(root)
{
	if (root == null) root = 'CNT';
	SISWEB.ficheros._path = '';
	SISWEB.ficheros._prevPath = new Array();
	SISWEB.ficheros._root = root;

	switch(root)
	{
	case 'CNT':
	{
		SISWEB.ficheros._title = 'Contenidos';
		break;
	}
	}

	SISWEB.ficheros.list();
},

click: function(type, name, url)
{
	if (type == 1)
	{
		SISWEB.ficheros._prevPath.push(SISWEB.ficheros._path);
		SISWEB.ficheros._path += name + '/';
		SISWEB.ficheros.list();
	}
	else
		window.open(url);
},

goBack: function()
{
	SISWEB.ficheros._path = SISWEB.ficheros._prevPath.pop();
	SISWEB.ficheros.list();
},

createDir: function()
{
	var dst = prompt('Nombre de la nueva carpeta');
	if (dst)
		SISWEB.ficheros.api(3, null, dst);
},

createFile: function()
{
	SISWEB.alert('<form id="createFile" action="' + SISWEB.apiURL + 'filemanager.php" method="POST" target="backiframe" enctype="multipart/form-data"><input type="hidden" name="action" value="5" /><input type="hidden" name="root" value="' + SISWEB.ficheros._root + '" /><input type="hidden" name="path" value="' + SISWEB.ficheros._path + '" /><input type="file" name="fichero" id="fichero" /></form>', 'Crear fichero', function()
	{
		if ($('#fichero').val())
		{
			$('#waitenv').show();
			$('#createFile').submit();
		}
	});
},

remove: function(type, src)
{
	SISWEB.confirm('¿Está seguro de eliminar ' + (type == 1 ? 'la carpeta' : 'el fichero') + ' "' + src + '"?', function()
	{
		SISWEB.ficheros.api(type == 1 ? 4 : 6, src);
	}, function() { });
},

rename: function(src)
{
	var dst = prompt('Nombre nuevo', src);
	if (dst)
		SISWEB.ficheros.api(7, src, dst);
},

list: function()
{
	$('#title').html(SISWEB.ficheros._title + ': ...cargando...');

	SISWEB.ficheros.api(1, null, null, function(data)
	{
		$('#title').html(SISWEB.ficheros._title + ': ' + (SISWEB.ficheros._path ? SISWEB.ficheros._path : 'Raíz'));

		var html = '<ul class="nav nav-pills">' + (data.raiz ? '' : '<li><a href="Javascript:SISWEB.ficheros.goBack();"><span class="glyphicon glyphicon-circle-arrow-left"></span> subir</a></li>') + '<li><a href="Javascript:SISWEB.ficheros.createDir();"><span class="glyphicon glyphicon-plus"> carpeta</span></a></li><li><a href="Javascript:SISWEB.ficheros.createFile();"><span class="glyphicon glyphicon-plus"> fichero</span></a></li></ul><div class="row filemanagerrow">';

		for(var i = 0; i < data.ficheros.length; i++)
			html += '<div class="col-md-2"><a href="' + (data.ficheros[i].isDir ? 'JavaScript:SISWEB.ficheros.click(1, \'' + data.ficheros[i].nombre + '\', \'' + data.ficheros[i].url + '\')' : data.ficheros[i].url) + '"' + (data.ficheros[i].isDir ? '' : ' target="_blank"') + '>' + (data.ficheros[i].imagen ? '<img src="' + data.ficheros[i].url + '" style="max-width:100px;max-height:100px;" />' : '<span class="filemanagericon glyphicon glyphicon-' + (data.ficheros[i].isDir ? 'folder-open' : 'file') + '"></span>') + '<br /><b>' + data.ficheros[i].nombre + '</b>' + (data.ficheros[i].isDir ? '' : '<br /><small>' + data.ficheros[i].bytes + '</small>') + '</a><br /><a href="JavaScript:SISWEB.ficheros.rename(\'' + data.ficheros[i].nombre + '\')"><span class="glyphicon glyphicon-pencil"></span></a>' + (data.ficheros[i].del ? ' <a href="JavaScript:SISWEB.ficheros.remove(' + (data.ficheros[i].isDir ? 1 : 2) + ', \'' + data.ficheros[i].nombre + '\')"><span class="glyphicon glyphicon-trash"></span></a>' : '') + '</div>';

		html += '</div>';

		$('#panel').html(html);
	});
},

api: function(action, src, dst, fn)
{
	var data = { root:SISWEB.ficheros._root, action:action, path:SISWEB.ficheros._path };

	if (typeof src != 'undefined' && src != null) data.src = src;
	if (typeof dst != 'undefined' && dst != null) data.dst = dst;

	$('#waitenv').show();

	SISWEB._api('filemanager', function(data)
	{
		$('#waitenv').hide();

		if (data == false)
			SISWEB.alert('Hubo un error inesperado.');
		else if (typeof fn == 'undefined')
			SISWEB.ficheros.list();
		else
			fn(data);
	}, data);
}

}