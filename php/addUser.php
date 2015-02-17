<?php
require_once "settings.php";
require_once "utils.php";

$guid = escapeURLData($_POST["guid"]);
$name = escapeURLData($_POST["name"]);
$password = escapeURLData($_POST["password"]);
$csrfToken = escapeURLData($_POST["csrfToken"]);

if (empty($guid) || empty($name) || empty($password)) {
	echo '{"success":false,"description":"Sorry, guid, name and password are required fields."}';
} else {
	$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);

	if (!isValidCsrfToken($mysqli, $guid, $csrfToken)) {
		echo '{"success":false,"description":"Sorry, invalid token."}';
	} else {
		if ($mysqli->connect_errno) {
		    echo '{"success":false,"description":"Sorry, unable to connect to the database."}';
		} else {
			if ($stmt = $mysqli->prepare("SELECT name FROM users WHERE guid = ? AND name = ?")) {
				if ($stmt->bind_param("ss", $guid, $name)) {
					$stmt->execute();
					$stmt->store_result();

					if ($stmt->num_rows > 0) { // The user name is already being used.
						echo '{"success":false,"description":"User names must be unique. Please use a different name."}';
					} else {
						$salt = createSalt();
						$hash = generateHash($password, $salt);

						if ($stmt = $mysqli->prepare("INSERT INTO users (guid, name, description, password, salt) VALUES (?, ?, ?, ?, ?)")) {
							$description = escapeURLData($_POST["description"]);
							if ($stmt->bind_param("sssss", $guid, $name, $description, $hash, $salt)) {
								$stmt->execute();
								echo '{"success":true, "id":' . $stmt->insert_id . '}';
							} else {
								echo '{"success":false,"description":"addUser - Binding insert parameters failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
							}
						} else {
							echo '{"success":false,"description":"addUser - Prepare insert failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
						}
					}
				} else {
					echo '{"success":false,"description":"addUser - Binding parameters failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
				}
			} else {
		   		echo '{"success":false,"description":"addUser - Prepare failed: (' . $mysqli->errno . ')' . $mysqli->error . '"}';
			}

			$stmt->close();
		}
	}
}
?>