-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Хост: localhost:3306
-- Время создания: Ноя 28 2023 г., 22:24
-- Версия сервера: 10.6.12-MariaDB-0ubuntu0.22.04.1
-- Версия PHP: 8.1.2-1ubuntu2.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `texbot`
--

-- --------------------------------------------------------

--
-- Структура таблицы `function_names`
--

DROP TABLE IF EXISTS `function_names`;
CREATE TABLE `function_names` (
  `id` int(11) NOT NULL,
  `name` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `grades`
--

DROP TABLE IF EXISTS `grades`;
CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'ID пользователя, запросившего оценки',
  `date_create` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Дата создания записи',
  `photo` varchar(50) DEFAULT NULL COMMENT 'photo_id фотографии'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Журнал запросов оценки пользователей';

-- --------------------------------------------------------

--
-- Структура таблицы `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `course` tinyint(11) NOT NULL COMMENT 'Курс группы',
  `spec` varchar(8) NOT NULL COMMENT 'Шифр специальности группы',
  `enrolled_at` int(11) NOT NULL COMMENT 'Год поступления',
  `period_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Группы техникума';

-- --------------------------------------------------------

--
-- Структура таблицы `mails`
--

DROP TABLE IF EXISTS `mails`;
CREATE TABLE `mails` (
  `id` int(11) NOT NULL,
  `target` text DEFAULT NULL,
  `message` text DEFAULT NULL,
  `author` int(11) DEFAULT NULL,
  `date_create` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `occupancy_cache`
--

DROP TABLE IF EXISTS `occupancy_cache`;
CREATE TABLE `occupancy_cache` (
  `id` int(11) NOT NULL,
  `day` date NOT NULL COMMENT 'День занятости',
  `place` text NOT NULL COMMENT 'Собственно кабинет',
  `photo` varchar(50) NOT NULL COMMENT 'Строка photo для вложения в сообщение'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `pairs`
--

DROP TABLE IF EXISTS `pairs`;
CREATE TABLE `pairs` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL COMMENT 'К какому расписанию относится эта пара',
  `ptime` time NOT NULL COMMENT 'Время пары',
  `name` int(11) NOT NULL COMMENT 'Ссылка на название пары'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Пары расписаний';

-- --------------------------------------------------------

--
-- Структура таблицы `pair_names`
--

DROP TABLE IF EXISTS `pair_names`;
CREATE TABLE `pair_names` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL COMMENT 'Название пары'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Названия всех существующих пар';

-- --------------------------------------------------------

--
-- Структура таблицы `pair_places`
--

DROP TABLE IF EXISTS `pair_places`;
CREATE TABLE `pair_places` (
  `id` int(11) NOT NULL,
  `pair_id` int(11) NOT NULL COMMENT 'ID пары',
  `teacher_id` int(11) DEFAULT NULL COMMENT 'ID преподавателя этой пары в этом месте',
  `place` text DEFAULT NULL COMMENT 'Место проведения пары'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `period_ids`
--

DROP TABLE IF EXISTS `period_ids`;
CREATE TABLE `period_ids` (
  `id` int(11) NOT NULL,
  `num` smallint(6) NOT NULL COMMENT 'Номер семестра',
  `value` mediumint(9) NOT NULL COMMENT 'Значение period_id',
  `group_id` int(11) NOT NULL COMMENT 'Группа записи'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Значения period_id для электронного дневника';

-- --------------------------------------------------------

--
-- Структура таблицы `schedules`
--

DROP TABLE IF EXISTS `schedules`;
CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `gid` int(11) NOT NULL COMMENT 'ID группы расписания',
  `day` date NOT NULL COMMENT 'Дата расписания',
  `photo` varchar(50) DEFAULT NULL COMMENT 'Значение photo кэшированного изображения расписания'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Расписания групп';

-- --------------------------------------------------------

--
-- Структура таблицы `stats`
--

DROP TABLE IF EXISTS `stats`;
CREATE TABLE `stats` (
  `id` int(11) NOT NULL,
  `caller_gid` int(11) DEFAULT NULL COMMENT 'Какая группа вызвала эту функцию',
  `caller_teacher` int(11) DEFAULT NULL COMMENT 'Какой преподаватель вызвал эту функцию',
  `func_id` int(11) NOT NULL COMMENT 'ID функции (см. index.php)',
  `date_create` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Время вызова'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `teachers`
--

DROP TABLE IF EXISTS `teachers`;
CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `surname` text NOT NULL COMMENT 'Фамилия преподавателя'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `teacher_schedule_cache`
--

DROP TABLE IF EXISTS `teacher_schedule_cache`;
CREATE TABLE `teacher_schedule_cache` (
  `id` int(11) NOT NULL,
  `day` date NOT NULL COMMENT 'День расписания преподавателя',
  `teacher_id` int(11) NOT NULL COMMENT 'ID преподавателя',
  `photo` varchar(50) NOT NULL COMMENT 'photo для кэширования'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `vk_id` int(11) NOT NULL COMMENT 'ID пользователя во ВКонтакте',
  `state` int(11) NOT NULL DEFAULT 0 COMMENT 'Состояние пользователя (FSM)',
  `type` tinyint(11) NOT NULL DEFAULT 0 COMMENT 'Тип пользователя. 1 - студент, 2 - преподаватель, 0 - неопределено',
  `question_progress` tinyint(11) NOT NULL DEFAULT 1 COMMENT 'Прогресс вопросов регистрации',
  `allows_mail` tinyint(11) NOT NULL DEFAULT 0 COMMENT 'Разрешена ли рассылка',
  `gid` int(11) DEFAULT NULL COMMENT 'ID группы пользователя',
  `journal_login` text DEFAULT NULL COMMENT 'Логин от электронного дневника АВЕРС',
  `journal_password` text DEFAULT NULL COMMENT 'Пароль от электронного дневника',
  `checked_grades_this_iteration` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Проверены ли оценки на этой итерации?',
  `teacher_id` smallint(11) DEFAULT NULL COMMENT 'ID преподавателя для этого аккаунта'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Пользователи Техбота';

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `function_names`
--
ALTER TABLE `function_names`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `period_id` (`period_id`);

--
-- Индексы таблицы `mails`
--
ALTER TABLE `mails`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `occupancy_cache`
--
ALTER TABLE `occupancy_cache`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `pairs`
--
ALTER TABLE `pairs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `name` (`name`);

--
-- Индексы таблицы `pair_names`
--
ALTER TABLE `pair_names`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `pair_places`
--
ALTER TABLE `pair_places`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pair_id` (`pair_id`);

--
-- Индексы таблицы `period_ids`
--
ALTER TABLE `period_ids`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`);

--
-- Индексы таблицы `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gid` (`gid`);

--
-- Индексы таблицы `stats`
--
ALTER TABLE `stats`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `teacher_schedule_cache`
--
ALTER TABLE `teacher_schedule_cache`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gid` (`gid`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `occupancy_cache`
--
ALTER TABLE `occupancy_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `pairs`
--
ALTER TABLE `pairs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `pair_names`
--
ALTER TABLE `pair_names`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `pair_places`
--
ALTER TABLE `pair_places`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `period_ids`
--
ALTER TABLE `period_ids`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `stats`
--
ALTER TABLE `stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `teacher_schedule_cache`
--
ALTER TABLE `teacher_schedule_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `groups_ibfk_1` FOREIGN KEY (`period_id`) REFERENCES `period_ids` (`id`);

--
-- Ограничения внешнего ключа таблицы `pairs`
--
ALTER TABLE `pairs`
  ADD CONSTRAINT `pairs_ibfk_1` FOREIGN KEY (`name`) REFERENCES `pair_names` (`id`),
  ADD CONSTRAINT `pairs_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `pair_places`
--
ALTER TABLE `pair_places`
  ADD CONSTRAINT `pair_places_ibfk_1` FOREIGN KEY (`pair_id`) REFERENCES `pairs` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `period_ids`
--
ALTER TABLE `period_ids`
  ADD CONSTRAINT `period_ids_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`);

--
-- Ограничения внешнего ключа таблицы `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`gid`) REFERENCES `groups` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `teacher_schedule_cache`
--
ALTER TABLE `teacher_schedule_cache`
  ADD CONSTRAINT `teacher_schedule_cache_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`gid`) REFERENCES `groups` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
