<?php
class Model {
	protected static $table_name;

	// Возвращает одну запись, найденную по id
	public static function getById($id) {
		$db = Database::getConnection();
		$stm = $db->prepare("SELECT * FROM ".static::$table_name." WHERE id=:id");
		$stm->bindValue(":id", $id, SQLITE3_INTEGER);
		return $stm->execute()->fetchArray();
	}

	// Собирает все записи из таблицы
	public static function all() {
		$db = Database::getConnection();
		return $db->query("SELECT * FROM ".static::$table_name);
	}

	// Собирает записи по условию
	public static function where($field, $value) {
		$db = Database::getConnection();
		$stm = $db->prepare("SELECT * FROM ".static::$table_name." WHERE $field=:$field");
		if (!$stm) {
			// Скорее всего такого поля нет
			return false;
		}
		$stm->bindValue(":$field", $value);
		return $stm->execute();
	}

	// Создаёт запись в таблице
	public static function create($fields) {
		$db = Database::getConnection();

		// Составление SQL-запроса
		$existing_fields = array_keys($fields);
		$field_names = implode(',', $existing_fields);
		$field_names_prepare = ':'.implode(',:', $existing_fields);
		//$field_names_prepare = implode(',', array_fill(0, count($fields), '?'));
		$sql_request = "INSERT INTO ".static::$table_name." ($field_names) VALUES ($field_names_prepare)";
		$stm = $db->prepare($sql_request);

		// Привязка параметров
		foreach ($fields as $key => $value) {
			$stm->bindValue(":$key", $value);
		}

		$stm->execute();
		return $db->lastInsertRowID;
	}
}
