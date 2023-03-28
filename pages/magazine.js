SISWEB.magazine =
{

_pages: 0,
_camp: 0,
_prop: 0,
_code: 0,
_root: '',
_loaded: false,

conf: {
	validUserTypes: [ SISWEB.USRTYPE_EMPRE, SISWEB.USRTYPE_REVEN ],
	enabled: [ 'cleanBody' ]
},

remove: function(idx)
{
	var items = new Array();

	for(var i = 0; i < SISWEB.pedidosCarga._orderItems.length; i++)
		if (i != idx)
			items.push(SISWEB.pedidosCarga._orderItems[i]);

	SISWEB.pedidosCarga._orderItems = items;
	SISWEB.magazine._cartUpdated();
	$('#modal').modal('hide');
},

showCart: function()
{
	if (SISWEB.pedidosCarga._orderItems.length == 0) return;

	var html = '<table class="table table-bordered table-hover"><tr><th>Código</th><th>Tipo</th><th>Color</th><th>Talle</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th><th>Quitar</th></tr>';

	var tot = 0;
	var items = 0;

	for(var i = 0; i < SISWEB.pedidosCarga._orderItems.length; i++)
	{
		items += SISWEB.pedidosCarga._orderItems[i].q * 1;
		tot += SISWEB.pedidosCarga._orderItems[i].q * SISWEB.pedidosCarga._orderItems[i].precio;

		html += '<tr><td>' + SISWEB.pedidosCarga._orderItems[i].code + '</td><td>' + SISWEB.pedidosCarga._orderItems[i].type + ' - ' + SISWEB.pedidosCarga._orderItems[i].typeLabel + '</td><td>' + SISWEB.pedidosCarga._orderItems[i].color + ' - ' + SISWEB.pedidosCarga._orderItems[i].colorLabel + '</td><td>' + SISWEB.pedidosCarga._orderItems[i].tall + '</td><td>' + SISWEB.pedidosCarga._orderItems[i].q + '</td><td>$ ' + SISWEB.pedidosCarga._orderItems[i].precio + '</td><td>$ ' + SISWEB.pedidosCarga._orderItems[i].precio * SISWEB.pedidosCarga._orderItems[i].q + '</td><td><button onclick="SISWEB.magazine.remove(' + i + ')"><span class="glyphicon glyphicon-trash"></span></button></td></tr>';
	}

	html += '</table><p>' + items + ' items, $ ' + tot + '</p><br /><button type="button" class="btn btn-success" id="confirm">Confirmar</button>';

	SISWEB._modal('Carrito', html);
},

addProduct: function()
{
	SISWEB.pedidosCarga._addItem(SISWEB.magazine._code, $('#tipo').val(), $('#color').val(), $('#talle').val(), $('#q').val(), SISWEB.magazine._camp, $("#tipo option:selected").text(), $("#color option:selected").text(), SISWEB.magazine._cartUpdated);

	$('#modal').modal('hide');
},

selectProduct: function(code)
{
	var html = '<div class="row"><div class="col-md-6"><select id="tipo" class="form-control"></select><br /><select id="talle" class="form-control"></select></div><div class="col-md-6"><select id="color" class="form-control"></select><br /><input id="q" placeholder="cantidad" class="form-control" /></div></div><br /><button type="button" class="btn btn-success" id="add" disabled="disabled">Agregar al carrito</button>';

	SISWEB._modal('Agregar producto ' + code, html);

	SISWEB.magazine._code = code;

	SISWEB.magazine._loadDropList('tipo', 'get-tipos', { }, 'Tipo', 'tipos');

	$('#tipo').change(function() { SISWEB.magazine._chgValue(1); });
	$('#color').change(function() { SISWEB.magazine._chgValue(2); });
	$('#talle').change(function() { SISWEB.magazine._chgValue(3); });
	$('#q').change(function() { SISWEB.magazine._chgValue(3); });
	$('#add').click(function() { SISWEB.magazine.addProduct(); });
},

load: function()
{
	$('#magazine').fadeIn(1000);
	var flipbook = $('.magazine');
	if (flipbook.width() == 0 || flipbook.height() == 0)
	{
		setTimeout(SISWEB.magazine.load, 10);
		return;
	}

	var w = $(window).width() - 100;
	var h = Math.floor(w / 2 / SISWEB.magazine._prop);

	if (h > $(window).height() - 170)
	{
		h = $(window).height() - 170;
		w = Math.floor(h * SISWEB.magazine._prop * 2);
	}

	$('.next-button').css({ height:h + 'px' });
	$('.previous-button').css({ height:h + 'px' });

	flipbook.turn(
	{
	width: w,
	height: h,
	duration: 1000,
	gradients: true,
	autoCenter: true,
	elevation: 50,
	pages: SISWEB.magazine._pages,
	acceleration: !isChrome(),
	when:
	{
		turning: function(event, page, view)
		{
			var book = $(this),
			currentPage = book.turn('page'),
			pages = book.turn('pages');
			disableControls(page);
			$('.thumbnails .page-'+currentPage).parent().removeClass('current');
			$('.thumbnails .page-'+page).parent().addClass('current');
		},
		turned: function(event, page, view)
		{
			disableControls(page);
			$(this).turn('center');
			if (page == 1) $(this).turn('peel', 'br');
			$('#pgnum').html('Pág. ' + page + ' de ' + SISWEB.magazine._pages);
		},
		missing: function (event, pages)
		{
			for (var i = 0; i < pages.length; i++) addPage(pages[i], $(this));
		}
	}
	}
	);

	$('.magazine-viewport').zoom(
	{
		flipbook: $('.magazine'),
		max: function() { return largeMagazineWidth()/$('.magazine').width(); },
		when:
		{
			swipeLeft: function() { $(this).zoom('flipbook').turn('next'); },
			swipeRight: function() { $(this).zoom('flipbook').turn('previous'); },
			resize: function(event, scale, page, pageElement)
			{
				if (scale==1)
					loadSmallPage(page, pageElement);
				else
					loadLargePage(page, pageElement);
			},
			zoomIn: function ()
			{
				$('.thumbnails').hide();
				$('.made').hide();
				$('.magazine').removeClass('animated').addClass('zoom-in');
				$('#btnZoom span').removeClass('glyphicon-zoom-in').addClass('glyphicon-zoom-out');

				if (!window.escTip && !$.isTouch)
				{
					escTip = true;
					$('<div />', {'class': 'exit-message'}).
					html('<div>Presione ESC para salir</div>').
					appendTo($('body')).
					delay(2000).
					animate({opacity:0}, 500, function() { $(this).remove();});
				}
			},
			zoomOut: function ()
			{
				$('.exit-message').hide();
				$('.thumbnails').fadeIn();
				$('.made').fadeIn();
				$('#btnZoom span').removeClass('glyphicon-zoom-out').addClass('glyphicon-zoom-in');
				setTimeout(function(){
					$('.magazine').addClass('animated').removeClass('zoom-in');
					resizeViewport();
				}, 0);
			}
		}
	});

	if ($.isTouch)
		$('.magazine-viewport').bind('zoom.doubleTap', zoomTo);
	else
		$('.magazine-viewport').bind('zoom.tap', zoomTo);

	$(document).keydown(function(e)
	{
		switch (e.keyCode)
		{
		case 37: // left arrow
			$('.magazine').turn('previous');
			e.preventDefault();
			break;
		case 39: //right arrow
			$('.magazine').turn('next');
			e.preventDefault();
			break;
		case 27:
			$('.magazine-viewport').zoom('zoomOut');
			e.preventDefault();
			break;
		}
	});

	$('.thumbnails').click(function(event)
	{
		var page;
		if (event.target && (page=/page-([0-9]+)/.exec($(event.target).attr('class'))) )
			$('.magazine').turn('page', page[1]);
	});
	$('.thumbnails li').
	bind($.mouseEvents.over, function() { $(this).addClass('thumb-hover');})
	.bind($.mouseEvents.out, function() { $(this).removeClass('thumb-hover'); });
	if ($.isTouch)
		$('.thumbnails').addClass('thumbanils-touch').bind($.mouseEvents.move, function(event) { event.preventDefault(); });
	else
	{
		$('.thumbnails ul').mouseover(function() { $('.thumbnails').addClass('thumbnails-hover'); })
		.mousedown(function() { return false; })
		.mouseout(function() { $('.thumbnails').removeClass('thumbnails-hover'); });
	}

	if ($.isTouch)
		$('.magazine').bind('touchstart', regionClick);
	else
		$('.magazine').click(regionClick);

	$('.next-button').bind($.mouseEvents.over, function() { $(this).addClass('next-button-hover'); })
	.bind($.mouseEvents.out, function() { $(this).removeClass('next-button-hover'); })
	.bind($.mouseEvents.down, function() { $(this).addClass('next-button-down'); })
	.bind($.mouseEvents.up, function() { $(this).removeClass('next-button-down');})
	.click(function() { $('.magazine').turn('next'); });

	$('.previous-button').bind($.mouseEvents.over, function() { $(this).addClass('previous-button-hover'); })
	.bind($.mouseEvents.out, function() { $(this).removeClass('previous-button-hover'); })
	.bind($.mouseEvents.down, function() { $(this).addClass('previous-button-down'); })
	.bind($.mouseEvents.up, function() { $(this).removeClass('previous-button-down'); })
	.click(function() { $('.magazine').turn('previous'); });

	$('#btnPrev').click(function() { $('.magazine').turn('previous'); });
	$('#btnNext').click(function() { $('.magazine').turn('next'); });
	$('#btnFirst').click(function() { $('.magazine').turn('page', 1); });
	$('#btnLast').click(function() { $('.magazine').turn('page', SISWEB.magazine._pages); });
	$('#btnGo').click(function() { var page = prompt('Ingrese la página a saltar:'); $('.magazine').turn('page', page); });

	resizeViewport();

	$('.magazine').addClass('animated');

	SISWEB.magazine._loaded = true;
},

init: function()
{
	// return;
	$('#magazine').hide();

	SISWEB.pedidosCarga._orderItems = new Array();
	SISWEB.magazine._camp = 0;

	SISWEB._api('get-campanias', function(data)
	{
		for (var i = 0; i < data.campanias.length; i++)
			if (data.campanias[i].activa)
			{
				SISWEB.magazine._camp = data.campanias[i].campania;
				break;
			}

		if (! SISWEB.magazine._camp) return false;

		SISWEB._api('get-magazine', function(data)
		{
			SISWEB.magazine._pages = data.pages;
			SISWEB.magazine._prop = data.prop;

			SISWEB.magazine._root = 'camp/' + SISWEB.magazine._camp + '/';

			$('#pgnum').html('Pág. 1 de ' + SISWEB.magazine._pages);

			var thumbs = document.getElementById('thumbs');
			thumbs.innerHTML = '<li class="i"><img src="' + SISWEB.magazine._root + '1-thumb.jpg" width="76" height="100" class="page-1" /><span>1</span></li>';

			var d = (SISWEB.magazine._pages - 1) / 2;
			var df = Math.floor(d);

			for(var i = 1; i <= df; i++)
			{
				var li = document.createElement('li');
				li.className = 'd';
				li.innerHTML = '<img src="' + SISWEB.magazine._root + (i * 2) + '-thumb.jpg" width="76" height="100" class="page-' + (i * 2) + '" /><img src="' + SISWEB.magazine._root + (i * 2 + 1) + '-thumb.jpg" width="76" height="100" class="page-' + (i * 2 + 1) + '" /><span>' + (i * 2) + '-' + (i * 2 + 1) + '</span>';
				thumbs.appendChild(li);
			}

			if (d > df)
			{
				var li = document.createElement('li');
				li.className = 'i';
				li.innerHTML = '<img src="' + SISWEB.magazine._root + SISWEB.magazine._pages + '-thumb.jpg" width="76" height="100" class="page-' + SISWEB.magazine._pages + '" /><span>' + SISWEB.magazine._pages + '</span>';
				thumbs.appendChild(li);
			}

			$('#btnZoom').bind('click', function()
			{
				if ($('#btnZoom span').hasClass('glyphicon-zoom-in'))
					$('.magazine-viewport').zoom('zoomIn');
				else
					$('.magazine-viewport').zoom('zoomOut');
			});

			$('#btnExit').click(function() { window.location.hash = '#home'; });
			$('#btnCart').click(function() { SISWEB.magazine.showCart(); });

			if (SISWEB.magazine._loaded)
				SISWEB.magazine.load();
			else
				yepnope({
				test : Modernizr.csstransforms,
				yep: ['contrib/turn.min.js'],
				nope: ['contrib/turn.html4.min.js'],
				both: ['contrib/zoom.min.js', 'contrib/magazine.js', 'contrib/magazine.css'],
				complete: SISWEB.magazine.load
				});

		}, { campania:SISWEB.magazine._camp });
	});
},

_cartUpdated: function()
{
	if (SISWEB.pedidosCarga._orderItems.length > 0)
	{
		var tot = 0;
		var items = 0;
		for (var i = 0; i < SISWEB.pedidosCarga._orderItems.length; i++)
		{
			items += SISWEB.pedidosCarga._orderItems[i].q * 1;
			tot += SISWEB.pedidosCarga._orderItems[i].q * SISWEB.pedidosCarga._orderItems[i].precio;
		}

		var lbl = items + ' unidades, $ ' + tot;
	}
	else
		var lbl = 'Carrito vacío';

	$('#cartsize').html(lbl);
},

_loadDropList: function(id, service, data, lbl, obj)
{
	return SISWEB._loadDropList(SISWEB.magazine._camp, SISWEB.magazine._code, id, service, data, lbl, obj);
},

_chgValue: function(lvl)
{
	if (lvl == 3)
	{
		var talle = $('#talle').val();
		var q = $('#q').val();

		if (q && talle)
			$('#add').attr('disabled', null);
		else
			$('#add').attr('disabled', 'disabled');
	}
	else
	{
		$('#add').attr('disabled', 'disabled');

		if (lvl == 1)
		{
			$('#talle').html('');
			SISWEB.magazine._loadDropList('color', 'get-colores', { tipo:$('#tipo').val() }, 'Color', 'colores');
		}
		else if (lvl == 2)
			SISWEB.magazine._loadDropList('talle', 'get-talles', { tipo:$('#tipo').val(), color:$('#color').val() }, 'Talle', 'talles');
	}
}

};