<?php
// Класс генерации таблиц

class TableGenerator {
	private $filename_base = "table";
	private $font =  __DIR__.'/../fonts/Lato-Regular.ttf';
	private $peer_id;
	/* Если $peer_id == null, то фотографии загружаются как публичные и доступны всем пользователям.
	 * Иначе у загруженной фотографии будет владелец со значением $peer_id и фотографию сможет просмотреть
	 * только он (и наверное админ сообщества - не тестировал)
	 */

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

	protected $line_size_constraints = [0, 0];
	protected $title_line_size = 0;

	public function __construct($peer_id, $data, $title) {
		$this->peer_id = $peer_id;
		$this->data = $data;
		$this->title = $title;
	}

	// Запускает все необходимые действия по генерации, сохранению и загрузке изображения
	// Возвращает int - photo_id загруженного изображения 
	public function run() : string {
		$image = $this->generateImage();
		$filename = $this->saveImage($image);
		return $this->uploadImage($filename);
	}

	// Генерирует изображение таблицы
	protected function generateImage() : GdImage {
		$img = $this->makeTableImage($this->data, $this->line_size_constraints, $this->title, $this->title_line_size, $this->theme);
		return $img;
	}

	// Сохраняет изображение в директорию /tmp
	private function saveImage($image) : string {
		$filename = "/tmp/".$this->filename_base.rand(1111,9999).".png";
		imagepng($image, $filename);
		return $filename;
	}

	// Загружает изображение на сервер ВКонтакте
	private function uploadImage($filename) : string {
		// Получаем URL загрузки изображения
		$params = ['access_token'=>$_ENV['vk_token'], 'peer_id'=>$this->peer_id, 'v'=>'5.131'];
		$response = file_get_contents(vk_api_endpoint.'/photos.getMessagesUploadServer?'.http_build_query($params));
		$data = json_decode($response);
		$photoupload_url = $data->response->upload_url;

		// Выполняем загрузку
		$ch = curl_init();
		$data = array('photo'=>new CURLFile($filename));
		curl_setopt($ch, CURLOPT_URL, $photoupload_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = json_decode(curl_exec($ch));

		// Сохраняем изображение в личное сообщение
		$params = [
			'access_token'=>$_ENV['vk_token'],
			'photo'=>$response->photo,
			'server'=>$response->server,
			'hash'=>$response->hash,
			'v'=>'5.131'
		];
		$data = json_decode(file_get_contents(vk_api_endpoint.'/photos.saveMessagesPhoto?'.http_build_query($params)));
		return "photo".$data->response[0]->owner_id.'_'.$data->response[0]->id;
	}

	// Создаёт поверхность таблицы из данного двумерного массива данных
	private function makeTableImage($data, $line_size_constraints, $title, $title_line_size, $theme) {
		$width = count($data[0]);
		$height = count($data);

		$row_sizes = []; // Для хранения высоты строк таблицы (некоторые яйчейки имеют несколько строк)
		$col_sizes = []; // Для хранения ширины столбцов таблицы (для полного вмещения текста)
		$cells = []; // Для хранения яйчеек текста

		// Определение размеров тела таблицы
		// Заодно разбиваем длинный текст на строки
		for ($y = 0; $y < $height; $y++) {
			$row_height = 0;
			$cell_row = [];

			for ($x = 0; $x < $width; $x++) {
				if ($data[$y][$x] == null) {
					$lines = ['(н/д)'];
				} else {
					$lines = $this->splitLongString($data[$y][$x], $line_size_constraints[$x]);
				}

				$cell_row[] = $lines; // Сохранение для того чтобы при отрисовке текста не вызывать снова splitLongString

				// Вычисление и сохранение размеров текста в яйчейке
				list($celltext_width, $celltext_height) = $this->getTextSize($lines, $theme, $theme['body_font_size'], $theme['body_line_height']);

				// Добавление padding
				$celltext_width += $theme['padding'] * 2;
				$celltext_height += $theme['padding'] * 2;

				if (isset($col_sizes[$x])) {
					$col_sizes[$x] = max($col_sizes[$x], $celltext_width);
				} else {
					$col_sizes[$x] = $celltext_width;
				}
				$row_height = max($row_height, $celltext_height);
			}

			$row_sizes[$y] = $row_height;
			$cells[] = $cell_row;
		}

		// Вычисление ширины и высоты тела таблицы.
		$body_width = array_sum($col_sizes);
		$body_height = array_sum($row_sizes);

		// Разбиение подписи и определение размеров
		$title_lines = $this->splitLongString($title, $title_line_size);
		list($title_width, $title_height) = $this->getTextSize($title_lines, $theme, $theme['title_font_size'], $theme['title_line_height']);

		$table_width = $theme['padding'] * 2 + max($body_width, $title_width);
		$table_height = $theme['padding'] * 3 + $body_height + $title_height;

		#region Создание изображения
		// Создание изображения, загрузка цветов и шрифта
		$im = imagecreatetruecolor($table_width, $table_height);
		$colors = [];
		foreach ($theme['colors'] as $color_name => $color) {
			$colors[$color_name] = imagecolorallocate($im, $color[0], $color[1], $color[2]);
		}

		// Заполнение заднего фона
		imagefilledrectangle($im, 0, 0, $table_width - 1, $table_height - 1, $colors['background']);

		// Отрисовка названия
		$line_y = $theme['padding'] + $theme['title_line_height'];
		foreach ($title_lines as $line) {
			imagettftext($im, $theme['title_font_size'], 0, $theme['padding'], $line_y, $colors['title_color'], $this->font, $line);
			$line_y += $theme['title_line_height'] + $theme['line_spacing'];
		}

		// Отрисовка тела таблицы
		$body_y = $theme['padding'] * 2 + $title_height;
		$line_y = $body_y;
		for ($y = 0; $y < $height; $y++) {
			$this->drawTableLine(
				$im,
				$theme['padding'],
				$line_y,
				$theme['padding'] + $body_width - 1,
				$line_y + $row_sizes[$y] - 1,
				$colors,
				($y % 2 == 0),
				$data[$y]
			);

			// Содержимое яйчеек!
			$cellrow_x = $theme['padding'] * 2; // x-координата отрисовки строк текста яйчейки
			for ($x = 0; $x < $width; $x++) {
				$cellrow_y = $line_y + $theme['padding']; // y-координата отрисовки конкретной строки текста яйчейки
				foreach ($cells[$y][$x] as $celltext) {
					imagettftext($im, $theme['body_font_size'], 0, $cellrow_x, $cellrow_y + $theme['body_line_height'], $colors['body_fg'], $this->font, $celltext);
					$cellrow_y += $theme['body_line_height'] + $theme['line_spacing'];
				}
				$cellrow_x += $col_sizes[$x];// + $theme['padding'] * 2;
			}
			$line_y += $row_sizes[$y];
		}

		// Рамка
		imagerectangle($im, $theme['padding'], $body_y, $theme['padding'] + $body_width, $body_y + $body_height, $colors['title_color']);

		#endregion

		return $im;
	}
	
	// Разбивает длинную строку на линии, перенося слова (слова - это участки текста, разделённые пробелами)
	// TODO: заменить на wordwrap? а она поддерживает utf-8?
	private function splitLongString($text, $line_size) : array {

		// Не разделять слова
		if ($line_size == 0) {
			return [$text];
		}
		
		$output = [];
		$current_line = "";
		$words = explode(" ", $text);

		for ($i = 0; $i < count($words); $i++) {
			// Если строка после прибавления будет больше чем line_size, то её нужно будет перенести на новую строку
			// Если после переноса строка не вмещается в line_size, то разбить строку вручную на участки по line_size символов
			// А если строка вмещается, просто прибавить её
			$current_line_size = mb_strlen($current_line);
			if ($current_line_size + mb_strlen($words[$i]) + 1 <= $line_size) {
				$current_line .= $words[$i]." ";
			} else {
				if ($current_line_size > 0) {
					$output[] = $current_line;
				}

				if (mb_strlen($words[$i]) + 1 > $line_size) { // Строка после переноса не вмещается
					while (mb_strlen($words[$i]) > $line_size) {
						$output[] = mb_substr($words[$i], 0, $line_size);
						$words[$i] = mb_substr($words[$i], $line_size);
					}
					$current_line = $words[$i]." ";
				} else {
					$current_line = $words[$i]." ";
				}
			}
		}

		// Добавление оставшихся данных
		$output[] = $current_line;

		return $output;
	}

	// Вычисляет высоту и ширину текста, разделённого с помощью $this->splitLongString без padding
	private function getTextSize($lines, $theme, $font_size, $line_height) {
		$celltext_width = 0;
		foreach ($lines as $line) {
			$box = imagettfbbox($font_size, 0, $this->font, $line);
			$celltext_width = max($celltext_width, $box[2] - $box[0] + $theme['padding'] * 2);
		}
		return [$celltext_width, count($lines) * ($line_height + $theme['line_spacing']) - $theme['line_spacing']];
	}

	// Отрисовывает задний фон строки таблицы
	protected function drawTableLine($im, $x1, $y1, $x2, $y2, $colors, $is_even, $text_row) : void {
		if ($is_even) {
			imagefilledrectangle($im, $x1, $y1, $x2, $y2, $colors['body_bg_even']);
		} else {
			imagefilledrectangle($im, $x1, $y1, $x2, $y2, $colors['body_bg_odd']);
		}
	}
}