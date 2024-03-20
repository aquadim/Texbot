<?php
// Бот
define('vk_api_endpoint', "https://api.vk.com/method/");

class Bot {

	private $responses;			// Все сообщения бота
	private $wait_responses;	// Сообщения с просьбой подождать
	private $keyboards;			// Клавиатуры
	private $data;				// Данные запроса от ВК
	private $vid;				// ID ВК пользователя по запросу которого выполняется обработка

	public function __construct($input) {
		$this->responses = array(
			"hi1"=> "Привет, я - Техбот. Моя задача - облегчить твою жизнь, но, для начала, мне нужно задать несколько вопросов",
			"hi2"=> "Ознакомься с условиями использования прежде чем использовать мои функции",
			"tos"=> "===УСЛОВИЯ ИСПОЛЬЗОВАНИЯ===\n1. Я могу ошибаться, ведь я всего лишь программный код\n2. Разработчики и администрация не отвечают за возможный ущерб, причинённый ошибкой в функции, ведь они не могут знать мгновенно что произошёл сбой\n3. Использование моих функций абсолютно бесплатное и ни к чему вас не обязывает\n4. Сторонние клиенты ВКонтакте не поддерживаются",
			"question_are_you_student"=> "%d. Ты студент?",
			"question-who-are-you"=> "%d. Выбери себя из списка",
			"question-who-are-you-no-number"=> "Выбери себя из списка",
			"question_what_is_your_course"=> "%d. На каком курсе сейчас учишься?",
			"question_what_is_your_group"=> "%d. Какая из этих групп твоя?",
			"question_can_send_messages"=> "%d. Можно ли тебе присылать сообщения об обновлении бота, предупреждениях и др.?",
			"welcome_post_reg"=> "Ответы сохранены, добро пожаловать!",
			"pick_day"=> "Выбери день",
			"no_relevant_data"=> "Я ничего не знаю о расписании, подожди какое-то время",
			"enter_login"=> "Введи логин",
			"enter_password"=> "Введи пароль",
			"done"=> "Готово!",
			"returning"=> "Возвращаемся",
			"get-next-student"=> "Остаётся %s %s до начала пары %s. Начало в %s (%s) (Расписание может быть неточным!)",
			"get-next-teacher"=> "Остаётся %s %s до начала пары %s. Начало в %s с группой %s в %s (Расписание может быть неточным!)",
			"get-next-fail"=> "Не удалось узнать какая пара будет следующей",
			"select-course"=> "Выбери курс группы",
			"select-group"=> "Выбери специальность группы",
			"no-data"=> "❌ Нет данных",
			"bells-schedule"=> "Звонки в понедельник:\n1 пара: 8:00 - 9:35 (перерыв в 8:45)\n2 пара: 9:45 - 11:20 (перерыв в 10:30)\nКл час: 11:30 - 12:15\nОбед: 12:15-13:00\n3 пара: 13:00 - 14:35 (перерыв в 13:45)\n4 пара: 14:45 - 16:20 (перерыв в 15:30)\n5 пара: 16:30 - 18:05 (перерыв в 17:15).\n\nЗвонки со вторника по пятницу\n1 пара: 8:00 - 9:35 (перерыв в 8:45)\n2 пара: 9:45 - 11:20 (перерыв в 10:30)\nОбед: 11:20 - 12:20\n3 пара: 12:20 - 13:55 (перерыв в 13:05)\n4 пара: 14:05 - 15:40 (перерыв в 14:50)\n5 пара: 15:50 - 17:25 (перерыв в 16:35)\n\nЗвонки в субботу\n1 пара: 8:00 - 9:25 (перерыв в 8:40)\n2 пара: 09:35 - 11:00 (перерыв в 10:15)\n3 пара: 11:10 - 12:35 (перерыв в 11:50)\n4 пара: 12:45 - 14:10 (перерыв в 13:25)",
			"profile-identifier-student"=> "👥 Ваша группа: %s",
			"profile-identifier-teacher"=> "👤 Ваша фамилия: %s",
			"profile-journal-not-filled"=> "\n⚠ Вы не указывали логин и пароль от электронного журнала",
			"profile-journal-filled"=> "\n🆔 Логин, используемый для сбора ваших оценок - %s",
			"profile-mail-allowed"=> "\n✅ Вы разрешили присылать вам рассылочные сообщения",
			"profile-mail-not-allowed"=> "\n❌ Вы запретили присылать вам рассылочные сообщения",
			"updating-menu"=> "Обновляем меню!",
			"started-editing"=> "Начинаем изменять твой профиль!",
			"wrong_input"=> "Это не подойдёт",
			"admin-welcome"=> "Добро пожаловать в панель администрации!",
			"enter-mail-target"=> "Введи цель рассылки. Допустимые значения *ИС, 3*, *, 3ИС",
			"enter-mail-message"=> "Введи текст рассылки",
			"mail-saved"=> "Данные рассылки сохранены, затронуто пользователей: {0}",
			"mail-disabled"=> "Больше не потревожу! Если снова захочешь получать рассылки - то включить их можно в меню профиля",
			"stats"=> "Вот HTML разметка, позволющая просмотреть статистику",
			"grades-working" => "🕓 Терпение, оценки ещё обрабатываются",
			"credentials-unknown" => "Чтобы получить твои оценки мне нужно узнать логин и пароль от аккаунта в электронном дневнике.\nМожешь ввести их в меню профиля или с помощью этой кнопки",
			"write-teacher" => "Напиши фамилию преподавателя",
			"teacher-not-found" => "Преподаватель не найден",
			"grades-fail" => "Не удалось собрать оценки с помощью данных логина и пароля. Пожалуйста перепроверь их правильность, и, если нужно, введи заново",
			"write-teacher-reg" => "%d. Напиши свою фамилию",
			"write-cab" => "Напиши номер кабинета",
			"cabinet-fail" => "❌ Такого кабинета нет, либо данные о расписании не сгенерированы. Повтори попытку позже"
		);

		$this->wait_responses = array(
			"🕓 Подожди",
			"🕓 Подожди немного",
			"🕓 Секунду",
			"🕓 Будет сделано!",
			"🕓 Рисую картинку...",
			"🕓 Собираю данные...",
			"🕓 Запрос принят",
			"🕓 Уже работаю над этим"
		);

		$this->keyboards = array(
			"yn_text"=> '{"one_time":true,"inline":false,"buttons":[[{"color":"primary","action":{"type":"text","payload":null,"label":"Да"}},{"color":"negative","action":{"type":"text","payload":null,"label":"Нет"}}]]}',
			"cancel"=> '{"one_time":false,"inline":false,"buttons":[[{"color":"negative","action":{"type":"text","payload":null,"label":"Отмена"}}]]}',
			"return"=> '{"one_time":false,"inline":false,"buttons":[[{"color":"negative","action":{"type":"text","payload":null,"label":"Вернуться"}}]]}',
			"tos"=> '{"one_time":false,"inline":true,"buttons":[[{"color":"primary","action":{"type":"callback","payload":"{\"type\":1}","label":"Показать условия использования"}}]]}',
			"unsubscribe"=> '{"one_time":false,"inline":true,"buttons":[[{"color":"negative","action":{"type":"callback","payload":"{\"type\":9}","label":"Запретить рассылки"}}]]}',
			"stud_hub"=> '{"one_time":false,"inline":false,"buttons":[[{"color":"primary","action":{"type":"text","payload":null,"label":"Расписание"}},{"color":"primary","action":{"type":"text","payload":null,"label":"Оценки"}},{"color":"primary","action":{"type":"text","payload":null,"label":"Что дальше?"}}],[{"color":"secondary","action":{"type":"text","payload":null,"label":"Где преподаватель?"}},{"color":"secondary","action":{"type":"text","payload":null,"label":"Расписание группы"}},{"color":"secondary","action":{"type":"text","payload":null,"label":"Звонки"}}],[{"color":"secondary","action":{"type":"text","payload":null,"label":"Профиль"}}]]}',
			"teacher_hub"=> '{"one_time":false,"inline":false,"buttons":[[{"color":"primary","action":{"type":"text","payload":null,"label":"Расписание"}},{"color":"primary","action":{"type":"text","payload":null,"label":"Кабинеты"}},{"color":"primary","action":{"type":"text","payload":null,"label":"Что дальше?"}}],[{"color":"secondary","action":{"type":"text","payload":null,"label":"Где преподаватель?"}},{"color":"secondary","action":{"type":"text","payload":null,"label":"Расписание группы"}},{"color":"secondary","action":{"type":"text","payload":null,"label":"Звонки"}}],[{"color":"secondary","action":{"type":"text","payload":null,"label":"Профиль"}}]]}',
			"enter_journal_credentials"=> '{"one_time":false,"inline":true,"buttons":[[{"color":"primary","action":{"type":"callback","payload":"{\"type\":3,\"after_profile\":false}","label":"Ввести логин и пароль"}}]]}',
			"empty"=> '{"one_time":false,"inline":false,"buttons":[]}',
		);

		// Определяем данные запроса
		$this->data = json_decode($input);

		// Проверка секретного ключа
		if ($this->data->secret != $_ENV['vk_secret']) {
			exit();
		}

		// Если событие - подтверждение сервера, то нужно вернуть строку подтверждения
		if ($this->data->type == "confirmation") {
			exit($_ENV["confirmation_token"]);
		}

		// Закрываем соединение для того чтобы скрипт мог работать больше чем 10 секунд
		// Скрипт должен уметь работать больше чем 10 секунд потому что если vk не получил "ok"
		// за 10 секунд от сервера, он пришлёт запрос ещё раз. На самом деле сервер обрабатывал первый
		// запрос, и когда он его закончил, он ответил бы "ok", но второй запрос уже прислался...
		// Так будет происходить 5 раз перед тем как вк не сдастся и не прекратит присылать новые запросы
		// https://ru.stackoverflow.com/q/893864/418543
		ob_end_clean();
		header("Connection: close");
		ignore_user_abort(true); // just to be safe
		ob_start();
		echo "ok";
		$size = ob_get_length();
		header("Content-Length: ".$size);
		ob_end_flush();
		flush();
		
		switch ($this->data->type) {
			case "message_new":
				$this->vid = $this->data->object->message->from_id;
				break;
			case "message_event":
				$this->vid = $this->data->object->peer_id;
				break;
			default:
				$this->vid = null;
				break;
		}

		set_exception_handler(array($this, "reportException"));
		set_error_handler(array($this, "reportError"), E_ALL);

	}

	#region Работа с API ВКонтакте

	// Отправка сообщения пользователю ВКонтакте
	// Возвращает id отправленного сообщения
	public static function sendMessageVk($vid, string $msg = null, string $keyboard = null, string $attachment = null) : int {
		$params = array(
			"peer_id" => $vid,
			"message" => $msg,
			"keyboard" => $keyboard,
			"attachment" => $attachment,
			"random_id" => 0,
			"access_token" => $_ENV['vk_token'],
			"v" => "5.131"
		);
		$response = file_get_contents(vk_api_endpoint."messages.send?".http_build_query($params));
		$data = json_decode($response);
		return $data->response;
	}

	// Изменение сообщения
	public function editMessageVk($vid, int $msg_id, string $msg = null, string $keyboard = null, string $attachment = null) : void {
		$params = array(
			"peer_id" => $vid,
			"message" => $msg,
			"attachment" => $attachment,
			"group_id" => $_ENV['public_id'],
			"conversation_message_id" => $msg_id,
			"keyboard" => $keyboard,
			"access_token" => $_ENV['vk_token'],
			"v" => "5.131"
		);
		$response = file_get_contents(vk_api_endpoint."messages.edit?".http_build_query($params));
	}

	// Возвращает разметку кнопки клавиатуры
	private function getKeyboardButton($label, $color, $type, $payload = null) {
		return array(
			"color" => $color,
			"action" => array(
				"type" => $type,
				"payload" => $payload,
				"label" => $label
			)
		);
	}

	// Возвращает разметку всей клавиатуры
	private function getKeyboard($one_time, $is_inline, $buttons) {
		$keyboard = array("inline"=>$is_inline, "buttons"=>$buttons);
		if (!$is_inline) {
			$keyboard["one_time"] = $one_time;
		}
		return json_encode($keyboard);
	}
	#endregion

	#region Генераторы клавиатур
	// Генерирует клавиатуру выбора курса
	private function makeKeyboardSelectCourse($intent) {
		$buttons = array(
			array(
				$this->getKeyboardButton("1", "primary", "callback", array("type"=>PAYLOAD_SELECT_COURSE, "intent"=>$intent, "num"=>1)),
				$this->getKeyboardButton("2", "primary", "callback", array("type"=>PAYLOAD_SELECT_COURSE, "intent"=>$intent, "num"=>2))
			),
			array(
				$this->getKeyboardButton("3", "primary", "callback", array("type"=>PAYLOAD_SELECT_COURSE, "intent"=>$intent, "num"=>3)),
				$this->getKeyboardButton("4", "primary", "callback", array("type"=>PAYLOAD_SELECT_COURSE, "intent"=>$intent, "num"=>4))
			)
		);
		return $this->getKeyboard(true, true, $buttons);
	}

	// Генерирует клавиатуру выбора группы
	private function makeKeyboardSelectGroup(mysqli_result $groups, int $intent) {
		$buttons = array();
		$added_in_row = 0; // Сколько кнопок уже добавлено в этот ряд
		$current_row = 0; // Какой сейчас обрабатывается ряд
		while ($group = $groups->fetch_array()) {
			$buttons[$current_row][] = $this->getKeyboardButton(
				$group["spec"],
				"primary",
				"callback",
				array("type"=>PAYLOAD_SELECT_GROUP, "intent"=>$intent, "gid" => $group["id"])
			);
			$added_in_row++;
			if ($added_in_row == 3) { // 3 кнопки на строку
				$current_row++;
				if ($current_row == 3) { // Больше кнопок добавлять нельзя
					break;
				}
				$buttons[$current_row] = array();
				$added_in_row = 0;
			}
		}
		
		return $this->getKeyboard(true, true, $buttons);
	}

	// Возвращает клавиатуру выбора даты
	private function makeKeyboardSelectRelevantDate($intent, $target) {
		$relevant_dates = ScheduleModel::getRelevantDates();
		if ($relevant_dates->num_rows == 0) {
			return false;
		}

		// Строки на сегодня и на завтра
		$date_today = date('Y-m-d');
		$date_tomorrow = date('Y-m-d', time() + 86400);

		$buttons = array();
		while ($date = $relevant_dates->fetch_array()) {
			// Создаём надпись
			if ($date['day'] == $date_today) {
				$label = 'Сегодня';
			} else if ($date['day'] == $date_tomorrow) {
				$label = 'Завтра';
			} else {
				$parts = explode('-', $date['day']);
				$label = $parts[2].' '.$GEN_MONTH_NUM_TO_STR[$parts[1]];
			}

			$buttons[] = array($this->getKeyboardButton(
				$label,
				'primary',
				'callback',
				array('type'=>PAYLOAD_SELECT_DATE, 'intent'=>$intent, 'target'=>$target, 'date'=>$date['day'])
			));
		}

		return $this->getKeyboard(false, true, $buttons);
	}

	// Создаёт клавиатуру настроек профиля
	private function makeProfileKeyboard($user) {
		$buttons = [];

		if ($user['type'] == 1) {
			$row = [$this->getKeyboardButton("Сменить группу", 'primary', 'callback', array('type'=>PAYLOAD_PROFILE_ACTION, 'intent' => INTENT_EDIT_STUDENT))];

			if ($user['journal_login'] == null) {
				$label = 'Ввести логин и пароль';
				$color = 'positive';
			} else {
				$label = 'Изменить логин и пароль';
				$color = 'primary';
			}
			$row[] = $this->getKeyboardButton($label, $color, 'callback', array('type'=>PAYLOAD_ENTER_CREDENTIALS, 'after_profile'=>true));
			$buttons[] = $row;
		}

		if ($user['allows_mail']) {
			$label = 'Запретить рассылку';
			$color = 'negative';
		} else {
			$label = 'Разрешить рассылки';
			$color = 'positive';
		}
		$buttons[] = [$this->getKeyboardButton($label, $color, 'callback', array('type'=>PAYLOAD_TOGGLE_MAIL))];

		return $this->getKeyboard(false, true, $buttons);

		// TODO: смена типа аккаунта
	}
	#endregion

	#region Ответы Техбота
	// Показывает условия использования
	private function answerShowTerms($vid) {
		$this->sendMessageVk($vid, $this->responses['tos']);
	}

	// Первое взаимодействие с ботом
	private function answerOnMeet($vid) {
		$this->sendMessageVk($vid, $this->responses['hi1']);
		$this->sendMessageVk($vid, $this->responses['hi2'], $this->keyboards['tos']);
		$this->answerAskIfStudent($vid, 1);
	}

	// Вопрос: Ты студент?
	private function answerAskIfStudent($vid, $progress) {
		$this->sendMessageVk($vid, sprintf($this->responses['question_are_you_student'], $progress), $this->keyboards['yn_text']);
	}

	// Вопрос: На каком ты курсе?
	private function answerAskCourseNumber($vid, $text, $intent, $edit=false, $msg_id=null) {
		if (!$edit) {
			$this->sendMessageVk(
				$vid,
				$text,
				$this->makeKeyboardSelectCourse($intent)
			);	
		} else {
			$this->editMessageVk(
				$vid,
				$msg_id,
				$text,
				$this->makeKeyboardSelectCourse($intent)
			);
		}
	}

	// Вопрос: Какая из этих групп твоя?
	private function answerAskStudentGroup($vid, $progress, $course) {
		$group_names = database.getGroupsByCourse(course);
		$this->sendMessageVk(
			$vid,
			$this->responses['question_what_is_your_group'].format(progress),
			$this->makeKeyboardSelectGroup($group_names, null, intents.registration)
		);
	}

	// Вопрос: можно ли присылать рассылки
	private function answerAskIfCanSend($vid, $progress) {
		$this->sendMessageVk($vid, sprintf($this->responses['question_can_send_messages'], $progress), $this->keyboards['yn_text']);
	}

	// Неверный ввод данных
	private function answerWrongInput($vid) {
		$this->sendMessageVk($vid, $this->responses['wrong_input']);
	}

	// Добро пожаловать
	private function answerPostRegistration($vid, $user_type) {
		if ($user_type == 1) {
			$this->sendMessageVk($vid, $this->responses['welcome_post_reg'], $this->keyboards['stud_hub']);
		} else {
			$this->sendMessageVk($vid, $this->responses['welcome_post_reg'], $this->keyboards['teacher_hub']);
		}
	}

	// Отправляет сообщение с выбором даты
	private function answerSelectDate($vid, $target, $intent, $edit=false, $msg_id=null) {
		$keyboard = $this->makeKeyboardSelectRelevantDate($intent, $target);

		if (!$keyboard) {
			$this->sendMessageVk($vid, $this->responses['no_relevant_data']);
		} else {
			if (!$edit) {
				$this->sendMessageVk($vid, $this->responses['pick_day'], $keyboard);
			} else {
				$this->editMessageVk($vid, $msg_id, $this->responses['pick_day'], $keyboard);
			}
		}
	}

	// Изменяет сообщение с просьбой подождать
	private function answerEditWait($vid, $msg_id) {
		$this->editMessageVk($vid, $msg_id, $this->wait_responses[array_rand($this->wait_responses)]);
	}

	// Отправляет сообщение с просьбой подождать
	private function answerSendWait($vid) : int {
		return $this->sendMessageVk($vid, $this->wait_responses[array_rand($this->wait_responses)]);
	}

	// Показ расписания для группы
	private function answerShowScheduleForGroup($vid, $date, $gid, $msg_id) {
		$response = ScheduleModel::getForGroup($date, $gid);

		if (!$response) { // Такого расписания нет
			$this->editMessageVk($vid, $msg_id, $this->responses['no-data']);
			return;
		}

		if ($response['photo'] != null) { // Расписание кэшировано, отправляем сейчас
			$this->editMessageVk($vid, $msg_id, "Пожалуйста учти что расписание может быть неточным", null, $response['photo']);
			return;
		}

		// Нет кэшированного изображения, делаем
		$this->answerEditWait($vid, $msg_id);
		$data = PairModel::getPairsOfSchedule($response["id"]);
		$gen = new GroupScheduleGenerator($data, "Расписание группы ".GroupModel::getGroupName($gid).' на '.$date);
		$attachment = $gen->run();
		$this->editMessageVk($vid, $msg_id, "Пожалуйста учти что расписание может быть неточным", null, $attachment);

		ScheduleModel::createCache($response['id'], $attachment);
	}

	// Показ расписания преподавателя
	private function answerShowScheduleForTeacher($vid, $date, $teacher_id, $msg_id) {
		// Получить кэшированное расписание
		$response = TeacherScheduleModel::getCached($date, $teacher_id);

		if ($response != false) { // Кэшированное расписание есть
			$this->editMessageVk($vid, $msg_id, null, null, $response['photo']);
			return;
		}

		$this->answerEditWait($vid, $msg_id);
		$data = PairModel::getPairsOfTeacher($date, $teacher_id);
		$teacher = TeacherModel::getById($teacher_id);
		$gen = new TeacherScheduleGenerator($data, "Расписание преподавателя ".$teacher['surname'].' на '.$date);
		$attachment = $gen->run();
		$this->editMessageVk($vid, $msg_id, "Пожалуйста учти что расписание может быть неточным", null, $attachment);

		TeacherScheduleModel::create($date, $teacher_id, $attachment);
	}
	
	// Показ занятости кабинетов
	private function answerShowCabinetOccupancy($vid, $date, $cabinet, $msg_id) {
		// Получить кэшированное расписание
		$response = OccupancyCacheModel::getCached($date, $cabinet);

		if ($response != false) { // Кэшированное расписание есть
			$this->editMessageVk($vid, $msg_id, null, null, $response['photo']);
			return;
		}

		$this->answerEditWait($vid, $msg_id);
		$data = PairModel::getCabinetOccupancy($date, $cabinet);
		if (count($data) == 1) { // Только одна строка в ответе - и та - подписи колонок
			$this->editMessageVk($vid, $msg_id, $this->responses['cabinet-fail']);
			return false;
		}
		$gen = new OccupancyGenerator($data, "Расписание занятости кабинета ".$cabinet.' на '.$date);
		$attachment = $gen->run();
		$this->editMessageVk($vid, $msg_id, null, null, $attachment);

		OccupancyCacheModel::create($date, $cabinet, $attachment);
	}

	// Показ оценок
	private function answerShowGrades($vid, $user_id, $user_gid, $login, $password) {
		if ($login == null || $password == null) {
			$this->sendMessageVk($vid, $this->responses['credentials-unknown'], $this->keyboards['enter_journal_credentials']);
			return;
		}

		// Поиск кэшированного изображения
		$cached = GradesModel::getRecent($user_id);
		if ($cached) { // Как минимум 10 минут назад были запрошены оценки
			$this->sendMessageVk($vid, null, null, $cached['photo']);
			return;
		}
		
		$this->answerSendWait($vid);

		// Получение данных
		$grades_data = GradesGetter::getGradesData($login, $password);
		
		if ($grades_data === false) {
			$this->sendMessageVk($vid, $this->responses['grades-fail']);
			return;
		}
		$gen = new GradesGenerator($grades_data, 'Твои оценки на '.date('Y-m-d H:i'));
		$attachment = $gen->run();
		$this->sendMessageVk($vid, null, null, $attachment);

		// Создание записи кэша
		GradesModel::create($user_id, $attachment);
	}

	// Спрашиваем логин журнала
	private function answerAskJournalLogin($vid) {
		$this->sendMessageVk($vid, $this->responses['enter_login'], $this->keyboards['cancel']);
	}

	// Спрашиваем пароль журнала
	private function answerAskJournalPassword($vid) {
		$this->sendMessageVk($vid, $this->responses['enter_password'], $this->keyboards['cancel']);
	}

	// Ответ: Готово!
	private function answerDone($vid) {
		$this->sendMessageVk($vid, $this->responses['done']);
	}

	// Возвращает пользователя в хаб
	private function answerToHub($vid, $user_type, $text) {
		if ($user_type == 1) {
			$this->sendMessageVk($vid, $text, $this->keyboards['stud_hub']);
		} else {
			$this->sendMessageVk($vid, $text, $this->keyboards['teacher_hub']);
		}
	}

	private function answerWhatsNext($vid, $target, $for_teacher) {
		// Отвечает какая пара следующая
		if (!$for_teacher) {
			$response = PairModel::getNextGroupPair($target);
		} else {
			$response = PairModel::getNextTeacherPair($target);
		}

		if (!$response) {
			$this->sendMessageVk($vid, $this->responses['get-next-fail']);
			return;
		}

		// Оставшееся время
		$hours_left = intdiv($response['dt'], 60);
		$minutes_left = $response['dt'] % 60;

		if ($for_teacher == false) {
			$this->sendMessageVk($vid, sprintf($this->responses['get-next-student'],
				$this->num_word($hours_left, array('час', 'часа', 'часов'), true),
				$this->num_word($minutes_left, array('минута', 'минуты', 'минут'), true),
				$response['pair_name'],
				$response['pair_time'],
				$response['pair_place']
			));
		} else {
			$this->sendMessageVk($vid, sprintf($this->responses['get-next-teacher'],
				$this->num_word($hours_left, array('час', 'часа', 'часов'), true),
				$this->num_word($minutes_left, array('минута', 'минуты', 'минут'), true),
				$response['pair_name'],
				$response['pair_time'],
				$response['pair_group'],
				$response['pair_place']
			));
		}
	}

	// Отправляет сообщение с выбором группы
	private function answerSelectGroupSpec($vid, $msg_id, $course, $intent) {
		$groups = GroupModel::getAllByCourse($course);
		$this->editMessageVk(
			$vid,
			$msg_id,
			$this->responses['select-group'],
			$this->makeKeyboardSelectGroup($groups, $intent)
		);
	}

	private function answerBells($vid) {
		// Отправляет сообщение с расписанием звонков
		$this->sendMessageVk($vid, $this->responses['bells-schedule']);
	}

	// Отправляет сообщение с настройкой профиля
	// Если $edit - true, то Техбот будет редактировать сообщение с id $msg_id
	private function answerShowProfile($vid, $user, $edit, $msg_id=null) {
		$message = "";

		if ($user['type'] == 1) { // Студент
			$message .= sprintf($this->responses['profile-identifier-student'], GroupModel::getGroupName($user['gid']));
			if ($user['journal_login'] == null) {
				$message .= $this->responses['profile-journal-not-filled'];
			} else {
				$message .= sprintf($this->responses['profile-journal-filled'], $user['journal_login']);
			}
		} else { // Преподаватель
			$message .= sprintf($this->responses['profile-identifier-teacher'], TeacherModel::getById($user['teacher_id'])['surname']);
		}

		if ($user['allows_mail']) {
			$message .= $this->responses['profile-mail-allowed'];
		} else {
			$message .= $this->responses['profile-mail-not-allowed'];
		}

		$keyboard = $this->makeProfileKeyboard($user);

		if ($edit) {
			$this->editMessageVk($vid, $msg_id, $message, $keyboard);
		} else {
			$this->sendMessageVk($vid, $message, $keyboard);
		}
	}

	// Просьба написать фамилию преподавателя
	private function answerAskSelectTeacher($vid) {
		$this->sendMessageVk($vid, $this->responses['write-teacher'], $this->keyboards['return']);
	}

	// "Не найдено преподавателя"
	private function answerTeacherNotFound($vid) {
		$this->sendMessageVk($vid, $this->responses['teacher-not-found']);
	}

	// Просьба написать фамилию чтобы зарегистрироваться
	private function answerAskTeacherSignature($vid, $progress) {
		$this->sendMessageVk($vid, sprintf($this->responses['write-teacher-reg'], $progress));
	}

	// Просьба написать номер кабинета
	private function answerAskCabNumber($vid) {
		$this->sendMessageVk($vid, $this->responses['write-cab'], $this->keyboards['cancel']);
	}

	#endregion

	# region Обработка ошибок
	// Функция обработки ошибок
	public function mailErrorReport($message, $file, $line, $trace) {
		$report = "<b>Произошла ошибка в Техботе</b>\n";
		$report .= "<b>Пользователь у которого появилась ошибка: </b> https://vk.com/id".$this->vid."\n";
		$report .= "<b>Сообщение ошибки: </b> ".$message."\n";
		$report .= "<b>Файл: </b> ".$file."\n";
		$report .= "<b>Строка: </b> ".$line."\n";

		// Трассировка ошибки
		if (isset($trace) && count($trace) > 0) {
			$report .= "<b>Трассировка ошибки:</b> (чем выше тем позже)<pre>\n";
			foreach ($trace as $item) {
				$report .= "{$item['file']}:{$item['line']} -- функция {$item['function']}\n";
			}
			$report .= "</pre>\n";
		} else {
			$report .= "<b>Трассировка ошибки отсутствует</b>\n";
		}

		if (isset($this->debugpage)) {
			$report .= $this->debugpage;
		}

		if ($_ENV["notifications_type"] == "email") { // email
			$headers = "MIME-Version: 1.0\n";
			$headers .= "From: Техбот <{$_ENV['notifier_email']}>\n";
			$headers .= "Content-type: text/html; charset=utf-8\n";

			mail($_ENV["webmaster_email"], "Ошибка в Техботе", $report, $headers);

		} else if ($_ENV["notifications_type"] == "telegram") {
			// telegram
			$params = ['chat_id'=>$_ENV['notifier_bot_chat'], 'text'=>$report, 'parse_mode'=>'html'];
			$fp = fopen("https://api.telegram.org/bot".$_ENV['notifier_bot_token']."/sendMessage?".http_build_query($params), 'r');
			fclose($fp);
		}

		$this->sendMessageVk($this->vid, "Ой-ёй! Произошла ошибка! Разработчик уведомлён, в скором времени будет починено");
		exit();
	}

	// Callback-функция для set_exception_handler
	public function reportException(Throwable $e) {
		$this->mailErrorReport($e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace());
	}

	// Callback-функция для set_error_handler
	public function reportError(int $errno, string $errstr, string $errfile, int $errline) {
		$this->mailErrorReport($errstr, $errfile, $errline, null);
	}
	#endregion

	// Обработка обычного сообщения. Возвращает true, если необходимо обновить профиль пользователя
	private function handlePlainMessage($text, &$user): bool {
		$vid = $user['vk_id'];

		switch ($user['state']) {
			case STATE_REG_1: // После "Ты студент?
				if ($text == 'Да') {
					// Пользователь - студент
					$user['type'] = 1;
					$user['question_progress'] += 1;
					$user['state'] = STATE_VOID;
					$this->answerAskCourseNumber($vid, sprintf($this->responses['question_what_is_your_course'], $user['question_progress']), INTENT_REGISTRATION);
					return true;
				} else if ($text == 'Нет') {
					// Пользователь - преподаватель;

					// Предложение по улучшению: с помощью vk api получать фамилию пользователя
					// и на основании этих данных определять id преподавателя. Если кто то это смотрит, то
					// чур вы это сделаете :)

					$user['type'] = 2;
					$user['question_progress'] += 1;
					$user['state'] = STATE_REG_ENTER_SIGNATURE;
					$this->answerAskTeacherSignature($vid, $user['question_progress']);
					return true;
				} else {
					// Неверный ввод;
					$this->answerWrongInput($vid);
					return false;
				};

			case STATE_REG_CAN_SEND: // После "Можно ли отправлять сообщения?" при регистрации;
				if ($text == 'Да') {
					$user['allows_mail'] = 1;
				} else if ($text == 'Нет') {
					$user['allows_mail'] = 0;
				} else {
					$this->answerWrongInput($this->vid);
					return false;
				}
				$user['state'] = STATE_HUB;
				$this->answerPostRegistration($this->vid, $user['type']);
				return true;

			case STATE_HUB: // Главное меню
				switch ($text) {
					case 'Расписание':
						if ($user['type'] == 1) {
							$this->answerSelectDate($this->vid, $user['gid'], INTENT_STUD_RASP_VIEW);
						} else {
							$this->answerSelectDate($this->vid, $user['teacher_id'], INTENT_TEACHER_RASP_VIEW);
						}
						StatModel::create($user['gid'], $user['type'], FUNC_RASP);
						return false;
					case 'Оценки':
						if ($user['type'] != 1) return false; // Не студентам нельзя
						$this->answerShowGrades($this->vid, $user['id'], $user['gid'], $user['journal_login'], $user['journal_password']);
						StatModel::create($user['gid'], $user['type'], FUNC_GRADES);
						return true;
					case 'Что дальше?':
						if ($user['type'] == 1) {
							$this->answerWhatsNext($this->vid, $user['gid'], false);
						} else {
							$this->answerWhatsNext($this->vid, $user['teacher_id'], true);
						}
						StatModel::create($user['gid'], $user['type'], FUNC_RASP);
						return false;
					case 'Кабинеты':
						if ($user['type'] != 2) return false; // Не преподавателям нельзя
						$user['state'] = STATE_ENTER_CAB;
						$this->answerAskCabNumber($this->vid);
						StatModel::create($user['gid'], $user['type'], FUNC_VIEW_CABS);
						return true;
					case 'Где преподаватель?':
						$user['state'] = STATE_ENTER_TEACHER;
						$this->answerAskSelectTeacher($this->vid);
						StatModel::create($user['gid'], $user['type'], FUNC_WHERE_TEACHER);
						return true;
					case 'Расписание группы':
						$this->answerAskCourseNumber($this->vid, $this->responses['select-course'], INTENT_STUD_RASP_VIEW);
						StatModel::create($user['gid'], $user['type'], FUNC_OTHER_RASP);
						return false;
					case 'Профиль':
						$this->answerShowProfile($this->vid, $user, false);
						return false;
					case 'Звонки':
						$this->answerBells($this->vid);
						StatModel::create($user['gid'], $user['type'], FUNC_BELLS);
						return false;
					case '.':
						$this->answerToHub($vid, $user['type'], $this->responses['updating-menu']);
						return false;
					default:
						return false;
				}

			case STATE_ENTER_LOGIN:
			case STATE_ENTER_LOGIN_AFTER_PROFILE: // Ввод логина электронного дневника
				if ($this->checkIfCancelled($text, $user)) return true;

				$user['journal_login'] = $text;

				if ($user['state'] == STATE_ENTER_LOGIN) {
					$user['state'] = STATE_ENTER_PASSWORD;
				} else {
					$user['state'] = STATE_ENTER_PASSWORD_AFTER_PROFILE;
				}

				$this->answerAskJournalPassword($this->vid);
				return true;

			case STATE_ENTER_PASSWORD:
			case STATE_ENTER_PASSWORD_AFTER_PROFILE: // Ввод пароля электронного дневника
				if ($this->checkIfCancelled($text, $user)) return true;

				$user['journal_password'] = sha1($text); // Электронный дневник хранит пароли в sha1. Да...
				$user['state'] = STATE_HUB;

				$this->answerDone($this->vid);
				$this->answerToHub($this->vid, $user['type'], $this->responses['returning']);
				if ($user['state'] == STATE_ENTER_LOGIN_AFTER_PROFILE) {
					$this->answerShowProfile($this->vid, $user, false);
				}

				return true;

			case STATE_ENTER_TEACHER: // Ввод преподавателя (для функции "Где преподаватель")
				if ($this->checkIfCancelled($text, $user)) return true;

				// Неверный формат фамилии мы преобразовываем.
				$text = $this->fixTeacherSurname($text);

				$teacher = TeacherModel::getBySurname($text);
				if (!$teacher) {
					$this->answerTeacherNotFound($this->vid);
				} else {
					$this->answerSelectDate($this->vid, $teacher['id'], INTENT_TEACHER_RASP_VIEW);
				}
				return true;

			case STATE_REG_ENTER_SIGNATURE: // Ввод фамилии преподавателя для регистрации
				$text = $this->fixTeacherSurname($text);
				$teacher = TeacherModel::getBySurname($text);
				if (!$teacher) {
					$this->answerTeacherNotFound($this->vid);
					return false;
				} else {
					$user['teacher_id'] = $teacher['id'];
				}
				$user['question_progress'] += 1;
				$user['state'] = STATE_REG_CAN_SEND;
				$this->answerAskIfCanSend($this->vid, $user['question_progress']);
				return true;

			case STATE_ENTER_CAB: // Ввод кабинета
				if ($this->checkIfCancelled($text, $user)) return true;
				$this->answerToHub($this->vid, $user['type'], $this->responses['returning']);
				$this->answerSelectDate($vid, $text, INTENT_VIEW_CABINETS);
				$user['state'] = STATE_HUB;
				return true;

			case STATE_VOID: // Заглушка;
				return false;

			default:
				return false;
		}
	}

	// Обработка сообщений обратного вызова. Возвращает true, если необходимо обновить профиль пользователя
	private function handleCallbackMessage($data, $msg_id, &$user) : bool {
		switch ($data->type) {
			case PAYLOAD_SELECT_COURSE: // Выбран курс. Намерение передаётся дальше
				$this->answerSelectGroupSpec($this->vid, $msg_id, $data->num, $data->intent);
				return false;

			case PAYLOAD_SELECT_GROUP: // Выбрана группа
				switch ($data->intent) {
					case INTENT_REGISTRATION: // Студент регистрируется
						$user['gid'] = $data->gid;
						$user['question_progress'] += 1;
						$user['state'] = STATE_REG_CAN_SEND;
						$this->answerAskIfCanSend($this->vid, $user['question_progress']);
						return true;

					case INTENT_STUD_RASP_VIEW: // Выполняется просмотр расписания
						$this->answerSelectDate($this->vid, $data->gid, INTENT_STUD_RASP_VIEW, true, $msg_id);
						return false;

					case INTENT_EDIT_STUDENT: // Изменение группы студента
						$user['gid'] = $data->gid;
						$this->answerShowProfile($this->vid, $user, true, $msg_id);
						return true;

					case INTENT_EDIT_TYPE: // Преподаватель становится студентом
						$user['type'] = 1;
						$user['teacher_id'] = null;
						$user['gid'] = data['gid'];
						$user['state'] = STATE_HUB;
						$this->answerToHub(vid, 1, $this->answers['welcome_post_reg']);
						return true;

					default: // Нет намерения для такого типа данных
						return false;
				}

			case PAYLOAD_SELECT_DATE: // Выбрана дата
				switch ($data->intent) {
					case INTENT_STUD_RASP_VIEW: // Просмотр расписания группы
						$this->answerShowScheduleForGroup($this->vid, $data->date, $data->target, $msg_id);
						return false;

					case INTENT_TEACHER_RASP_VIEW: // Просмотр расписания преподавателя
						$this->answerShowScheduleForTeacher($this->vid, $data->date, $data->target, $msg_id);
						return false;

					case INTENT_VIEW_CABINETS: // Просмотр занятости кабинетов
						$this->answerShowCabinetOccupancy($this->vid, $data->date, $data->target, $msg_id);
						return false;
				}

			case PAYLOAD_SHOW_TERMS: // Вывод условий использования
				$this->answerShowTerms($this->vid);
				return false;

			case PAYLOAD_TOGGLE_MAIL: // Отказ/соглашение на принятие рассылки (в профиле)
				if ($user['allows_mail']) {
					$user['allows_mail'] = false;
				} else {
					$user['allows_mail'] = true;
				}
				$this->answerShowProfile($this->vid, $user, true, $msg_id);
				return true;

			case PAYLOAD_PROFILE_ACTION:
				switch ($data->intent) {
					case INTENT_EDIT_STUDENT: // Показать клавиатуру выбора курса для того чтобы студент мог изменить свою группу
						$this->answerAskCourseNumber($this->vid, $this->responses['select-course'], INTENT_EDIT_STUDENT, true, $msg_id);
						return false;

					default:
						return false;
				}

			case PAYLOAD_ENTER_CREDENTIALS:
				if ($data->after_profile == false) {
					$user['state'] = STATE_ENTER_LOGIN;
				} else {
					$user['state'] = STATE_ENTER_LOGIN_AFTER_PROFILE;
				}
				$this->answerAskJournalLogin($this->vid);
				return true;

			default:
				return false;
		}
		/*
		if data['type'] == PayloadTypes.select_teacher:
			# Удаляем прошлые сообщения
			to_delete = ''
			for i in range(data['msg_id'], data['msg_id'] + data['amount']):
				to_delete += str(i) + ','
			api.delete(to_delete)

			# Выбран преподаватель... но для чего?
			if data['intent'] == INTENT_TEACHER_RASP_VIEW:
				# Просмотр расписания преподавателя
				self.answerSelectDate(vid, null, data['teacher_id'], INTENT_TEACHER_RASP_VIEW, false)
				return false

			if data['intent'] == intents.registration:
				# Преподаватель регистрируется
				user['teacher_id'] = data['teacher_id']
				user['question_progress'] += 1
				user['state'] = STATE_REG_CAN_SEND
				self.answerAskIfCanSend(vid, user['question_progress'])
				return true

			if data['intent'] == intents.edit_type:
				# Студент становится преподавателем
				api.delete(data['msg_id'])
				user['gid'] = null
				user['teacher_id'] = data['teacher_id']
				user['state'] = STATE_HUB
				user['type'] = 2
				self.answerToHub(vid, 2, self.answers['welcome_post_reg'])
				return true

		if data['type'] == PayloadTypes.edit_group:
			# Изменение группы, привязанной к пользователю
			if data['intent'] == intents.edit_student:
				self.answerSelectGroupCourse(vid, data['msg_id'], intents.edit_student, true)
				return false

		if data['type'] == PayloadTypes.toggle_mail:
			# Переключение разрешения рассылки
			if user['allows_mail'] == 1:
				user['allows_mail'] = 0
			else:
				user['allows_mail'] = 1
			self.answerShowProfile(vid, data['msg_id'], user, true)
			return true

		if data['type'] == PayloadTypes.edit_type:
			# Изменяем тип профиля
			user['question_progress'] = 1;
			user['state'] = States.void
			msg_id = self.answerOnStartedEdit(vid)
			if user['type'] == 1:
				# Изменяем на преподавателя. Для этого спрашиваем кто он
				msg_id = self.answerAskTeacherWhenEditing(vid)
				self.answerSelectTeacher(vid, msg_id + 1, intents.edit_type)
			else:
				# Изменяем на студента. Спрашиваем его курс
				self.answerSelectGroupCourse(vid, msg_id + 1, intents.edit_type, false)
			return true

		if data['type'] == PayloadTypes.unsubscribe:
			user['allows_mail'] = 0
			self.answerMailDisabled(vid)
			return true
		*/
	}

	// Обработка запроса
	public function handleRequest() {

		// Получение данных пользователя при входящих запросах
		if ($this->data->type == "message_event" || $this->data->type == "message_new") {
			// Получение информации о пользователе
			$user = UserModel::getByVkId($this->vid);
			if (!$user) {
				// Пользователь не зарегистрирован, создаём его
				$this->answerOnMeet($this->vid);
				UserModel::create($this->vid);
				return;
			}
		}

		switch ($this->data->type) {

			case "message_new":
				$text = $this->data->object->message->text;
				if (strlen($text) == 0) break; // Нет текста в сообщении - не обрабатываем

				// Обрабатываем запрос
				$need_update = $this->handlePlainMessage($text, $user);
				if ($need_update) UserModel::save($user);
				break;

			case "message_event":
				$payload = $this->data->object->payload;
				$msg_id = $this->data->object->conversation_message_id;

				// Обработка сообщения
				$need_update = $this->handleCallbackMessage($payload, $msg_id, $user);
				if ($need_update) UserModel::save($user);
				break;

			default:
				exit("unknown event");
		}
	}

	// Склонение слова
	// https://snipp.ru/php/word-declination
	private function num_word($value, $words, $show = true) {
		$num = $value % 100;

		if ($num > 19) { 
			$num = $num % 10; 
		}
		
		$out = ($show) ?  $value . ' ' : '';
		switch ($num) {

			case 1:
				$out .= $words[0]; break;

			case 2:
			case 3:
			case 4:
				$out .= $words[1]; break;
				
			default:
				$out .= $words[2]; break;
		}
		return $out;
	}

	// Проверяет если пользователь запросил отмену. Если да - то возвращаем его в хаб
	private function checkIfCancelled($text, &$user) {
		if ($text == 'Отмена' || $text == 'Вернуться') {
			$user['state'] = STATE_HUB;
 			$this->answerToHub($user['vk_id'], $user['type'], $this->responses['returning']);
			return true;
		} else {
			return false;
		}
	}

	// Преобразует возможно неправильную фамилию преподавателя в правильную
	private function fixTeacherSurname($text) {
		// 1. Начинается с большой буквы
		$text = mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
		// 2. Ё заменяется на Е
		$text = str_replace('ё', 'е', $text);
		return $text;
	}
}
