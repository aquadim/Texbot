<?php
class UserModel extends Model {
	protected static $table_name = "users";

	// Возвращает данные группы по курсу и специальности
	public static function getByVkId($vk_id) {
		$db = Database::getConnection();
		$stm = $db->prepare("SELECT * FROM users WHERE vk_id=?");
		$stm->bind_param("i", $vk_id);
		$stm->execute();
		return $stm->get_result()->fetch_array();
	}

	// Создаёт запись пользователя
	public static function create($vk_id) : int {
		$db = Database::getConnection();
		$stm = $db->prepare("INSERT INTO users (vk_id) VALUES (?)");
		$stm->bind_param("i", $vk_id);
		$stm->execute();
		return $stm->insert_id;
	}
}
