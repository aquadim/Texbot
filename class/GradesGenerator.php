<?php
// Класс генерации таблицы оценок

class GradesGenerator extends TableGenerator {
	protected $theme = [
		"body_line_height" => 25,
		"title_line_height" => 35,

		"line_spacing"=> 5,
		"padding"=> 10,

		"colors"=> [
			"background"=> [37, 37, 37],
			"title_color"=> [255, 255, 255],
			"body_bg_even"=> [43, 43, 43],
			"body_bg_odd" => [48, 48, 48],
			"body-fg" => [221, 221, 221]
		],
	
		"title_font_size"=> 30,
		"body_font_size"=> 20
	];

	protected $line_size_constraints = [35, 40, 0];
	protected $title_line_size = 0;
}