<?php
require_once "settings.php";
require_once "utils.php";


$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($mysqli->connect_errno) {
    echo '{"success":false,"description":"Sorry, unable to connect to the database."}';
} else {
	if ($stmt = $mysqli->prepare("SELECT user_id FROM groups_for_users WHERE guid = ? AND group_id = ?")) {
		$guid = escapeURLData($_POST["guid"]);
	 	$id = escapeURLData($_POST["id"]);

		if ($stmt->bind_param("si", $guid, $id)) {
			$stmt->execute();

			$stmt->bind_result($userId);

			$stmt->store_result();

			$rows = array();
			while($stmt->fetch()) {
				$rows[] = $userId;
			}

			echo '{"success":true,"users":' . json_encode($rows) . '}';
		} else {
			echo '{"success":false,"description":"getUsersForGroup - Binding parameters failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
		}

		$stmt->close();
	} else {
		echo '{"success":false,"description":"getUsersForGroup - Prepare failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
	}
}

?>