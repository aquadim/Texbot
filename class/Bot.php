<?php
// Бот
class Bot {

	private $responses; // Все сообщения бота
	private $wait_responses; // Сообщения с просьбой подождать
	private $keyboards; // Клавиатуры
	private $data; // Данные запроса от ВК
	private $vid; // ID ВК пользователя по запросу которого выполняется обработка

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
			"get-next-student"=> "Остаётся %s %s до начала пары %s. Начало в %s (%s)",
			"get-next-teacher"=> "Остаётся %s %s до начала пары %s. Начало в %s с группой %s в %s",
			"get-next-fail"=> "Не удалось узнать какая пара будет следующей",
			"select-teacher"=> "Выбери преподавателя (стр. %d/%d)",
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
			"type-cabinet"=> "Введи номер кабинета",
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
			"teacher-not-found" => "Преподаватель не найден"
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
			"to-hub"=> '{"one_time":false,"inline":false,"buttons":[[{"color":"primary","action":{"type":"text","payload":null,"label":"На главную"}}]]}',
			"course_nums"=> '{"one_time":true,"inline":false,"buttons":[[{"color":"primary","action":{"type":"text","payload":null,"label":"1"}},{"color":"primary","action":{"type":"text","payload":null,"label":"2"}}],[{"color":"primary","action":{"type":"text","payload":null,"label":"3"}},{"color":"primary","action":{"type":"text","payload":null,"label":"4"}}]]}',
			"tos"=> '{"one_time":false,"inline":true,"buttons":[[{"color":"primary","action":{"type":"callback","payload":"{\"type\":1}","label":"Показать условия использования"}}]]}',
			"unsubscribe"=> '{"one_time":false,"inline":true,"buttons":[[{"color":"negative","action":{"type":"callback","payload":"{\"type\":9}","label":"Запретить рассылки"}}]]}',
			"stud_hub"=> '{"one_time":false,"inline":false,"buttons":[[{"color":"primary","action":{"type":"text","payload":null,"label":"Расписание"}},{"color":"primary","action":{"type":"text","payload":null,"label":"Оценки"}},{"color":"primary","action":{"type":"text","payload":null,"label":"Что дальше?"}}],[{"color":"secondary","action":{"type":"text","payload":null,"label":"Где преподаватель?"}},{"color":"secondary","action":{"type":"text","payload":null,"label":"Расписание группы"}},{"color":"secondary","action":{"type":"text","payload":null,"label":"Звонки"}}],[{"color":"secondary","action":{"type":"text","payload":null,"label":"Профиль"}}]]}',
			"teacher_hub"=> '{"one_time":false,"inline":false,"buttons":[[{"color":"primary","action":{"type":"text","payload":null,"label":"Расписание"}},{"color":"primary","action":{"type":"text","payload":null,"label":"Кабинеты"}},{"color":"primary","action":{"type":"text","payload":null,"label":"Что дальше?"}}],[{"color":"secondary","action":{"type":"text","payload":null,"label":"Где преподаватель?"}},{"color":"secondary","action":{"type":"text","payload":null,"label":"Расписание группы"}},{"color":"secondary","action":{"type":"text","payload":null,"label":"Звонки"}}],[{"color":"secondary","action":{"type":"text","payload":null,"label":"Профиль"}}]]}',
			"enter_journal_credentials"=> '{"one_time":false,"inline":true,"buttons":[[{"color":"primary","action":{"type":"callback","payload":"{\"type\":3,\"after_profile\":false}","label":"Ввести логин и пароль"}}]]}',
			"empty"=> '{"one_time":false,"inline":false,"buttons":[]}',
			"admin-hub"=> '{"one_time":false,"inline":false,"buttons":[[{"color":"primary","action":{"type":"text","payload":null,"label":"Рассылка"}},{"color":"primary","action":{"type":"text","payload":null,"label":"Статистика"}},{"color":"negative","action":{"type":"text","payload":null,"label":"Выход"}}]]}'
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
	private function sendMessageVk($vid, string $msg = null, string $keyboard = null, string $attachment = null) : int {
		$params = array(
			"peer_id" => $vid,
			"message" => $msg,
			"keyboard" => $keyboard,
			"attachment" => $attachment,
			"random_id" => 0,
			"access_token" => $_ENV['vk_token'],
			"v" => "5.131"
		);
		$fp = fopen(vk_api_endpoint."messages.send?".http_build_query($params), 'r');
		$data = json_decode(stream_get_contents($fp));
		fclose($fp);
		return $data->response;
	}

	// Изменение сообщения
	private function editMessageVk($vid, int $msg_id, string $msg = null, string $keyboard = null, string $attachment = null) {
		$params = array(
			"peer_id" => $vid,
			"message" => $msg,
			"keyboard" => $keyboard,
			"attachment" => $attachment,
			"conversation_message_id" => $msg_id,
			"access_token" => $_ENV['vk_token'],
			"v" => "5.131"
		);
		$fp = fopen(vk_api_endpoint."messages.edit?".http_build_query($params), 'r');
		fclose($fp);
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
			$this->editMessageVk($vid, $msg_id, null, null, $response['photo']);
			return;
		}

		// Нет кэшированного изображения, делаем
		$this->answerEditWait($vid, $msg_id);
		$data = PairModel::getPairsOfSchedule($response["id"]);
		$gen = new GroupScheduleGenerator(null, $data, "Расписание группы ".GroupModel::getGroupName($gid).' на '.date('Y-m-d'));
		$attachment = $gen->run();
		$this->editMessageVk($vid, $msg_id, null, null, $attachment);

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
		$gen = new TeacherScheduleGenerator(null, $data, "Расписание преподавателя ".$teacher['surname'].' на '.date('Y-m-d'));
		$attachment = $gen->run();
		$this->editMessageVk($vid, $msg_id, null, null, $attachment);

		TeacherScheduleModel::create($date, $teacher_id, $attachment);
	}

	//~ private function answerShowScheduleForTeacher($vid, $msg_id, $date, $teacher_id) {
		//~ // Показ расписания для преподавателя
		//~ response = database.getCachedScheduleOfTeacher(date, teacher_id)
		//~ if response:
			//~ # Есть кэшированное
			//~ $this->sendMessageVk($vid, null, null, 'photo-'+str($_ENV['public_id'])+'_'+str(response['photo_id']))
			//~ return
		//~ msg_id = $this->sendMessageVk($vid, self.getRandomWaitText())

		//~ self.tasks.append(graphics.TeacherScheduleGenerator(
			//~ $vid,
			//~ $_ENV['public_id'],
			//~ self.themes['rasp'],
			//~ self,
			//~ 'teacher-schedule',
			//~ msg_id,
			//~ date,
			//~ teacher_id
		//~ ))
		//~ self.tasks[-1].start()
	//~ }

	// Показ оценок
	private function answerShowGrades($vid, $user_id, $login, $password) {
		if ($login == null || $password == null) {
			$this->sendMessageVk($vid, $this->responses['credentials-unknown'], $this->keyboards['enter_journal_credentials']);
			return;
		}
		// Проверяем если пользователь уже получал оценки недавно
		$response = GradesModel::getRecent($user_id);
		if ($response) { // Как минимум 10 минут назад были запрошены оценки
			if ($response['photo'] == null) { // Оценки ещё собираются
				$this->sendMessageVk($vid, $this->responses['grades-working']);
			} else {
				$this->sendMessageVk($vid, null, null, $response['photo']);
			}
			return;
		} else {
			$this->answerSendWait($vid);
		}

		// TODO: изменять присланное сообщение, а не присылать новое
		$data = $this->getGradesData($login, $password);
		$gen = new GradesGenerator($vid, $data, 'Твои оценки на '.date('Y-m-d H:i'));
		$attachment = $gen->run();
		$this->sendMessageVk($vid, null, null, $attachment);

		// Оставляем только часть с photo_id
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
		}
	}

	//~ private function answerSelectTeacher($vid, message_id, intent) {
		//~ // Отправляет сообщения с клавиатурами выбора преподавателя

		//~ # Узнаём какие вообще есть преподаватели
		//~ teachers = database.getAllTeachers()
		//~ keyboards = self.makeTeacherSelectKeyboards(teachers, intent, message_id)
		//~ amount = len(keyboards)

		//~ for index, k in enumerate(keyboards) {
			//~ $this->sendMessageVk($vid, $this->responses['select-teacher'].format(index + 1, amount), k)
			
	//~ // Отправляет сообщение с выбором курса
	//~ private function answerSelectGroupCourse($vid, $msg_id, $intent, $edit) {
		//~ $keyboard = $this->keyboardSelectCourse($msg_id, $intent);
		//~ if (($edit) {
			//~ $this->editMessageVk($vid, $msg_id, $this->responses['select-course'], keyboard);
		//~ } else {
			//~ $this->sendMessageVk($vid, $this->responses['select-course'], keyboard);
		//~ }
	//~ }

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
			$message .= sprintf($this->responses['profile-identifier-teacher'], TeacherModel::getById($user['teacher_id']['surname']));
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
	
	//~ private function answerAskCabNumber($vid) {
		//~ // Просит преподавателя написать кабинет
		//~ $this->sendMessageVk($vid, $this->responses['type-cabinet'], $this->keyboards['cancel'])

	//~ private function answerShowCabinetOccupancy($vid, date, place) {
		//~ // Показ занятости кабинетов
		//~ response = database.getCachedPlaceOccupancy(date, place)
		//~ if (response{
			//~ # Есть кэшированное
			//~ $this->sendMessageVk($vid, null, null, 'photo-'+str($_ENV['public_id'])+'_'+str(response['photo_id']))
			//~ return

		//~ msg_id = $this->sendMessageVk($vid, self.getRandomWaitText())
		//~ self.tasks.append(graphics.CabinetGenerator(
			//~ $vid,
			//~ $_ENV['public_id'],
			//~ self.themes['rasp'],
			//~ self,
			//~ 'teacher-schedule',
			//~ msg_id,
			//~ date,
			//~ place
		//~ ))
		//~ self.tasks[-1].start()

	//~ private function answerOnStartedEdit($vid) {
		//~ // Нужна для очистки клавиатуры при старте смены типа профиля
		//~ return $this->sendMessageVk($vid, $this->responses['started-editing'], $this->keyboards['empty'])

	//~ private function answerShowAdminPanel($vid) {
		//~ // Показ панели администрации
		//~ $this->sendMessageVk($vid, $this->responses['admin-welcome'], $this->keyboards['admin-hub'])

	//~ private function answerAskMailTarget($vid) {
		//~ // Просит ввести цель рассылки
		//~ $this->sendMessageVk($vid, $this->responses['enter-mail-target'], $this->keyboards['cancel'])

	//~ private function answerAskMailMessage($vid) {
		//~ // Просит ввести текст рассылки
		//~ $this->sendMessageVk($vid, $this->responses['enter-mail-message'], $this->keyboards['cancel'])

	//~ private function answerMailDisabled($vid) {
		//~ // Уведомляет об отключении рассылки
		//~ $this->sendMessageVk($vid, $this->responses['mail-disabled'])

	// Просьба написать фамилию преподавателя
	private function answerAskSelectTeacher($vid) {
		$this->sendMessageVk($vid, $this->responses['write-teacher'], $this->keyboards['cancel']);
	}

	// "Не найдено преподавателя"
	private function answerTeacherNotFound($vid) {
		$this->sendMessageVk($vid, $this->responses['teacher-not-found']);
	}

	#endregion

	# region Обработка ошибок
	// Функция обработки ошибок
	public function mailErrorReport($message, $file, $line, $trace) {
		$report = "<pre>Произошла ошибка в Техботе</pre>\n";
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
					$this->answerAskCourseNumber($vid, sprintf($this->responses['question_what_is_your_course'], $user['progress']), INTENT_REGISTRATION);
					return true;
				} else if ($text == 'Нет') {
					// Пользователь - преподаватель;
					$user['type'] = 2;
					$user['question_progress'] += 1;
					$user['state'] = STATE_VOID;
					$msg_id = $this->answerAskTeacherSignature($vid, $user['question_progress']);
					$this->answerSelectTeacher($vid, $msg_id + 1, intents.registration);
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
						$this->answerShowGrades($this->vid, $user['id'], $user['journal_login'], $user['journal_password']);
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
					//~ case 'Кабинеты' and $user['type'] == 2) {
						//~ $user['state'] = States.enter_cab;
						//~ $this->answerAskCabNumber($this->vid);
						//~ database.addStatRecord($user['gid'], $user['type'], 7);
						//~ return true;
					case 'Где преподаватель?':
						$user['state'] = STATE_ENTER_TEACHER;
						$this->answerAskSelectTeacher($this->vid);
						StatModel::create($user['gid'], $user['type'], FUNC_WHERE_TEACHER);
						return true;
					case 'Расписание группы':
						$this->answerAskCourseNumber($this->vid, $this->responses['select-course'], INTENT_STUD_RASP_VIEW);
						StatModel::create($user['gid'], $user['type'], FUNC_OTHER_RASP);
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
				// 1. Начинается с большой буквы
				$text = mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
				// 2. Ё заменяется на Е
				$text = str_replace('ё', 'е', $text);

				$teacher = TeacherModel::getBySurname($text);
				if (!$teacher) {
					$this->answerTeacherNotFound($this->vid);
				} else {
					$this->answerSelectDate($this->vid, $teacher['id'], INTENT_TEACHER_RASP_VIEW);
				}

				$this->answerToHub($this->vid, $user['type'], $this->responses['returning']);
				$user['state'] = STATE_HUB;
				return true;

			case STATE_VOID: // Заглушка;
				return false;

			default:
				return false;
		}

		//~ if ($user['state'] == States.enter_cab) {;
			//~ // Ввод кабинета;
			//~ if ($this->checkIfCancelled($text, $user)) {;
				//~ return true;
			//~ $user['state'] = STATE_HUB;
			//~ $this->answerToHub($this->vid, $user['type'], $this->answers['returning']);
			//~ $this->answerSelectDate($this->vid, $msg_id + 1, $text, INTENT_VIEW_CABINETS);
			//~ return true;
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
						$this->answerShowCabinetOccupancy(vid, data['date'], data['target']);
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

	// Возвращает таблицу оценок, совместимую с TableGenerator
	private function getGradesData($login, $password) {
		// Создаём разделяемый обработчик
		$sh = curl_share_init();
		curl_share_setopt($sh, CURLSHOPT_SHARE, CURL_LOCK_DATA_COOKIE); // Делимся куками

		// Подаём запрос в электронный дневник на авторизацию
		$auth = curl_init('http://avers.vpmt.ru:8081/region_pou/region.cgi/login');
		curl_setopt($auth, CURLOPT_COOKIEFILE, "");
		curl_setopt($auth, CURLOPT_SHARE, $sh);
		curl_setopt($auth, CURLOPT_POST, 1);
		curl_setopt($auth, CURLOPT_POSTFIELDS, 'username='.$login.'&userpass='.$password);
		curl_setopt($auth, CURLOPT_ENCODING, 'windows-1251');
		curl_setopt($auth, CURLOPT_RETURNTRANSFER, 1);
		curl_exec($auth);

		// Запрос на экспорт оценок
		$grades = curl_init('http://avers.vpmt.ru:8081/region_pou/region.cgi/journal_och?page=1&marks=1&export=1');
		curl_setopt($grades, CURLOPT_COOKIEFILE, "");
		curl_setopt($grades, CURLOPT_SHARE, $sh);
		curl_setopt($grades, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($grades);

		// Разрыв сессии с журналом
		$logout = curl_init('http://avers.vpmt.ru:8081/region_pou/region.cgi/logout');
		curl_setopt($logout, CURLOPT_COOKIEFILE, "");
		curl_setopt($logout, CURLOPT_SHARE, $sh);
		curl_setopt($logout, CURLOPT_RETURNTRANSFER, 1);
		curl_exec($logout);

		// Парсинг экспортного XML
		// Данные хранятся в строках с тэгом Row
		// Первые 3 не содержат оценок, их пропускаем
		// Последний ряд тоже не содержит оценок, его не обрабатываем
		$doc = new DOMDocument();
		$doc->loadXML($data);
		$rows = $doc->getElementsByTagName("Row");

		$output = [['Дисциплина', 'Оценки', 'Средний балл']];
		for ($y = 4; $y < count($rows) - 2; $y++) {
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

	// Проверяет если пользователь запросил отмену. Если да - то возвращаем его в хаб
	private function checkIfCancelled($text, &$user) {
		if ($text == 'Отмена') {
			$user['state'] = STATE_HUB;
 			$this->answerToHub($user['vk_id'], $user['type'], $this->responses['returning']);
			return true;
		} else {
			return false;
		}
	}
}
