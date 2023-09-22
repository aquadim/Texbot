<?php
// index.php
// Главный файл на который поступают запросы к Вадяботу

if (!isset($_REQUEST)) {
	return;
}

require_once("vendor/autoload.php");
require_once("config.php");
require_once("api.php");
require_once("answers.php");

// Загрузка .env переменных
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Автозагрузка моделей
spl_autoload_register(function($classname) {
	if (preg_match('/Model$/', $classname)) {
		require_once __DIR__.'/models/'.$classname.'.php';
	}
});

// Класс для работы с БД
class Database {
    private static $db;
    private $connection;
    
    private function __construct() {
        $this->connection = new SQLite3(__DIR__.'/db.sqlite3');
    }

    function __destruct() {
        $this->connection->close();
    }

    public static function getConnection() {
        if (self::$db == null) {
            self::$db = new Database();
        }
        return self::$db->connection;
    }
}

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

		if (strlen($text) == 0) {
			// Нет текста в сообщении
			break;
		}

		// Получаем информацию о пользователе
		$user = UserModel::where("vid", $vid);
		if (!$user) {
			// Пользователь не зарегистрирован
			answerOnMeet($vid);
			UserModel::create([
				"vk_id" => $vid,
				"state" => 0
			]);
		}

		break;

	// TODO: message_deny
	// TODO: message_allow
}

echo "ok";
