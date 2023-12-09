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
			SET state=?, type=?, question_progress=?, allows_mail=?, gid=?, journal_login=?, journal_password=?, teacher_id=?
			WHERE id=?"
		);
		$stm->bind_param(
			"iiiiissii",
			$user['state'],
			$user['type'],
			$user['question_progress'],
			$user['allows_mail'],
			$user['gid'],
			$user['journal_login'],
			$user['journal_password'],
			$user['teacher_id'],
			$user['id']
		);
		$stm->execute();
	}

	// Получает все количества двоек по предметам
	public static function getNegativeGradesCount(int $user_id, int $period_id) {
		$db = Database::getConnection();
		$stm = $db->prepare("SELECT discipline, count FROM negative_grades WHERE user_id=? AND period_id=?");
		$stm->bind_param("ii", $user_id, $period_id);
		$stm->execute();
		$result = $stm->get_result();
		$output = [];
		while ($row = $result->fetch_array()) {
			$output[$row['discipline']] = $row['count'];
		}
		return $output;
	}

	// Сохраняет количества двоек
	public static function saveNegativeGradesCount(int $user_id, $data, int $period_id) : void {
		$db = Database::getConnection();

		$stm_clean = $db->prepare("DELETE FROM negative_grades WHERE user_id=? AND period_id=?");
		$stm_add = $db->prepare("INSERT INTO negative_grades (user_id, discipline, count, period_id) VALUES(?,?,?,?)");
		$stm_set_checked = $db->prepare("UPDATE users SET checked_grades_this_iteration=1 WHERE id=?");

		// Удаляются предыдущие записи
		$stm_clean->bind_param("ii", $user_id, $period_id);
		$stm_clean->execute();

		// Заносятся новые
		foreach ($data as $discipline=>$neg_count) {
			$stm_add->bind_param("isii", $user_id, $discipline, $neg_count, $period_id);
			$stm_add->execute();
		}

		// И для пользователя устанавливается checked_grades_this_iteration
		$stm_set_checked->bind_param("i", $user_id);
		$stm_set_checked->execute();
	}
}
