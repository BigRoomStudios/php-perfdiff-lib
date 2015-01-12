<?php

define('PACKAGE_DIR', dirname(dirname(__FILE__)));

$autoload_path = PACKAGE_DIR.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

if(!file_exists(PACKAGE_DIR.DIRECTORY_SEPARATOR.'vendor/autoload.php')) {
	die('Please run `$ composer install` prior to executing tests.');
}

require_once($autoload_path);

array_shift($argv);
$test_file = array_shift($argv);

if(empty($test_file)) {
	die('No test script specified.');
}

if(!preg_match('/\.php$/', $test_file)) {
	$test_file .= '.php';
}

if(file_exists($test_file)) {
	require_once($test_file);
} elseif(file_exists(PACKAGE_DIR.DIRECTORY_SEPARATOR.$test_file)) {
	require_once(PACKAGE_DIR.DIRECTORY_SEPARATOR.$test_file);
} elseif(file_exists(PACKAGE_DIR.DIRECTORY_SEPARATOR.'example_tests'.DIRECTORY_SEPARATOR.$test_file)) {
	require_once(PACKAGE_DIR.DIRECTORY_SEPARATOR.'example_tests'.DIRECTORY_SEPARATOR.$test_file);
} else {
	die('Unable to load Test File, either relative, absolute, or in the example_test directory.  Please make sure the file exists.');
}