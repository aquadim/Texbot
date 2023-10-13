<?php
// Отчёт об ошибке

class ErrorReportView extends View {
	protected $message;
	protected $file;
	protected $line;
	protected $trace;
	protected $vid;

	// Возвращает строку для отправки уведомления в телеграм
	public function plain():string {
		$output = "<pre>Произошла ошибка в Техботе</pre>\n";
		$output .= "<b>Пользователь у которого появилась ошибка: </b> https://vk.com/id".$this->vid;
		$output .= "<b>Сообщение ошибки: </b> ".$this->message."\n";
		$output .= "<b>Файл: </b> ".$this->file."\n";
		$output .= "<b>Строка: </b> ".$this->line."\n";

		// Трассировка ошибки
		if (isset($this->trace) && count($this->trace) > 0) {
			$output .= "<b>Трассировка ошибки:</b> (чем выше тем позже)<pre>\n";
			foreach ($this->trace as $item) {
				$output .= "{$item['file']}:{$item['line']} -- функция {$item['function']}\n";
			}
			$output .= "</pre>\n";
		} else {
			$output .= "<b>Трассировка ошибки отсутствует</b>\n";
		}

		return $output;
	}
	
	public function view():void { ?>
		
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<style>body {background: #252525; font-family: sans; color: #ddd}</style>
	</head>
	<body>
		<h1>Произошла ошибка в Техботе</h1>
		<p><strong>Сообщение ошибки:</strong> <?= $this->message ?></p>
		<p><strong>Файл:</strong> <?=  $this->file ?></p>
		<p><strong>Строка:</strong> <?=  $this->line ?></p>
		<?php if (isset($this->trace) && count($this->trace) > 0) { ?>
			<p><strong>Трассировка ошибки:</strong> (чем выше тем позже)</p>
			<pre><?php foreach ($this->trace as $item) {
				echo "{$item['file']}:{$item['line']} -- функция {$item['function']}\n";
			} ?></pre>
		<?php } else {  ?>
			<p>Трассировка ошибки отсутствует</p>
		<?php } ?>
	</body>
</html>

<?php }}
