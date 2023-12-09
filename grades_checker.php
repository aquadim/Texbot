<?php
// Скрипт для проверки количества двоек

require_once "vendor/autoload.php";
require_once __DIR__."/class/Database.php";
require_once __DIR__."/class/GradesGetter.php";
require_once __DIR__."/models/Model.php";
require_once __DIR__."/models/UserModel.php";
require_once __DIR__."/class/Bot.php";

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

while ($user = $response->fetch_array()) {
	$need_send_message = false;		// Нужно ли отправлять сообщение
	$message = "Сводка двоек:\n\n";	// Итоговое сообщение
	$total_negatives = 0;			// Общее количество двоек
	$new_discipline_exists = 0;		// Существует ли дисциплина, которой раньше Техбот не видел

	// Прошлые оценки
	$past_grades = UserModel::getNegativeGradesCount($user['user_id'], $user['period_id']);

	// Массив текущих оценок (не ассоциативный)
	$now_grades_numeric = getGradesData($user['journal_login'], $user['journal_password'], $user['period_id']);

	// Ассоциативный массив текущих оценок (ключ - название дисциплины, значение - количество двоек)
	$now_grades_assoc = [];
	foreach ($now_grades_numeric as $item) {
		$now_grades_assoc[$item[0]] = count_chars($item[1], 0)[50]; // 50 - число 2 в ascii
	}
	UserModel::saveNegativeGradesCount($user['user_id'], $now_grades_assoc, $user['period_id']);

	echo "===Текущие оценки===\n";
	print_r($now_grades_assoc);
	echo "===Прошлые оценки===\n";
	print_r($past_grades);

	// Если оценки пользователя ещё не проверялись, не проверяем дальше
	if (count($past_grades) == 0) {
		return;
	}

	foreach ($now_grades_assoc as $discipline => $bad_count) {
		$total_negatives += $bad_count;

		if (array_key_exists($discipline, $past_grades) == false) {
			// В прошлом такого предмета не существовало
			$new_discipline_exists = true;
			
			if ($bad_count > 0) {
				// Количество двоек увеличилось, причём с нуля, так как предмета ещё не было
				$past_count = 0;
				$direction = 1;
			} else {
				// Нет двоек и не было, пропускаем итерацию
				continue;
			}
		} else {
			// Предмет уже существовал
			$past_grades[$discipline];
			if ($bad_count > $past_grades[$discipline]) {
				// Количество двоек увеличилось
				$direction = 1;
	
			} else if ($bad_count < $past_grades[$discipline]) {
				// Количество двоек уменьшилось
				$direction = -1;
	
			} else {
				// Количество двоек не изменилось
				continue;
			}
		}

		// Если continue не вызвался, то значит количество двоек изменилось, поэтому необходимо
		// оповестить об этом
		$need_send_message = true;

		// Определяем эмодзи для дисциплины
		if (0 <= $bad_count && $bad_count <= 3) {
			$emoji = $emojis[$direction][0];
		} else if (3 < $bad_count && $bad_count <= 7) {
			$emoji = $emojis[$direction][1];
		} else if (7 < $bad_count && $bad_count <= 10) {
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

		$message .= $past_grades[$discipline].' до '.$bad_count."\n";
	}

	$message .= "\nОбщее количество двоек: ".$total_negatives;

	if ($need_send_message) {
		Bot::sendMessageVk($user['vk_id'], $message);
	}
}