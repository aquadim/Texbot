<?php
// Этот файл не содержит класс, только функцию загрузки и парсинга оценок
// Возвращает таблицу оценок, совместимую с TableGenerator
function getGradesData($login, $password) {
	// Создаём разделяемый обработчик
	$sh = curl_share_init();
	curl_share_setopt($sh, CURLSHOPT_SHARE, CURL_LOCK_DATA_COOKIE); // Делимся куками

	// Подаём запрос в электронный дневник на авторизацию
	$auth = curl_init('http://223.255.1.16:8081/region_pou/region.cgi/login');
	curl_setopt($auth, CURLOPT_COOKIEFILE, "");
	curl_setopt($auth, CURLOPT_SHARE, $sh);
	curl_setopt($auth, CURLOPT_POST, 1);
	//~ curl_setopt($auth, CURLOPT_POSTFIELDS, 'username='.username.'&userpass='.sha1(password));
	curl_setopt($auth, CURLOPT_POSTFIELDS, 'username='.$login.'&userpass='.$password);
	curl_setopt($auth, CURLOPT_ENCODING, 'windows-1251');
	curl_setopt($auth, CURLOPT_RETURNTRANSFER, 1);
	curl_exec($auth);

	// Получаем текущий period_id. period_id определяет на какой семестр собираются оценки
	$page = curl_init('http://223.255.1.16:8081/region_pou/region.cgi/journal_och?page=1&clear=1');
	curl_setopt($page, CURLOPT_COOKIEFILE, "");
	curl_setopt($page, CURLOPT_SHARE, $sh);
	curl_setopt($page, CURLOPT_ENCODING, 'windows-1251');
	curl_setopt($page, CURLOPT_RETURNTRANSFER, 1);
	$html = curl_exec($page);

	libxml_use_internal_errors(true);

	$doc = new DOMDocument();
	$doc->loadHTML($html);
	$possible_ids = $doc->getElementsByTagName("option");
	if (date("m") > 7) {
		// Семестр в учебном году первый -- берём по индексу 1 (см. HTML страницы журнала)
		$period_id = $possible_ids[1]->attributes['value']->value;
	} else {
		// Семестр в учебном году последний -- берём по индексу 2
		$period_id = $possible_ids[2]->attributes['value']->value;
	}

	// Запрос на экспорт оценок
	$grades = curl_init('http://223.225.1.16:8081/region_pou/region.cgi/journal_och?page=1&marks=1&period_id='.$period_id.'&export=1');
	$headers = [];
	curl_setopt($grades, CURLOPT_COOKIEFILE, "");
	curl_setopt($grades, CURLOPT_SHARE, $sh);
	curl_setopt($grades, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($grades, CURLOPT_HEADERFUNCTION,
		function($curl, $header) use (&$headers) { // Собираем ответные заголовки https://stackoverflow.com/a/41135574/15146417
			$len = strlen($header);
			$header = explode(':', $header, 2);
			if (count($header) < 2) // ignore invalid headers
				return $len;
			$headers[strtolower(trim($header[0]))][] = trim($header[1]);
			return $len;
		});
	$data = curl_exec($grades);

	// Разрыв сессии с журналом
	$logout = curl_init('http://223.255.1.16:8081/region_pou/region.cgi/logout');
	curl_setopt($logout, CURLOPT_COOKIEFILE, "");
	curl_setopt($logout, CURLOPT_SHARE, $sh);
	curl_setopt($logout, CURLOPT_RETURNTRANSFER, 1);
	curl_exec($logout);

	// Парсинг экспортного XML
	// Данные хранятся в строках с тэгом Row
	// Первые 3 не содержат оценок, их пропускаем
	// Последний ряд тоже не содержит оценок, его не обрабатываем
	// Если XML загрузить не удаётся, то **скорее всего** логин и пароль неверны
	$doc = new DOMDocument();
	$doc->loadXML($data);
	$rows = $doc->getElementsByTagName("Row");

	$output = [];
	for ($y = 4; $y < count($rows) - 1; $y++) {
		$output_row = [];
		$children = $rows[$y]->childNodes;
		// Children - дочерние узлы тэга Row
		// Переносы строк в документе считаются узлом текста, поэтому
		// [0] - текстовый узел
		// [1] - содержит название дисциплины
		// [2] - текстовый узел
		// [3] - содержит оценки
		// [4] - текстовый узел
		// [5] - средний балл
		// [6] - текстовый узел
		$output[] = [
			trim($children[1]->nodeValue),
			trim($children[3]->nodeValue),
			trim($children[5]->nodeValue)
		];
	}

	// Закрываем сессии curl
	curl_share_close($sh);
	curl_close($auth);
	curl_close($grades);


	return $output;
}
