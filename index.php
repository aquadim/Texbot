<?php
// Главный файл на который поступают запросы к Вадяботу
require_once(__DIR__."/config.php");

// Выполнение маршрутизации
$routes = array(
	"/" => ['BotController', 'handleRequest'],
	"/admin" => ['AdminController', 'index']
);

// Объявление общих переменных
$GEN_MONTH_NUM_TO_STR = [9=>"сентября",10=>"октября",11=>"ноября",12=>"декабря",1=>"января",2=>"февраля",3=>"марта",4=>"апреля",5=>"мая",6=>"июня",7=>"июля",8=>"августа"];

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
