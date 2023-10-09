<?php
class TeacherModel extends Model {
	protected static $table_name = "teachers";

	// Возвращает данные преподавателя по его фамилии
	public static function getBySurname($surname) {
		$db = Database::getConnection();
		$stm = $db->prepare("SELECT * FROM teachers WHERE surname=?");
		$stm->bind_param("s", $surname);
		$stm->execute();
		return $stm->get_result()->fetch_array();
	}
}
