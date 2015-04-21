<?php
include_once 'utils.php';

class Output {
	private $type;
	private $status;
	private $messages;

	function __construct($type) {
		$this->type = $type;
		$this->status = 'ok';
		$this->messages = array();
	}

	function add_error_message($msg) {
		$this->status = 'error';
		array_push($this->messages, $msg . ': invalid');
	}

	function get_results() {
		return array(
			'type' => $this->type,
			'status' => $this->status,
			'contents' => $this->messages
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
	$output = new Output('Checking if values are set in "php/settings.php":');
	if (file_exists('settings.php')) {
		include 'settings.php';

		if (!isset($db_host))
			$output->add_error_message('$db_host is missing');
		if (!isset($db_user))
			$output->add_error_message('$db_user is missing');
		if (!isset($db_password))
			$output->add_error_message('$db_password is missing');
		if (!isset($db_name))
			$output->add_error_message('$db_name is missing');
	} else {
		$output->add_error_message('The file "php/settings.php" is missing.');
	}

	return $output->get_results();
}

function check_database_accessibility() {
	$output = new Output('Checking database accessibility:');
	if (file_exists('settings.php')) {
		include 'settings.php';

		$db_host = isset($db_host) ? $db_host : '';
		$db_user = isset($db_user) ? $db_user : '';
		$db_password = isset($db_password) ? $db_password : '';
		$db_name = isset($db_name) ? $db_name : '';
		$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
		if ($mysqli->connect_errno)
			$output->add_error_message($mysqli->connect_error);
	} else {
		$output->add_error_message('The file \'php/settings.php\' is missing.');
	}
	return $output->get_results();
}

function check_cross_domain_access() {
	$output = new Output('Checking cross domain access: ');
	if (!ini_get('allow_url_fopen'))
		$output->add_error_message('The PHP configuration for "allow_url_fopen" is disabled');
	try {
		$xml = file_get_contents('http://edge.adobe-dcfs.com/ddp/issueServer/issues?targetDimension=all&accountId');
		if (!$xml)
			$output->add_error_message('Cannot read from the fulfillment feed');
	} catch (Exception $e) {
		$output->add_error_message('Exception: ' . $e->getMessage());
	}
	return $output->get_results();
}

function check_fulfillment_url_availability() {
	$output = new Output('Checking the fulfillment server:');
	$url = 'http://edge.adobe-dcfs.com/ddp/issueServer/issues?accountId';
	$isConnected = _helper_check_connection($url);
	if (!$isConnected)
		$output->add_error_message('Unable to connect to the fulfillment server @ ' . $url);
	return $output->get_results();
}

function check_http_connectivity() {
	$output = new Output('Checking HTTP connection:');
	$url = 'http://www.adobe.com/';
	$isConnected = _helper_check_connection($url);
	if (!$isConnected)
		$output->add_error_message('Unable to connect to ' . $url);
	return $output->get_results();
}

function check_https_connectivity() {
	$output = new Output('Checking HTTPS connection:');
	$url = 'https://www.google.com/';
	$isConnected = _helper_check_connection($url);
	if (!$isConnected)
		$output->add_error_message('Unable to connect to ' . $url);
	return $output->get_results();
}

function check_php_modules() {
	$output = new Output('Checking php modules:');
	if (!extension_loaded('mysql'))
		$output->add_error_message('\'MySQL\' is not installed.');
	if (!function_exists('mysqli_connect'))
		$output->add_error_message('\'MySQLi\' extension is not installed.');
	if (!function_exists('curl_exec'))
		$output->add_error_message('\'cURL\' extension is not installed.');
	if (!function_exists('file_get_contents'))
		$output->add_error_message('\'file_get_contents\' extension is not installed.');
	return $output->get_results();
}

$option = isset($_POST['check']) ? escapeURLData($_POST['check']) : 'all';
$output = array();

switch ($option) {
	case 'php_modules':
		array_push($output, check_php_modules());
		break;
	case 'config_file':
		array_push($output, check_config_file());
		break;
	case 'database_accessibility':
		array_push($output, check_database_accessibility());
		break;
	case 'http_connectivity':
		array_push($output, check_http_connectivity());
		break;
	case 'https_connectivity':
		array_push($output, check_https_connectivity());
		break;
	case 'fulfillment_url_availability':
		array_push($output, check_fulfillment_url_availability());
		break;
	case 'cross_domain_access':
		array_push($output, check_cross_domain_access());
		break;
	case 'all':
		array_push($output, check_php_modules());
		array_push($output, check_config_file());
		array_push($output, check_database_accessibility());
		array_push($output, check_http_connectivity());
		array_push($output, check_https_connectivity());
		array_push($output, check_fulfillment_url_availability());
		array_push($output, check_cross_domain_access());
		break;
	default:
		$error = new Output('Checking post parameter:');
		$error->add_error_message('Invalid post value.');
		array_push($output, $error->get_results());
		break;
}

print_r(json_encode($output));
return;