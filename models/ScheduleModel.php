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
}
