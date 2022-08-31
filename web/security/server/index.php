<?php

header("Content-Type: application/json");

$url = explode("/", $_GET["_Pagina"]);
$_GET["controller"] = isset($url[1]) ? $url[1] : null;
$_GET["function"] = isset($url[2]) ? $url[2] : null;
$_GET["param"] = isset($url[3]) ? $url[3] : null;
unset($url);

$file = "security/server/controllers/" . $_GET["controller"] . "Controller.php";

if (file_exists($file)) {
	include_once($file);

	if (class_exists($_GET["controller"])) {
		$obj = new $_GET["controller"]();
		if (method_exists($obj, $_GET["function"])) {
			$returnFunction = call_user_func([$obj, $_GET["function"]], $_GET["param"]);
		}
	} else if (function_exists($_GET["function"])) {
		$returnFunction = call_user_func($_GET["function"], $_GET["param"]);
	}
}

if (isset($returnFunction)) {
	echo json_encode(is_array($returnFunction) ? $returnFunction : ["retorno" => $returnFunction]);
} else {
	http_response_code(404);
	echo json_encode(["status" => false, "msg" => "A função solicitada não foi encontrada"]);
}
