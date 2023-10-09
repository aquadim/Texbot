<?php
class PairsModel extends Model {
	protected static $table_name = "pairs";

	public static function create(int $schedule_id, string $time, string $pairname, string $teacher) : void {
		$db = Database::getConnection();

		// Получаем название пары
		$stm = $db->prepare("SELECT id FROM pair_names WHERE name=?");
		$stm->bind_param("s", $pairname);
		$stm->execute();
		$result = $stm->get_result();
		if ($result == false) {
			// Такой пары мы не знаем - создаём
			$insert_pair_name_stm = $db->prepare("INSERT INTO pair_names (name) VALUES(?)");
			$insert_pair_name_stm->bind_param("s", $pairname);
			$insert_pair_name_stm->execute();
			$pair_name_id = $insert_pair_name_stm->insert_id;
		} else {
			$pair_name_id = $result->fetchArray()['id'];
		}

		// Создём пару
		$insert_pair_stm = $db->prepare("INSERT INTO pairs (schedule_id, ptime, name) VALUES(?, ?, ?)");
		$insert_pair_stm->bind_param("isi", $schedule_id, $time, $pair_name_id);
	
		// Создаём места пары
		// Разбиваем $teacher слэшэм
		$places = explode("/", $teacher);
		foreach ($places as $place) {
			list($teacher, $cabinet) = explode(" ", $place);
			TeacherModel::getBySurname($teacher);
		}
	}
}
