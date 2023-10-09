<?php
class GroupModel extends Model {
	protected static $table_name = "groups";

	// Возвращает данные группы по курсу и названию (например: $course=3, $name=ИС)
	public static function getByParams($course, $name) {
		$db = Database::getConnection();
		$stm = $db->prepare("SELECT * FROM groups WHERE course=? AND spec=?");
		$stm->bind_param("is", $course, $name);
		$stm->execute();
		return $stm->get_result()->fetch_array();
	}
}
