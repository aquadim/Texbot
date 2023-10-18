<?php
// Модель мест проведения пар
/*
 * pair_id - ID пары
 * teacher_id - ID преподавателя этой пары в этом месте
 * place - Место проведения пары
 */
class PairPlaceModel extends Model {
	protected static $table_name = "pair_places";

	public static function create(int $pair_id, string $teacher_data) : void {
		$db = Database::getConnection();

		$stm = $db->prepare("INSERT INTO pair_places (pair_id, teacher_id, place) VALUES (?,?,?)");
		$stm->bind_param("iis", $pair_id, $teacher_id, $location);

		// Разделяем $teacher_data слэшем
		$places = explode('/', $teacher_data);
		foreach ($places as $place) {
			// Разделяем $place пробелом
			$parts = explode(" ", $place);
			$teacher_surname = $parts[0];
			if (count($parts) >= 2) {
				$location = $parts[1];
			}
			$teacher = TeacherModel::getBySurname($teacher_surname);
			if ($teacher == false) { // Преподаватель не найден. TODO: Добавить уведомление об этом?
				$teacher_id = null;
			} else {
				$teacher_id = $teacher['id'];
			}
			$stm->execute();
		}
	}
}
