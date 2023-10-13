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

	// Сохранение данных пользователя
	public static function save($user) : void {
		$db = Database::getConnection();
		$stm = $db->prepare("
			UPDATE users
			SET state=?, type=?, question_progress=?, allows_mail=?, gid=?, journal_login=?, journal_password=?, teacher_id=?"
		);
		$stm->bind_param(
			"iiiiissi",
			$user['state'],
			$user['type'],
			$user['question_progress'],
			$user['allows_mail'],
			$user['gid'],
			$user['journal_login'],
			$user['journal_password'],
			$user['teacher_id']
		);
		$stm->execute();
	}
}
