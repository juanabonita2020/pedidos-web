<?php if (empty($JBSYSWEBAPI)) die;

$api->config(array(

// core
'debug' => false,
'usageStats' => false,
'baseURL' => 'http://juanabonita.com.ar/pedidosweb/',
'notice' => '',

// notify
'notifyEmail' => 'ayudapedidosweb@juanabonita.com',
'notifyEmailRegister' => 'registracionweb@juanabonita.com',
'notifyBlockedZoneD' => 'cuentasdelis@juanabonita.com',
'notifyBlockedZoneE' => 'cuentas@juanabonita.com',
'notifyControlEnvios' => 'controlenvios@juanabonita.com',

// database
'dbHost' => 'localhost',
'dbName' => 'juana_pw',
'dbUser' => 'root',
'dbPass' => 'JUA2021DEV',
'dbPersistent' => false,
'dbLogFile' => true,

// SMTP server
'smtpHost' => 'mail.juanabonita.com',
'smtpPort' => '465',
'smtpAuth' => true,
'smtpSecure' => true,
'smtpEnabled' => true,
'smtpBatch' => true,

// SMTP server 1
'smtpUser' => 'registracionweb@juanabonita.com',
'smtpPass' => '5hdcgr84',

// SMTP server 2
'smtpUser2' => 'infopedidosweb@juanabonita.com',
'smtpPass2' => 'PED_2022_Dev',

// SMTP server 3
'smtpUser2' => 'infopedidosweb@juanabonita.com',
'smtpPass2' => 'PED_2022_Dev',

// SMTP server 4
'smtpUser4' => 'gestionesdeventas@juanabonita.com',
'smtpPass4' => 'c1zaf29r',

'sessionTime' => 28800, //session.gc_maxlifetime
'executionTime' => 300, //max_execution_time
'uploadMaxSize' => '32M', //post_max_size, upload_max_filesize
'enableCrossDomain' => true,
//'maintMode' => true,

'loginLideresURL' => 'http://sisweb.juanabonita.com/sis-web/backend/auth-user-sis-web.php'

));
