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
	if ($stmt = $mysqli->prepare("SELECT guid, id, name, description FROM users WHERE guid = ? ORDER BY name")) {
		if ($stmt->bind_param("s", $guid)) {
			$stmt->execute();

			$stmt->bind_result($guid, $id, $name, $description);

			$stmt->store_result();

			$rows = array();
			while($stmt->fetch()) {
				$user = new stdClass;
				$user->guid = $guid;
				$user->id = $id;
				$user->name = $name;
				$user->description = $description;
				$rows[] = $user;
			}

			echo '{"success":true,"users":' . json_encode($rows) . '}';
		} else {
			echo '{"success":false,"description":"getUsers - Binding users parameters failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
		}

		$stmt->close();
	} else {
		echo '{"success":false,"description":"getUsers - Prepare users failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
	}
}
?>
