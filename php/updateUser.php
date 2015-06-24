<?php
require_once "settings.php";
require_once "utils.php";

//ini_set('display_errors', 1);

$user = json_decode(escapeURLData($_POST["user"]));

$guid = $user->guid;
$id = $user->id;
$name = $user->name;
$password = $user->password;
$description = $user->description;
$folios = $user->folios;
$groups = $user->groups;
$csrfToken = $user->csrfToken;

$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);

if (!isValidCsrfToken($mysqli, $guid, $csrfToken)) {
	echo '{"success":false,"description":"Sorry, invalid token."}';
} else if (empty($guid) || empty($id) || empty($name)) {
	echo '{"success":false,"description":"Sorry, required fields missing."}';
} else {

	if ($mysqli->connect_errno) {
	    echo '{"success":false,"description":"Sorry, unable to connect to the database."}';
	} else {
		if ($stmt = $mysqli->prepare("SELECT name FROM users WHERE guid = ? AND name = ? AND id <> ? ")) {
			if ($stmt->bind_param("ssi", $guid, $name, $id)) {
				$stmt->execute();
				$stmt->store_result();

				if ($stmt->num_rows > 0) { // The user name is already being used.
					echo '{"success":false,"description":"User names must be unique. Please use a different name."}';
				} else {
					if (!empty($password)) {
						$salt = createSalt();
						$hash = generateHash($password, $salt);

						$stmt = $mysqli->prepare("UPDATE users SET name = ?, description = ?, password = ?, salt = ? WHERE id = ? AND guid = ?");
						$stmt->bind_param("ssssis", $name, $description, $hash, $salt, $id, $guid);
					} else {
						$stmt = $mysqli->prepare("UPDATE users SET name = ?, description = ? WHERE id = ? AND guid = ?");
						$stmt->bind_param("ssis", $name, $description, $id, $guid);
					}

					if (!$stmt->execute()) {
						echo '{"success":false,"description":"Sorry, unable to update user."}';
						exit;
					}

					// Delete the existing folios from folios_for_users.
					$stmt = $mysqli->prepare("DELETE FROM folios_for_users WHERE user_id = ? AND guid = ?");
					$stmt->bind_param("is", $id, $guid);
					$stmt->execute();

					if (count($folios) > 0) {
						$insertFolios = array();
						foreach ($folios as $row) {
							$insertFolios[] = '("' . escapeURLData($row) . '", ' . $id . ', "' . $guid . '")';
						}

						$stmt = $mysqli->prepare("INSERT INTO folios_for_users (product_id, user_id, guid) VALUES " . implode(",", $insertFolios));
						$stmt->execute();
					}

					// Delete the existing groups from groups_for_users.
					$stmt = $mysqli->prepare("DELETE FROM groups_for_users WHERE user_id = ? AND guid = ?");
					$stmt->bind_param("is", $id, $guid);
					$stmt->execute();

					if (count($groups) > 0) {
						$insertGroups = array();
						foreach ($groups as $row) {
							$insertGroups[] = '("' . escapeURLData($row) . '", ' . $id . ', "' . $guid . '")';
						}

						$stmt = $mysqli->prepare("INSERT INTO groups_for_users (group_id, user_id, guid) VALUES " . implode(",", $insertGroups));
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