<?php
require_once "settings.php";
require_once "utils.php";

// ini_set('display_errors', 1);

$guid = escapeURLData($_POST["guid"]);
$csrfToken = escapeURLData($_POST["csrfToken"]);

$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);

if (!isValidCsrfToken($mysqli, $guid, $csrfToken)) {
	echo '{"success":false,"description":"Sorry, invalid token."}';
	exit;
}

if ($mysqli->connect_errno) {
    echo '{"success":false,"description":"Sorry, unable to connect to the database."}';
} else {
	if ($stmt = $mysqli->prepare("SELECT guid, id, name, description FROM groups WHERE guid = ? ORDER BY name")) {
		if ($stmt->bind_param("s", $guid)) {
			$stmt->execute();

			$stmt->bind_result($guid, $id, $name, $description);

			$stmt->store_result();

			$rows = array();
			while($stmt->fetch()) {
				$group = new stdClass;
				$group->guid = $guid;
				$group->id = $id;
				$group->name = $name;
				$group->description = $description;
				$rows[] = $group;
			}

			echo '{"success":true,"groups":' . json_encode($rows) . '}';
		} else {
			echo '{"success":false,"description":"getGroups - Binding groups parameters failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
		}

		$stmt->close();
	} else {
		echo '{"success":false,"description":"getGroups - Prepare groups failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
	}
}
?>
