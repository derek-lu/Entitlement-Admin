<?php
/*
 * Returns XML containing a users entitled folios.
 * Part of the required API calls for implementing entitlement.
 */
require_once "../php/settings.php";
require_once "../php/utils.php";

// ini_set('display_errors', 1);

$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);

$authToken = escapeURLData($_REQUEST["authToken"]);

// Get the group id for this authToken.
$stmt = $mysqli->prepare("SELECT id FROM users WHERE auth_token = ?");
$stmt->bind_param("s", $authToken);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($userId);
$stmt->fetch();

if ($userId) {
	// Create the XML.
	$xml = simplexml_load_string("<result/>");
	$xml->addAttribute("httpResponseCode", "200");
	$entitlements = $xml->addChild("entitlements");

	// Get the groups for this user.
	$stmt = $mysqli->prepare("SELECT group_id FROM groups_for_users WHERE user_id = ?");
	$stmt->bind_param("i", $userId);
	$stmt->execute();
	$stmt->bind_result($groupId);
	$stmt->store_result();

	if ($stmt->num_rows > 0) {
		// Construct the "in"
		$groupIds = "";
		while ($stmt->fetch()) {
			$groupIds .= "," . $groupId;
		}

		// Remove the leading comma
		$groupIds = ltrim($groupIds, ",");

		$select = "SELECT product_id FROM folios_for_groups WHERE group_id IN ($groupIds) UNION SELECT product_id FROM folios_for_users WHERE user_id = ?";
	} else {
		$select = "SELECT product_id FROM folios_for_users WHERE user_id = ?";
	}

	$stmt->close();

	// Get the entitlements for the group and user.
	$stmt = $mysqli->prepare($select);
	$stmt->bind_param("i", $userId);
	$stmt->execute();
	$stmt->bind_result($productId);
	$stmt->store_result();

	while($stmt->fetch()) {
		$entitlements->addChild("productId", $productId);
	}

	header("Content-Type: application/xml");
	echo $xml->asXML();
} else {
	returnErrorResponse();
}

?>
