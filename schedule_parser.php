<?php
// Скрипт для парсинга расписания
// Спасибо за помощь (и код):
// https://stackoverflow.com/questions/63249647/how-to-read-table-cell-content-via-phpword-library
// https://stackoverflow.com/questions/50994146/read-ms-word-document-with-php-word

require_once(__DIR__."/config.php");

// Определения цветов для функции displayMessage
if (php_sapi_name() == "cli") { // Скрипт запущен через консоль
	define("COLOR_DEFAULT", "");
	define("COLOR_YELLOW", "\033[93m");
	define("COLOR_RED", "\033[91m");
	define("COLOR_GREEN", "\033[92m");
	define("COLOR_TERMINATOR", "\033[0m");

} else { // Скрипт запущен через веб-сервер, добавляем html в вывод скрипта
	define("COLOR_DEFAULT", "<code style='color:#CCCCCC'>");
	define("COLOR_YELLOW", "<code style='color:#EE8F03'>");
	define("COLOR_RED", "<code style='color:#FF5F47'>");
	define("COLOR_GREEN", "<code style='color:#47FF4B'>");
	define("COLOR_TERMINATOR", "</code>");

	?>
	<!DOCTYPE html>
	<html>
		<head>
			<style>html{background-color:#222222}table,tr,td{border:1px solid silver;border-collapse:collapse;color:silver;padding:0.2em}</style>
		</head>
		<body>
		<pre><?php
}

// Выводит строку информации и окрашивает её в определённый цвет
function displayMessage($string, $color = COLOR_DEFAULT) {
	echo $color.$string.COLOR_TERMINATOR."\n";
}

// Возвращает текст из run элемента
function getTextFromRun($element) {
	$children = $element->getElements();
	$runtext = "";
	foreach ($children as $child) {
		if (method_exists($child, 'getText')) {
			$runtext .= $child->getText();
		} else if (method_exists($child, 'getContent')) {
			$runtext .= $child->getContent();
		}
	}
	return $runtext;
}

// Возвращает true если данная $string - название группы
// Строка - название группы, если первый символ - число, а число слов - именно два
function isGroupName($string) {
	$parts = explode(" ", $string);
	if (count($parts) != 2) {
		return false;
	}
	if (!is_numeric($parts[0])) {
		return false;
	}
	return true;
}

#region Считывание информации
// Загрузка файла расписания
$phpWord = \PhpOffice\PhpWord\IOFactory::load('/tmp/schedule.docx');

// Считывание всей информации документа
$tables = array();
$textruns = array();

displayMessage("===Cбор информации документа===");
// Проходимся по всем секциям и по всем элементам секций в документе
foreach ($phpWord->getSections() as $section) {
	foreach ($section->getElements() as $element) {
		switch (get_class($element)) {

			// Если элемент - таблица, то добавляем её в массив чтобы обработать её позже
			case "PhpOffice\PhpWord\Element\Table":
				displayMessage("Таблица", COLOR_YELLOW);
				$tables[] = $element;
				break;

			// Если элемент - текстоподобный, то считываем весь его текст и добавляем в массив текстов
			// Позже этот массив будет обработан
			case "PhpOffice\PhpWord\Element\ListItemRun":
			case "PhpOffice\PhpWord\Element\TextRun":
				$runtext = getTextFromRun($element);

				if (strlen($runtext) > 0) {
					displayMessage("Текстовый элемент ($runtext)", COLOR_YELLOW);
					$textruns[] = $runtext;
				} else {
					// Пустые строки незачем добавлять в textruns
					displayMessage("Пустой текст", COLOR_YELLOW);
				}

				break;

			// Элемент неизвестен, он просто не будет обработан
			default:
				displayMessage("Неопознанный элемент: ".get_class($element), COLOR_RED);
				break;
		}
	}
}
#endregion

#region Парсинг данных

/* Поиск дат расписаний. Так как общий формат названия таблиц следует такому шаблону
 * РАСПИСАНИЕ ЗАНЯТИЙ на <день недели> <число с 0 впереди если оно меньше 10> <месяц в родительном падеже>
 * то элемент должен проходить проверку следующих условий для того чтобы считаться датой расписания
 *
 * 1. Текст должен иметь не менее 6 слов (слово - последовательность символов, ограниченная пробелами)
 * 2. Первые два слова - расписание занятий (в любом регистре)
 * 3. В тексте должен присутствовать месяц в родительном падеже (октября, ноября, декабря, ...)
 * 4. Перед месяцем должно присутствовать слово. Это слово обязано содержать только цифры т.к. это число месяца
 * 5. В тексте должно присутствовать название какого-либо дня недели. */
displayMessage("\n===Проверка дат расписаний===");
$dates = array();
$month_names = array("января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря");
$weekday_names = array("понедельник", "вторник", "среду", "четверг", "пятницу", "субботу", "воскресенье");

foreach ($textruns as $text) {
	$words = explode(" ", $text);

	// Проверка условия #1
	if (count($words) < 6) {
		displayMessage("$text не дата расписания - количество слов меньше 6", COLOR_RED);
		continue;
	}

	// Проверка условия #2	
	if (mb_strtolower($words[0]) != "расписание" || mb_strtolower($words[1]) != "занятий") {
		displayMessage("$text не дата расписания - первые два слова не 'расписание занятий'", COLOR_RED);
		continue;
	}

	// Проверка условия #3
	$month_id = 0;
	$found_month = false;
	foreach ($month_names as $name) {
		if (str_contains($text, $name)) {
			$found_month = true;
			break;
		}
		$month_id++;
	}
	if (!$found_month) {
		displayMessage("$text не дата расписания - не обнаружено месяца", COLOR_RED);
		continue;
	}

	// Проверка условия #4
	$month_word_index = array_search($month_names[$month_id], $words);
	if (!is_numeric($words[$month_word_index - 1])) {
		displayMessage("$text не дата расписания - число месяца не обнаружено", COLOR_RED);
		continue;
	}

	// Проверка условия #5
	$found_weekday = false;
	foreach ($weekday_names as $wd) {
		if (in_array($wd, $words)) {
			$found_weekday = true;
			break;
		}
	}
	if (!$found_weekday) {
		displayMessage("$text не дата расписания - не обнаружен день недели", COLOR_RED);
		continue;
	}

	displayMessage("$text - дата расписания", COLOR_GREEN);

	// Все условия пройдены, continue не вызывался, а значит эта строка - дата расписания!
	// На основании предыдущих данных определяем дату в формате дд-мм-гггг
	// Как год берётся текущий год на сервере
	// https://stackoverflow.com/a/1699980
	$dates[] = mktime(
		$hour = 0,
		$minute = 0,
		$second = 0,
		$month = $month_id + 1,
		$day = $words[$month_word_index - 1],
	);
}

displayMessage("\n===Парсинг таблиц===");
if (count($dates) != count($tables)) {
	displayMessage("Предупреждение: количество дат не совпадает с количеством таблиц", COLOR_YELLOW);
}
$counter = 0;
$date_relevancy = time() + 129600; // После какой временной отметки расписание не актуально? (текущее время + 1.5 дня)
foreach($dates as $date) {
	// Проверяем актуальность даты
	if ($date > $date_relevancy) {
		continue;
	}

	// Дата актуальна. Парсим таблицу, связанную с этой датой
	displayMessage("Выполняется парсинг таблицы для временной метки: ".date("Y-m-d", $date));
	$table = $tables[$counter]; // Объект таблицы из документа
	$data = array(); // Двумерный массив, содержащий в себе данные таблицы

	$rows = $table->getRows();
	foreach ($rows as $row) {
		$datarow = array();
		$cells = $row->getCells();
        foreach ($cells as $cell) {
			$celltext = '';
			foreach ($cell->getElements() as $element) {
				$celltext .= getTextFromRun($element);
			}
			$datarow[] = trim($celltext, "\xC2\xA0\n ");
        }
        $data[] = $datarow;
    }

    // Настоящий парсинг таблицы
    $dataheight = count($data);
    $datawidth = count($data[0]);

	// Проверка второго столбца таблицы
    for ($y = 0; $y < $dataheight; $y++) {
		if (isGroupName($data[$y][0]) == true) {
			// Эта яйчейка - название группы. Значит справа от неё есть другие названия, а снизу - пары этой группы
			for ($x = 0; $x < $datawidth; $x+=1) { // Проходит все названия групп в строке
				$group_parts = explode(" ", $data[$y][$x]);

				if (count($group_parts) === 2) {
					$group = GroupModel::getByParams(intval($group_parts[0]), $group_parts[1]);
				} else {
					/* Возможны ошибки в названиях групп или случаи, когда первый столбец имеет группу, а
					 * остальные нет. Такие столбцы не парсятся, а просто пропускаются */
					continue;
				}

				if ($group == false) {
					displayMessage("Неопознанная группа: ".$data[$y][$x], COLOR_RED);
					continue;
				}

				// Создание записи расписания
				$schedule_id = ScheduleModel::create($group['id'], date("Y-m-d", $date));

				// Парсинг пар группы
				$group_y = $y;
				while ($group_y + 1 < $dataheight && isGroupName($data[$group_y + 1][$x]) == false) {
					$group_y++;

					$time = $data[$group_y][$x * 2];
					// Пустая яйчейка
					if (strlen($time) < 2) continue;

					$pair_name = $data[$group_y][$x * 2 + 1];
					// Пустая яйчейка
					if (strlen($pair_name) < 3) continue;
					
					$teacher_data = $data[$group_y + 1][$x * 2 + 1];

					$pair_id = PairModel::create($schedule_id, $time, $pair_name);
					PairPlaceModel::create($pair_id, $teacher_data);
				}
			}
		}
	}
    
	$counter++;
}

#endregion

if (php_sapi_name() != "cli") {
	?></pre>
	</body>
	</html>
	<?php
}