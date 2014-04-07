<?php 
/** Файл конфигурации */

// Define path to SamsonPHP modules
if( !defined('PHP_P')) define('PHP_P', 'vendor/SamsonPHP/');
// Define path to SamsonJS modules
if( !defined('JS_P'))  define('JS_P', 'vendor/SamsonJS/');
// Define path to SamsonCMS modules
if( !defined('CMS_P')) define('CMS_P', '../../SamsonCMS/');

// Подключить фреймворк SamsonPHP
require( PHP_P.'core/samson.php');

// Установим локализации сайта
setlocales( 'en' );

/** Конфигурация DEV для ActiveRecord */
class ActiveRecordConfig extends \samson\core\Config
{
	public $__module = 'activerecord';

	public $name 	= 'playtoptv';
	public $login 	= 'samsonos';
	public $pwd 	= 'AzUzrcVe4LJJre9f';	
}

/** Config for compressor */
class CompressorConfig extends \samson\core\Config
{
    public $__module = 'compressor';

    public $output = '/var/www.final/playtop.tv/www/';
}
