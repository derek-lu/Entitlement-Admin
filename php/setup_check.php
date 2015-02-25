<?php

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
			$this->messages = [];
		}
		$this->messages[] = $msg;
	}

	function get_json() {
		return [
			'type' => $this->type,
			'status' => $this->status,
			'message' => $this->messages
		];
	}
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

	return $result->get_json();
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
	return $result->get_json();
}

function check_database_initialization() {

}

function check_fullfillment_url_availability() {

}

function check_http_connectivity() {

}

function check_https_connectivity() {

}

function check_php_modules() {
	$result = new output('Checking php modules:');
	if (!extension_loaded('mysql'))
		$result->add_message('\'MySQL\' is not installed.');
	if (!function_exists('mysqli_connect'))
		$result->add_message('\'MySQLi\' extension is not installed.');
	if (!function_exists('curl_exec'))
		$result->add_message('\'cURL\' extension is not installed.');
	return $result->get_json();
}

echo '<pre>';
print_r(check_php_modules());
print_r(check_config_file());
print_r(check_database_accessibility());
// check_database_initialization();
// check_http_connectivity();
// check_https_connectivity();
// check_fullfillment_url_availability();
echo '</pre>';