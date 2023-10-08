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
		return $stm->get_result();
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
		$result = $stm->execute();

		// Проверяем есть ли результат
		if ($result->fetchArray()) {
			$result->reset();
			return $result;
		} else {
			return false;
		}
	}

	// Создаёт запись в таблице
	public static function create($fields) {
		$db = Database::getConnection();

		// Составление SQL-запроса
		$existing_fields = array_keys($fields);
		$field_names = implode(',', $existing_fields);
		$field_names_prepare = ':'.implode(',:', $existing_fields);
		$sql_request = "INSERT INTO ".static::$table_name." ($field_names) VALUES ($field_names_prepare)";
		$stm = $db->prepare($sql_request);

		// Привязка параметров
		foreach ($fields as $key => $value) {
			$stm->bindValue(":$key", $value);
		}

		$stm->execute();
		return $db->lastInsertRowID();
	}

	// Сохраняет данные одной строки, принимая массив одной строки
	public static function save($object) {
		$db = Database::getConnection();

		$stm = $db->prepare(static::$update_string);
		foreach ($object as $field=>$value) {
			$stm->bindValue(":$field", $value);
		}
		$stm->execute();
	}
}
