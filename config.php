<?php
// Файл конфигурации
require_once("vendor/autoload.php");
require_once(__DIR__."/class/Database.php");

define('vk_api_endpoint', "https://api.vk.com/method/");

// Загрузка .env переменных
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

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