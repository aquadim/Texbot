<?php
// Модель оценок
/*
 * user_id - ID пользователя, запросившего оценки  
 * date_create - Дата создания записи  
 * photo_id - photo_id фотографии 
 */

class GradesModel extends Model {
	protected static $table_name = "grades";

	public static function create(int $user_id, string $photo_str) {
		$db = Database::getConnection();
		$stm = $db->prepare("INSERT INTO grades (user_id, photo) VALUES (?, ?)");
		$stm->bind_param("is", $user_id, $photo_str);
		$stm->execute();
		return $stm->insert_id;
	}

	// Возвращает самое недавнее photo для оценок пользователя
	public static function getRecent($user_id) {
		$db = Database::getConnection();
		$stm = $db->prepare("
			SELECT photo
			FROM grades
			WHERE user_id=? AND date_create > DATE_SUB(NOW(), INTERVAL 10 MINUTE)
			ORDER BY date_create DESC
			LIMIT 1;"
		);
		$stm->bind_param("i", $user_id);
		$stm->execute();
		return $stm->get_result()->fetch_array();
	}
}
