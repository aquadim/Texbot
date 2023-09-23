<?php
// Родительский класс контроллеров
class Controller {
	private string $request_uri;

	public function __construct(string $request_uri) {
		$this->request_uri = $request_uri;
	}
}
