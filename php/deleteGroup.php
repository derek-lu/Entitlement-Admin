<?php
require_once "settings.php";
require_once "utils.php";

$guid = escapeURLData($_POST["guid"]);
$csrfToken = escapeURLData($_POST["csrfToken"]);

$mysqli = new mysqli($db_host, $db_user, $db_password, "entitlement_admin");

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
			if ($stmt = $mysqli->prepare("DELETE FROM groups WHERE guid = ? AND id = ?")) {
				if ($stmt->bind_param("ss", $guid, $id)) {
					$stmt->execute();
					$stmt->close();
					
					if ($stmt = $mysqli->prepare("DELETE FROM folios_for_groups WHERE group_id = ? AND guid = ?")) {
						if ($stmt->bind_param("ss", $id, $guid)) {
							$stmt->execute();
							echo '{"success":true}';
						} else {
							echo '{"success":false,"description":"deleteGroup - Binding folios_for_groups parameters failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
						}
					} else {
						echo '{"success":false,"description":"deleteGroup - Prepare folios_for_groups failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
					}
				} else {
					echo '{"success":false,"description":"deleteGroup - Binding groups parameters failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
				}

				$stmt->close();
			} else {
				echo '{"success":false,"description":"deleteGroup - Prepare groups failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
			}
		}
	}
}

?>