<?php
require_once "settings.php";
require_once "utils.php";

//ini_set('display_errors', 1);

$data = json_decode($_POST["data"]);

$productId = escapeURLData($data->productId);
$guid = escapeURLData($data->guid);
$groupIds = $data->groupIds;
$userIds = $data->userIds;
$csrfToken = escapeURLData($data->csrfToken);

$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);

if (!isValidCsrfToken($mysqli, $guid, $csrfToken)) {
	echo '{"success":false,"description":"Sorry, invalid token."}';
} else if (empty($productId) || empty($guid)) {
	echo '{"success":false,"description":"Sorry, productId and guid are required fields."}';
} else {

	if ($mysqli->connect_errno) {
	    echo '{"success":false,"description":"Sorry, unable to connect to the database."}';
	} else {
		// Remove the existing group ids.
		if ($stmt = $mysqli->prepare("DELETE FROM folios_for_groups WHERE product_id = ? AND guid = ?")) {
			if ($stmt->bind_param("ss", $productId, $guid)) {
				if (!$stmt->execute()) {
					echo '{"success":false,"description":"Sorry, unable to delete groups for folio."}';
					exit;
				}
			} else {
				echo '{"success":false,"description":"updateFolio - Binding delete parameters failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
				exit;
			}

			$stmt->close();
		} else {
	   		echo '{"success":false,"description":"updateFolio - Prepare delete failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
	   		exit;
		}

		// Remove the existing user ids.
		if ($stmt = $mysqli->prepare("DELETE FROM folios_for_users WHERE product_id = ? AND guid = ?")) {
			if ($stmt->bind_param("ss", $productId, $guid)) {
				if (!$stmt->execute()) {
					echo '{"success":false,"description":"Sorry, unable to delete groups for folio."}';
					exit;
				}
			} else {
				echo '{"success":false,"description":"updateFolio - Binding delete parameters failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
				exit;
			}

			$stmt->close();
		} else {
	   		echo '{"success":false,"description":"updateFolio - Prepare delete failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
	   		exit;
		}

		// Add the new group ids.
		if (count($groupIds) > 0) {
			$insertFolios = array();
			foreach ($groupIds as $row) {
				$insertFolios[] = '("' . $productId . '", "' . escapeURLData($row) . '", "' . $guid . '")';
			}

			$stmt = $mysqli->prepare("INSERT INTO folios_for_groups (product_id, group_id, guid) VALUES " . implode(",", $insertFolios));
			$stmt->execute();
			$stmt->close();
		}

		// Add the new user ids.
		if (count($userIds) > 0) {
			$insertFolios = array();
			foreach ($userIds as $row) {
				$insertFolios[] = '("' . $productId . '", "' . escapeURLData($row) . '", "' . $guid . '")';
			}

			$stmt = $mysqli->prepare("INSERT INTO folios_for_users (product_id, user_id, guid) VALUES " . implode(",", $insertFolios));
			$stmt->execute();
			$stmt->close();
		}

		echo '{"success":true}';
	}
}

?>