<?php

class Log {

    private $_startTime = 0;
    // private $_dir = "/var/www/html/logs_pw/backend";
    // private $_dir_svr = "/var/www/html/logs_pw/backend_server";
    private $_dir = "/home/juana/public_html/logs_pw/backend";
    private $_dir_svr = "/home/juana/public_html/logs_pw/backend_server";

    function __construct($_startTime) {
        $this->_startTime = $_startTime;
        
        $server = $_SERVER['DOCUMENT_ROOT'];
        //$server = "/home/juana/public_html";
        //pedidosweb.juanabonita.com.ar
        //"www.juanabonita.com.ar"
        if ($_SERVER['HTTP_HOST'] == 'pedidosweb.juanabonita.com.ar') {
            $server = "/home/juana/public_html";
        }
	
	 $server = "/home/juana/public_html";

        $this->_dir = $server . '/logs_pw/backend';
        $this->_dir_svr = $server . '/logs_pw/backend_server';
    }

    function loguear() {
        $startTime = $this->_startTime;
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = '';
        }
        if (isset($_SERVER['REQUEST_URI'])) {
            $url = $_SERVER['REQUEST_URI'];
        } else {
            $url = '';
        }
        $endTime = microtime(TRUE);
        $last = number_format($endTime - $startTime, 5);

        $dir = $this->_dir;

        $log_file = $dir . "/log_backend_" . date('Y-m-d') . ".txt";
        /* if (!ES_PRODUCCION) {
          $log_file = "/var/www/html/logs/test_log_backend_" . date('Y-m-d') . ".txt";
          }
         */

        if (!file_exists($dir) && !is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

//Create log file if it doesn't exist.
        if (!file_exists($log_file)) {
            fopen($log_file, 'w') or exit("Can't create $log_file!");
        }
//Check permissions of file.
        if (!is_writable($log_file)) {
//throw exception if not writable
//throw new Exception("ERROR: Unable to write to file!", 1);
            return;
        }


        $openFile = $log_file;
// 'a' option = place pointer at end of file
        $file = fopen($openFile, 'a') or exit("Can't open $openFile!");

        $strSep = '#.#';

        $strWrite = join($strSep, [$ip, $url, $startTime, $endTime, $last/* , $strVars */]);

        fwrite($file, $strWrite . PHP_EOL);
        fclose($file);
        if (!$ip) {
            Log::caca();
        }
    }

    static private function caca() {
        $val = json_encode($_SERVER);

        $dir = $this->_dir_svr;

        $log_file = $dir . "/log_backend_" . date('Y-m-d') . ".txt";
        //Create log file if it doesn't exist.

        if (!file_exists($dir) && !is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (!file_exists($log_file)) {
            $file = fopen($log_file, 'w') or exit("Can't create $log_file!");
            fclose($file);
        }

        if (!is_writable($log_file)) {
            return;
        }

        $openFile = $log_file;
        // 'a' option = place pointer at end of file
        $file = fopen($openFile, 'a') or exit("Can't open $openFile!");

        fwrite($file, $val . PHP_EOL);
        fclose($file);
    }

}
