<?php
/*
 * Renews an authToken and returns XML containing either a
 * successful or unsuccessful response code.
 * Part of the required API calls for implementing entitlement.
 */
require_once "../php/settings.php";
require_once "../php/utils.php";

//ini_set('display_errors', 1);

$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);

$authToken = escapeURLData($_REQUEST["authToken"]);

$stmt = $mysqli->prepare("SELECT id FROM users WHERE auth_token = ?");
$stmt->bind_param("s", $authToken);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) { // authToken is valid so send back the same one.
	$xml = simplexml_load_string("<result />");
	$xml->addAttribute("httpResponseCode", "200");
	$xml->addChild("authToken", $authToken);
} else {
	$xml = simplexml_load_string("<result />");
	$xml->addAttribute("httpResponseCode", "401");
}

header("Content-Type: application/xml");
echo $xml->asXML();
?>
