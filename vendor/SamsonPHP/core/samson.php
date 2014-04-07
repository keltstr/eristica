<?php
/**
 * Пусковой файл SamsonPHP
 * @package SamsonPHP
 * @author Vitaly Iegorov <vitalyiegorov@gmail.com>
 * @version 5.0.0
 */

// Константы фреймворка SamsonPHP
require('constants.php');

// Установим временную локаль
date_default_timezone_set(date_default_timezone_get() );

// Установим ограничение на выполнение скрипта
set_time_limit( __SAMSON_MAX_EXECUTION__ );

// Установим ограничение на выполнение скрипта
ini_set( 'max_execution_time', __SAMSON_MAX_EXECUTION__ );

//echo microtime(TRUE) - __SAMSON_T_STARTED__;

// Начать сессию
session_start();

// Remove unnessesar files umask
$old_umask = umask(0);

//print_R($_SESSION);

//echo microtime(TRUE) - __SAMSON_T_STARTED__;

// Подключение основных файлов фреймворка
require('include.php');

//
// Функции шорткаты(Shortcut) - для красоты и простоты кода системы
//


/**
 * System(Система) - Получить объект для работы с ядром системы
 * @return samson\core\Core Ядро системы
 */
function & s()
{
	// т.к. эта функция вызывается очень часто - создадим статическую переменную
	static $_v; 
	
	// Если переменная не определена - получим единственный экземпляр ядра	
	if( ! isset($_v) )
	{		
		// Если существует отпечаток ядра, загрузим его
		if( isset( $GLOBALS["__CORE_SNAPSHOT"]) ) 
		{			
			$_v = unserialize(base64_decode($GLOBALS["__CORE_SNAPSHOT"]));			
		}
		// Создадим экземпляр
		else $_v = new samson\core\Core();		
	}
		
	// Вернем указатель на ядро системы
	return $_v; 
}

/**
 * Error(Ошибка) - Получить класс для работы с ошибками и отладкой системы
 *
 * @return \samson\core\Error Класс для работы с ошибками и отладкой системы
 */
function & error(){static $_error; return ( $_error = isset($_error) ? $_error : new \samson\core\Error());}

// Создадим экземпляр класса
error();

/**
 * Module(Модуль) - Получить Текущий модуль / Модуль по имени из стека модулей системы
 * @see iCore::module();
 * 
 * @param mixed $module Указатель на модуль системы * 
 * @return \samson\core\Module Текущую / Модель по её имени / или FALSE если модель не найдена
 */
function & m( $module = NULL )
{ 
	// т.к. эта функция вызывается очень часто - создадим статическую переменную
	static $_s; 
	
	// Если переменная не определена - получим единственный экземпляр ядра
	if( !isset($_s)) $_s = & s();
	
	// Вернем указатель на модуль системы
	return $_s->module( $module );	
}

/**
 * Module Out - получить специальный виртуальный модуль ядра системы "LOCAL" для генерации и вывода
 * промежуточных представлений, что бы избежать затирания/изменения контекста текущего модуля
 * системы. Эта функция является шорткатом вызова m('local')
 * 
 * @see m()
 * @deprecated Use just m() thank's to new rendering model
 * @return iModule Указатель на виртуальный модуль "LOCAL" ядра системы
 */
function & mout(){ return m();  }

/**
 * View variable( Переменная представления ) - Вывести значение переменной представления
 * текущего модуля системы в текущий поток вывода.
 * 
 * @see iModule
 * @see iCore::active()
 * 
 * Это дает возможность использовать функцию в представлениях для более компактной записи:
 * <code><?php v('MODULE_VAR')?></code>
 * 
 * Для возравщения значения переменной, без её вывода в поток, необходимо использовать
 * 	Для переменный представления:
 * 		m( MODEL_NAME )->set( VAR_NAME ) либо $VIEW_VAR_NAME
 * 	Для переменных модуля:
 * 		m( MODEL_NAME )->VAR_NAME;
 * 
 * @param string $name 	Имя переменной представления текущего модуля 
 */
function v( $name, $realName = NULL )
{ 
	// Получим указатель на текущий модуль
	$m = & m();
	
	// Если передана ПРАВДА - используем первый параметр как имя
	if( is_bool( $realName ) && ($realName === true)) $realName = '__dm__'.$name;
	else $realName = '__dm__'.$realName;
	
	// Если задано специальное значение - выведем его
	if( isset($realName) && $m->offsetExists( $realName ))echo $m[ $realName ];	
	// Если дополнительный параметр не задан и у текущего модуля задана требуемое
	// поле - выведем его значение в текущий поток вывода
	else if ( $m->offsetExists( $name )) echo $m[ $name ];
	// Otherwise just output 	
	else echo $name;
}

/** 
 * IV(If view variable) - output view variable only if is correctly set for view output * 
 */
function iv( $name, $realName = NULL )
{
	// Cache current module pointer
	$m = & m();
	
	// If view variable is set - echo it
	if ( isvalue( $m, $name) ) echo $m[ $name ];	
}



/**
 * View variable for Input( Переменная представления ) - Вывести значение переменной представления
 * текущего модуля системы в текущий поток вывода c декодирования HTML символов.
 * 
 * Используется для HTML полей ввода 
 * 
 * @see v() 
 * @param string $name 	Имя переменной представления текущего модуля 
 */
function vi( $name ){ $m = & m();  if( $m->offsetExists( $name )) echo htmlentities($m[ $name ], ENT_QUOTES,'UTF-8');}
	
/**
 * Figure out if module view variable value is correctly set for view output
 * 
 * @param \samson\core\iModule	$m		Pointer to module
 * @param string 	$name 	View variable name
 * @param mixed 	$value	Value to compare
 * @return boolean If view variable can be displayed in view
 */
function isvalue( $m, $name, $value = null )
{	
	// If we have module view variable
	if( isset($m[ $name ]) )
	{
		// Get value
		$var = $m[ $name ];
		
		//trace($name.'-'.$var.'-'.gettype( $var ).'-'.$value);
	
		// Get variable type
		switch( gettype( $var ) )
		{
			// If this is boolean and it matches $value
			case 'boolean': return (isset($value) ? $var == $value : $var);
			// If this is number and it matches $value
			case 'integer': return (isset($value) ? $var === intval($value): false);
			// If this is double and it matches $value
			case 'double':  return (isset($value) ? $var === doubleval($value): false);
			// If this is not empty array
			case 'array':   return sizeof($var); 			
			// If this is a string and it matches $value or if no $value is set string is not empty
			case 'string':  return  (!isset($value) && isset($var{0})) || 
									(isset($value) && $var === strval($value)) ; 
			// If this is an object consider it as ok
			case 'object': return true;
			// Not supported for now
			case 'NULL':
			case 'unknown type': 
			default: return false;
		}		
	}
}

/**
 * Is Value( Является ли значением) - Проверить является ли переменная представления 
 * указанным значением. Метод проверяет тип переменной и в зависимости от этого проверяет 
 * соответствует ли переменная представления заданному значению:
 *  - проверяется задана ли переменная вообще
 *  - если передана строка то проверяется соответствует ли она заданному значению
 *  - если передано число то проверяется равно ли оно заданому значению 
 *  
 * Все сравнения происходят при преобразовании входного значения в тип переменной
 * представления. 
 * 
 * По умолчанию выполняется сравнение значения переменной представления 
 * с символом '0'. Т.к. это самый частый вариант использования когда необходимо получить значение
 * переменной объекта полученного из БД, у которого все поля это строки, за исключением
 * собственно описанных полей.
 * 
 * @param string 	$name 		Module view variable name
 * @param mixed 	$value 		Value for checking
 * @param string	$success	Value for outputting in case of success  
 * @param string	$failure	Value for outputting in case of failure 
 * @param boolean	$inverse	Value for outputting in case of success
 * @return boolean Соответствует ли указанная переменная представления переданному значению
 */
function isval( $name, $value = null, $success = null, $failure = null, $inverse = false)
{	
	// Pointer to current module
	$m = & m();
	
	// Flag for checking module value
	$ok = isvalue( $m, $name, $value );

	// If inversion is on
	if( $inverse ) $ok = ! $ok;
	
	// If we have success value - output it
	if( $ok && isset( $success) ) v($success);
	// If we have failure value - output it
	else if( isset($failure)) v($failure);
	
	return $ok;
}

/**
 * Is Variable exists, also checks:
 *  - if module view variable is not empty array
 *  - if module view variable is not empty string
 *
 * @param string 	$name Имя переменной для проверки
 * @param string	$success	Value for outputting in case of success
 * @param string	$failure	Value for outputting in case of failure    
 * @return boolean True if variable exists
 */
function isv( $name, $success = null, $failure = null ){ return isval($name, null, $success, $failure ); }

/**
 * Is Variable DOES NOT exists, also checks:
 *  - if module view variable is empty array
 *  - if module view variable is empty string
 *
 * @param string 	$name Имя переменной для проверки
 * @param string	$success	Value for outputting in case of success
 * @param string	$failure	Value for outputting in case of failure 
 * @return boolean True if variable exists
 */
function isnv( $name, $success = null, $failure = null ){ return isval( $name, null, $success, $failure, true ); }

/**
 * Is NOT value - checks if module view variable value does not match $value 
 * 
 * @param string 	$name 	 Module view variable name
 * @param mixed 	$value 	 Value for checking
 * @param string	$success Value for outputting in case of success
 * @param string	$failure Value for outputting in case of failure   
 * @return boolean True if value does NOT match
 */
function isnval( $name, $value = null, $success = null, $failure = null ){ return isval($name, $value, $success, $failure, true ); }

/**
 * Echo HTML link tag with text value from module view variable
 *  
 * @param string $name	View variable name
 * @param string $href	Link url
 * @param string $class	CSS class
 * @param string $id	HTML identifier
 * @param string $title	Title tag value
 */
function vhref( $name, $href = null, $class = null, $id = null,  $title = null )
{
	$m = & m();
	
	// If value can be displayed
	if( isvalue( $m, $name ) || isvalue( $m, $href ) )
	{		
		$name = isset( $m[ $name ] ) ? $m[ $name ] : $name;
		
		$href = isset( $m[ $href ] ) ? $m[ $href ] : $href;
		
		echo '<a id="'.$id.'" class="'.$class.'" href="'.$href.'" title="'.$title.'" >'.$name.'</a>';
	}
}

/**
 * Render IMG html tag
 * @param string $src 	Module view variable name to parse and get path to image
 * @param string $id	Image identifier
 * @param string $class	Image CSS class
 * @param string $alt	Image alt text
 * @param string $dummy	Dummy image path not set * 
 */
function vimg( $src, $id='', $class='', $alt = '', $dummy = null )
{
	// Закешируем ссылку на текущий модуль
	$m = & m();

	// Проверим задана ли указанная переменная представления в текущем модуле
	if( $m->offsetExists( $src )) $src = $m[ $src ];
	//
	elseif( isset($dummy))$src = $dummy;

	// We always build path to images fully independant of web-application or module relatively to base web-app
	if( $src{0} != '/' ) $src = '/'.$src;
		
	// Выведем изображение	
	echo '<img src="'.$src.'" id="'.$id.'" class="'.$class.'" alt="'.$alt.'" title="'.$alt.'">';
}

/**
 * Output view variable as date with formating
 * If view variable exists then output it as date with formatting
 *
 * @param string $name 	 Module view variable name
 * @param string $format Date format string
 * @param string $function Function callback to render date
 */
function vdate( $name, $format = 'h:i d.m.y', $function = 'date' )
{
	// Cache current module
	$m = & m();

	// If view variable is set - echo with format
	if ( $m->offsetExists( $name )) echo $function( $format, strtotime($m[ $name ]));
	
}

/**
 * Is Module ( Является ли текущий модуль указанным ) - Проверить совпадает ли имя текущего модуля с указанным 
 * 
 * @param string $name Имя требуемого модуля для сравнения с текущим 
 * @return boolean Является ли имя текущего модуля равным переданному
 */
function ism( $name ){ return (m()->id() == $name); };

/**
 * Error(Ошибка) - Зафиксировать ошибку работы системы
 * 
 * @param string 	$error_msg	Текст ошибки
 * @param numeric 	$error_code	Код ошибки
 * @param mixed 	$args		Специальные "жетоны" для вставки в текст ошибки
 * @param mixed 	$ret_val	Value that must be returned by the function
 * @return boolean FALSE для остановки работы функции или условия
 */
function e( $error_msg = '', $error_code = E_USER_NOTICE, $args = NULL, & $ret_val = false )
{	
	// Сохраним указатель на класс в память
	static $_e; 
	
	// Получим ошибку			
	$_e = isset( $_e ) ? $_e : error();
	
	// Если передан только один аргумент то сделаем из него массив для совместимости
	$args = is_array( $args ) ? $args : array( $args );
	
	// "Украсим" сообщение об ошибке используя переданные аргументы, если они есть
	if( isset( $args ) ) $error_msg = debug_parse_markers( $error_msg, $args );
	
	// Вызовем обработчик ошибки, передадим правильный указатель
	error()->handler( $error_code, $error_msg, NULL, NULL, NULL, debug_backtrace() );

	return $ret_val;
}

/**
 * Получить содержание представления.
 *
 * Данный метод выполняет вывод ПРЕДСТАВЛЕНИЯ(Шаблона) с подключением
 * переменных и всей логики ядра системы в отдельный буферезированный поток вывода. Нужно не путать с методом
 * которой подключает обычные PHP файлы, т.к. этот метод отвечает только за вывод представлений.
 *
 * Так же при выводе представления учитываются все установленные пути приложения.
 *
 * @param string $view 			Путь к представлению для вывода
 * @param string $vars 			Коллекция переменных которые будут доступны в выводимом представлении
 * @param string $prefix 		Дополнительный префикс который возможно добавить к именам переменных в представлении
 *
 * @see iCore::import
 *
 * @return string Содержание представления
 */
function output( $view, array $vars = NULL, $prefix = NULL )
{		
	return s()->render($view,$vars);	
}

/**
 * URL(УРЛ) - Получить объект для работы с URL
 * @return samson\core\URL Объект для работы с URL
 */
function & url(){ static $_v; return ( $_v = isset($_v) ? $_v : new \samson\core\URL()); }

/**
 * Build URL relative to current web-application, method accepts any number of arguments,
 * every argument starting from 2-nd firstly considered as module view parameter, if no such
 * parameters is found - its used as string.
 *
 * If current locale differs from default locale, current locale prepended to the beginning of URL
 *
 * @see URL::build()
 *
 * @return string Builded URL
 */
function url_build()
{
    // Get cached URL builder for speedup
	static $_v;
	$_v = isset($_v) ? $_v : url();

    // Get passed arguments
	$args = func_get_args();

    // If we have current locale set
    if (\samson\core\SamsonLocale::current() != \samson\core\SamsonLocale::DEF) {
        // Add locale as first url parameter
        array_unshift($args, \samson\core\SamsonLocale::current());
    }

    // Call URL builder with parameters
	return call_user_func_array( array( $_v, 'build' ), $args );
}

/** 
 * Echo builded URL from passed parameters
 * @see url_build 
 */
function url_base()
{
    $args = func_get_args();

    // Call URL builder and echo its result
    echo call_user_func_array('url_build', $args);
}

/**
 * Echo builded URL from passed parameters, prepending first parameter as current module identifier
 * @see url_build()
 */
function module_url()
{
    $args = func_get_args();

	echo call_user_func_array('url_build', array_merge(array(url()->module), $args));
}

/**
 * Установить все доступные локализации для текущего веб-приложения.
 * Локализацию по умолчанию, передавать не нужно, т.к. она уже включена в список
 * и описана в <code>SamsonLocale::DEF</code>
 *
 * Достуные локализации передаются в функцию в виде обычных аргументов функции:
 * Для исключения ошибок рекомендуется передавать константы класса SamsonLocale
 * <code>setlocales( SamsonLocale::UA, SamsonLocale::EN )</code>
 *
 * @see SamsonLocale::set()
 */
function setlocales(){
    $args = func_get_args();
    \samson\core\SamsonLocale::set($args);
}

/**
 * Установить/Получить текущую локализацию сайта
 *
 * @see SamsonLocale::current()
 * @param string $locale Значение локали
 * @return string Возвращает текущее значение локали сайта до момента вызова метода
 */
function locale( $locale = NULL ){ return \samson\core\SamsonLocale::current( $locale ); }

/**
 * Check if passed locale alias matches current locale and output in success
 * @param string $locale Locale alias to compare with current locale
 * @param string $output Output string on success
 * @return boolean True if passed locale alias matches current locale
 */
function islocale( $locale, $output = '' ){ if( \samson\core\SamsonLocale::$current_locale == $locale) { echo $output; return true; } return false; }

/** 
 * Build string with locale to use in URL and file path
 * @param string $l Locale name to use, if not passed - current locale is used
 * @return string locale path if current locale is not default locale
 */
function locale_path( $l = null )
{ 
	// If no locale is passed - get current locale	
	$l = !isset($l) ? locale() : $l;
	 
	// Build path starting with locale 
	return ($l != \samson\core\SamsonLocale::DEF)? $l.'/' : '';
}

/**
 * Сформировать правильное имя класса с использованием namespace, если оно не указано
 * Функция нужна для обратной совместимости с именами классов без NS
 * 
 * @param string $class_name Имя класса для исправления
 * @param string $ns		 Пространство имен которому принадлежит класс
 * @return string Исправленное имя класса
 */
function ns_classname( $class_name, $ns = 'samson\activerecord' )
{		
	// If core rendering model is NOT array loading
	if( s()->render_mode != samson\core\iCore::RENDER_ARRAY )
	{ 
		return ( strpos($class_name, __NS_SEPARATOR__) !== false ) ? $class_name : $ns.__NS_SEPARATOR__.$class_name;
	}
	// Array loading render model
	else 
	{
		// If first char is namespace - remove it
		if($class_name{0} == '\\') $class_name = substr( $class_name, 1 );
				
		// If class exists - return it
		if( class_exists( $class_name )) return $class_name;
		// If classname with namespaces passed - transform it
		else if( strpos($class_name, __NS_SEPARATOR__) !== false ) 
		{
			// Get only namespace from classname
			$ns = trim(nsname($class_name));		
			
			// Transform namespace and class name
			return str_replace('\\', '_', $ns).'_'.classname( $class_name );
		}
		// Add namespace to class name and transform it
		else return str_replace('\\', '_', $ns).'_'.$class_name;
	}	
}

/**
 * Tranform classname with namespace to universal form
 * @param string $class_name Classname for transformation
 * @return mixed Transformed classname in universal format
 */
function uni_classname( $class_name )
{
	return trim(str_replace('\\', '_',strtolower($class_name)));	
}

//elapsed('core included');