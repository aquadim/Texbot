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

	// Возвращает время до следующей пары у группы
	public static function getNextGroupPair($group_id) {
		$db = Database::getConnection();
		$stm = $db->prepare("
			SELECT
				TIME_FORMAT(pairs.ptime, '%H:%i') AS pair_time,
				pair_names.name AS pair_name, 
				GROUP_CONCAT(teachers.surname, ' ', pair_places.place SEPARATOR ' / ') as pair_place,
				TIMESTAMPDIFF(MINUTE, CURRENT_TIMESTAMP, TIMESTAMP(schedules.day, pairs.ptime)) AS dt
			FROM
				pairs
				LEFT JOIN pair_places ON pair_places.pair_id = pairs.id
				LEFT JOIN schedules ON schedules.id = pairs.schedule_id
				LEFT JOIN teachers ON teachers.id = pair_places.teacher_id
				LEFT JOIN pair_names ON pair_names.id = pairs.name
			WHERE schedules.gid = ? AND TIMESTAMP(schedules.day, pairs.ptime) > CURRENT_TIMESTAMP
			GROUP BY pair_places.pair_id
			ORDER BY schedules.day ASC, pairs.ptime ASC
			LIMIT 1");
		$stm->bind_param("i", $group_id);
		$stm->execute();
		return $stm->get_result()->fetch_array(MYSQLI_ASSOC);
	}

	// Возвращает время до следующей пары у преподавателя
	public static function getNextTeacherPair($teacher_id) {
		$db = Database::getConnection();
		$stm = $db->prepare("
			SELECT
				TIME_FORMAT(pairs.ptime, '%H:%i') AS pair_time,
				pair_names.name AS pair_name, 
				IFNULL(pair_places.place, '(н/д)') as pair_place,
                CONCAT(groups.course, ' ', groups.spec) AS pair_group,
				TIMESTAMPDIFF(MINUTE, CURRENT_TIMESTAMP, TIMESTAMP(schedules.day, pairs.ptime)) AS dt
			FROM
				pairs
				LEFT JOIN pair_places ON pair_places.pair_id = pairs.id
				LEFT JOIN schedules ON schedules.id = pairs.schedule_id
				LEFT JOIN teachers ON teachers.id = pair_places.teacher_id
				LEFT JOIN pair_names ON pair_names.id = pairs.name
				LEFT JOIN groups ON groups.id = schedules.gid
			WHERE pair_places.teacher_id = ? AND TIMESTAMP(schedules.day, pairs.ptime) > CURRENT_TIMESTAMP
			GROUP BY pair_places.pair_id
			ORDER BY schedules.day ASC, pairs.ptime ASC
			LIMIT 1");
		$stm->bind_param("s", $teacher_id);
		$stm->execute();
		return $stm->get_result()->fetch_array(MYSQLI_ASSOC);
	}
	
	// Возвращает данные для показа в функции расписания
	public static function getPairsOfSchedule($schedule_id) {
		$db = Database::getConnection();
		$stm = $db->prepare("
		SELECT
			TIME_FORMAT(pairs.ptime, '%H:%i'),
			pair_names.name,
			GROUP_CONCAT(teachers.surname, ' ', pair_places.place SEPARATOR ' / ')
		FROM pairs
		LEFT JOIN pair_names ON pairs.name = pair_names.id
		LEFT JOIN pair_places ON pair_places.pair_id = pairs.id
		LEFT JOIN teachers ON teachers.id = pair_places.teacher_id
		WHERE schedule_id=?
		GROUP BY pair_places.pair_id");
		$stm->bind_param("i", $schedule_id);
		$stm->execute();
		$out = $stm->get_result()->fetch_all(MYSQLI_NUM);
		array_unshift($out, array("Время", "Пара", "Место проведения"));
		return $out;
	}

	// Возвращает данные для показа в функции расписания преподавателя
	public static function getPairsOfTeacher($date, $teacher_id) {
		$db = Database::getConnection();
		$stm = $db->prepare("
			SELECT
				TIME_FORMAT(pairs.ptime, '%H:%i'),
				pair_names.name,
				pair_places.place,
				CONCAT(groups.course, ' ', groups.spec)
			FROM pairs
				LEFT JOIN schedules ON schedules.id = pairs.schedule_id
				LEFT JOIN pair_names ON pairs.name = pair_names.id
				LEFT JOIN pair_places ON pair_places.pair_id = pairs.id
				LEFT JOIN groups ON groups.id = schedules.gid
			WHERE pair_places.teacher_id=? AND schedules.day=?
			ORDER BY pairs.ptime");
		$stm->bind_param("is", $teacher_id, $date);
		$stm->execute();
		$out = $stm->get_result()->fetch_all(MYSQLI_NUM);
		array_unshift($out, array("Время", "Пара", "Место проведения", "Группа"));
		return $out;
	}
}
