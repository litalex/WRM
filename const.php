<?
/* ***/
        
//date_default_timezone_set('Etc/GMT-4');
date_default_timezone_set('Etc/GMT-3');
setlocale(LC_ALL, 'ru_RU');
ini_set("memory_limit", "512M"); // установка размера памяти для скрипта

error_reporting(E_ALL);             

define( 'CACHE_TIME', 300 );  // 300 секунд (5 минут)
define( 'ENVIROMENT', 'dev' );

define( 'SQLCONNECTSTRING', 'uid=UID;pwd=PWD;eng=ENG;CS=cp1251;links=tcpip{ip=IP:PORT}' );

define( 'SITEADDRESS', 'ADRESS' ); 
?>
