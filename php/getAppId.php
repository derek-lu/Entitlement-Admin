<?php
require_once "settings.php";
require_once "utils.php";

$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);

$guid = escapeURLData($_POST["guid"]);
$appId = escapeURLData($_POST["appId"]);

if ($mysqli->connect_errno) {
    echo '{"success":false,"description":"Sorry, unable to connect to the database."}';
} else {
	if ($stmt = $mysqli->prepare("SELECT app_id FROM app_ids WHERE guid = ?")) {
		if ($stmt->bind_param("s", $guid)) {
			$stmt->execute();
			$stmt->bind_result($appId);
			$stmt->fetch();

			echo '{"appId":"' . $appId . '"}';
		} else {
			echo '{"success":false,"description":"getAppId - Binding groups parameters failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
		}

		$stmt->close();
	} else {
		echo '{"success":false,"description":"getAppId - Prepare groups failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
	}
}

?>