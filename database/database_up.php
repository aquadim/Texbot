<?php
// database.php
// Файл управления базой данных

// Создаёт таблицы в БД
function createTables() {
	$db = new SQLite3(__DIR__."/db.sqlite3");
	$db->exec("PRAGMA journal_mode=WAL");
	
	// Таблица users
	// vk_id - id ВКонтакте
	// state - состояние пользователя
	// admin - администратор ли
	// type - тип пользователя (0 - неопределено, 1 - студент, 2 - преподаватель)
	// question_progress - прогресс вопросов
	// allows_mail - разрешена ли рассылка
	// gid - id группы преподавателя/студента
	// journal_login - логин в ЭЖ
	// joirnal_password - пароль в ЭЖ
	// teacher_id - id учителя
	$db->exec(
		"CREATE TABLE users(
		id INTEGER,
		vk_id INTEGER,
		state INTEGER DEFAULT -1,
		admin INTEGER DEFAULT 0,
		type INTEGER DEFAULT 0,
		question_progress INTEGER DEFAULT 1,
		allows_mail INTEGER DEFAULT 0,
		gid INTEGER,
		journal_login TEXT,
		journal_password TEXT,
		teacher_id INTEGER,
		PRIMARY KEY('id'))"
	);

	// Таблица users_grades - оценки пользователей
	// user_id - id пользователя
	// date_create - дата создания
	// photo_id - id фотографии
	$db->exec(
		"CREATE TABLE users_grades(
		id INTEGER,
		user_id INTEGER,
		date_create DATETIME,
		photo_id INTEGER,
		PRIMARY KEY('id'),
		FOREIGN KEY('user_id') REFERENCES 'users'('id') ON DELETE CASCADE)"
	);

	// Таблица groups - группы техникума
	// course - номер курса
	// spec - специальность
	// join_year - год поступления
	$db->exec(
		"CREATE TABLE groups(
		id INTEGER,
		course INTEGER,
		spec TEXT,
		join_year INTEGER,
		PRIMARY KEY('id'))"
	);

	// Таблица schedules - расписания занятий
	// gid - id группы расписания
	// day - день расписания
	// photo_id - id фото вконтакте для этого расписания
	// can_clean - можно ли очисить
	$db->exec(
		"CREATE TABLE schedules(
		id INTEGER,
		gid INTEGER,
		day DATE,
		photo_id INTEGER DEFAULT NULL,
		can_clean INTEGER DEFAULT 0,
		PRIMARY KEY('id'))"
	);

	// Таблица pairs
	// schedule_id - id расписания для пары
	// ptime - время пары
	// sort - переменная для сортировки
	// name - название пары
	$db->exec(
		"CREATE TABLE pairs(
		id INTEGER,
		schedule_id INTEGER,
		ptime DATETIME,
		sort INTEGER,
		name TEXT,
		PRIMARY KEY('id'),
		FOREIGN KEY('schedule_id') REFERENCES 'schedules'('id') ON DELETE CASCADE)"
	);

	// Таблица pairs_places
	// pair_id - id пары
	// teacher_id - id преподавателя, ведущего пару
	// place - место пары
	$db->exec(
		"CREATE TABLE pairs_places(
		id INTEGER,
		pair_id INTEGER,
		teacher_id INTEGER,
		place TEXT,
		PRIMARY KEY('id'),
		FOREIGN KEY('pair_id') REFERENCES 'pairs'('id') ON DELETE CASCADE)"
	);

	// Таблица teachers
	// surname - фамилия
	$db->exec(
		"CREATE TABLE teachers(
		id INTEGER,
		surname TEXT,
		PRIMARY KEY('id'))"
	);

	// Таблица teachers_schedule_cache - хранение photo_id для расписаний преподавателя
	$db->exec(
		"CREATE TABLE teachers_schedule_cache(
		id INTEGER,
		day DATE,
		teacher_id INTEGER,
		photo_id INTEGER,
		PRIMARY KEY('id'),
		FOREIGN KEY('teacher_id') REFERENCES 'teachers'('id') ON DELETE CASCADE ON UPDATE CASCADE)"
	);

	// Таблица stats
	$db->exec(
		"CREATE TABLE stats(
		id INTEGER,
		caller_gid INTEGER,
		caller_teacher INTEGER,
		func_id INTEGER,
		date_create DATETIME,
		PRIMARY KEY('id'))"
	);

	// Таблица function_names
	$db->exec(
		"CREATE TABLE function_names(
		id INTEGER,
		name TEXT,
		PRIMARY KEY('id'))"
	);

	// Таблица occupancy_cache - кэширование занятости кабинетов
	// day - дата
	// place - место
	// photo_id - id фото
	$db->exec(
		"CREATE TABLE occupancy_cache(
		id INTEGER,
		day DATE,
		place TEXT,
		photo_id INTEGER,
		PRIMARY KEY('id'))"
	);

	$db->exec(
		"CREATE TABLE mails (
		id INTEGER,
		target TEXT,
		message TEXT,
		author INTEGER,
		date_create DATETIME,
		PRIMARY KEY('id'))"
	);

	// https://ru.stackoverflow.com/a/1537321/418543
	// Триггер очистки записей кэширования
	$db->exec(
		"CREATE TRIGGER schedule_cleaner AFTER DELETE ON schedules 
		BEGIN 
			DELETE FROM teachers_schedule_cache WHERE teachers_schedule_cache.day = OLD.day;
			DELETE FROM occupancy_cache WHERE occupancy_cache.day = OLD.day;
		END"
	);

	// Триггер очистки мест пар (потому что почему то связь трёх таблиц не работает)
	$db->exec(
		"CREATE TRIGGER pairs_cleaner AFTER DELETE ON pairs 
		BEGIN 
			DELETE FROM pairs_places WHERE pairs_places.pair_id = OLD.id;
		END"
	);
}

createTables();