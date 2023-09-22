<?php
// api.php
// Файл для работы с api ВКонтакте

// Отправляет сообщение
function sendMessage($vid, $msg, $keyboard = null, $attachment = null) {
	$params = array(
		"peer_id" => $vid,
		"message" => $msg,
		"keyboard" => $keyboard,
		"attachment" => $attachment,
		"random_id" => 0,
		"access_token" => $_ENV['vk_token'],
		"v" => "5.131"
	);
	file_get_contents(vk_api_endpoint."messages.send?".http_build_query($params));
}