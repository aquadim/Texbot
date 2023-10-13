<?php
// Контроллер бота

// Состояния
define('STATE_REG_1', 0);		 				// Ответ студент или нет
define('STATE_SELECT_COURSE', 1);				// Выбор курса студента
define('STATE_VOID', 2);						// Нет реакции бота
define('STATE_REG_CAN_SEND', 3);				// Ответ можно ли отправлять рассылки
define('STATE_HUB', 4);							// Выбор функции
define('STATE_ENTER_LOGIN', 5);					// Ввод логина
define('STATE_ENTER_PASSWORD', 6);				// Ввод пароля
define('STATE_ENTER_LOGIN_AFTER_PROFILE', 7);	// Ввод логина, потом ставим 8
define('STATE_ENTER_PASSWORD_AFTER_PROFILE', 8);// Ввод пароля, потом показываем профиль
define('STATE_ENTER_CAB', 9);					// Ввод кабинета

// Типы payload
define('PAYLOAD_SELECT_GROUP', 0);		// Выбор группы
define('PAYLOAD_SHOW_TERMS', 1);		// Показать условия использования
define('PAYLOAD_SELECT_DATE' , 2);		// Выбор даты
define('PAYLOAD_ENTER_CREDENTIALS', 3);	// Ввод данных журнала
define('PAYLOAD_SELECT_TEACHER', 4);	// Выбор преподавателя
define('PAYLOAD_SELECT_COURSE', 5); 	// Выбор курса
define('PAYLOAD_EDIT_GROUP', 6); 		// Смена группы
define('PAYLOAD_TOGGLE_MAIL', 7); 		// Переключение разрешения рассылок
define('PAYLOAD_EDIT_TYPE', 8); 		// Смена типа аккаунта
define('PAYLOAD_UNSUBSCRIBE', 9); 		// Запрет рассылки

// Намерения
define('INTENT_REGISTRATION', 0);		// Для регистрации
define('INTENT_STUD_RASP_VIEW', 1); 	// Просмотр расписания группы
define('INTENT_TEACHER_RASP_VIEW', 2);	// Просмотр расписания преподавателя
define('INTENT_EDIT_STUDENT', 3);		// Изменение для студента
define('INTENT_VIEW_CABINETS', 4); 		// Просмотр занятости кабинетов
define('INTENT_EDIT_TYPE', 5); 			// Изменение типа профиля

class BotController extends Controller {

	private $responses; // Все сообщения бота
	private $wait_responses; // Сообщения с просьбой подождать
	private $keyboards; // Клавиатуры
	private $data; // Данные запроса от ВК
	private $vid; // ID ВК пользователя по запросу которого выполняется обработка

	public function __construct(string $request_uri) {
		parent::__construct($request_uri);

		$this->responses = array(
			"hi1"=> "Привет, я - Техбот. Моя задача - облегчить твою жизнь, но, для начала, мне нужно задать несколько вопросов",
			"hi2"=> "Ознакомься с условиями использования прежде чем использовать мои функции",
			"tos"=> "1. Я могу ошибаться, ведь я всего лишь программный код\n2. Разработчики и администрация не отвечают за возможный ущерб, причинённый ошибкой в функции, ведь они не могут знать мгновенно что произошёл сбой\n3. Использование моих функций абсолютно бесплатное и ни к чему вас не обязывает\n4. Сторонние клиенты ВКонтакте не поддерживаются",
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
			"get-next-student"=> "Остаётся {0} {1} до начала пары {2}. Начало в {4} ({3})",
			"get-next-teacher"=> "Остаётся {0} {1} до начала пары {2}. Начало в {3} с группой {4} в {5}",
			"get-next-fail"=> "Не удалось узнать какая пара будет следующей",
			"select-teacher"=> "Выбери преподавателя (стр. {0}/{1})",
			"select-course"=> "Выбери курс группы",
			"select-group"=> "Выбери специальность группы",
			"no-data"=> "(нет данных)",
			"bells-schedule"=> "Звонки в понедельник:\n1 пара: 8:00 - 9:35 (перерыв в 8:45)\n2 пара: 9:45 - 11:20 (перерыв в 10:30)\nКл час: 11:30 - 12:15\nОбед: 12:15-13:00\n3 пара: 13:00 - 14:35 (перерыв в 13:45)\n4 пара: 14:45 - 16:20 (перерыв в 15:30)\n5 пара: 16:30 - 18:05 (перерыв в 17:15).\n\nЗвонки со вторника по пятницу\n1 пара: 8:00 - 9:35 (перерыв в 8:45)\n2 пара: 9:45 - 11:20 (перерыв в 10:30)\nОбед: 11:20 - 12:20\n3 пара: 12:20 - 13:55 (перерыв в 13:05)\n4 пара: 14:05 - 15:40 (перерыв в 14:50)\n5 пара: 15:50 - 17:25 (перерыв в 16:35)\n\nЗвонки в субботу\n1 пара: 8:00 - 9:25 (перерыв в 8:40)\n2 пара: 09:35 - 11:00 (перерыв в 10:15)\n3 пара: 11:10 - 12:35 (перерыв в 11:50)\n4 пара: 12:45 - 14:10 (перерыв в 13:25)",
			"profile-identifier-student"=> "👥 Ваша группа: {0}",
			"profile-identifier-teacher"=> "👤 Ваша фамилия: {0}",
			"profile-journal-not-filled"=> "\n⚠ Вы не указывали логин и пароль от электронного журнала",
			"profile-journal-filled"=> "\n🆔 Логин, используемый для сбора ваших оценок - {0}",
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
			"no-data"=> "(Нет данных)",
			"exception"=> "Произошла ошибка! Текст ошибки: {0}. Разработчик уведомлён"
		);

		$this->wait_responses = array(
			"Подожди",
			"Подожди немного",
			"Секунду",
			"Будет сделано!",
			"Рисую картинку...",
			"Собираю данные...",
			"Запрос принят",
			"Уже работаю над этим"
		);

		$this->keyboards = array(
			"yn_text"=> '{"one_time":false,"inline":false,"buttons":[[{"color":"primary","action":{"type":"text","payload":null,"label":"Да"}},{"color":"negative","action":{"type":"text","payload":null,"label":"Нет"}}]]}',
			"cancel"=> '{"one_time":false,"inline":false,"buttons":[[{"color":"negative","action":{"type":"text","payload":null,"label":"Отмена"}}]]}',
			"to-hub"=> '{"one_time":false,"inline":false,"buttons":[[{"color":"primary","action":{"type":"text","payload":null,"label":"На главную"}}]]}',
			"course_nums"=> '{"one_time":true,"inline":false,"buttons":[[{"color":"primary","action":{"type":"text","payload":null,"label":"1"}},{"color":"primary","action":{"type":"text","payload":null,"label":"2"}}],[{"color":"primary","action":{"type":"text","payload":null,"label":"3"}},{"color":"primary","action":{"type":"text","payload":null,"label":"4"}}]]}',
			"tos"=> '{"one_time":false,"inline":true,"buttons":[[{"color":"primary","action":{"type":"text","payload":"{\"type\":1}","label":"Показать условия использования"}}]]}',
			"unsubscribe"=> '{"one_time":false,"inline":true,"buttons":[[{"color":"negative","action":{"type":"text","payload":"{\"type\":9}","label":"Запретить рассылки"}}]]}',
			"stud_hub"=> '{"one_time":false,"inline":false,"buttons":[[{"color":"primary","action":{"type":"text","payload":null,"label":"Расписание"}},{"color":"primary","action":{"type":"text","payload":null,"label":"Оценки"}},{"color":"primary","action":{"type":"text","payload":null,"label":"Что дальше?"}}],[{"color":"secondary","action":{"type":"text","payload":null,"label":"Где преподаватель?"}},{"color":"secondary","action":{"type":"text","payload":null,"label":"Расписание группы"}},{"color":"secondary","action":{"type":"text","payload":null,"label":"Звонки"}}],[{"color":"secondary","action":{"type":"text","payload":null,"label":"Профиль"}}]]}',
			"teacher_hub"=> '{"one_time":false,"inline":false,"buttons":[[{"color":"primary","action":{"type":"text","payload":null,"label":"Расписание"}},{"color":"primary","action":{"type":"text","payload":null,"label":"Кабинеты"}},{"color":"primary","action":{"type":"text","payload":null,"label":"Что дальше?"}}],[{"color":"secondary","action":{"type":"text","payload":null,"label":"Где преподаватель?"}},{"color":"secondary","action":{"type":"text","payload":null,"label":"Расписание группы"}},{"color":"secondary","action":{"type":"text","payload":null,"label":"Звонки"}}],[{"color":"secondary","action":{"type":"text","payload":null,"label":"Профиль"}}]]}',
			"enter_journal_credentials"=> '{"one_time":false,"inline":true,"buttons":[[{"color":"primary","action":{"type":"text","payload":"{\"type\":3,\"after_profile\":false}","label":"Ввести логин и пароль"}}]]}',
			"empty"=> '{"one_time":false,"inline":false,"buttons":[]}',
			"admin-hub"=> '{"one_time":false,"inline":false,"buttons":[[{"color":"primary","action":{"type":"text","payload":null,"label":"Рассылка"}},{"color":"primary","action":{"type":"text","payload":null,"label":"Статистика"}},{"color":"negative","action":{"type":"text","payload":null,"label":"Выход"}}]]}'
		);

		// Определяем данные запроса
		// TODO: добавить проверку строки secret
		$this->data = json_decode(file_get_contents("php://input"));
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
	private function sendMessageVk($vid, string $msg, string $keyboard = null, string $attachment = null) {
		$params = array(
			"peer_id" => $vid,
			"message" => $msg,
			"keyboard" => $keyboard,
			"attachment" => $attachment,
			"random_id" => 0,
			"access_token" => $_ENV['vk_token'],
			"v" => "5.131"
		);
		file_get_contents(vk_api_endpoint."messages.send?".http_build_query($params));
	}

	// Изменение сообщения
	private function editMessageVk($vid, string $msg, int $msg_id, string $keyboard = null, string $attachment = null) {
		$params = array(
			"peer_id" => $vid,
			"message" => $msg,
			"keyboard" => $keyboard,
			"attachment" => $attachment,
			"conversation_message_id" => $msg_id,
			"access_token" => $_ENV['vk_token'],
			"v" => "5.131"
		);
		file_get_contents(vk_api_endpoint."messages.edit?".http_build_query($params));
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
				$buttons[$current_row] = array();
				$added_in_row = 0;
			}
		}
		return $this->getKeyboard(true, true, $buttons);
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
	private function answerAskCourseNumber($vid, $progress) {
		$this->sendMessageVk(
			$vid,
			sprintf($this->responses['question_what_is_your_course'], $progress),
			$this->makeKeyboardSelectCourse(INTENT_REGISTRATION)
		);
	}

	//~ // Вопрос: Какая из этих групп твоя?
	//~ private function answerAskStudentGroup($vid, $progress, $course) {
		//~ group_names = database.getGroupsByCourse(course);
		//~ $this->sendMessageVk(
			//~ $vid,
			//~ $this->responses['question_what_is_your_group'].format(progress),
			//~ self.makeKeyboardSelectGroup(group_names, None, intents.registration)
		//~ );
	//~ }

	//~ // Вопрос: можно ли присылать рассылки
	//~ private function answerAskIfCanSend($vid, $progress) {
		//~ $this->sendMessageVk($vid, $this->responses['question_can_send_messages'].format(progress), $this->keyboards['yn_text']);
	//~ }

	// Неверный ввод данных
	private function answerWrongInput($vid) {
		$this->sendMessageVk($vid, $this->responses['wrong_input']);
	}

	//~ // Добро пожаловать
	//~ private function answerPostRegistration($vid, $user_type) {
		//~ if (user_type == 1) {
			//~ $this->sendMessageVk($vid, $this->responses['welcome_post_reg'], $this->keyboards['stud_hub']);
		//~ } else {
			//~ $this->sendMessageVk($vid, $this->responses['welcome_post_reg'], $this->keyboards['teacher_hub']);
		//~ }
	//~ }

	//~ private function answerSelectDate($vid, $msg_id, $target, $intent, $edit=False) {
		//~ // Отсылает сообщение с выбором даты
		//~ $keyboard = self.makeKeyboardSelectRelevantDate(intent, msg_id, target)

		//~ if not keyboard:
			//~ $this->sendMessageVk($vid, $this->responses['no_relevant_data'])
		//~ else:
			//~ if edit:
				//~ api.edit($vid, msg_id, $this->responses['pick_day'], kb=keyboard)
			//~ else:
				//~ $this->sendMessageVk($vid, $this->responses['pick_day'], kb=keyboard)

	//~ private function answerShowScheduleForGroup($vid, date, gid) {
		//~ // Показ расписания для группы
		//~ response = database.getScheduleDataForGroup(date, gid)

		//~ if not response:
			//~ $this->sendMessageVk($vid, $this->responses['no-data'])
			//~ return

		//~ # Расписание кэшировано?
		//~ if response['photo_id']:
			//~ # Прикол для Виталия :P
			//~ if $vid == 240088163:
				//~ $this->sendMessageVk($vid, self.getRandomWaitText())
			//~ $this->sendMessageVk($vid, None, None, 'photo-'+str(self.public_id)+'_'+str(response['photo_id']))
			//~ return

		//~ # Нет кэшированного изображения, делаем
		//~ msg_id = $this->sendMessageVk($vid, self.getRandomWaitText())

		//~ schedule_id = database.getScheduleId(gid, date)
		//~ pairs = database.getPairsForGroup(schedule_id)
		//~ if not pairs:
			//~ api.edit(self.$vid, self.msg_id, $this->responses['no-data'])
			//~ return
		//~ group_name = database.getGroupName(gid)

		//~ task_code = 'gsg-' + str(random.randint(0,99999))

		//~ task = graphics.GroupScheduleGenerator(
			//~ task_code,
			//~ $vid,
			//~ self.public_id,
			//~ self.themes['rasp'],
			//~ self,
			//~ 'group-schedule',
			//~ msg_id,
			//~ date,
			//~ pairs,
			//~ group_name,
			//~ schedule_id
		//~ )
		//~ self.tasks[task_code] = task
		//~ self.tasks[task_code].start()

	//~ private function answerShowScheduleForTeacher($vid, msg_id, date, teacher_id) {
		//~ // Показ расписания для преподавателя
		//~ response = database.getCachedScheduleOfTeacher(date, teacher_id)
		//~ if response:
			//~ # Есть кэшированное
			//~ $this->sendMessageVk($vid, None, None, 'photo-'+str(self.public_id)+'_'+str(response['photo_id']))
			//~ return
		//~ msg_id = $this->sendMessageVk($vid, self.getRandomWaitText())

		//~ self.tasks.append(graphics.TeacherScheduleGenerator(
			//~ $vid,
			//~ self.public_id,
			//~ self.themes['rasp'],
			//~ self,
			//~ 'teacher-schedule',
			//~ msg_id,
			//~ date,
			//~ teacher_id
		//~ ))
		//~ self.tasks[-1].start()

	//~ private function answerShowGrades($vid, user_id, msg_id, login, password) {
		//~ // Показ оценок
		//~ # Проверяем если пользователь уже получал оценки
		//~ photo_id = database.getMostRecentGradesImage(user_id)
		//~ if photo_id:
			//~ $this->sendMessageVk($vid, None, None, 'photo-'+str(self.public_id)+'_'+str(photo_id))
		//~ else:
			//~ $this->sendMessageVk($vid, self.getRandomWaitText())
			//~ # Запускаем процесс сбора оценок
			//~ self.tasks.append(graphics.GradesGenerator(
				//~ $vid,
				//~ self.public_id,
				//~ self.themes['grades'],
				//~ self,
				//~ 'grades',
				//~ msg_id,
				//~ login,
				//~ password,
				//~ $this->keyboards['enter_journal_credentials'],
				//~ user_id
			//~ ))
			//~ self.tasks[-1].start()

	//~ private function answerAskJournalLogin($vid) {
		//~ // Спрашиваем логин журнала
		//~ $this->sendMessageVk($vid, $this->responses['enter_login'], $this->keyboards['cancel'])

	//~ private function answerAskJournalPassword($vid) {
		//~ // Спрашиваем пароль журнала
		//~ $this->sendMessageVk($vid, $this->responses['enter_password'], $this->keyboards['cancel'])

	//~ private function answerDone($vid) {
		//~ // Ответ: Готово!
		//~ $this->sendMessageVk($vid, $this->responses['done'])

	//~ private function answerToHub($vid, user_type, text) {
		//~ // Возвращает пользователя в хаб
		//~ if user_type == 1:
			//~ $this->sendMessageVk($vid, text, $this->keyboards['stud_hub'])
		//~ else:
			//~ $this->sendMessageVk($vid, text, $this->keyboards['teacher_hub'])

	//~ private function answerToAdminHub($vid, text) {
		//~ // Возвращает пользователя в хаб администрации
		//~ $this->sendMessageVk($vid, text, $this->keyboards['admin-hub'])

	//~ private function answerWhatsNext($vid, target, for_teacher) {
		//~ // Отвечает какая пара следующая
		//~ if for_teacher:
			//~ response = database.getNextPairForTeacher(target)
		//~ else:
			//~ response = database.getNextPairForGroup(target)

		//~ if not response:
			//~ $this->sendMessageVk($vid, $this->responses['get-next-fail'])
			//~ return

		//~ # Оставшееся время
		//~ hours_left = response['dt'] * 24
		//~ minutes_left = (hours_left - int(hours_left)) * 60

		//~ if for_teacher == False:
			//~ $this->sendMessageVk($vid, $this->responses['get-next-student'].format(
				//~ str(round(hours_left)) + ' ' + formatHoursGen(round(hours_left)),
				//~ str(round(minutes_left)) + ' ' + formatMinutesGen(round(minutes_left)),
				//~ response['pair_name'],
				//~ response['pair_place'],
				//~ response['pair_time']
			//~ ))
		//~ else:
			//~ $this->sendMessageVk($vid, $this->responses['get-next-teacher'].format(
				//~ str(round(hours_left)) + ' ' + formatHoursGen(round(hours_left)),
				//~ str(round(minutes_left)) + ' ' + formatMinutesGen(round(minutes_left)),
				//~ response['pair_name'],
				//~ response['pair_time'],
				//~ response['pair_group'],
				//~ response['pair_place']
			//~ ))

	//~ private function answerSelectTeacher($vid, message_id, intent) {
		//~ // Отправляет сообщения с клавиатурами выбора преподавателя

		//~ # Узнаём какие вообще есть преподаватели
		//~ teachers = database.getAllTeachers()
		//~ keyboards = self.makeTeacherSelectKeyboards(teachers, intent, message_id)
		//~ amount = len(keyboards)

		//~ for index, k in enumerate(keyboards) {
			//~ $this->sendMessageVk($vid, $this->responses['select-teacher'].format(index + 1, amount), k)

	//~ private function answerUpdateHub($vid, user_type) {
		//~ // Присылает клавиатуру с меню
		//~ if user_type == 1:
			//~ $this->sendMessageVk($vid, $this->responses['updating-menu'], $this->keyboards['stud_hub'])

	// Отправляет сообщение с выбором курса
	private function answerSelectGroupCourse($vid, $msg_id, $intent, $edit) {
		$keyboard = $this->keyboardSelectCourse($msg_id, $intent);
		if ($edit) {
			$this->editMessageVk($vid, $msg_id, $this->responses['select-course'], keyboard);
		} else {
			$this->sendMessageVk($vid, $this->responses['select-course'], keyboard);
		}
	}

	// Отправляет сообщение с выбором группы
	private function answerSelectGroupSpec($msg_id, $course, $intent) {
		$groups = GroupModel::getAllByCourse($course);
		$this->editMessageVk(
			$this->vid,
			$this->responses['select-group'],
			$msg_id,
			$this->makeKeyboardSelectGroup($groups, $msg_id, $intent)
		);
	}

	//~ private function answerBells($vid) {
		//~ // Отправляет сообщение с расписанием звонков
		//~ $this->sendMessageVk($vid, $this->responses['bells-schedule'])

	//~ private function answerShowProfile($vid, msg_id, user, edit) {
		//~ // Отправляет сообщение профиля
		//~ message = ""

		//~ if user['type'] == 1:
			//~ # Студент
			//~ message += $this->responses['profile-identifier-student'].format(database.getGroupName(user['gid']))
			//~ if user['journal_login'] == None:
				//~ message += $this->responses['profile-journal-not-filled']
			//~ else:
				//~ message += $this->responses['profile-journal-filled'].format(user['journal_login'])
		//~ else:
			//~ # Преподаватель
			//~ message += $this->responses['profile-identifier-teacher'].format(database.getTeacherSurname(user['teacher_id']))

		//~ if user['allows_mail'] == 1:
			//~ message += $this->responses['profile-mail-allowed']
		//~ else:
			//~ message += $this->responses['profile-mail-not-allowed']

		//~ keyboard = self.makeProfileKeyboard(msg_id, user)

		//~ if edit:
			//~ api.edit($vid, msg_id, message, keyboard)
		//~ else:
			//~ $this->sendMessageVk($vid, message, keyboard)

	//~ private function answerAskTeacherSignature($vid, question_progress) {
		//~ // Просит преподавателя выбрать себя из списка
		//~ return $this->sendMessageVk($vid, $this->responses['question-who-are-you'].format(question_progress), $this->keyboards['empty'])

	//~ private function answerAskCabNumber($vid) {
		//~ // Просит преподавателя написать кабинет
		//~ $this->sendMessageVk($vid, $this->responses['type-cabinet'], $this->keyboards['cancel'])

	//~ private function answerShowCabinetOccupancy($vid, date, place) {
		//~ // Показ занятости кабинетов
		//~ response = database.getCachedPlaceOccupancy(date, place)
		//~ if response:
			//~ # Есть кэшированное
			//~ $this->sendMessageVk($vid, None, None, 'photo-'+str(self.public_id)+'_'+str(response['photo_id']))
			//~ return

		//~ msg_id = $this->sendMessageVk($vid, self.getRandomWaitText())
		//~ self.tasks.append(graphics.CabinetGenerator(
			//~ $vid,
			//~ self.public_id,
			//~ self.themes['rasp'],
			//~ self,
			//~ 'teacher-schedule',
			//~ msg_id,
			//~ date,
			//~ place
		//~ ))
		//~ self.tasks[-1].start()

	//~ private function answerAskTeacherWhenEditing($vid) {
		//~ // Просит преподавателя выбрать себя когда он переходит из студента
		//~ return $this->sendMessageVk($vid, $this->responses['question-who-are-you-no-number'])

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

	//~ private function answerShowStats($vid, file_id) {
		//~ // Отправляет файл со статистикой
		//~ $this->sendMessageVk($vid, $this->responses['stats'], None, 'doc'+str($vid)+'_'+str(file_id))

	#endregion

	# region Обработка ошибок
	// Функция обработки ошибок
	public function mailErrorReport($message, $file, $line, $trace) {
		$view = new ErrorReportView([
			"message" => $message,
			"file" => $file,
			"line" => $line,
			"trace" => $trace,
			"vid" => $this->vid
		]);

		if ($_ENV["notifications_type"] == "email") {
			// email
			$report = $view->render();
			
			$headers = "MIME-Version: 1.0\n";
			$headers .= "From: Техбот <{$_ENV['notifier_email']}>\n";
			$headers .= "Content-type: text/html; charset=utf-8\n";
		
			mail($_ENV["webmaster_email"], "Ошибка в Техботе", $report, $headers);

		} else if ($_ENV["notifications_type"] == "telegram") {
			// telegram
			$report = urlencode($view->plain());
			file_get_contents("https://api.telegram.org/bot{$_ENV['notifier_bot_token']}/sendMessage?chat_id={$_ENV['notifier_bot_chat']}&text=$report&parse_mode=html");
		}
		exit("ok");
	}

	// Callback-функция для set_exception_handler
	public function reportException(Throwable $e) {
		$this->mailErrorReport($e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace());
	}

	// Callback-функция для set_error_handler
	private function reportError(int $errno, string $errstr, string $errfile, int $errline) {
		$this->mailErrorReport($errstr, $errfile, $errline, null);
	}
	#endregion

	// Обработка обычного сообщения. Возвращает true, если необходимо обновить профиль пользователя
	private function handlePlainMessage($text, &$user): bool {
		$vid = $user['vk_id'];
		
		//~ if ($user['state'] == STATE_HUB) {;
			//~ if ($text == 'Расписание') {;
				//~ if ($user['type'] == 1) {;
					//~ $this->answerSelectDate($vid, $msg_id + 1, $user['gid'], intents.stud_rasp_view);;
				//~ } else {;
					//~ $this->answerSelectDate($vid, $msg_id + 1, $user['teacher_id'], intents.teacher_rasp_view);;
				//~ };
				//~ database.addStatRecord($user['gid'], $user['type'], 1);;
			//~ if ($text == 'Оценки' and $user['type'] == 1) {;
				//~ $this->answerShowGrades($vid, $user['id'], $msg_id + 1, $user['journal_login'], $user['journal_password']);
				//~ database.addStatRecord($user['gid'], $user['type'], 2);
			//~ if ($text == 'Кабинеты' and $user['type'] == 2) {;
				//~ $user['state'] = States.enter_cab;
				//~ $this->answerAskCabNumber($vid);
				//~ database.addStatRecord($user['gid'], $user['type'], 7);
				//~ return true;
			//~ if ($text == 'Что дальше?') {;
				//~ if ($user['type'] == 1) {;
					//~ $this->answerWhatsNext($vid, $user['gid'], false);
				//~ else { {;
					//~ $this->answerWhatsNext($vid, $user['teacher_id'], true);
				//~ database.addStatRecord($user['gid'], $user['type'], 3);
			//~ if ($text == 'Где преподаватель?') {;
				//~ $this->answerSelectTeacher($vid, $msg_id + 1, intents.teacher_rasp_view);
				//~ database.addStatRecord($user['gid'], $user['type'], 4);
			//~ if ($text == 'Расписание группы') {;
				//~ $this->answerSelectGroupCourse($vid, $msg_id + 1, intents.stud_rasp_view, false);
				//~ database.addStatRecord($user['gid'], $user['type'], 5);
			//~ if ($text == 'Звонки') {;
				//~ $this->answerBells($vid);
				//~ database.addStatRecord($user['gid'], $user['type'], 6);
			//~ if ($text == 'Профиль') {;
				//~ $this->answerShowProfile($vid, $msg_id + 1, $user, false);
			//~ if ($text == '.') {;
				//~ $this->answerUpdateHub($vid, $user['type']);
			//~ if ($text == 'admin' and $user['admin']) {;
				//~ // "Оно находится прямо рядом с тобой и ты его даже не замечаешь" - Майк, из сериала "Очень странные дела";
				//~ $user['state'] = States.admin;
				//~ $this->answerShowAdminPanel($vid);
				//~ return true;
			//~ return false;
		//~ if ($user['state'] == STATE_VOID) {;
			//~ // Заглушка;
			//~ return false;

		switch ($user['state']) {
			case STATE_REG_1: // После "Ты студент?
				if ($text == 'Да') {
					// Пользователь - студент
					$user['type'] = 1;
					$user['question_progress'] += 1;
					$user['state'] = STATE_SELECT_COURSE;
					$this->answerAskCourseNumber($vid, $user['question_progress']);
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

			default:
				return false;
		}
		
		//~ if ($user['state'] == STATE_SELECT_COURSE) {
			//~ // После "На каком ты курсе?" при регистрации;
			//~ if (!(is_numeric($text) && 1 <= intval($text) && intval($text) < 5)) {
				//~ $this->answerWrongInput($vid);
				//~ return false;
			//~ }
			//~ $user['state'] = STATE_VOID;
			//~ $user['question_progress'] += 1;
			//~ $this->answerAskStudentGroup($vid, $user['question_progress'], $text);
			//~ return true;
		//~ }
		
		//~ if ($user['state'] == States.reg_can_send) {;
			//~ // После "Можно ли отправлять сообщения?" при регистрации;
			//~ if ($text == 'Да') {;
				//~ $user['allows_mail'] = 1;
			//~ else if ($text == 'Нет') {;
				//~ $user['allows_mail'] = 0;
			//~ else { {;
				//~ $this->answerWrongInput($vid);
				//~ return false;
			//~ $user['state'] = States.hub;
			//~ $this->answerPostRegistration($vid, $user['type']);
			//~ return true;
		//~ if ($user['state'] == States.enter_login or $user['state'] == States.enter_login_after_profile) {;
			//~ // Ввод логина;
			//~ if ($this->checkIfCancelled($text, $user)) {;
				//~ return true;
			//~ $user['journal_login'] = $text;
			//~ if ($user['state'] == States.enter_login) {;
				//~ $user['state'] = States.enter_password;
			//~ else { {;
				//~ $user['state'] = States.enter_password_after_profile;
			//~ $this->answerAskJournalPassword($vid);
			//~ return true;

		//~ if ($user['state'] == States.enter_password or $user['state'] == States.enter_password_after_profile) {;
			//~ // Ввод пароля;
			//~ if ($this->checkIfCancelled($text, $user)) {;
				//~ return true;
			//~ $user['journal_password'] = hashlib.sha1(bytes($text, "utf-8")).hexdigest();

			//~ $this->answerDone($vid);
			//~ $this->answerToHub($vid, $user['type'], $this->answers['returning']);
			//~ if ($user['state'] == States.enter_password_after_profile) {;
				//~ $this->answerShowProfile($vid, $msg_id + 1, $user, false);

			//~ $user['state'] = States.hub;
			//~ return true;

		//~ if ($user['state'] == States.enter_cab) {;
			//~ // Ввод кабинета;
			//~ if ($this->checkIfCancelled($text, $user)) {;
				//~ return true;
			//~ $user['state'] = States.hub;
			//~ $this->answerToHub($vid, $user['type'], $this->answers['returning']);
			//~ $this->answerSelectDate($vid, $msg_id + 1, $text, intents.view_cabinets);
			//~ return true;

		//~ if ($user['state'] == States.admin) {;
			//~ if ($text == 'Выход') {;
				//~ $user['state'] = States.hub;
				//~ $this->answerToHub($vid, $user['type'], $this->answers['returning']);
				//~ return true;

			//~ if ($text == 'Рассылка') {;
				//~ $user['state'] = States.mail_input_target;
				//~ database.addMailRecord($user['id']);
				//~ $this->answerAskMailTarget($vid);
				//~ return true;

			//~ if ($text == 'Статистика') {;
				//~ // Генерируем HTML;
				//~ path = $this->generateHtmlStats();
				//~ // Загружаем документ;
				//~ doc_id = api.uploadDocument($vid, path);
				//~ $this->answerShowStats($vid, doc_id);
		//~ if ($user['state'] == States.mail_input_target) {;
			//~ mail_id = database.getMostRecentMailRecord($user['id']);
			//~ if ($text == 'Отмена') {;
				//~ $user['state'] = States.admin;
				//~ $this->answerToAdminHub($vid, $this->answers['returning']);
				//~ database.deleteMail(mail_id);
				//~ return true;
			//~ $user['state'] = States.mail_input_message;
			//~ database.updateMail(mail_id, 'target', $text);
			//~ $this->answerAskMailMessage($vid);
			//~ return true;
		//~ if ($user['state'] == States.mail_input_message) {;
			//~ mail_id = database.getMostRecentMailRecord($user['id']);
			//~ $user['state'] = States.admin;
			//~ if ($text == 'Отмена') {;
				//~ database.deleteMail(mail_id);
				//~ $this->answerToAdminHub($vid, $this->answers['returning']);
			//~ else { {;
				//~ database.updateMail(mail_id, 'message', $text);
				//~ api.tgAlert(;
					//~ 'Автор рассылки) { https) {//vk.com/id'+str($user['vk_id'])+'. Текст) { '+$text,;
					//~ 'Создана рассылка в техботе';
				//~ );
				//~ mail_info = database.getMailInfo(mail_id);
				//~ mail_$users = database.getUsersByMask(mail_info['target']);
				//~ api.massSend(;
					//~ mail_$users,;
					//~ mail_info['message'],;
					//~ $this->keyboards['unsubscribe'];
				//~ );
				//~ $this->answerToAdminHub($vid, $this->answers['mail-saved'].format(len(mail_$users)));
			//~ return true
	}

	private function handleCallbackMessage($payload, $msg_id, &$user) : bool {
		switch ($payload->type) {
			case PAYLOAD_SELECT_COURSE: // Выбран курс. Намерение передаётся дальше
				$this->answerSelectGroupSpec($msg_id, $payload->num, $payload->intent);
				return true;

			default:
				return false;
		}
		/*
		if data['type'] == PayloadTypes.select_date:
			# Выбрана дата.. но для чего?
			if data['intent'] == intents.stud_rasp_view:
				# Просмотр расписания группы
				self.answerShowScheduleForGroup(vid, data['date'], data['target'])
				return False
			if data['intent'] == intents.teacher_rasp_view:
				# Просмотр расписания преподавателя
				self.answerShowScheduleForTeacher(vid, data['msg_id'], data['date'], data['target'])
				return False
			if data['intent'] == intents.view_cabinets:
				# Просмотр занятости кабинетов
				self.answerShowCabinetOccupancy(vid, data['date'], data['target'])
				return False

		if data['type'] == PayloadTypes.select_course:
			# Выбран курс. intent передаётся дальше
			self.answerSelectGroupSpec(vid, data['msg_id'], data['num'], data['intent'])

		if data['type'] == PayloadTypes.show_terms:
			# Показ условий использования
			self.answerShowTerms(vid)
			return False

		if data['type'] == PayloadTypes.select_group:
			# Выбрана группа.. но для чего?
			if data['intent'] == intents.registration:
				user['gid'] = data['gid']
				user['question_progress'] += 1
				user['state'] = States.reg_can_send
				self.answerAskIfCanSend(vid, user['question_progress'])
				return True
			if data['intent'] == intents.stud_rasp_view:
				self.answerSelectDate(vid, data['msg_id'], data['gid'], intents.stud_rasp_view, True)
				return False
			if data['intent'] == intents.edit_student:
				user['gid'] = data['gid']
				self.answerShowProfile(vid, data['msg_id'], user, True)
				return True
			if data['intent'] == intents.edit_type:
				# Преподаватель становится студентом
				user['type'] = 1
				user['teacher_id'] = None
				user['gid'] = data['gid']
				user['state'] = States.hub
				self.answerToHub(vid, 1, self.answers['welcome_post_reg'])
				return True

		if data['type'] == PayloadTypes.enter_credentials:
			# Переводим пользователя на ввод логина и пароля дневника
			if data['after_profile'] == False:
				user['state'] = States.enter_login
			else:
				user['state'] = States.enter_login_after_profile
			self.answerAskJournalLogin(vid)
			return True

		if data['type'] == PayloadTypes.select_teacher:
			# Удаляем прошлые сообщения
			to_delete = ''
			for i in range(data['msg_id'], data['msg_id'] + data['amount']):
				to_delete += str(i) + ','
			api.delete(to_delete)

			# Выбран преподаватель... но для чего?
			if data['intent'] == intents.teacher_rasp_view:
				# Просмотр расписания преподавателя
				self.answerSelectDate(vid, None, data['teacher_id'], intents.teacher_rasp_view, False)
				return False

			if data['intent'] == intents.registration:
				# Преподаватель регистрируется
				user['teacher_id'] = data['teacher_id']
				user['question_progress'] += 1
				user['state'] = States.reg_can_send
				self.answerAskIfCanSend(vid, user['question_progress'])
				return True

			if data['intent'] == intents.edit_type:
				# Студент становится преподавателем
				api.delete(data['msg_id'])
				user['gid'] = None
				user['teacher_id'] = data['teacher_id']
				user['state'] = States.hub
				user['type'] = 2
				self.answerToHub(vid, 2, self.answers['welcome_post_reg'])
				return True

		if data['type'] == PayloadTypes.edit_group:
			# Изменение группы, привязанной к пользователю
			if data['intent'] == intents.edit_student:
				self.answerSelectGroupCourse(vid, data['msg_id'], intents.edit_student, True)
				return False

		if data['type'] == PayloadTypes.toggle_mail:
			# Переключение разрешения рассылки
			if user['allows_mail'] == 1:
				user['allows_mail'] = 0
			else:
				user['allows_mail'] = 1
			self.answerShowProfile(vid, data['msg_id'], user, True)
			return True

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
				self.answerSelectGroupCourse(vid, msg_id + 1, intents.edit_type, False)
			return True

		if data['type'] == PayloadTypes.unsubscribe:
			user['allows_mail'] = 0
			self.answerMailDisabled(vid)
			return True
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
				exit("ok");
			}
		}

		switch ($this->data->type) {

			// Подтверждение сервера
			case "confirmation":
				exit($_ENV['confirmation_token']);
				break;

			case "message_new":
				print_r($this->data);
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
			
			// TODO: message_deny
			// TODO: message_allow

			default:
				exit("unknown event");
		}

		exit("ok");
	}
}
