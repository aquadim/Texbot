<?php
// Файл загрузки period_ids
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

$data = file(__DIR__."/periodIds.csv");
for ($y = 1; $y < count($data); $y++) {
	$values = explode("," $data[$y]);

	$group_name = $values[1];
	$group_course = intval(date("Y")) - $values[2] + 1;

	$gid = GroupModel::getByParams($group_course, $group_name)['id'];
}