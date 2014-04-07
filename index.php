<?php

// Подключить файл конфигурации SamsonPHP
require( 'config.php' );

// Запуска ядра SamsonPHP
s()
	->load( PHP_P.'resourcer' )									// Подключим модуль Управления ресурсами
	//->load( PHP_P.'activerecord' )								// Загрузим модуль для работы с БД
	->load( PHP_P.'compressor' )								// Подключим модуль Сворачиватель сайта
	->load( PHP_P.'less' )										// Подключим модуль Сворачиватель сайта
	->load( JS_P.'core' ) 										// Подключим модуль SamsonJS		
	->load( JS_P.'md5' ) 										// Загрузим модуль SamsonJS: SJSMD5
	->load( JS_P.'lightbox/') 									// Загрузим модуль SamsonJS: LightBox
	->load( JS_P.'tinybox' ) 									// Загрузим модуль SamsonJS: SJSMD5	
	//->load( PHP_P.'minify' )									// Подключим модуль минификации ресурсов
	->load( PHP_P.'deploy' )									// Подключим модуль Сворачиватель сайта
	->load( PHP_P.'i18n' )	
	->start( 'main' ); 											// Запустим ядро системы
