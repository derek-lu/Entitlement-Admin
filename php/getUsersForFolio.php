<?php
require_once "settings.php";
require_once "utils.php";

ini_set('display_errors', 1);

$mysqli = new mysqli($db_host, $db_user, $db_password, "entitlement_admin");

if ($mysqli->connect_errno) {
    echo '{"success":false,"description":"Sorry, unable to connect to the database."}';
} else {
	if ($stmt = $mysqli->prepare("SELECT user_id FROM folios_for_users WHERE guid = ? AND product_id = ?")) {
		$guid = escapeURLData($_POST["guid"]);
	 	$productId = escapeURLData($_POST["productId"]);

		if ($stmt->bind_param("ss", $guid, $productId)) {
			$stmt->execute();

			$stmt->bind_result($userId);

			$stmt->store_result();

			$rows = array();
			while($stmt->fetch()) {
				$rows[] = $userId;
			}

			echo '{"success":true,"users":' . json_encode($rows) . '}';
		} else {
			echo '{"success":false,"description":"getUsersForFolio - Binding parameters failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
		}

		$stmt->close();
	} else {
		echo '{"success":false,"description":"getUsersForFolio - Prepare failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
	}
}

?>