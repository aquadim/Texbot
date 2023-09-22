<?php
// index.php
// Главный файл на который поступают запросы к Вадяботу

if (!isset($_REQUEST)) {
	return;
}

require_once("vendor/autoload.php");
require_once("config.php");
require_once("api.php");

// Загрузка .env переменных
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Обработка запроса
$data = json_decode(file_get_contents("php://input"));
switch ($data->type) {
	// Подтверждение сервера
	case "confirmation":
	exit($_ENV['confirmation_token']);
	break;

	// Новое входящее сообщение
	case "message_new":
	$vid = $data->object->message->from_id;
	$text = $data->object->message->text;
	sendMessage($vid, $text);
	break;

	// TODO: message_deny
	// TODO: message_allow
}

echo "ok";
