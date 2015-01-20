<?php
require_once "settings.php";
require_once "utils.php";

$mysqli = new mysqli($db_host, $db_user, $db_password, "entitlement_admin");
if ($mysqli->connect_errno) {
    echo '{"success":false,"description":"Sorry, unable to connect to the database."}';
} else {
	$guid = escapeURLData($_POST["guid"]);
	$id = escapeURLData($_POST["id"]);
	
	if ($stmt = $mysqli->prepare("SELECT group_id FROM groups_for_users WHERE guid = ? AND user_id = ?")) {
		if ($stmt->bind_param("ss", $guid, $id)) {
			$stmt->execute();

			$stmt->bind_result($id);

			$stmt->store_result();

			$rows = "";
			while($stmt->fetch()) {
				$rows[] = $id;
			}

			echo '{"success":true,"groups":' . json_encode($rows) . '}';
		} else {
			echo '{"success":false,"description":"getGroupsForUser - Binding groups parameters failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
		}

		$stmt->close();
	} else {
		echo '{"success":false,"description":"getGroupsForUser - Prepare groups failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
	}
}

?>