<?php
class OccupancyCacheModel extends Model {
	protected static $table_name = "occupancy_cache";

	public static function create($day, $cabinet, $photo) {
		$db = Database::getConnection();
		$stm = $db->prepare("INSERT INTO occupancy_cache (day, photo, place) VALUES(?, ?, ?)");
		$stm->bind_param('sss', $day, $photo, $cabinet);
		$stm->execute();
	}

	// Возвращает кэшированное расписание занятости кабинета
	public static function getCached($date, $cabinet) {
		$db = Database::getConnection();
		$stm = $db->prepare("SELECT photo FROM occupancy_cache WHERE day=? AND place=?");
		$stm->bind_param("si", $date, $cabinet);
		$stm->execute();
		return $stm->get_result()->fetch_array();
	}
}
