<?php
/**
 * SamsonCMS v 2.7
 * 
 * Модифицированный пусковой скрипт SamsonPHP
 * для подключения "удаленного" модуля SamsonCMS
 * 
 * @author Vitaly Iegorov <vitalyiegorov@gmail.com>  
 */

// Define path to SamsonPHP modules
if( !defined('PHP_P')) define('PHP_P', '../../../SamsonPHP/');
// Define path to SamsonJS modules
if( !defined('JS_P'))  define('JS_P', '../../../SamsonJS/');
// Define path to SamsonCMS modules
if( !defined('CMS_P')) define('CMS_P', '../../../SamsonCMS/');

// Подключить файл конфигурации SamsonPHP
require( '../config.php');

use \samson\core\Config;

// Конфигурации для модуля "Работы" SamsonsCMS
class ProductAppConfig extends Config 		{ protected $__module='product'; 		protected $__path = '../../ProductApp'; }
// Конфигурации для модуля "Материалы" SamsonsCMS
class MaterialAppConfig extends Config 	{ protected $__module='material';}
// Конфигурации для модуля "Навигация" SamsonsCMS
class StructureAppConfig extends Config	{ protected $__module='structure';}
// Конфигурации для модуля "Пользователи" SamsonsCMS
class UserAppConfig extends Config		{ protected $__module='user';}
// Конфигурации для модуля "Помощь" SamsonsCMS
class HelpAppConfig extends Config		{ protected $__module='help';}
// Конфигурации для модуля "Дополнительные поля" SamsonsCMS

// Подключить пусковой файл SamsonCMS
require( CMS_P.'main/index.php');	