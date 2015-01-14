<?php
require_once "settings.php";
require_once "utils.php";

//ini_set('display_errors', 1);


$guid = escapeURLData($_POST["guid"]);
$csrfToken = escapeURLData($_POST["csrfToken"]);

$mysqli = new mysqli($db_host, $db_user, $db_password, "entitlement_admin");

if (!isValidCsrfToken($mysqli, $guid, $csrfToken)) {
	echo '{"success":false,"description":"Sorry, invalid token."}'; 
	exit;
}

if ($mysqli->connect_errno) {
    echo '{"success":false,"description":"Sorry, unable to connect to the database."}';
} else {
	if ($stmt = $mysqli->prepare("SELECT * FROM app_ids WHERE guid <> ? AND app_id = ?")) {
		$appId = escapeURLData($_POST["appId"]);

		if ($stmt->bind_param("ss", $guid, $appId)) {
			$stmt->execute();
			$stmt->store_result();

			if ($stmt->num_rows > 0) { // The group name is already being used by another account.
				echo '{"success":false,"description":"Sorry, the app id you entered is being used. Please enter a different one."}';
			} else {
				if ($stmt = $mysqli->prepare("SELECT * FROM app_ids WHERE guid = ?")) {
					if ($stmt->bind_param("s", $guid)) {
						$stmt->execute();
						$stmt->store_result();

						if ($stmt->num_rows > 0) { // Check if an appId already exists for this guid.
							$stmt = $mysqli->prepare("UPDATE app_ids SET app_id = ? WHERE guid = ?");
							$stmt->bind_param("ss", $appId, $guid);
						} else {
							$stmt = $mysqli->prepare("INSERT INTO app_ids (guid, app_id) VALUES (?, ?)");
							$stmt->bind_param("ss", $guid, $appId);
						}

						if ($stmt->execute())
							echo '{"success":true}';
						else
							echo '{"success":false,"description":"Sorry, unable to set the app id."}';

					} else {
						echo '{"success":false,"description":"setAppId - Binding select app_ids parameters failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
					}
				} else {
					echo '{"success":false,"description":"setAppId - Prepare insert failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
				}
			}
		} else {
			echo '{"success":false,"description":"setAppId - Binding groups parameters failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
		}

		$stmt->close();
	} else {
		echo '{"success":false,"description":"setAppId - Prepare groups failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
	}
}

?>