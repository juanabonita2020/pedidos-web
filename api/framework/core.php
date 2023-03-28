<?php $FRAMEWORK = 1;

require 'framework/contrib/phpmailer/class.phpmailer.php';
require 'framework/contrib/phpmailer/class.smtp.php';

if (! function_exists('password_hash'))
	require 'framework/contrib/password.php';

class FRAMEWORK
{

protected $_userId;
protected $_userType;
protected $_userName;
protected $_isCron = false;
protected $_input = array();
protected $_checkSession = true;
protected $_logReq = false;
protected $_disabled = false;
protected $_logReqData = array();
protected $_dbInsertId;
protected $_dbRowCount;
protected $_config = array();
protected $_validUserTypes;
protected $_sessionName = 'APP';
protected $_dbh;
protected $_enaDbLogFile = false;

private $_smtp;
private $_smtp2;
private $_smtp3;
private $_smtp4;
private $_dbLogFile = null;
private $_readUncomm = false;

function __construct()
{
	if (! $this->_isCron)
	{
		if (! empty($_REQUEST['_sessid'])) session_id($_REQUEST['_sessid']);
		session_start();
	}
}
	
// carga la configuración
function config($cfg)
{
	foreach($cfg as $k => $v) $this->_config[$k] = $v;

	if (! $this->_isCron)
	{
		ini_set('session.gc_maxlifetime', $this->_config['sessionTime']);
		session_set_cookie_params($this->_config['sessionTime']);

		if (! empty($this->_config['executionTime']))
		{
			ini_set('max_execution_time', $this->_config['executionTime']);
			set_time_limit($this->_config['executionTime']);
		}

		if (! empty($this->_config['uploadMaxSize']))
		{
			ini_set('post_max_size', $this->_config['uploadMaxSize']);
			ini_set('upload_max_filesize', $this->_config['uploadMaxSize']);
		}
	}

	if (! empty($this->_config['debug']))
	{
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
	}
}

// procesa una llamada a la API
function process()
{
	// ejecutar primera etapa del framework
	$input = array();
	if (($res = $this->_processStage1($input)) !== true) return $res;
	
	// ejecutar segunda etapa del framework
	$this->_processStage2($input);
}

protected function _processStage1(&$input)
{
	// verificamos si activar el log de consultas en fichero
	if (! empty($this->_config['dbLogFile'])) $this->_enaDbLogFile = true;
	
	// verificamos el modo mantenimiento
	if ($this->_checkMaintMode && ! empty($this->_config['maintMode']))
		return $this->_error('Modo mantenimiento', 3);
	
	// comprobamos que la sesión esté iniciado, salvo que el servicio no lo obligue
	if (($this->_userId = $this->_getSessionVar('userId')) !== null)
		$this->_userName = $this->_getSessionVar('userName');
	else if ($this->_checkSession)
		return $this->_error('Sesión cerrada', 1);
	
	// verificamos que el usuario tenga permiso sobre el servicio
	$this->_userType = $this->_getSessionVar('userType');
	if (is_array($this->_validUserTypes) && ! in_array($this->_userType, $this->_validUserTypes))
		return $this->_forbidden();
	
	// cargamos el arreglo de parámetros de entrada
	//TODO: check required and type
	if (! $this->_isCron && count($this->_input))
	{
		foreach($this->_input as $k => $i)
			$input[$k] = isset($_REQUEST[$k]) ? $_REQUEST[$k] : null;
	}
	
	// creamos la conexión con la BBDD
	try
	{
		$this->_dbh = new PDO('mysql:dbname='.$this->_config['dbName'].';host='.$this->_config['dbHost'], $this->_config['dbUser'], $this->_config['dbPass'], array(PDO::ATTR_PERSISTENT => $this->_config['dbPersistent']));
	} catch (PDOException $e)
	{
		die('DB CONNECTION ERROR');
	}
	if ($this->_logReq)
		$this->_logReqData[] = 'DB: db='.$this->_config['dbName'].', host='.$this->_config['dbHost'].', user='.$this->_config['dbUser'];
	
	// creamos el objeto para envío de emails
	if ($this->_config['smtpEnabled'])
	{
		$this->_smtp = new PHPMailer();
		$this->_smtp->IsSMTP();
		$this->_smtp->Host = $this->_config['smtpHost'];
		$this->_smtp->Port = $this->_config['smtpPort'];
		$this->_smtp->SMTPAuth = $this->_config['smtpAuth'];
		$this->_smtp->Username = $this->_config['smtpUser'];
		$this->_smtp->Password = $this->_config['smtpPass'];
		$this->_smtp->SMTPSecure = $this->_config['smtpSecure']; //TSL
		$this->_smtp->SetFrom($this->_config['smtpUser']);
		$this->_smtp->CharSet = 'utf-8';
		$this->_smtp->IsHTML = true;
		
		$this->_smtp2 = new PHPMailer();
		$this->_smtp2->IsSMTP();
		$this->_smtp2->Host = $this->_config['smtpHost'];
		$this->_smtp2->Port = $this->_config['smtpPort'];
		$this->_smtp2->SMTPAuth = $this->_config['smtpAuth'];
		$this->_smtp2->Username = $this->_config['smtpUser2'];
		$this->_smtp2->Password = $this->_config['smtpPass2'];
		$this->_smtp2->SMTPSecure = $this->_config['smtpSecure']; //TSL
		$this->_smtp2->SetFrom($this->_config['smtpUser2']);
		$this->_smtp2->CharSet = 'utf-8';
		$this->_smtp2->IsHTML = true;

		$this->_smtp3 = new PHPMailer();
		$this->_smtp3->IsSMTP();
		$this->_smtp3->Host = $this->_config['smtpHost'];
		$this->_smtp3->Port = $this->_config['smtpPort'];
		$this->_smtp3->SMTPAuth = $this->_config['smtpAuth'];
		$this->_smtp3->Username = $this->_config['smtpUser3'];
		$this->_smtp3->Password = $this->_config['smtpPass3'];
		$this->_smtp3->SMTPSecure = $this->_config['smtpSecure']; //TSL
		$this->_smtp3->SetFrom($this->_config['smtpUser3']);
		$this->_smtp3->CharSet = 'utf-8';
		$this->_smtp3->IsHTML = true;

		$this->_smtp4 = new PHPMailer();
		$this->_smtp4->IsSMTP();
		$this->_smtp4->Host = $this->_config['smtpHost'];
		$this->_smtp4->Port = $this->_config['smtpPort'];
		$this->_smtp4->SMTPAuth = $this->_config['smtpAuth'];
		$this->_smtp4->Username = $this->_config['smtpUser4'];
		$this->_smtp4->Password = $this->_config['smtpPass4'];
		$this->_smtp4->SMTPSecure = $this->_config['smtpSecure']; //TSL
		$this->_smtp4->SetFrom($this->_config['smtpUser4']);
		$this->_smtp4->CharSet = 'utf-8';
		$this->_smtp4->IsHTML = true;
	}
	
	// cargamos las variables globales
	foreach($this->_dbGetAll('web_variables_globales') as $v)
		$this->_global[$v['parametro']] = $v['valor'];

	if ($this->_logReq)
	{
		$this->_logReqData[] = 'Server Info: '.print_r($_SERVER, true);
		$this->_logReqData[] = 'Input: '.print_r($input, true);
		$this->_logReqData[] = 'User: id='.$this->_userId.', name='.$this->_userName.', type='.$this->_userType;
	}
	
	return true;
}

protected function _processStage2(&$input)
{
	// si el servicio tiene una lógica de validación, la ejecutamos, sino se procede a ejecutar el servicio
	if (! $this->_disabled && ($output = $this->_validateProcess($input)) === true)
		$output = $this->_process($input);

	// si el servicio lo tiene se ejecuta el post-procesamiento del servicio
	$this->_postProcess($input, $output);

	// se procesa el resultado del servicio
	if ($this->_isCron)
		$this->_saveLog($this->_cronId);
	else
		$this->_returnResult($output);
}

protected function _process($input)
{
	return array();
}

protected function _postProcess($input, &$output)
{
}

protected function _validateProcess($input)
{
	return true;
}

// guarda un dato en la sesión
protected function _saveSessionVar($name, $val)
{
	$_SESSION[$this->_sessionName][$name] = $val;
}

// obtiene un dato de la sesión
protected function _getSessionVar($name)
{
	return isset($_SESSION[$this->_sessionName][$name]) ? $_SESSION[$this->_sessionName][$name] : null;
}

// habilita/deshabilita read uncommitted
protected function _dbReadUncomm($on)
{
	$this->_readUncomm = $on;
}

// obtiene un registro de la BBDD
protected function _dbGetOne($table, $where = null, $columns = null, $orderby = null)
{
	$r = $this->_dbGetAll($table, $where, $columns, $orderby);
	if (isset($r[0])) return $r[0];
	return false;
}

// obtiene varios registros de la BBDD
protected function _dbGetAll($table, $where = null, $columns = null, $orderby = null)
{
	$query = 'SELECT ';

	if ($columns == null)
		$query .= '*';
	else
	{
		$select = array();

		foreach($columns as $k => $v)
			if (is_numeric($k))
				$select[] = '`'.$v.'`';
			else
				$select[] = '`'.$k.'` as `'.$v.'`';

		$query .= implode(', ', $select);
	}

	$query .= ' FROM '.$table;

	$params = array();

	if ($where !== null)
		$query .= ' WHERE '.$this->_dbGetWhere($where, $params);

	if ($orderby != null) $query .= ' ORDER BY '.$orderby;

	return $this->_dbQuery($query, $params);
}

// inserta un registro en la BBDD
protected function _dbInsert($table, $data)
{
	return $this->_dbAlter('INSERT INTO', $table, $data);
}

// actualiza un registro en la BBDD
protected function _dbUpdate($table, $data, $where = null)
{
	return $this->_dbAlter('UPDATE', $table, $data, $where);
}

// elimina un registro en la BBDD
protected function _dbDelete($table, $where = null)
{
	return $this->_dbAlter('DELETE FROM', $table, null, $where);
}

// ejecuta una consulta en la BBDD
protected function _dbQuery($query, $values = array(), $logInFile = null)
{
	try
	{
		$queryLog = 'Query: '.$query.', Values:'.print_r($values, true);
		
		if ($this->_logReq) $this->_logReqData[] = $queryLog;

		if ($this->_readUncomm)
		{
			$sth = $this->_dbh->prepare('SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED');
			$sth->execute();
			$this->_dbh->beginTransaction();
		}

		//~ print_r($values);
		$sth = $this->_dbh->prepare($query);
		if (! $sth->execute($values))
			die('DB ERROR: '.$queryLog.', error#='.$sth->errorCode().', errorData='.print_r($sth->errorInfo(), true));
		
		if ($this->_readUncomm) $this->_dbh->commit();
		
		$this->_dbInsertId = $this->_dbh->lastInsertId();
		$this->_dbRowCount = $sth->rowCount();
		$res = $sth->fetchAll(PDO::FETCH_ASSOC);
		if ($this->_logReq)
			$this->_logReqData[] = 'Results: '.print_r($res, true).', Insert ID: '.$this->_dbInsertId;
		if ($this->_enaDbLogFile)
		{
			$_query = trim($query);
			foreach(array('DELETE' => 'DELETE FROM'/*, 'INSERT' => 'INSERT INTO'*/, 'REPLACE' => 'REPLACE INTO', 'UPDATE' => 'UPDATE') as $k => $v)
				if (substr(strtoupper($_query), 0, strlen($v)) == $v)
				{
					$_logInFile = $k;
					$_query = trim(substr($_query, strlen($v)));
					break;
				}
			if (! empty($_logInFile))
			{
				if ($this->_dbLogFile === null)
				{
					if (empty($logInFile))
					{
						$logInFile = explode(' ', $_query);
						$logInFile = trim($logInFile[0]);
					}
					$path = 'logs/db/'.date('Ymd').'/'.	$logInFile.'-'.$_logInFile.'/';
					if (! file_exists($path)) mkdir($path, 0755, true);
					$file = $path.time().'.log';
					$this->_dbLogFile = fopen($file, 'c');
					fputs($this->_dbLogFile, '------ '.date('r').' ------'."\n".'Server Info: '.print_r($_SERVER, true)."\n".'Request: '.print_r($_REQUEST, true)."\n".'User: id='.$this->_userId.', name='.$this->_userName.', type='.$this->_userType);
				}
				
				fputs($this->_dbLogFile, "\n".$queryLog);
			}	
		}		
		return $res;
	}
	catch (Exception $e)
	{
		die('DB ERROR: '.$e->getMessage());
	}
}

// calcula paginación
protected function _dbPager(&$query, $params, $pg, $size = 20, $orderBy = '', $select = null, &$query2 = null)
{
	if (! $pg) $pg = 1;
	$res = array('page' => $pg);

	$q = 'SELECT '.($select == null ? 'COUNT(*) c' : $select).' '.$query;
	$r = $this->_dbQuery($q, $params);

	$res['count'] = ($select == null ? $r[0]['c'] : $this->_dbRowCount);

	$res['pages'] = ceil($res['count'] / $size);
	$res['next'] = ($pg == $res['pages'] ? 0 : $pg + 1);
	$res['prev'] = ($pg == 1 ? 0 : $pg - 1);

	$_q = $orderBy.' LIMIT '.(($pg - 1) * $size).','.$size;
	$query .= $_q;
	$query2 .= $_q;

	return $res;
}

// convierte una fecha DD/MM/YYYY a YYYY-MM-DD
protected function _toDate($date)
{
	list($d, $m, $y) = explode('/', $date);
	return $y.'-'.$m.'-'.$d;
}

// convierte una fecha  YYYY-MM-DD a DD/MM/YYYY
protected function _fromDate($date)
{
	list($y, $m, $d) = explode('-', substr($date, 0, 10));
	return $d.'/'.$m.'/'.$y;
}

// formatea un valor flotante
protected function _toNumber($float, $dec = 2, $noFraForInt = false)
{
	return number_format($float, $noFraForInt && $float - intval($float) == 0 ? 0 : $dec, ',', '.');
}

// procesa un error de permiso de acceso
protected function _forbidden()
{
	return $this->_error('Acceso denegado', 2);
}

// envía un email
protected function _sendmail($to, $subject, $body, $smtp = 1, $debug = false, $sendNow = false, &$info = null)
{
	if (! $this->_config['smtpEnabled']) return true;

	if ($this->_config['smtpBatch'] && ! $sendNow)
	{
		$this->_dbInsert('web_sendmail', array(
		'to' => $to,
		'subject' => $subject,
		'body' => $body,
		'smtp' => $smtp
		));

		return true;
	}

	switch($smtp)
	{
	case 1: $_smtp = $this->_smtp; break;
	case 2: $_smtp = $this->_smtp2; break;
	case 3: $_smtp = $this->_smtp3; break;
	case 4: $_smtp = $this->_smtp4; break;
	}

	if ($info !== null) $info = array('error' => 0, 'recps' => array());

	$_smtp->ClearAllRecipients();
	foreach(explode(',', $to) as $t)
	{
		$t = trim($t);
		if ($info !== null) $info['recps'][$t] = true;
		$_smtp->AddAddress($t, '');
	}

	$_smtp->Subject = $subject;
	$_smtp->IsHTML = true;
	$_smtp->MsgHTML($body);
	$_smtp->SMTPDebug = $debug;

	$res = $_smtp->Send();

	if ($debug) print_r($_smtp);

	if (! $res)
	{
		if ($info !== null)
		{
			$trans = $_smtp->getTranslations();
			if (substr($_smtp->ErrorInfo, 0, strlen($trans['recipients_failed'])) == $trans['recipients_failed'])
			{
				$info['error'] = 1;
				$matches = array();
				preg_match_all('/<(.*)>/', $_smtp->ErrorInfo, $matches);
				foreach($matches[1] as $m)
					$info['recps'][$m] = false;
			}
			else if (substr($_smtp->ErrorInfo, 0, strlen($trans['empty_message'])) == $trans['empty_message'])
				$info['error'] = 2;
		}
		return $_smtp->ErrorInfo;
	}

	return true;
}

// envía una notificación al soporte por email
protected function _sendmailSupport($subject, $body, $smtp = 1)
{
	return $this->_sendmail($this->_config['notifyEmail'], $subject, $body, $smtp);
}

// agrega un registro de log
protected function _log($id, $msg, $path = '')
{
	$path = 'logs/'.$path;
	if (! file_exists($path)) mkdir($path, 0755, true);
	$file = $path.$id.'.log';
	$fd = fopen($file, 'a');
	fputs($fd, '------ '.date('r').' ------'."\n".$msg."\n");
	fclose($fd);
}

// hash texto
protected function _hash($text)
{
	return password_hash($text, PASSWORD_DEFAULT);
	//return crypt($text, '$2y$10$'.str_replace('+', '.', base64_encode(mcrypt_create_iv(22, MCRYPT_DEV_URANDOM))).'$');
}

// validar hash
protected function _validateHash($text, $hash)
{
	return password_verify($text, $hash);
}

// descarga un fichero
protected function _downloadFile($file)
{
	if (! file_exists($file)) return false;
	$filename = basename($file);
	header('Content-Disposition: attachment; filename="'.$filename.'"');
	header('Content-Transfer-Encoding: binary');
	readfile($file);
	die;
}

// ejecuta una consulta de modificación en la BBDD
protected function _dbAlter($stat, $table, $data, $where = null)
{
	$query = $stat.' '.$table;
	$params = array();

	if ($data != null)
	{
		$sets = array();
		foreach($data as $k => $v)
		{
			$sets[] = '`'.$k.'` = ?';
			$params[] = $v;
		}
		$query .= ' SET '.implode(', ', $sets);
	}

	if ($where !== null)
		$query .= ' WHERE '.$this->_dbGetWhere($where, $params);

	return $this->_dbQuery($query, $params, $table);
}

// arma un WHERE para una consulta
protected function _dbGetWhere($where, &$params)
{
	$_where = array();

	foreach($where as $k => $v)
		if ($v === null)
			$_where[] = '`'.$k.'` IS NULL';
		else
		{
			$params[] = $v;
			$_where[] = '`'.$k.'` = ?';
		}

	return implode(' AND ', $_where);
}

// procesa un error
protected function _error($msg, $code = 0)
{
	return $this->_returnResult(array('error' => $msg, 'code' => $code));
}

// procesa un resultado
protected function _returnResult($res)
{
	if ($this->_logReq)
		$this->_logReqData[] = 'Output data: '.print_r($res, true);

	if (($output = json_encode($res)) === false)
		$output = 'ERROR: '.json_last_error_msg()."\n\n".'Output: '.print_r($res, true);

	if ($this->_logReq)
		$this->_logReqData[] = 'Output: '.$output;

	$req = basename($_SERVER['SCRIPT_FILENAME']);
	$req = substr($req, 0, strlen($req) - 4);

	$this->_saveLog('req-'.$req);

	if (! empty($this->_config['enableCrossDomain']))
		header('Access-Control-Allow-Origin: *');

	die($output);
}

// guardar los logs
protected function _saveLog($prefix)
{
	return;
	if ($this->_logReq)
	{
		$log = '';
		foreach($this->_logReqData as $l) $log .= "\n\n".$l;
		$this->_log($prefix.'-'.time(), $log, $prefix.'/');
	}
}

// realiza el login del usuario
protected function _loginUser($data)
{
	
}

// realizar un HTTP requests
protected function _httpReq($method, $url, $params = array())
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	if ($method == 'POST')
	{
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
	}
	$out = curl_exec($ch);
	curl_close($ch);
	return $out;
}
	
}

class FRAMEWORKCRON extends FRAMEWORK
{

protected $_cronId = 'cron';
protected $_isCron = true;
protected $_checkSession = false;

function __construct()
{
	if (! empty($_SERVER['REQUEST_METHOD'])) die;
	parent::__construct();
}

}
