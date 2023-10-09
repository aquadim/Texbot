<?php
class ScheduleModel extends Model {
	protected static $table_name = "schedules";

	public static function create(int $gid, string $day) {
		$db = Database::getConnection();

		// Проверяем есть ли уже расписание с такими группой и днём
		$check_stm = $db->prepare("SELECT id FROM ".static::$table_name." WHERE gid=? AND day=?");
		$check_stm->bind_param("is", $gid, $day);
		$check_stm->execute();
		$result = $check_stm->get_result();
		if ($result != false) { // Такое расписание уже есть
			// Очищаем его photo_id
			$db->query("UPDATE ".static::$table_name." SET photo_id=NULL");

			// Удаляем все пары, связанные с этим расписанием
			// Они наполнятся опять в schedule_parser.php
			$row = $result->fetchArray();
			$del_stm = $db->prepare("DELETE FROM pairs WHERE schedule_id=?");
			$del_stm->bind_param("i", $row["id"]);
			$del_stm->execute();

			return $row["id"];
		}

		$stm = $db->prepare("INSERT INTO ".static::$table_name." (gid, day) VALUES (?, ?)");
		$stm->bind_param("is", $gid, $day);
		$stm->execute();
		return $stm->insert_id;
	}
}
