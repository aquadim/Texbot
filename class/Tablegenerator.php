<?php
// Класс генерации таблиц

class TableGenerator {
	protected static $filename_base = "table";

	public function __construct() {
		
	}

	// Запускает все необходимые действия по генерации, сохранению и загрузке изображения
	// Возвращает int - photo_id загруженного изображения 
	public function run() : int {
		$image = $this->generateImage();
		$filename = $this->saveImage($image);
		return $this->uploadImage($filename);
	}

	// Генерирует изображение таблицы
	private function generateImage() : GdImage {
		$img = imagecreatetruecolor(300, 300);

		return $img;
	}

	// Сохраняет изображение в директорию /tmp
	private function saveImage($image) : string {
		$filename = "/tmp/".static::$filename_base.rand(1111,9999).".png";
		imagepng($image, $filename);
		return $filename;
	}

	// Загружает изображение на сервер ВКонтакте
	private function uploadImage($filename) : int {
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
}