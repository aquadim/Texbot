<?php
// Класс генерации таблиц

class TableGenerator {
	protected static $filename_base = "table";
	protected $peer_id;
	protected $theme;

	public function __construct($peer_id, $theme) {
		$this->peer_id = $peer_id;

		// TODO: убрать
		$this->data = [['Тест', 'Вторая колонка']];
		$this->line_size_constraints = [5, 5];
		$this->title = ['Название таблицы'];
		$this->title_line_size = 10;
		$this->theme = json_decode($theme);
	}

	// Запускает все необходимые действия по генерации, сохранению и загрузке изображения
	// Возвращает int - photo_id загруженного изображения 
	public function run() /*: int*/ {
		$image = $this->generateImage();
		$filename = $this->saveImage($image);
		echo $filename;
		//~ return $this->uploadImage($filename);
	}

	// Генерирует изображение таблицы
	protected function generateImage() /*: GdImage*/ {
		$img = $this->makeTableImage($this->data, $this->line_size_constraints, $this->title, $this->title_line_size);
		return $img;
	}

	// Сохраняет изображение в директорию /tmp
	protected function saveImage($image) : string {
		$filename = "/tmp/".static::$filename_base.rand(1111,9999).".png";
		imagepng($image, $filename);
		return $filename;
	}

	// Загружает изображение на сервер ВКонтакте
	protected function uploadImage($filename) : int {
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
		print_r($data);
		return $data->response[0]->id;
	}

	// Создаёт поверхность таблицы из данного двумерного массива данных
	private function makeTableImage($data, $line_size_constraints, $table_title, $table_title_line_size) {
		$width = count($data[0]);
		$height = count($data);

		$row_sizes = []; // Для хранения высоты строк таблицы (некоторые яйчейки имеют несколько строк)
		$col_sizes = []; // Для хранения ширины столбцов таблицы (для полного вмещения текста)

		// Определение размеров таблицы
		for ($y = 0; $y < $height; $y++) {
			$row_height = 0;

			for ($x = 0; $x < $width; $x++) {
				if ($data[$y][$x] == null) {
					$lines = ['(н/д)'];
				} else {
					$lines = $this->splitLongString($data[$y][$x], $line_size_constraints[$x]);
				}

				// Вычисление размеров текста в яйчейке
				$celltext_width = 0;
				$celltext_height = $this->theme->line_spacing;
				foreach ($lines as $line) {
					$box = imagettfbbox(20, 0, __DIR__.'/../fonts/Lato-Regular.ttf', $line);
					$celltext_width = max($celltext_width, abs($box[0] - $box[2]) + $this->theme->padding * 2);
					$celltext_height += abs($box[5] - $box[3]) + $this->theme->line_spacing;
				}

				if (isset($col_sizes[$x])) {
					$col_sizes[$x] = max($col_sizes[$x], $celltext_width);
				} else {
					$col_sizes[$x] = $celltext_width;
				}

				$row_height = max($row_height, $celltext_height);
			}

			$row_sizes[$y] = $row_height;
		}

		// Подпись

		$table_width = $this->theme->padding * 2 + array_sum($col_sizes);
		$table_height = $this->theme->padding * 2 + array_sum($row_sizes);
		
		// Создание изображения
		$im = imagecreatetruecolor($table_width, $table_height);

		// Выделение цветов
		$bg = imagecolorallocate($im, $this->theme->background[0], $this->theme->background[1], $this->theme->background[2]);

		// Отрисовка
		imagefilledrectangle($im, 0, 0, $table_width - 1, $table_height - 1, $bg);

		return $im;
	}
	
	// Разбивает длинную строку на линии, перенося слова (слова - это участки текста, разделённые пробелами)
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
}

$t = new TableGenerator(1, '{"line_spacing": 5, "padding": 5, "background": [220,220,220]}');
$t->run();