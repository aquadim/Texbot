<?php
// Класс для работы с БД
class Database {
	private static $db;
	private $connection;
	
	private function __construct() {
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
		$this->connection = new mysqli($_ENV["db_server"], $_ENV["db_user"], $_ENV["db_password"], $_ENV["db_name"]);
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