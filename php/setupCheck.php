<?php
include_once 'utils.php';

class output {
	private $type;
	private $status;
	private $messages;

	function __construct($type) {
		$this->type = $type;
		$this->status = 'ok';
		$this->messages = null;
	}

	function add_message($msg) {
		if ($this->status === 'ok') {
			$this->status = 'error';
			$this->messages = array();
		}
		array_push($this->messages, $msg);
	}

	function get() {
		return array(
			'type' => $this->type,
			'status' => $this->status,
			'content' => $this->messages
		);
	}
}

function _helper_check_connection($url) {
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_CONNECTTIMEOUT => 5,
		CURLOPT_HEADER => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_URL => $url
	));
	$curl_response = curl_exec($curl);
	return ($curl_response) ? true : false;
}

function check_config_file() {
	$result = new output('Checking php/settings.php:');
	if (file_exists('settings.php')) {
		include 'settings.php';

		if (!isset($db_host))
			$result->add_message('Missing value: \'$db_host\'.');
		if (!isset($db_user))
			$result->add_message('Missing value: \'$db_user\'.');
		if (!isset($db_password))
			$result->add_message('Missing value: \'$db_password\'.');
		if (!isset($db_name))
			$result->add_message('Missing value: \'$db_name\'.');
	} else {
		$result->add_message('The file \'php/settings.php\' is missing.');
	}

	return $result->get();
}

function check_database_accessibility() {
	$result = new output('Checking database accessibility:');
	if (file_exists('settings.php')) {
		include 'settings.php';

		$db_host = isset($db_host) ? $db_host : '';
		$db_user = isset($db_user) ? $db_user : '';
		$db_password = isset($db_password) ? $db_password : '';
		$db_name = isset($db_name) ? $db_name : '';
		$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
		if ($mysqli->connect_errno)
			$result->add_message($mysqli->connect_error);
	} else {
		$result->add_message('The file \'php/settings.php\' is missing.');
	}
	return $result->get();
}

function check_cross_domain_access() {
	$result = new output('Checking cross domain access: ');
	if (!ini_get('allow_url_fopen'))
		$result->add_message('The PHP configuration for "allow_url_fopen" is disabled');
	try {
		$xml = file_get_contents('http://edge.adobe-dcfs.com/ddp/issueServer/issues?targetDimension=all&accountId');
		if (!$xml)
			$result->add_message('Cannot read from the Fulfillment Feed');
	} catch (Exception $e) {
		$result->add_message('File Get Error: ' . $e->getMessage());
	}
	return $result->get();
}

function check_fulfillment_url_availability() {
	$result = new output('Checking the fulfillment server:');
	$url = 'http://edge.adobe-dcfs.com/ddp/issueServer/issues?accountId';
	$isConnected = _helper_check_connection($url);
	if (!$isConnected)
		$result->add_message('Unable to connect to the fulfillment server @ ' . $url);
	return $result->get();
}

function check_http_connectivity() {
	$result = new output('Checking HTTP connection:');
	$url = 'http://www.adobe.com/';
	$isConnected = _helper_check_connection($url);
	if (!$isConnected)
		$result->add_message('Unable to connect to ' . $url);
	return $result->get();
}

function check_https_connectivity() {
	$result = new output('Checking HTTPS connection:');
	$url = 'https://www.google.com/';
	$isConnected = _helper_check_connection($url);
	if (!$isConnected)
		$result->add_message('Unable to connect to ' . $url);
	return $result->get();
}

function check_php_modules() {
	$result = new output('Checking php modules:');
	if (!extension_loaded('mysql'))
		$result->add_message('\'MySQL\' is not installed.');
	if (!function_exists('mysqli_connect'))
		$result->add_message('\'MySQLi\' extension is not installed.');
	if (!function_exists('curl_exec'))
		$result->add_message('\'cURL\' extension is not installed.');
	if (!function_exists('file_get_contents'))
		$result->add_message('\'file_get_contents\' extension is not installed.');
	return $result->get();
}

$option = isset($_POST['check']) ? escapeURLData($_POST['check']) : 'all';
$result = array();

switch ($option) {
	case 'php_modules':
		array_push($result, check_php_modules());
		break;
	case 'config_file':
		array_push($result, check_config_file());
		break;
	case 'database_accessibility':
		array_push($result, check_database_accessibility());
		break;
	case 'http_connectivity':
		array_push($result, check_http_connectivity());
		break;
	case 'https_connectivity':
		array_push($result, check_https_connectivity());
		break;
	case 'fulfillment_url_availability':
		array_push($result, check_fulfillment_url_availability());
		break;
	case 'cross_domain_access':
		array_push($result, check_cross_domain_access());
		break;
	case 'all':
		array_push($result, check_php_modules());
		array_push($result, check_config_file());
		array_push($result, check_database_accessibility());
		array_push($result, check_http_connectivity());
		array_push($result, check_https_connectivity());
		array_push($result, check_fulfillment_url_availability());
		array_push($result, check_cross_domain_access());
		break;
	default:
		$error = new output('Checking post parameter:');
		$error->add_message('Invalid post value.');
		array_push($result, $error->get());
		break;
}

print_r(json_encode($result));
return;