<?php
// Класс для работы с БД
class Database {
	private static $db;
	private $connection;
	
	private function __construct() {
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
		$this->connection = new mysqli('localhost', $_ENV["db_user"], $_ENV["db_password"], $_ENV["db_name"]);
		if ($this->connection->connect_errno) {
			throw new RuntimeException('ошибка соединения mysqli: ' . $this->connection->connect_error);
		}
		$this->connection->set_charset('utf8mb4');
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