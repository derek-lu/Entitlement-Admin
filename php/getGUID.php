<?php
require_once "settings.php";
require_once "utils.php";

//ini_set('display_errors', 1);

$info    = null;
$guid    = null;
$token   = null;
$success = false;

$adobeId  = escapeURLData($_POST['adobeId']);
$password = escapeURLData($_POST['password']);

if (empty($adobeId)) $info = "You must provide an Adobe ID";
else
if (empty($password)) $info = "You must provide a password";
else
{
	$url = "https://origin.adobe-dcfs.com/ddp/issueServer/signInWithCredentials?emailAddress=".urlencode($adobeId)."&password=".urlencode($password);
	if ($ch = curl_init($url)) {
		curl_setopt($ch, CURLOPT_COOKIEJAR, '/var/tmp/cookies.txt');
		curl_setopt($ch, CURLOPT_COOKIEFILE, '/var/tmp/cookies.txt');

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		$result = curl_exec($ch);
		$status = curl_getinfo($ch);

		if ($status != FALSE) {
			$responseXML = new SimpleXMLElement($result);
			$guid_array = $responseXML->xpath("//accountId");

			if (empty($guid_array)) {
				$info = "Sorry, the Adobe ID you entered is not a valid DPS account or the password is incorrect.";
			} else {
				$guid = $guid_array[0];

				// Check to see if there is a csrf token for this guid.
				// If there is then reuse it. It is an md5 hash of adobeId and guid.
				$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);

				if ($stmt = $mysqli->prepare("SELECT token FROM csrf_tokens WHERE guid = ?")) {
					$stmt->bind_param("s", $guid);
					$stmt->execute();
					$stmt->bind_result($token);
					$stmt->fetch();

					if (!$token) {
						// create a new csrf token. This isn't stored in the session since multiple servers will be used.
						$token = md5($adobeId . $guid);

						$stmt = $mysqli->prepare("INSERT INTO csrf_tokens (guid, token) VALUES (?, ?)");
						$stmt->bind_param("ss", $guid, $token);
						$stmt->execute();
					}

					$success = true;
				} else {
					$info = "The MySQL database is missing, please set that up first.";
				}
			}
		} else {
			$info = "Failed to connect to dps app builder service";
		}
	}

	header('Access-Control-Allow-Origin: *');
	echo "{\"success\": " . var_export($success, true) . ", \"guid\": \"$guid\", \"info\": \"$info\", \"csrfToken\":\"" . $token . "\"}";
}
?>
