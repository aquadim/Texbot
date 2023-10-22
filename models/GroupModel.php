<?php
// Модель групп техникума
/*
 * course - Курс группы
 * name - Шифр специальности группы
 */

class GroupModel extends Model {
	protected static $table_name = "groups";

	// Возвращает данные группы по курсу и специальности
	public static function getByParams($course, $name) {
		$db = Database::getConnection();
		$stm = $db->prepare("SELECT * FROM groups WHERE course=? AND spec=?");
		$stm->bind_param("is", $course, $name);
		$stm->execute();
		return $stm->get_result()->fetch_array();
	}

	// Возвращает все группы по заданному курсу
	public static function getAllByCourse($course) {
		$db = Database::getConnection();
		$stm = $db->prepare("SELECT * FROM groups WHERE course=?");
		$stm->bind_param("i", $course);
		$stm->execute();
		return $stm->get_result();
	}

	// Возвращает имя группы
	public static function getGroupName(int $gid) {
		$db = Database::getConnection();
		$stm = $db->prepare("SELECT CONCAT(groups.course, ' ', groups.spec) AS name FROM groups WHERE id=?");
		$stm->bind_param("i", $gid);
		$stm->execute();
		return $stm->get_result()->fetch_array()['name'];
	}
}
