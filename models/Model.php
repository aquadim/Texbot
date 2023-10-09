<?php
// Родительский класс моделей
class Model {
	protected static $table_name;

	// Возвращает одну запись, найденную по id
	public static function getById($id) {
		$db = Database::getConnection();
		$stm = $db->prepare("SELECT * FROM ".static::$table_name." WHERE id=?");
		$stm->bind_param("i", $id);
		$stm->execute();
		return $stm->get_result()->fetch_array();
	}

	// Собирает все записи из таблицы
	public static function all() {
		$db = Database::getConnection();
		return $db->query("SELECT * FROM ".static::$table_name);
	}
}
