<?php

// Подключить файл конфигурации SamsonPHP
require( 'config.php' );

// Запуска ядра SamsonPHP
s()
	->load( PHP_P.'resourcer' )									// Подключим модуль Управления ресурсами	
	->load( PHP_P.'compressor' )								// Подключим модуль Сворачиватель сайта
	->load( PHP_P.'less' )										// Подключим модуль Сворачиватель сайта
	->load( JS_P.'core' ) 										// Подключим модуль SamsonJS			
	->load( PHP_P.'minify' )									// Подключим модуль минификации ресурсов
	->load( PHP_P.'deploy' )									// Подключим модуль Сворачиватель сайта	
	->start( 'main' ); 											// Запустим ядро системы
