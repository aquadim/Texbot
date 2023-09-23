<?php
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