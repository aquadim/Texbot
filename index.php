<?php
// Главный файл на который поступают запросы к Вадяботу

require_once("vendor/autoload.php");
require_once(__DIR__."/config.php");
require_once(__DIR__."/database/Database.php");

// Автозагрузка классов
spl_autoload_register(function($classname) {
	if (preg_match('/Model$/', $classname)) {
		require_once __DIR__.'/models/'.$classname.'.php';
	} else if (preg_match('/Controller$/', $classname)) {
		require_once __DIR__.'/controllers/'.$classname.'.php';
	} else if (preg_match('/View$/', $classname)) {
		require_once __DIR__.'/views/'.$classname.'.php';
	}
});

// Загрузка .env переменных
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Обработка ошибок //

// Отправляет отчёт об ошибке на почту
// TODO: сделать сохранение ошибки в админку
// TODO: отправить сообщение пользователю что произошла ошибка
function mailErrorReport($message, $file, $line, $trace) {
	$view = new ErrorReportView([
		"message" => $message,
		"file" => $file,
		"line" => $line,
		"trace" => $trace
	]);

	if ($_ENV["notifications_type"] == "email") {
		// email
		$report = $view->render();
		
	    $headers = "MIME-Version: 1.0\n";
	    $headers .= "From: Техбот <{$_ENV['notifier_email']}>\n";
	    $headers .= "Content-type: text/html; charset=utf-8\n";
	
		mail($_ENV["webmaster_email"], "Ошибка в Техботе", $report, $headers);

	} else if ($_ENV["notifications_type"] == "telegram") {
		// telegram
		$report = urlencode($view->plain());
		file_get_contents("https://api.telegram.org/bot{$_ENV['notifier_bot_token']}/sendMessage?chat_id={$_ENV['notifier_bot_chat']}&text=$report&parse_mode=html");
	}

	exit("ok");
}

// Callback-функция для set_exception_handler
function reportException(Throwable $e) {
	mailErrorReport($e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace());
	return true;
}

// Callback-функция для set_error_handler
function reportError(int $errno, string $errstr, string $errfile, int $errline) {
	mailErrorReport($errstr, $errfile, $errline, null);
	exit();
}

set_exception_handler("reportException");
set_error_handler("reportError", E_ALL);

// Выполнение маршрутизации
$routes = array(
	"/" => ['BotController', 'handleRequest'],
	"/admin" => ['AdminController', 'index']
);

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
}
