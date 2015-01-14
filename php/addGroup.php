<?php
require_once "settings.php";
require_once "utils.php";

$guid = escapeURLData($_POST["guid"]);
$name = escapeURLData($_POST["name"]);
$csrfToken = escapeURLData($_POST["csrfToken"]);

if (empty($guid) || empty($name)) {
	echo '{"success":false,"description":"Sorry, guid and name are required fields."}';
} else {
	$mysqli = new mysqli($db_host, $db_user, $db_password, "entitlement_admin");

	if (!isValidCsrfToken($mysqli, $guid, $csrfToken)) {
		echo '{"success":false,"description":"Sorry, invalid token."}'; 
	} else {
		if ($mysqli->connect_errno) {
		    echo '{"success":false,"description":"Sorry, unable to connect to the database."}';
		} else {
			if ($stmt = $mysqli->prepare("SELECT name FROM groups WHERE guid = ? AND name = ?")) {
				if ($stmt->bind_param("ss", $guid, $name)) {
					$stmt->execute();
					$stmt->store_result();
					
					if ($stmt->num_rows > 0) { // The group name is already being used.
						echo '{"success":false,"description":"Group names must be unique. Please use a different name."}';
					} else {
						if ($stmt = $mysqli->prepare("INSERT INTO groups (guid, name, description) VALUES (?, ?, ?)")) {
							$description = escapeURLData($_POST["description"]);
							if ($stmt->bind_param("sss", $guid, $name, $description)) {
								$stmt->execute();
								echo '{"success":true, "id":' . $stmt->insert_id . '}';
							} else {
								echo '{"success":false,"description":"addGroup - Binding insert parameters failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
							}
						} else {
							echo '{"success":false,"description":"addGroup - Prepare insert failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
						}
					}
				} else {
					echo '{"success":false,"description":"addGroup - Binding parameters failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
				}
			} else {
		   		echo '{"success":false,"description":"addGroup - Prepare failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
			}

			$stmt->close();
		}
	}
}
?>