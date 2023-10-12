SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `function_names` (
  `id` int(11) NOT NULL,
  `name` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `course` tinyint(11) NOT NULL COMMENT 'Курс группы',
  `spec` varchar(8) NOT NULL COMMENT 'Шифр специальности группы'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Группы техникума';

CREATE TABLE `mails` (
  `id` int(11) NOT NULL,
  `target` text DEFAULT NULL,
  `message` text DEFAULT NULL,
  `author` int(11) DEFAULT NULL,
  `date_create` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `occupancy_cache` (
  `id` int(11) NOT NULL,
  `day` date DEFAULT NULL,
  `place` text DEFAULT NULL,
  `photo_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `pairs` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL COMMENT 'К какому расписанию относится эта пара',
  `ptime` time NOT NULL COMMENT 'Время пары',
  `name` int(11) NOT NULL COMMENT 'Ссылка на название пары'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Пары расписаний';

CREATE TABLE `pair_names` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL COMMENT 'Название пары'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Названия всех существующих пар';

CREATE TABLE `pair_places` (
  `id` int(11) NOT NULL,
  `pair_id` int(11) NOT NULL COMMENT 'ID пары',
  `teacher_id` int(11) DEFAULT NULL COMMENT 'ID преподавателя этой пары в этом месте',
  `place` text DEFAULT NULL COMMENT 'Место проведения пары'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `gid` int(11) NOT NULL COMMENT 'ID группы расписания',
  `day` date NOT NULL COMMENT 'Дата расписания',
  `photo_id` int(11) DEFAULT NULL COMMENT 'Значение photo_id кэшированного изображения расписания'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Расписания групп';

CREATE TABLE `stats` (
  `id` int(11) NOT NULL,
  `caller_gid` int(11) DEFAULT NULL,
  `caller_teacher` int(11) DEFAULT NULL,
  `func_id` int(11) DEFAULT NULL,
  `date_create` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `surname` text NOT NULL COMMENT 'Фамилия преподавателя'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `teachers_schedule_cache` (
  `id` int(11) NOT NULL,
  `day` date DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `photo_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `vk_id` int(11) DEFAULT NULL COMMENT 'ID пользователя во ВКонтакте',
  `state` int(11) DEFAULT -1 COMMENT 'Состояние пользователя (FSM)',
  `type` tinyint(11) DEFAULT 0 COMMENT 'Тип пользователя. 1 - студент, 2 - преподаватель, 0 - неопределено',
  `question_progress` tinyint(11) DEFAULT 1 COMMENT 'Прогресс вопросов регистрации',
  `allows_mail` tinyint(11) DEFAULT 0 COMMENT 'Разрешена ли рассылка',
  `gid` int(11) DEFAULT NULL COMMENT 'ID группы пользователя',
  `journal_login` text DEFAULT NULL COMMENT 'Логин от электронного дневника АВЕРС',
  `journal_password` text DEFAULT NULL COMMENT 'Пароль от электронного дневника',
  `teacher_id` smallint(11) DEFAULT NULL COMMENT 'ID преподавателя для этого аккаунта'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Пользователи Техбота';

CREATE TABLE `users_grades` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `date_create` datetime DEFAULT NULL,
  `photo_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `function_names`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mails`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `occupancy_cache`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `pairs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `name` (`name`);

ALTER TABLE `pair_names`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `pair_places`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pair_id` (`pair_id`);

ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gid` (`gid`);

ALTER TABLE `stats`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `teachers_schedule_cache`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gid` (`gid`);

ALTER TABLE `users_grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);


ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pairs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pair_names`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pair_places`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `pairs`
  ADD CONSTRAINT `pairs_ibfk_1` FOREIGN KEY (`name`) REFERENCES `pair_names` (`id`),
  ADD CONSTRAINT `pairs_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE;

ALTER TABLE `pair_places`
  ADD CONSTRAINT `pair_places_ibfk_1` FOREIGN KEY (`pair_id`) REFERENCES `pairs` (`id`) ON DELETE CASCADE;

ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`gid`) REFERENCES `groups` (`id`) ON DELETE CASCADE;

ALTER TABLE `users_grades`
  ADD CONSTRAINT `users_grades_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
