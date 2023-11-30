<?php
// Скрипт для проверки количества двоек

require_once "vendor/autoload.php";
require_once __DIR__."/class/Database.php";
require_once __DIR__."/class/GradesGetter.php";
require_once __DIR__."/models/Model.php";
require_once __DIR__."/models/UserModel.php";

// Определение эмодзи
$emojis = [
	1=>['😞', '🙁', '😖', '😭'],		// Когда двойки увеличиваются
	-1=>['😁', '😆', '😀', '🙂']		// Когда двойки уменьшаются
];

// Выборка людей для проверки
// Изменяйте LIMIT 1 на количество людей в выборке если нужно
function selectPeople($db) {
	return $db->query("
		SELECT vk_id, users.id as user_id, journal_login, journal_password, period_ids.value as period_id
		FROM users
		LEFT JOIN groups ON groups.id = users.gid
		LEFT JOIN period_ids ON period_ids.id = groups.period_id
		WHERE type=1 AND journal_login IS NOT NULL AND journal_password IS NOT NULL AND checked_grades_this_iteration=0
		AND users.id >= RAND() * (SELECT MAX(id) FROM users)
		ORDER BY users.id
		LIMIT 1");
}

// Загрузка .env переменных
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$db = Database::getConnection();

$response = selectPeople($db);
if ($response->num_rows == 0) {
	// Людей, подходящих под условие не осталось, а это значит что мы можем начать новую итерацию
	$db->query("UPDATE users SET checked_grades_this_iteration = 0");
	$response = selectPeople($db);
}

while ($row = $response->fetch_array()) {
	$need_send_message = false;		// Нужно ли отправлять сообщение
	$message = "Сводка двоек:\n\n";	// Итоговое сообщение
	$total_negatives = 0;			// Общее количество двоек

	//$grades = getGradesData($row['journal_login'], $row['journal_password'], $row['period_id']);
	$now_grades = [['Физическая культура', '5 4 4 5 4 2 2', ''], ['Математика', '5 4 4 5 4 2 2', ''], ];
	//$past_grades = UserModel::getNegativeGradesCount($row['user_id']);
	$past_grades = ['Физическая культура'=> 13];

	foreach ($now_grades as $item) {
		// Название дисциплины
		$discipline = $item[0];

		// Число двоек для предмета
		$now_count = count_chars($item[1], 0)[50]; // 50 - число 2 в ascii
		$total_negatives += $now_count;

		if (array_key_exists($discipline, $past_grades)) {
			// Двойки по этой дисциплине уже существовали
			$past_count = $past_grades[$discipline];
			if ($now_count > $past_grades[$discipline]) {
				// Количество двоек сейчас увеличилось
				$direction = 1;
			} else  if ($now_count < $past_grades[$discipline]) {
				// Количество двоек сейчас уменьшилось
				$direction = -1;
			}
		} else {
			// Двойки по этой дисциплине ещё не существовали (т.к. дисциплины без двоек не сохраняются в БД)
			$past_count = 0;
			if ($now_count == 0) {
				// Двоек не было раньше, и сейчас тоже нет
				continue;
			} else {
				// Двоек не было и сейчас появились, направление может быть только 1
				$direction = 1;
			}
		}

		$need_send_message = true;

		// Определяем эмодзи для дисциплины
		if (0 <= $now_count && $now_count <= 3) {
			$emoji = $emojis[$direction][0];
		} else if (3 < $now_count && $now_count <= 7) {
			$emoji = $emojis[$direction][1];
		} else if (7 < $now_count && $now_count <= 10) {
			$emoji = $emojis[$direction][2];
		} else {
			$emoji = $emojis[$direction][3];
		}

		$message .= $emoji." ".$discipline.": количество двоек";

		if ($direction == 1) {
			$message .= ' увеличилось с ';
		} else {
			$message .= ' уменьшилось с ';
		}

		$message .= $past_count.' до '.$now_count."\n";
	}

	$message .= "\nОбщее количество двоек: ".$total_negatives;

	if ($need_send_message) {
		echo $message;
	}
	
}