<?php
require_once "settings.php";
require_once "utils.php";


$mysqli = new mysqli($db_host, $db_user, $db_password, "entitlement_admin");

if ($mysqli->connect_errno) {
    echo '{"success":false,"description":"Sorry, unable to connect to the database."}';
} else {
	if ($stmt = $mysqli->prepare("SELECT product_id FROM folios_for_groups WHERE guid = ? AND group_id = ?")) {
		$guid = escapeURLData($_POST["guid"]);
	 	$id = escapeURLData($_POST["id"]);

		if ($stmt->bind_param("si", $guid, $id)) {
			$stmt->execute();

			$stmt->bind_result($product_id);

			$stmt->store_result();

			$rows = array();
			while($stmt->fetch()) {
				$rows[] = $product_id;
			}

			echo '{"success":true,"folios":' . json_encode($rows) . '}';
		} else {
			echo '{"success":false,"description":"getAppId - Binding groups parameters failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
		}

		$stmt->close();
	} else {
		echo '{"success":false,"description":"getAppId - Prepare groups failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
	}
}

?>