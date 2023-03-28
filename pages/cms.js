SISWEB.cms =
{

init: function(code)
{
	$.get('content/' + code + '.html', function(content)
	{
		$('#content').html(content);
	});
}

}