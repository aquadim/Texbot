<?php
class TeacherScheduleModel extends Model {
	protected static $table_name = "teacher_schedule_cache";

	public static function create($day, $teacher_id, $photo) {
		$db = Database::getConnection();
		$stm = $db->prepare("INSERT INTO teacher_schedule_cache (day, photo, teacher_id) VALUES(?, ?, ?)");
		$stm->bind_param('ssi', $day, $photo, $teacher_id);
		$stm->execute();
	}

	// Возвращает кэшированное расписание преподавателя
	public static function getCached($date, $teacher_id) {
		$db = Database::getConnection();
		$stm = $db->prepare("SELECT photo FROM teacher_schedule_cache WHERE day=? AND teacher_id=?");
		$stm->bind_param("si", $date, $teacher_id);
		$stm->execute();
		return $stm->get_result()->fetch_array();
	}
}
