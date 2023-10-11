<?php
// Модель пар
/*
 * schedule_id - К какому расписанию относится эта пара
 * ptime - Время пары
 * name - Ссылка на название пары
 */

class PairModel extends Model {
	protected static $table_name = "pairs";

	public static function create(int $schedule_id, string $time, string $pair_name) : int {
		$db = Database::getConnection();

		// Получаем название пары
		$pair_name_id = PairNamesModel::getByName($pair_name);

		// Форматируем time
		$datetimeobj = date_create_from_format("!G.i", $time);
		$timestring = $datetimeobj->format("H:i");

		// Создём пару
		$insert_pair_stm = $db->prepare("INSERT INTO pairs (schedule_id, ptime, name) VALUES(?, ?, ?)");
		$insert_pair_stm->bind_param("isi", $schedule_id, $timestring, $pair_name_id);
		$insert_pair_stm->execute();
		return $insert_pair_stm->insert_id;
	}
}
