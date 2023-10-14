<?php
// Модель записи статистики
/*
 * caller_gid - Какая группа вызвала эту функцию
 * caller_teacher - Какой преподаватель вызвал эту функцию 
 * func_id - ID функции (см. BotController) 
 * date_create - Время вызова 
 */

class StatModel extends Model {
	protected static $table_name = "stats";

	// Возвращает данные группы по курсу и специальности
	public static function create($caller, $user_type, $function_id) {
		$db = Database::getConnection();

		if ($user_type == 1) { // Вызвал студент
			$stm = $db->prepare("INSERT INTO stats (caller_gid, func_id) VALUES (?, ?)");
		} else {
			$stm = $db->prepare("INSERT INTO stats (caller_teacher, func_id) VALUES (?, ?)");
		}
		$stm->bind_param("ii", $caller, $function_id);
		$stm->execute();
	}
}
