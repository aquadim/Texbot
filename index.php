<?php
// Главный файл на который поступают запросы к Вадяботу

require_once "vendor/autoload.php";
require_once __DIR__."/class/Bot.php";
require_once __DIR__."/class/TableGenerator.php";
require_once __DIR__."/class/GroupScheduleGenerator.php";
require_once __DIR__."/class/TeacherScheduleGenerator.php";
require_once __DIR__."/class/GradesGenerator.php";
require_once __DIR__."/class/OccupancyGenerator.php";
require_once __DIR__."/class/Database.php";
require_once __DIR__."/class/GradesGetter.php";

define('vk_api_endpoint', "https://api.vk.com/method/");

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
define('STATE_ENTER_TEACHER', 10);				// Ввод преподавателя
define('STATE_REG_ENTER_SIGNATURE', 11);		// Ввод преподавателя (при регистрации преподавателя)

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
define('PAYLOAD_PROFILE_ACTION', 10);	// Действие в профиле 

// Намерения
define('INTENT_REGISTRATION', 0);		// Для регистрации
define('INTENT_STUD_RASP_VIEW', 1); 	// Просмотр расписания группы
define('INTENT_TEACHER_RASP_VIEW', 2);	// Просмотр расписания преподавателя
define('INTENT_EDIT_STUDENT', 3);		// Изменение для студента
define('INTENT_VIEW_CABINETS', 4); 		// Просмотр занятости кабинетов
define('INTENT_EDIT_TYPE', 5); 			// Изменение типа профиля

// Нумерация функций для статистики
define('FUNC_RASP', 0); // Расписание
define('FUNC_NEXT', 1); // Что дальше
define('FUNC_GRADES', 2); // Оценки
define('FUNC_BELLS', 3); // Звонки
define('FUNC_OTHER_RASP', 4); // Расписание другой группы
define('FUNC_WHERE_TEACHER', 5); // Где преподаватель
define('FUNC_VIEW_CABS', 6); // Занятость кабинетов

// Объявление общих переменных
define('GEN_MONTH_NUM_TO_STR', [9=>"сентября",10=>"октября",11=>"ноября",12=>"декабря",1=>"января",2=>"февраля",3=>"марта",4=>"апреля",5=>"мая",6=>"июня",7=>"июля",8=>"августа"]);

// Загрузка .env переменных
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Автозагрузка моделей
spl_autoload_register(function($classname) {
	if (preg_match('/Model$/', $classname)) {
		require_once __DIR__.'/models/'.$classname.'.php';
	}
});

$bot = new Bot(file_get_contents("php://input"));
$bot->handleRequest();