<?php
use samson\resourcer\ResourceRouter;

/**
 * Route(Маршрут) - Получить экземпляр класса для работы с маршрутами системы
 * @see ResourceRouter
 * @deprecated 
 * @return ResourceRouter Экземпляр класса для работы с маршрутами системы
 */
function & route(){	static $_v; return ( $_v = isset($_v) ? $_v : new ResourceRouter()); }

/**
 * SRC(Source) - Источник - сгенерирвать URL к ресурсу веб-приложения
 * Данный метод определяет текущее место работы веб-приложения
 * и строит УНИКАЛЬНЫЙ путь к требуемому ресурсу.
 *
 *  Это позволяет подключать CSS/JS/Image ресурсы в HTML/CSS не пережевая
 *  за их физическое месторасположение относительно веб-приложения, что
 *  в свою очередь дает возможность выносить модули(делать их внешними),
 *  а так же и целые веб-приложения.
 *
 * @param string $src 		Путь к ресурсу модуля
 * @param string $module 	Имя модуля которому принадлежит ресурс
 * @param string $return 	Флаг необходимо ли возвращать значение
 * @return string Возвращает сгенерированный адресс ссылки на получение ресурса для браузера
 */
function src( $src = '', $module = NULL ){ echo ResourceRouter::url( $src, $module );}