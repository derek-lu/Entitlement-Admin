<?php
/*
 * Signs in a user based on emailAddress/password. An emailAddress does
 * not have to be used however it is the name of the parameter.
 * Returns XML containing either a successful or unsuccessful response code.
 * Part of the required API calls for implementing entitlement.
 */
require_once "../php/settings.php";
require_once "../php/utils.php";

//ini_set('display_errors', 1);

$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);

$requestBody = file_get_contents('php://input');
$xml = simplexml_load_string($requestBody);

$emailAddress = escapeURLData($xml->emailAddress);
$password = escapeURLData($xml->password);

$appId = escapeURLData($_REQUEST["appId"]);

// Check for a matching guid before proceeding.
$stmt = $mysqli->prepare("SELECT guid FROM app_ids WHERE app_id = ?");
$stmt->bind_param("s", $appId);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($guid);
$stmt->fetch();

if (!$guid) {
	returnErrorResponse();
	exit;
}

// Get the salt value for this guid and name.
$stmt = $mysqli->prepare("SELECT salt, password FROM users WHERE guid = ? AND name = ?");
$stmt->bind_param("ss", $guid, $emailAddress);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($salt, $hashedPassword);
$stmt->fetch();


if ($stmt->num_rows == 0) { // Invalid name so no salt.
	returnErrorResponse();
	exit;
}

if (generateHash($password, $salt) != $hashedPassword) { // password does not match the hashed password.
	returnErrorResponse();
	exit;
} else {
	// Create and insert a new authToken.
	$authToken = createAuthToken($emailAddress . $appId);

	$stmt = $mysqli->prepare("UPDATE users SET auth_token = ? WHERE guid = ? AND name = ? ");
	$stmt->bind_param("sss", $authToken, $guid, $emailAddress);
	$stmt->execute();
	
	// Output the success xml.
	header("Content-Type: application/xml");
	$xml = simplexml_load_string("<result/>");
	$xml->addAttribute("httpResponseCode", '200');
	$xml->addChild("authToken", $authToken);
	echo $xml->asXML();
}

?>
