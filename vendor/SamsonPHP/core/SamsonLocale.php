<?php
namespace samson\core;

/**
 * Класс для поддержки локализации веб-приложения
 *
 * @author Vitaly Iegorov <vitalyiegorov@gmail.com>
 * @package SamsonPHP 
 * @version 0.1.0
 */
class SamsonLocale
{
	/** Локализация по умолчанию */
	const DEF = '';
	/** Украинская локализация */
	const UA = 'ua';
	/** Английская локализация */
	const EN = 'en';
	/** Русская локализация */
	const RU = 'ru';
	
	/**
	 * Коллекция поддерживаемых локализаций
	 * Используется для дополнительного контроля за локализациями
	 * @var array
	 */
	private static $supported = array( SamsonLocale::DEF, SamsonLocale::EN, SamsonLocale::UA, SamsonLocale::RU );
	
	/**
	 * Коллекция названий поддерживаемых локализаций
	 * @var array
	 */
	public static $alias = array( SamsonLocale::DEF=>'РУС', SamsonLocale::EN=>'ENG', SamsonLocale::UA=>'УКР', SamsonLocale::RU=>'РУС' );
	
	/**
	 * Текущая локализация веб-приложения
	 * @var string
	 */
	public static $current_locale = '';
	
	/**
	 * Коллекция подключенных локализаций для текущего веб-приложения
	 * Локаль по умолчанию RU, имеет представление пустышку - ''
	 * @var array
	 */
	public static $locales = array('');
	
	/**
	 * Проверить текущей значение установленной локали, и если выставлена
	 * не поддерживаемая локаль - установим локаль по умолчанию
	 */
	public static function check()
	{
		// Проверим значение установленной текущей локализации, если оно не входит в список доступных локализаций
		// установим локализацию по умолчанию
		if( ! in_array( strtolower(self::$current_locale), self::$locales ) ) self::$current_locale = self::DEF;
	}
	
	/**
	 * Установить все доступные локализации для текущего веб-приложения.
 	 * Локализацию по умолчанию, передавать не нужно, т.к. она уже включена в список
 	 * и описана в <code>SamsonLocale::DEF</code>
 	 * 
	 * Функция автоматически проверяет уже выставленное значение локализации веб-приложения
	 * и вслучаи его отсутствия, выставляет локализацию по умолчанию  
	 * 
	 * @param array $available_locales Коллекция с доступными локализациями веб-приложения
	 */
	public static function set( array $available_locales )
	{
		// Переберем локализации
		foreach ( $available_locales as $locale )
		{
			// Добавим в коллекцию доступных локализаций переданные
			if( in_array( strtolower($locale), self::$supported ) ) self::$locales[] = strtolower($locale);
			// Проверим разрешаемые локали
			else die('Устанавливаемая локализация "'.$locale.'" - не предусмотрена в SamsonLocale');		
		}			

		// Проверим значение установленной текущей локализации, если оно не входит в список доступных локализаций
		// установим локализацию по умолчанию
		self::check();
	}
	
	/**
	 * Получить все доступные локализации для текущего веб-приложения
	 */
	public static function get(){ return self::$locales; }
	
	/**
	 * Установить/Получить текущую локализацию веб-приложения
	 * @param string $locale Значение локализации веб-приложения для установки
	 * @return string 	Возвращается значение текущей локализации веб-приложения до момента 
	 * 					вызова данного метода 
	 */
	public static function current( $locale = NULL )
	{
		// Сохраним старое значение локали
		$_locale = self::$current_locale;	
		
		// Если ничего не передано - вернем текущее значение локали 
		if( !isset($locale) ) return $_locale;
		// Нам передано значение локали 
		else 
		{	
			// Только большой регистр
			$locale = strtolower( $locale );
			// Если требуется установть доступную локализацию
			//if( in_array( $locale, self::$locales ) ) self::$current_locale = $locale;
			// Установим локализацию по умолчанию
			//else self::$current_locale = SamsonLocale::DEF;
			self::$current_locale = $locale;
			
			// Запишем текущее значение локализации
			$_SESSION['__SAMSON_LOCALE__'] = self::$current_locale;					
			
			// Вернем текущее значение локали сайта до момента візова метода
			return $_locale;
		}	
	}
	
	/**
	 * Parse URL arguments
	 * @param array $args Collection of URL arguments
	 * @return boolean True if current locale has been changed
	 */
	public static function parseURL( array & $args )
	{		
		// Iterate defined site locales
		foreach ( self::$locales as $locale )
		{
			// Search locale string as URL argument
			if( ($key = array_search( $locale, $args )) !== FALSE )
			{
				// Change current locale
				self::current( $locale );
				
				// If this is not default locale(empty string) - remove it from URL arguments
				if( $locale != self::DEF )
				{
					// Remove argument contained locale string
					unset($args[ $key ]);
					
					// Reindex array
					$args = array_values($args);
				}
				
				// Return true status
				return true;				
			}			
		}
		
		return false; 
	} 
}
