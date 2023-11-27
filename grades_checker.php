<?php
// Скрипт для проверки количества двоек

// Выборка людей для проверки
// Изменяйте LIMIT 1 на количество людей в выборке если нужно
function selectPeople($db) {
	return $db->query("
		SELECT vk_id, journal_login, journal_password
		FROM users
		WHERE type=1 AND journal_login IS NOT NULL AND journal_password IS NOT NULL AND checked_grades_this_iteration = 0
		LIMIT 1
		ORDER BY RAND()");
}

require_once "vendor/autoload.php";
require_once __DIR__."/class/Database.php";
require_once __DIR__."/class/GradesGetter.php";

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
	$data = getGradesData($row['journal_login'], $row['journal_password']);
}