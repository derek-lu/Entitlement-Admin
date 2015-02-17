<?php

function escapeURLData($dataToEscape){
	if(get_magic_quotes_gpc()){
		$dataToEscape = stripslashes($dataToEscape);
	}

	return $dataToEscape;
}

/*
 * Validate a username that conforms to:
 * 5-20 characters long
 * lower or uppercase characters and numbers 0-9
 * "_-@." are all acceptable
 *
 * NOTE: this is not as restrictive as the email validator
 */
function validUsername($username) {
	if (preg_match("/@/", $username)) {
		return validEMail($username);
	} else {
		return preg_match("/^[_A-z0-9-+\.]{3,64}$/", $username);
	}
}

// Creates a salt for password hashing.
function createSalt() {
	$intermediateSalt = md5(uniqid(rand(), true));
    $salt = substr($intermediateSalt, 0, 6);
    return $salt;
}

// Hashes the password with salt value.
function generateHash($password, $salt) {
    return hash("sha256", $password . $salt);
}

// Creates an authToken.
function createAuthToken($s) {
	return md5("DPS".$s);
}

function returnErrorResponse() {
	header("Content-Type: application/xml");
	$xml = simplexml_load_string("<result/>");
	// Return an error response.
	$xml->addAttribute("httpResponseCode", '401');
	echo $xml->asXML();
}

// Verifies whether or not a csrf token is valid.
function isValidCsrfToken($mysqli, $guid, $token) {
	$stmt = $mysqli->prepare("SELECT token FROM csrf_tokens WHERE guid = ? AND token = ?");
	$stmt->bind_param("ss", $guid, $token);
	$stmt->execute();
	$stmt->bind_result($token);
	$stmt->fetch();

	if ($token)
		return true;
	else
		return false;
}

?>
