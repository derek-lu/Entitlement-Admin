<?php
/**
 *	Dispatcher for the following calls
 *	- /services/index.php/SignInWithCredentials
 *	- /services/index.php/entitlements
 *	- /services/index.php/RenewAuthToken
 */

require_once "../php/settings.php";
require_once "../php/utils.php";

$path_info = $_SERVER["PATH_INFO"];
$call = substr($path_info,1);

if ($call == "SignInWithCredentials" || $call == "RenewAuthToken" || $call == "entitlements") {
	$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
	switch ($call) {
		case "SignInWithCredentials":
			SignInWithCredentials($mysqli);
			break;
		case "RenewAuthToken":
			RenewAuthToken($mysqli);
			break;
		case "entitlements":
			entitlements($mysqli);
			break;
	}
} else {
	handleDefaultCall();
}

function SignInWithCredentials($mysqli) {
	$requestBody = file_get_contents('php://input');
	$xml = simplexml_load_string($requestBody);

	$emailAddress = escapeURLData($xml->emailAddress);
	$password = escapeURLData($xml->password);

	$appId = escapeURLData($_REQUEST["appId"]);
	// Make sure this app hasn't exceeded the number of requests in a 24 hour period.
	if (!$appId || doesExceedRequestMax($mysqli, $appId)) {
		returnErrorResponse();
		exit;
	}

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
}

function RenewAuthToken($mysqli) {
	$appId = escapeURLData($_REQUEST["appId"]);
	// Make sure this app hasn't exceeded the number of requests in a 24 hour period.
	if (!$appId || doesExceedRequestMax($mysqli, $appId)) {
		returnErrorResponse();
		exit;
	}

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
}

function entitlements($mysqli) {
	$appId = escapeURLData($_REQUEST["appId"]);
	// Make sure this app hasn't exceeded the number of requests in a 24 hour period.
	if (!$appId || doesExceedRequestMax($mysqli, $appId)) {
		returnErrorResponse();
		exit;
	}

	$authToken = escapeURLData($_REQUEST["authToken"]);

	// Get the group id for this authToken.
	$stmt = $mysqli->prepare("SELECT id FROM users WHERE auth_token = ?");
	$stmt->bind_param("s", $authToken);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($userId);
	$stmt->fetch();

	if ($userId) {
		// Create the XML.
		$xml = simplexml_load_string("<result/>");
		$xml->addAttribute("httpResponseCode", "200");
		$entitlements = $xml->addChild("entitlements");

		// Get the groups for this user.
		$stmt = $mysqli->prepare("SELECT group_id FROM groups_for_users WHERE user_id = ?");
		$stmt->bind_param("i", $userId);
		$stmt->execute();
		$stmt->bind_result($groupId);
		$stmt->store_result();

		if ($stmt->num_rows > 0) {
			// Construct the "in"
			$groupIds = "";
			while ($stmt->fetch()) {
				$groupIds .= "," . $groupId;
			}

			// Remove the leading comma
			$groupIds = ltrim($groupIds, ",");

			$select = "SELECT product_id FROM folios_for_groups WHERE group_id IN ($groupIds) UNION SELECT product_id FROM folios_for_users WHERE user_id = ?";
		} else {
			$select = "SELECT product_id FROM folios_for_users WHERE user_id = ?";
		}

		$stmt->close();

		// Get the entitlements for the group and user.
		$stmt = $mysqli->prepare($select);
		$stmt->bind_param("i", $userId);
		$stmt->execute();
		$stmt->bind_result($productId);
		$stmt->store_result();

		while($stmt->fetch()) {
			$entitlements->addChild("productId", $productId);
		}

		header("Content-Type: application/xml");
		echo $xml->asXML();
	} else {
		returnErrorResponse();
	}
}

function handleDefaultCall() {
	setXMLHeader();
	echo "<error>No such call</error>";
}

function setXMLHeader() {
	Header("Content-Type: application/xml; charset=utf-8");
}

function doesExceedRequestMax($mysqli, $appId) {
	return false;
}

?>
