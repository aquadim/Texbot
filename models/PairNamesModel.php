<?php
// Модель названий пар
/*
 * name - Название пары
 */

class PairNamesModel extends Model {
	protected static $table_name = "pair_names";

	// Возвращает id по названию. Если не обнаружено - создаётся
	public static function getByName($name) : int {
		$db = Database::getConnection();
		
		// Проверяем есть ли уже расписание с такими группой и днём
		$check_stm = $db->prepare("SELECT id FROM pair_names WHERE name=?");
		$check_stm->bind_param("s", $name);
		$check_stm->execute();
		$result = $check_stm->get_result()->fetch_array();
		if ($result != false) { // Такое название уже есть
			return $result["id"];
		}

		// Названия нет -- создаём
		$stm = $db->prepare("INSERT INTO pair_names (name) VALUES (?)");
		$stm->bind_param("s", $name);
		$stm->execute();
		return $stm->insert_id;
	}
}
