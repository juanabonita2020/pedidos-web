<?php require 'lib/kernel.php';

class API extends JBSYSWEBAPI
{

protected $_checkSession = false;

protected function _process($input)
{
	//~ echo password_hash('5758'.'ange17gia', PASSWORD_DEFAULT);

	/*$fd = fopen('data/usuarios-no-recup.txt', 'r');
	while($l = fgets($fd))
	{
		echo '<br />'.$l;
		$res = $this->_sendmail($l, 'Ya puedes ingresar a Juana Bonita', '<p>Hola, le enviamos este correo para informarles que los problemas para acceder al sistema Pedidos Web de Juana Bonita que tenían algunas cuentas de usuarios fueron resueltos.</p><p>Les pedimos nos informen por las vías habituales de soporte en caso de tener algún inconveniente adicional.</p><p>Muchas gracias.</p>');
	}*/

	//~var_dump($this->_userId);
	//var_dump($this->_getSessionVar('regionalNegocio'));

	//~echo 'Session time: '.ini_get('session.gc_maxlifetime');
	//echo getcwd();
	//print_r($_SERVER);
	//echo system('/opt/php5-5/bin/php -v');
	//print_r($output);
	//echo PHP_BINDIR;

	//foreach($this->_dbGetAll('web_usuarios') as $u) $this->_setPassword($u['id_web_usuarios'], '123');

	//echo $this->_hash('18311'.'Naj98r73f789v'); //efierros@juanabonita.com
	//$2y$10$mtyweTQOWn86L6pkmh/Z9u7EwAC6KUaQyF1ut1oRMlCkG7yTUXNQe

	/*$info = array();
	$res = $this->_sendmail('dmelgarejo@juanabonita.com,cuntasdelis@juanabonita.com', 'Prueba smtp 1', 'prueba', 2, false, true, $info);
	print_r($res);print_r($info);*/

	/*
	$path = 'logs/';
	$daysOld = 7;
	$q = 0;

	list($y, $m, $d) = explode('-', date('Y-m-d'));
	$ts = mktime(0, 0, 0, $m, $d, $y) - 86400 * ($daysOld - 1);
	$handle = opendir($path);
	while (false !== ($entry = readdir($handle))) if (substr($entry, 0, 1) != '.')
	{
		$file = $path.$entry;
		//echo $entry;
		if (filemtime($file) < $ts)
		{
			//unlink($path.$entry); //echo ' -> DELETE';
			$q++;
		}
		//echo '<br />';
	}
    closedir($handle);
	echo $q;die;
	*/
}

}

require 'lib/exe.php';
