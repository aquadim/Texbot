<?php
// Контроллер бота

class BotController extends Controller {

	private $responses;
	private $wait_responses;
	private $keyboards;

	public function __construct(string $request_uri) {
		parent::__construct($request_uri);

		$this->responses = array(
			"hi1"=> "Привет, я - Техбот. Моя задача - облегчить твою жизнь, но, для начала, мне нужно задать несколько вопросов",
			"hi2"=> "Ознакомься с условиями использования прежде чем использовать мои функции",
			"tos"=> "1. Я могу ошибаться, ведь я всего лишь программный код\n2. Разработчики и администрация не отвечают за возможный ущерб, причинённый ошибкой в функции, ведь они не могут знать мгновенно что произошёл сбой\n3. Использование моих функций абсолютно бесплатное и ни к чему вас не обязывает\n4. Сторонние клиенты ВКонтакте не поддерживаются",
			"question_are_you_student"=> "%d. Ты студент?",
			"question-who-are-you"=> "{0}. Выбери себя из списка",
			"question-who-are-you-no-number"=> "Выбери себя из списка",
			"question_what_is_your_course"=> "{0}. На каком курсе сейчас учишься?",
			"question_what_is_your_group"=> "{0}. Какая из этих групп твоя?",
			"question_can_send_messages"=> "{0}. Можно ли тебе присылать сообщения об обновлении бота, предупреждениях и др.?",
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
	}

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

	// Ответ на первое взаимодействие
	private function answerOnMeet($vid) {
		$this->sendMessageVk($vid, $this->responses['hi1']);
		$this->sendMessageVk($vid, $this->responses['hi2'], $this->keyboards['tos']);
		$this->answerAskIfStudent($vid, 1);
	}

	// Вопрос: Ты студент?
	private function answerAskIfStudent($vid, $progress) {
		$this->sendMessageVk($vid, sprintf($this->responses['question_are_you_student'], $progress), $this->keyboards['yn_text']);
	}

	// Обработка запроса
	public function handleRequest() {
		$data = json_decode(file_get_contents("php://input"));

		switch ($data->type) {

			// Подтверждение сервера
			case "confirmation":
				exit($_ENV['confirmation_token']);
				break;

			// Новое входящее сообщение
			case "message_new":
				$vid = $data->object->message->from_id;
				$text = $data->object->message->text;

				if (strlen($text) == 0) {
					// Нет текста в сообщении
					break;
				}

				// Получаем информацию о пользователе
				$user = UserModel::where("vid", $vid);
				if (!$user) {
					// Пользователь не зарегистрирован
					$this->answerOnMeet($vid);
					UserModel::create([
						"vk_id" => $vid,
						"state" => 0
					]);
				}

				break;

			// TODO: message_deny
			// TODO: message_allow
		}

		echo "ok";
	}
}
