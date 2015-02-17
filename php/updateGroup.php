<?php
require_once "settings.php";
require_once "utils.php";

// ini_set('display_errors', 1);

$group = json_decode($_POST["group"]);

$guid = escapeURLData($group->guid);
$id = escapeURLData($group->id);
$name = escapeURLData($group->name);
$description = escapeURLData($group->description);
$folios = $group->folios;
$users = $group->users;
$csrfToken = escapeURLData($group->csrfToken);

$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);

if (!isValidCsrfToken($mysqli, $guid, $csrfToken)) {
	echo '{"success":false,"description":"Sorry, invalid token."}'; 
} else if (empty($guid) || empty($id) || empty($name)) {
	echo '{"success":false,"description":"Sorry, required fields missing."}';
} else {

	if ($mysqli->connect_errno) {
	    echo '{"success":false,"description":"Sorry, unable to connect to the database."}';
	} else {
		if ($stmt = $mysqli->prepare("SELECT name FROM groups WHERE guid = ? AND name = ? AND id <> ? ")) {
			if ($stmt->bind_param("ssi", $guid, $name, $id)) {
				$stmt->execute();
				$stmt->store_result();
				
				if ($stmt->num_rows > 0) { // The group name is already being used.
					echo '{"success":false,"description":"Group names must be unique. Please use a different name."}';
					exit;
				} else {
					$stmt = $mysqli->prepare("UPDATE groups SET name = ?, description = ? WHERE id = ? AND guid = ?");
					$stmt->bind_param("ssis", $name, $description, $id, $guid);

					if (!$stmt->execute()) {
						echo '{"success":false,"description":"Sorry, unable to update group."}';
						exit;
					}

					// Delete the existing folios from folios_for_groups.
					$stmt = $mysqli->prepare("DELETE FROM folios_for_groups WHERE group_id = ? AND guid = ?");
					$stmt->bind_param("is", $id, $guid);
					$stmt->execute();

					if (count($folios) > 0) {
						$insertFolios = array(); 
						foreach ($folios as $row) {
							$insertFolios[] = '("' . escapeURLData($row) . '", ' . $id . ', "' . $guid . '")';
						}

						$stmt = $mysqli->prepare("INSERT INTO folios_for_groups (product_id, group_id, guid) VALUES " . implode(",", $insertFolios));
						$stmt->execute();
					}

					// Delete the existing users from groups_for_users.
					$stmt = $mysqli->prepare("DELETE FROM groups_for_users WHERE group_id = ? AND guid = ?");
					$stmt->bind_param("is", $id, $guid);
					$stmt->execute();

					if (count($users) > 0) {
						$insertUsers = array(); 
						foreach ($users as $row) {
							$insertUsers[] = '(' . escapeURLData($row) . ', ' . $id . ', "' . $guid . '")';
						}

						$stmt = $mysqli->prepare("INSERT INTO groups_for_users (user_id, group_id, guid) VALUES " . implode(",", $insertUsers));
						$stmt->execute();
					}

					echo '{"success":true}';

				}
			} else {
				echo '{"success":false,"description":"updateGroup - Binding parameters failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
			}
		} else {
	   		echo '{"success":false,"description":"updateGroup - Prepare failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
		}

		$stmt->close();
	}
}

?>