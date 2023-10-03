<?php
// Скрипт для парсинга расписания
// Спасибо за помощь (и код):
// https://stackoverflow.com/questions/63249647/how-to-read-table-cell-content-via-phpword-library
// https://stackoverflow.com/questions/50994146/read-ms-word-document-with-php-word

require_once("vendor/autoload.php");

// Определения цветов для функции displayMessage
define("COLOR_YELLOW", "\033[93m");
define("COLOR_RED", "\033[91m");
define("COLOR_GREEN", "\033[92m");
define("COLOR_TERMINATOR", "\033[0m");

// Выводит строку информации и окрашивает её в определённый цвет
function displayMessage($string, $color = null) {
	echo $color.$string.COLOR_TERMINATOR."\n";
}

#region Считывание информации
// Загрузка файла расписания
$phpWord = \PhpOffice\PhpWord\IOFactory::load('rasp2.docx');

// Считывание всей информации документа
$tables = array();
$textruns = array();

displayMessage("Сбор информации документа...");
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
				$children = $element->getElements();
				$runtext = "";
				foreach ($children as $child) {
					if (method_exists($child, 'getText')) {
						$runtext .= $child->getText();
					} else if (method_exists($child, 'getContent')) {
						$runtext .= $child->getContent();
					}
				}

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
 * 5. В тексте должно присутствовать название какого-либо дня недели.
*/
$month е
foreach ($textruns as $text) {
	$words = explode(" ", $text);

	// Проверка условия #1
	if (count($words) < 6) {
		displayMessage("$text не дата расписания - количество слов меньше 6", COLOR_RED);
		continue;
	}

	// Проверка условия #2
	if (strtolower($words[0]) != "расписание" || strtolower($words[1]) != "занятий") {
		displayMessage("$text не дата расписания - первые два слова не 'расписание занятий'", COLOR_RED);
		continue;
	}

	// Проверка условия #3
	$month_id = array_search()
}

#endregion