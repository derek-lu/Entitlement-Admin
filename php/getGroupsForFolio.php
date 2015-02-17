<?php
require_once "settings.php";
require_once "utils.php";

$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
if ($mysqli->connect_errno) {
    echo '{"success":false,"description":"Sorry, unable to connect to the database."}';
} else {
	$guid = escapeURLData($_POST["guid"]);
	$productId = escapeURLData($_POST["productId"]);
	
	if ($stmt = $mysqli->prepare("SELECT group_id FROM folios_for_groups WHERE guid = ? AND product_id = ?")) {
		if ($stmt->bind_param("ss", $guid, $productId)) {
			$stmt->execute();

			$stmt->bind_result($groupId);

			$stmt->store_result();

			$rows = "";
			while($stmt->fetch()) {
				$rows[] = $groupId;
			}

			echo '{"success":true,"groups":' . json_encode($rows) . '}';
		} else {
			echo '{"success":false,"description":"getGroupsForFolio - Binding groups parameters failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
		}

		$stmt->close();
	} else {
		echo '{"success":false,"description":"getGroupsForFolio - Prepare groups failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
	}
}

?>