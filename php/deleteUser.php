<?php
require_once "settings.php";
require_once "utils.php";

// ini_set('display_errors', 1);

$guid = escapeURLData($_POST["guid"]);
$csrfToken = escapeURLData($_POST["csrfToken"]);

$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);

if (!isValidCsrfToken($mysqli, $guid, $csrfToken)) {
	echo '{"success":false,"description":"Sorry, invalid token."}'; 
} else {
	$id = escapeURLData($_POST["id"]);

	if (empty($guid) || empty($id)) {
		echo '{"success":false,"description":"Sorry, guid and id are required fields."}';
	} else {
		if ($mysqli->connect_errno) {
		    echo '{"success":false,"description":"Sorry, unable to connect to the database."}';
		} else {
			if ($stmt = $mysqli->prepare("DELETE FROM users WHERE guid = ? AND id = ?")) {
				if ($stmt->bind_param("ss", $guid, $id)) {
					$stmt->execute();
					$stmt->close();

					// Delete from folios_for_users
					$stmt = $mysqli->prepare("DELETE FROM folios_for_users WHERE user_id = ? AND guid = ?");
					$stmt->bind_param("ss", $id, $guid);
					$stmt->execute();

					// Delete from groups_for_users
					$stmt = $mysqli->prepare("DELETE FROM groups_for_users WHERE user_id = ? AND guid = ?");
					$stmt->bind_param("ss", $id, $guid);
					$stmt->execute();

					echo '{"success":true}';
				} else {
					echo '{"success":false,"description":"deleteUser - Binding users parameters failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
				}

				$stmt->close();
			} else {
				echo '{"success":false,"description":"deleteUser - Prepare users failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
			}
		}
	}
}

?>