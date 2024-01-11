<?php
// Файл загрузки period_ids

// В скрипте происходят следующие действия:
// 1. Читается файл .csv с данными по группам
// 2. В базу заносятся эти данные (таблица period_ids)
// 3. Группам в базе присваиваются id записей

// В директории файла обязан быть файл с названием periodIds.csv
// Этот скрипт следует выполнять только когда все группы техникума
// были официально переведены на следующий КУРС

require_once __DIR__."/../vendor/autoload.php";
require_once __DIR__."/../class/Database.php";
require_once __DIR__."/../models/Model.php";
require_once __DIR__."/../models/GroupModel.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__."/..");
$dotenv->load();
$db = Database::getConnection();

// Сейчас начало учебного года или конец?
// true если начало
$is_starting_semester = date("m") > 7;

// Запрос на проверку существования period_id
$stm_check_period_id = $db->prepare("SELECT COUNT(*) FROM period_ids WHERE value=?");

// Запрос на добавление period_id
$stm_add_period_id = $db->prepare("INSERT INTO period_ids VALUES(NULL, ?, ?, ?)");

$data = file(__DIR__."/periodIds.csv");
for ($y = 1; $y < count($data); $y++) {
	$values = explode(",", $data[$y]);

	// Проверяем добавлен ли period_id в базу
	// period_id уникальны, поэтому дважды обрабатывать их не нужно
	$stm_check_period_id->bind_param("i", $values[7]);
	$stm_check_period_id->execute();
	$period_id_exists = $stm_check_period_id->get_result()->fetch_array()[0] > 0;
	if ($period_id_exists) {
		echo "period_id {$values[7]} существует в БД -- пропуск\n";
		continue;
	}

	$group_name = $values[1];
	if ($is_starting_semester) {
		$group_course = intval(date("Y")) - $values[2] + 1;
	} else {
		$group_course = intval(date("Y")) - $values[2];
	}

	echo "Ищем группу "$group_course.$group_name."... ";
	$group_info = GroupModel::getByParams($group_course, $group_name);
	if ($group_info == false) {
		echo "Неопознанная группа: ".$group_course.$group_name."\n";
		continue;
	}
	$gid = $group_info['id'];
	echo "Найдено - id=$gid\n";

	// Определение номера семестра
	// Если WEEK_NUM1 - 1, то это начало нового учебного года
	// Тем самым, номер семестра = ...
	if ($values[5] == 1) {
		// ... COURSE_NUM * 2 - 1
		$period_num = $values[4] * 2 - 1;
	} else {
		// ... COURSE_NUM * 2
		$period_num = $values[4] * 2;
	}

	echo "Для этой группы добавляется period_id для семестра #$period_num\n";

	// Добавляем запись о period_id
	$stm_add_period_id->bind_param("iii", $period_num, $values[7], $gid);
	$stm_add_period_id->execute();
}

#region Присвоение period_id группам

// Определение текущего года
$now_year = date("Y");

// Запрос выбора всех групп
$stm_select_all_groups = $db->prepare("SELECT * FROM groups");

// Запрос получения id записи в period_ids
$stm_get_period_id_id = $db->prepare("SELECT id FROM period_ids WHERE group_id=? AND num=?");

// Запрос обновления period_id у группы
$stm_set_period_id = $db->prepare("UPDATE groups SET period_id=? WHERE id=?");

// Цикл по всем группам
$stm_select_all_groups->execute();
$groups = $stm_select_all_groups->get_result();
while ($group = $groups->fetch_array()) {
	// Определяем какой сейчас семестр у группы
	if ($is_starting_semester) {
		$semester_num = ($now_year - $group['enrolled_at']) * 2 + 1;
	} else {
		$semester_num = ($now_year - $group['enrolled_at']) * 2 + 2;
	}

	$stm_get_period_id_id->bind_param("ii", $group['id'], $semester_num);
	$stm_get_period_id_id->execute();
	$period_id_id = $stm_get_period_id_id->get_result()->fetch_array()[0];

	$stm_set_period_id->bind_param("ii", $period_id_id, $group['id']);
	$stm_set_period_id->execute();
}
#endregion