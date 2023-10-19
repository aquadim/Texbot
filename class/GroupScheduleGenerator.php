<?php
// Класс генерации расписания

class GroupScheduleGenerator extends TableGenerator {
	protected $theme = [
		"body_line_height" => 25,
		"title_line_height" => 35,

		"line_spacing"=> 5,
		"padding"=> 10,

		"colors"=> [
			"background"=> [220,220,220],
			"title_color"=> [30, 30, 30],
			"body_bg_even"=> [180, 180, 170],
			"body_bg_odd" => [210, 210, 200],
			"body_fg" => [40, 40, 40]
		],
	
		"title_font_size"=> 30,
		"body_font_size"=> 20
	];

	protected $line_size_constraints = [0, 40, 25];
	protected $title_line_size = 35;
}