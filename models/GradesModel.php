<?php
// Модель оценок
/*
 * user_id - ID пользователя, запросившего оценки  
 * date_create - Дата создания записи  
 * photo_id - photo_id фотографии 
 */

class GradesModel extends Model {
	protected static $table_name = "grades";

	public static function create(int $gid, string $day) {
		$db = Database::getConnection();

		// Проверяем есть ли уже расписание с такими группой и днём
		$check_stm = $db->prepare("SELECT id FROM schedules WHERE gid=? AND day=?");
		$check_stm->bind_param("is", $gid, $day);
		$check_stm->execute();
		$result = $check_stm->get_result()->fetch_array();
		if ($result != false) { // Такое расписание уже есть
			// Очищаем его photo_id
			$db->query("UPDATE schedules SET photo_id=NULL WHERE id=".$result['id']);

			// Удаляем все пары, связанные с этим расписанием
			// Они наполнятся опять в schedule_parser.php
			$db->query("DELETE FROM pairs WHERE schedule_id=".$result['id']);

			return $result["id"];
		}

		// Расписания нет -- создаём
		$stm = $db->prepare("INSERT INTO schedules (gid, day) VALUES (?, ?)");
		$stm->bind_param("is", $gid, $day);
		$stm->execute();
		return $stm->insert_id;
	}

	// Возвращает самое недавнее photo_id для оценок пользователя
	public static function getRecent($user_id) {
		$db = Database::getConnection();
		$stm = $db->prepare('
			SELECT photo_id
			FROM grades
			WHERE user_id=? AND date_create > datetime("now", "localtime", "-10 minute")
			ORDER BY date_create DESC
			LIMIT 1');
		$stm->bind_param("i", $user_id);
		$stm->execute();
		return $stm->get_result()->fetch_array();
	}
}
