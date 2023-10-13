<?php
// Главный файл на который поступают запросы к Вадяботу
require_once(__DIR__."/config.php");

// Выполнение маршрутизации
$routes = array(
	"/" => ['BotController', 'handleRequest'],
	"/admin" => ['AdminController', 'index']
);

if (preg_match('/^\/(?:css|fonts|img|js)\//', $_SERVER["REQUEST_URI"])) {
	// Подаём без маршрутизации
	return false;
} else {
	// Ищем подходящий паттерн
	foreach ($routes as $route=>$callback) {
		$pattern = '/^'.str_replace('/', '\/', $route).'\/?((?:\?|\&)\w+=\w*)*$/';
		if (preg_match($pattern, $_SERVER["REQUEST_URI"])) {
			list($handler, $handle) = $callback;
			$h = new $handler($_SERVER["REQUEST_URI"]);
			$h->$handle();
			exit();
		}
	}
}
