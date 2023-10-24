<?php
// Модель расписаний
/*
 * gid - ID группы расписания 
 * day - Дата расписания 
 * photo_id - Значение photo_id кэшированного изображения расписания
 */

class ScheduleModel extends Model {
	protected static $table_name = "schedules";

	public static function create(int $gid, string $day) {
		$db = Database::getConnection();

		// Проверяем есть ли уже расписание с такими группой и днём
		$check_stm = $db->prepare("SELECT id FROM schedules WHERE gid=? AND day=?");
		$check_stm->bind_param("is", $gid, $day);
		$check_stm->execute();
		$result = $check_stm->get_result()->fetch_array();
		if ($result != false) { // Такое расписание уже есть
			// Очищаем его photo
			$db->query("UPDATE schedules SET photo=NULL WHERE id=".$result['id']);

			// Удаляем все пары, связанные с этим расписанием
			// Они наполнятся опять в schedule_parser.php
			$db->query("DELETE FROM pairs WHERE schedule_id=".$result['id']);

			// А так же кэшированные расписания преподавателей
			$stm = $db->prepare("DELETE FROM teacher_schedule_cache WHERE day=?");
			$stm->bind_param("s", $day);
			$stm->execute();

			// И кэшированные места кабинетов тоже
			$stm = $db->prepare("DELETE FROM occupancy_cache WHERE day=?");
			$stm->bind_param("s", $day);
			$stm->execute();

			return $result["id"];
		}

		// Расписания нет -- создаём
		$stm = $db->prepare("INSERT INTO schedules (gid, day) VALUES (?, ?)");
		$stm->bind_param("is", $gid, $day);
		$stm->execute();
		return $stm->insert_id;
	}

	// Возвращает актуальные ДАТЫ РАСПИСАНИЙ
	public static function getRelevantDates() : mysqli_result {
		$db = Database::getConnection();
		return $db->query("SELECT DISTINCT day FROM schedules WHERE day BETWEEN CURRENT_DATE AND CURRENT_DATE + 1");
	}

	// Возвращает данные для определённой группы и даты
	public static function getForGroup($date, $group) {
		$db = Database::getConnection();
		$stm = $db->prepare("SELECT id, photo FROM schedules WHERE day=? AND gid=?");
		$stm->bind_param('si', $date, $group);
		$stm->execute();
		return $stm->get_result()->fetch_array();
	}
	
	// Добавляет кэш расписанию
	public static function createCache($id, $photo) {
		$db = Database::getConnection();
		$stm = $db->prepare("UPDATE schedules SET photo=? WHERE id=?");
		$stm->bind_param('si', $photo, $id);
		$stm->execute();
	}
}
