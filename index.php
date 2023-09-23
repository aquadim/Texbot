<?php
// Главный файл на который поступают запросы к Вадяботу

require_once("vendor/autoload.php");
require_once(__DIR__."/config.php");
require_once(__DIR__."/database/Database.php");

// Загрузка .env переменных
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Выполнение маршрутизации
$routes = array(
	"" => ['BotController', 'handleRequest'],
	"/admin" => ['AdminController', 'index']
);

// Автозагрузка классов
spl_autoload_register(function($classname) {
	if (preg_match('/Model$/', $classname)) {
		require_once __DIR__.'/models/'.$classname.'.php';
	} else if (preg_match('/Controller$/', $classname)) {
		require_once __DIR__.'/controllers/'.$classname.'.php';
	}
});

if (preg_match('/^\/(?:css|fonts|img|video|js)\//', $_SERVER["REQUEST_URI"])) {
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
	// Ни один паттерн не подошёл, вызываем 404
	$controller = new NotFoundController();
	$controller->index();
}
