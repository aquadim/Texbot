<?php
// Родительский класс View
class View {
	public function __construct($data=array()) {
		$this->assignData($data);
	}

	// Загрузка переменных из $data в $this-><переменная>
	public function assignData($data) {
		extract($data, EXTR_REFS);
		foreach ($data as $key=>$value) {
			$this->$key = $value;
		}
	}

	// Возвращает контент как HTML, используя буферизацию вывода
	public function render() {
		ob_start();
		$this->view();
		return ob_get_clean();
	}
}
