<?php
// Класс генерации таблицы оценок

class GradesGenerator extends TableGenerator {
	protected $theme = [
		"body_line_height" => 25,
		"title_line_height" => 35,

		"line_spacing"=> 5,
		"padding"=> 15,

		"colors"=> [
			"background"=> [37, 37, 37],
			"title_color"=> [255, 255, 255],

			"body_bg_regular_even"=> [43, 43, 43],
			"body_bg_regular_odd" => [48, 48, 48],

			"body_bg_problem_even"=> [220, 30, 30],
			"body_bg_problem_odd" => [210, 40, 40],

			"body_bg_perfect_even"=> [160, 40, 180],
			"body_bg_perfect_odd" => [150, 40, 200],

			"body_fg" => [221, 221, 221]
		],
	
		"title_font_size"=> 30,
		"body_font_size"=> 20
	];

	protected $line_size_constraints = [35, 40, 0];
	protected $title_line_size = 0;

	// Отрисовывает задний фон строки таблицы
	// Но:
	// 1. Если по предмету есть двойка, делает градиент справа красным цветов
	// 2. Если по предмету только пятёрки, делает градиент справа фиолетовым
	// 3. Если по предмету выставлена семестровая оценка, градиент слева - жёлтый
	protected function drawTableLine($im, $x1, $y1, $x2, $y2, $colors, $is_even, $text_row) : void {
		$steps = 50;
		$block_width = ($x2 - $x1) / $steps;

		// Вычисление левого и правого цвета строки
		// TODO: добавить поддержку градиента для семестровых оценок
		$left_color = $this->theme['colors']['body_bg_regular'.($is_even ? '_even' : '_odd')];

		if (str_contains($text_row[1], '2')) { // Есть двойка
			$right_color = $this->theme['colors']['body_bg_problem'.($is_even ? '_even' : '_odd')];
		} else {
			// Подсчёт количества троек, четвёрок, пятёрок
			// Т.к. count_chars возвращает массив, в котором индекс - это номер символа из таблицы
			// ASCII, то символ тройки соответствует индексу 51, 4 - это 52, 5 - это 53.
			// Двойки не подсчитываем, т.к. в этом блоке кода их не может быть
			$info = count_chars($text_row[1], 0);
			$count3 = $info[51];
			$count4 = $info[52];
			$count5 = $info[53];
			if ($count3 == 0 && $count4 == 0 && $count5 > 0) { // Идеальные оценки
				$right_color = $this->theme['colors']['body_bg_perfect'.($is_even ? '_even' : '_odd')];
			} else { // Оценки обычные
				$right_color = $this->theme['colors']['body_bg_regular'.($is_even ? '_even' : '_odd')];
			}
		}

		// Вычисляем на какое значение нужно увеличивать компоненты цвета в каждом блоке градиента
		$delta_r = ($right_color[0] - $left_color[0]) / $steps;
		$delta_g = ($right_color[1] - $left_color[1]) / $steps;
		$delta_b = ($right_color[2] - $left_color[2]) / $steps;

		$current_r = $left_color[0];
		$current_g = $left_color[1];
		$current_b = $left_color[2];

		// Отрисовка градиента
		for ($i = 0; $i < $steps; $i++) {
			$color = imagecolorallocate($im, (int)$current_r, (int)$current_g, (int)$current_b);
			imagefilledrectangle($im, $x1 + (int)(ceil($i * $block_width)), $y1, $x1 + (int)(ceil(($i + 1) * $block_width)), $y2, $color);
			$current_r += $delta_r;
			$current_g += $delta_g;
			$current_b += $delta_b;
		}
	}
}